<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_prestamo_material_str = $_POST['id_prestamo_material'] ?? '';
    $estado_devolucion = filter_input(INPUT_POST, 'estado_devolucion', FILTER_SANITIZE_STRING);
    $observaciones = !empty($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : 'Sin observaciones.';
    $id_responsable = filter_input(INPUT_POST, 'id_responsable', FILTER_VALIDATE_INT);
    $rol_responsable = filter_input(INPUT_POST, 'rol_responsable', FILTER_SANITIZE_STRING);
    $responsable = filter_input(INPUT_POST, 'responsable', FILTER_SANITIZE_STRING);
    date_default_timezone_set('America/Bogota');
    $fecha_devolucion = date('Y-m-d h:i:s A');

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

            $sql_verificar = "SELECT pm.cantidad, pm.id_material, pm.id_instructor, m.tipo, m.estado_material AS material_estado_actual FROM prestamo_materiales pm JOIN materiales m ON pm.id_material = m.id_material WHERE pm.id_prestamo_material = ? AND pm.estado = 'pendiente'";
            $stmt_verificar = $conexion->prepare($sql_verificar);
            if (!$stmt_verificar) {
                throw new Exception("Error al preparar la verificación del préstamo: " . $conexion->error);
            }
            $stmt_verificar->bind_param("i", $id_prestamo);
            $stmt_verificar->execute();
            $prestamo = $stmt_verificar->get_result()->fetch_assoc();
            $stmt_verificar->close();
            
            if ($prestamo) {
                //Actualizar el estado del préstamo a 'devuelto'
                $sql_actualizar_prestamo = "UPDATE prestamo_materiales SET estado = 'devuelto' WHERE id_prestamo_material = ?";
                $stmt_actualizar_prestamo = $conexion->prepare($sql_actualizar_prestamo);
                if (!$stmt_actualizar_prestamo) {
                    throw new Exception("Error al preparar la actualización del préstamo: " . $conexion->error);
                }
                $stmt_actualizar_prestamo->bind_param("i", $id_prestamo);
                $stmt_actualizar_prestamo->execute();
                $stmt_actualizar_prestamo->close();

                // 2. Registrar la devolución
                $insert_devolucion_sql = "INSERT INTO devolucion_materiales (id_prestamo_material, id_responsable, rol_responsable, responsable, estado_devolucion, fecha_devolucion, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert_devolucion = $conexion->prepare($insert_devolucion_sql);
                if (!$stmt_insert_devolucion) {
                    throw new Exception("Error al preparar el registro de devolución: " . $conexion->error);
                }
                $stmt_insert_devolucion->bind_param("iisssss", $id_prestamo, $id_responsable, $rol_responsable, $responsable, $estado_devolucion, $fecha_devolucion, $observaciones);
                $stmt_insert_devolucion->execute();
                $stmt_insert_devolucion->close();

                //Stock y Estado del Material (solo para materiales NO CONSUMIBLES)
                if ($prestamo['tipo'] === 'no consumible') {
                    if ($estado_devolucion === 'bueno' || $estado_devolucion === 'regular') {
                        // Si es 'bueno' o 'regular', se devuelve al stock.
                        // El estado del material debería volver a 'disponible'.
                        $sql_actualizar_stock = "UPDATE materiales SET stock = stock + ?, estado_material = 'disponible' WHERE id_material = ?";
                        $stmt_actualizar_stock = $conexion->prepare($sql_actualizar_stock);
                        if (!$stmt_actualizar_stock) {
                            throw new Exception("Error al preparar la actualización de stock (bueno/regular): " . $conexion->error);
                        }
                        $stmt_actualizar_stock->bind_param("ii", $prestamo['cantidad'], $prestamo['id_material']);
                        $stmt_actualizar_stock->execute();
                        $stmt_actualizar_stock->close();

                    } elseif ($estado_devolucion === 'malo') {
                        // Si es 'malo',se devuelve al stock y el estado del material cambia a 'en_revision'.
                        $sql_update_material_status = "UPDATE materiales SET stock = stock + ?, estado_material = 'en_revision' WHERE id_material = ?";
                        $stmt_update_material_status = $conexion->prepare($sql_update_material_status);
                        if (!$stmt_update_material_status) {
                            throw new Exception("Error al preparar la actualización de estado (malo): " . $conexion->error);
                        }
                        $stmt_update_material_status->bind_param("ii", $prestamo['cantidad'], $prestamo['id_material']);
                        $stmt_update_material_status->execute();
                        $stmt_update_material_status->close();
                    }
                }
                // Si el material es CONSUMIBLE, no se hace nada con el stock ni el estado del material
                // ya que se asume que se consumió al prestarlo.
                
                if (!in_array($prestamo['id_instructor'], $instructores_afectados)) {
                    $instructores_afectados[] = $prestamo['id_instructor'];
                }
            } else {
                error_log("Intento de devolver préstamo ID " . $id_prestamo . " que no está pendiente o no existe.");
            }
        }

        // Verificar y actualizar la disponibilidad de todos los instructores afectados
        foreach ($instructores_afectados as $instructor_id) {
            $sql_check_loans = "SELECT COUNT(*) AS total_pendientes FROM prestamo_materiales WHERE id_instructor = ? AND estado = 'pendiente'";
            $stmt_check_loans = $conexion->prepare($sql_check_loans);
            if (!$stmt_check_loans) {
                throw new Exception("Error al preparar la verificación de préstamos pendientes del instructor: " . $conexion->error);
            }
            $stmt_check_loans->bind_param("i", $instructor_id);
            $stmt_check_loans->execute();
            $row_check = $stmt_check_loans->get_result()->fetch_assoc();
            $stmt_check_loans->close();

            if ($row_check['total_pendientes'] == 0) {
                $sql_update_instructor = "UPDATE instructores SET disponibilidad_prestamo = 'disponible' WHERE id_instructor = ?";
                $stmt_update_instructor = $conexion->prepare($sql_update_instructor);
                if (!$stmt_update_instructor) {
                    throw new Exception("Error al preparar la actualización de disponibilidad del instructor: " . $conexion->error);
                }
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
        error_log("Error en Registrar_Devolucion_Materiales.php: " . $e->getMessage() . " en " . $e->getFile() . " en la línea " . $e->getLine());
    } finally {
        if ($conexion && $conexion->ping()) {
            $conexion->close();
        }
    }

    echo json_encode($response);
    exit();
}
?>