<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once "../../ProhibirAcceso.php";

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materiales_seleccionados = $_POST['material_id'] ?? [];
    $cantidades_prestamo = $_POST['cantidad_prestamo'] ?? [];
    $instructor_id = filter_input(INPUT_POST, 'instructor', FILTER_VALIDATE_INT); 
    $id_responsable = filter_input(INPUT_POST, 'id_responsable', FILTER_VALIDATE_INT);
    $rol_responsable = filter_input(INPUT_POST, 'rol_responsable', FILTER_SANITIZE_STRING);
    $responsable =  filter_input(INPUT_POST, 'responsable', FILTER_SANITIZE_STRING);

    $response = ['success' => false, 'message' => ''];

    if (empty($materiales_seleccionados) || empty($cantidades_prestamo) || !$instructor_id || !$id_responsable || empty($rol_responsable)) {
        $response['message'] = "❌ Error: Datos de préstamo incompletos. Asegúrate de seleccionar materiales, cantidades y un instructor.";
        echo json_encode($response);
        exit();
    }

    try {
        if (!$conexion) {
            throw new Exception("No se pudo establecer la conexión a la base de datos.");
        }

        $conexion->begin_transaction();

        $hay_material_no_consumible = false;

        foreach ($materiales_seleccionados as $material_id_str) {
            $material_id = intval($material_id_str);
            $cantidad = intval($cantidades_prestamo[$material_id]);

            if ($cantidad <= 0) {
                throw new Exception("La cantidad para el material ID $material_id debe ser mayor que cero.");
            }

            $sql_verificar_material = "SELECT nombre, stock, tipo, estado_material FROM materiales WHERE id_material = ?";
            $stmt_verificar_material = $conexion->prepare($sql_verificar_material);
            $stmt_verificar_material->bind_param("i", $material_id);
            $stmt_verificar_material->execute();
            $material_data = $stmt_verificar_material->get_result()->fetch_assoc();
            $stmt_verificar_material->close();

            if (!$material_data) throw new Exception("Material con ID $material_id no encontrado.");
            if ($material_data['estado_material'] !== 'disponible') throw new Exception("El material '{$material_data['nombre']}' no está disponible para préstamo (estado: {$material_data['estado_material']}).");
            if ($material_data['stock'] < $cantidad) throw new Exception("Stock insuficiente para el material '{$material_data['nombre']}'.");

            $sql_actualizar_stock = "UPDATE materiales SET stock = stock - ? WHERE id_material = ?";
            $stmt_actualizar_stock = $conexion->prepare($sql_actualizar_stock);
            $stmt_actualizar_stock->bind_param("ii", $cantidad, $material_id);
            $stmt_actualizar_stock->execute();
            $stmt_actualizar_stock->close();

            date_default_timezone_set('America/Bogota');
            $fecha_prestamo = date('Y-m-d h:i:s A');
            $fecha_limite_devolucion = null;
            $estado_prestamo = '';

            if ($material_data['tipo'] === 'consumible') {
                $estado_prestamo = 'consumido'; // Se marca como devuelto directamente
            } else { // 'no consumible'
                $estado_prestamo = 'pendiente';
                $fecha_limite_devolucion = date('Y-m-d h:i:s A', strtotime('+6 hours'));
                $hay_material_no_consumible = true;
            }

            $sql_insertar_prestamo = "INSERT INTO prestamo_materiales (id_material, cantidad, id_instructor, fecha_prestamo, estado, fecha_limite_devolucion, id_responsable, rol_responsable, responsable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insertar_prestamo = $conexion->prepare($sql_insertar_prestamo);
            $stmt_insertar_prestamo->bind_param("iiisssiss", $material_id, $cantidad, $instructor_id, $fecha_prestamo, $estado_prestamo, $fecha_limite_devolucion, $id_responsable, $rol_responsable, $responsable);
            $stmt_insertar_prestamo->execute();
            $prestamo_id = $conexion->insert_id;
            $stmt_insertar_prestamo->close();
            $fecha_devolucion_consumible = date("Y-m-d h:i:s A");

            if ($material_data['tipo'] === 'consumible') {
                $insert_devolucion_sql = "INSERT INTO devolucion_materiales (id_prestamo_material, id_responsable, rol_responsable, responsable, estado_devolucion, fecha_devolucion, observaciones) VALUES (?, ?, ?, ?, 'consumido', ?, 'Material consumible registrado automáticamente.')";
                $stmt_insert_devolucion = $conexion->prepare($insert_devolucion_sql);
                $stmt_insert_devolucion->bind_param("issss", $prestamo_id, $id_responsable, $rol_responsable, $responsable, $fecha_devolucion_consumible);
                $stmt_insert_devolucion->execute();
                $stmt_insert_devolucion->close();
            }
        }

        if ($hay_material_no_consumible) {
            $sql_update_instructor = "UPDATE instructores SET disponibilidad_prestamo = 'no_disponible' WHERE id_instructor = ?";
            $stmt_update_instructor = $conexion->prepare($sql_update_instructor);
            $stmt_update_instructor->bind_param("i", $instructor_id);
            $stmt_update_instructor->execute();
            $stmt_update_instructor->close();
        }

        $conexion->commit();
        $response['success'] = true;
        $response['message'] = '✅ Préstamo(s) registrado(s) exitosamente.';

    } catch (Exception $e) {
        if ($conexion->in_transaction) $conexion->rollback();
        $response['message'] = '❌ Error al procesar el préstamo: ' . $e->getMessage();
    } finally {
        if ($conexion) $conexion->close();
    }
    
    echo json_encode($response);
    exit();
}
?>