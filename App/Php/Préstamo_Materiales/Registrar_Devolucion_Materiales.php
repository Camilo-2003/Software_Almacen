<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_prestamo_material_str = $_POST['id_prestamo_material'] ?? '';
    $estado_devolucion = filter_input(INPUT_POST, 'estado_devolucion', FILTER_SANITIZE_STRING);
    $observaciones = !empty($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : 'Sin observaciones.';
    $id_responsable = filter_input(INPUT_POST, 'id_responsable', FILTER_VALIDATE_INT);
    $rol_responsable = filter_input(INPUT_POST, 'rol_responsable', FILTER_SANITIZE_STRING);
    $responsable = filter_input(INPUT_POST, 'responsable', FILTER_SANITIZE_STRING);
    
    //cantidad a devolver
    $cantidad_a_devolver = isset($_POST['cantidad_a_devolver']) ? filter_input(INPUT_POST, 'cantidad_a_devolver', FILTER_VALIDATE_INT) : null;
    
    date_default_timezone_set('America/Bogota');
    $fecha_devolucion = date('Y-m-d H:i:s');

    $response = ['success' => false, 'message' => ''];

    if (empty($id_prestamo_material_str) || empty($estado_devolucion) || !$id_responsable || empty($rol_responsable)) {
        $response['message'] = "❌ Error: Datos de devolución incompletos o inválidos.";
        echo json_encode($response);
        exit();
    }
    
    try {
        if (!$conexion) {
            throw new Exception("No se pudo establecer la conexión a la base de datos.");
        }

        $conexion->begin_transaction();

        $ids_a_procesar = explode(',', $id_prestamo_material_str);
        $instructores_afectados = [];

        foreach ($ids_a_procesar as $id_prestamo) {
            $id_prestamo = intval($id_prestamo);

            $sql_verificar = "SELECT pm.cantidad, pm.id_material, pm.id_instructor, m.tipo FROM prestamo_materiales pm JOIN materiales m ON pm.id_material = m.id_material WHERE pm.id_prestamo_material = ? AND pm.estado = 'pendiente'";
            $stmt_verificar = $conexion->prepare($sql_verificar);
            if (!$stmt_verificar) throw new Exception("Error al preparar la verificación del préstamo: " . $conexion->error);
            $stmt_verificar->bind_param("i", $id_prestamo);
            $stmt_verificar->execute();
            $prestamo = $stmt_verificar->get_result()->fetch_assoc();
            $stmt_verificar->close();
            
            if ($prestamo) {
                $cantidad_prestada_total = (int)$prestamo['cantidad'];
                $cantidad_devuelta_ahora = $cantidad_prestada_total; // Por defecto, se devuelve todo

                // devoluciones parciales (solo si se envía la cantidad y es una devolución única)
                if ($cantidad_a_devolver !== null && count($ids_a_procesar) === 1) {
                    if ($cantidad_a_devolver <= 0) throw new Exception("La cantidad a devolver debe ser mayor que cero.");
                    if ($cantidad_a_devolver > $cantidad_prestada_total) throw new Exception("No puedes devolver más materiales de los que tienes prestados.");
                    $cantidad_devuelta_ahora = $cantidad_a_devolver;
                }

                // 1. Actualizar el registro del préstamo original
                if ($cantidad_devuelta_ahora < $cantidad_prestada_total) {
                    //Restar la cantidad del préstamo
                    $sql_actualizar_prestamo = "UPDATE prestamo_materiales SET cantidad = cantidad - ? WHERE id_prestamo_material = ?";
                    $stmt_actualizar_prestamo = $conexion->prepare($sql_actualizar_prestamo);
                    if (!$stmt_actualizar_prestamo) throw new Exception("Error al preparar la actualización parcial del préstamo: " . $conexion->error);
                    $stmt_actualizar_prestamo->bind_param("ii", $cantidad_devuelta_ahora, $id_prestamo);
                } else {
                    // Devolución COMPLETA: Cambiar el estado a 'devuelto'
                    $sql_actualizar_prestamo = "UPDATE prestamo_materiales SET estado = 'devuelto' WHERE id_prestamo_material = ?";
                    $stmt_actualizar_prestamo = $conexion->prepare($sql_actualizar_prestamo);
                    if (!$stmt_actualizar_prestamo) throw new Exception("Error al preparar la actualización completa del préstamo: " . $conexion->error);
                    $stmt_actualizar_prestamo->bind_param("i", $id_prestamo);
                }
                $stmt_actualizar_prestamo->execute();
                $stmt_actualizar_prestamo->close();

                //Registrar la devolución con la cantidad correcta
                $insert_devolucion_sql = "INSERT INTO devolucion_materiales (id_prestamo_material, id_responsable, rol_responsable, responsable, cantidad, estado_devolucion, fecha_devolucion, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert_devolucion = $conexion->prepare($insert_devolucion_sql);
                if (!$stmt_insert_devolucion) throw new Exception("Error al preparar el registro de devolución: " . $conexion->error);
                $stmt_insert_devolucion->bind_param("iississs", $id_prestamo, $id_responsable, $rol_responsable, $responsable, $cantidad_devuelta_ahora, $estado_devolucion, $fecha_devolucion, $observaciones);
                $stmt_insert_devolucion->execute();
                $stmt_insert_devolucion->close();

                //Actualizar stock y estado del material (si no es consumible)
                if ($prestamo['tipo'] === 'no consumible') {
                    $nuevo_estado_material = 'disponible';
                    if ($estado_devolucion === 'malo') {
                        $nuevo_estado_material = 'en_revision';
                    }

                    // Siempre se devuelve al stock la cantidad devuelta, y se actualiza el estado del material
                    $sql_actualizar_stock = "UPDATE materiales SET stock = stock + ?, estado_material = ? WHERE id_material = ?";
                    $stmt_actualizar_stock = $conexion->prepare($sql_actualizar_stock);
                    if (!$stmt_actualizar_stock) throw new Exception("Error al preparar la actualización de stock: " . $conexion->error);
                    $stmt_actualizar_stock->bind_param("isi", $cantidad_devuelta_ahora, $nuevo_estado_material, $prestamo['id_material']);
                    $stmt_actualizar_stock->execute();
                    $stmt_actualizar_stock->close();
                }
                
                if (!in_array($prestamo['id_instructor'], $instructores_afectados)) {
                    $instructores_afectados[] = $prestamo['id_instructor'];
                }
            } else {
                error_log("Intento de devolver préstamo ID " . $id_prestamo . " que no está pendiente o no existe.");
            }
        }

        //Verificar y actualizar la disponibilidad de los instructores afectados
        foreach ($instructores_afectados as $instructor_id) {
            $sql_check_loans = "SELECT COUNT(*) AS total_pendientes FROM prestamo_materiales WHERE id_instructor = ? AND estado = 'pendiente'";
            $stmt_check_loans = $conexion->prepare($sql_check_loans);
            if (!$stmt_check_loans) throw new Exception("Error al preparar verificación de préstamos del instructor: " . $conexion->error);
            $stmt_check_loans->bind_param("i", $instructor_id);
            $stmt_check_loans->execute();
            $row_check = $stmt_check_loans->get_result()->fetch_assoc();
            $stmt_check_loans->close();

            if ($row_check['total_pendientes'] == 0) {
                $sql_update_instructor = "UPDATE instructores SET disponibilidad_prestamo = 'disponible' WHERE id_instructor = ?";
                $stmt_update_instructor = $conexion->prepare($sql_update_instructor);
                if (!$stmt_update_instructor) throw new Exception("Error al preparar actualización de disponibilidad del instructor: " . $conexion->error);
                $stmt_update_instructor->bind_param("i", $instructor_id);
                $stmt_update_instructor->execute();
                $stmt_update_instructor->close();
            }
        }

        $conexion->commit();
        $response['success'] = true;
        $response['message'] = '✅ Devolución(es) registrada(s) exitosamente.';

    } catch (Exception $e) {
        if ($conexion->in_transaction) $conexion->rollback();
        $response['message'] = '❌ Error al registrar la devolución: ' . $e->getMessage();
        error_log("Error en Registrar_Devolucion_Materiales.php: " . $e->getMessage());
    } finally {
        if ($conexion && $conexion->ping()) {
            $conexion->close();
        }
    }

    echo json_encode($response);
    exit();
}
?>