<?php
header('Content-Type: application/json');
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL); 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Habilitar reportes de errores de MySQLi

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

$response = ['success' => false, 'message' => ''];

if ($conexion->connect_error) {
    $response['message'] = "Error de conexión a la base de datos: " . $conexion->connect_error;
    echo json_encode($response);
    exit();
}

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Error al decodificar JSON: ' . json_last_error_msg();
    echo json_encode($response);
    exit();
}

$id_prestamo_equipo = isset($data['id_prestamo_equipo']) ? intval($data['id_prestamo_equipo']) : 0;
$items_to_add = isset($data['items_to_add']) ? (array)$data['items_to_add'] : [];
$items_to_remove = isset($data['items_to_remove']) ? (array)$data['items_to_remove'] : [];

$id_responsable = isset($data['id_responsable']) ? intval($data['id_responsable']) : 0;
$rol_responsable = isset($data['rol_responsable']) ? $data['rol_responsable'] : '';

if ($id_prestamo_equipo <= 0) {
    $response['message'] = 'ID de préstamo inválido.';
    echo json_encode($response);
    exit();
}

$responsable_valido = false;
if ($rol_responsable === 'almacenista' && $id_responsable === $_SESSION['id_almacenista']) {
    $responsable_valido = true;
} elseif ($rol_responsable === 'administrador' && $id_responsable === $_SESSION['id_administrador']) {
    $responsable_valido = true;
}

if (!$responsable_valido) {
    $response['message'] = "ID o rol del responsable no coinciden con la sesión activa. Acción no permitida.";

    $session_id = '';
    if (isset($_SESSION['id_almacenista'])) {
        $session_id = $_SESSION['id_almacenista'];
    } elseif (isset($_SESSION['id_administrador'])) {
        $session_id = $_SESSION['id_administrador'];
    }
    echo json_encode($response);
    exit();
}

$conexion->begin_transaction(); 
$changes_made = 0;
$messages = [];

try {
    $stmt_update_detalle = $conexion->prepare("UPDATE prestamo_equipos_detalle SET estado_item_prestamo = 'cancelado', fecha_devolucion_item = CURDATE() WHERE id_prestamo_equipo_detalle = ? AND estado_item_prestamo = 'prestado'");
    $stmt_update_equipo = $conexion->prepare("UPDATE equipos SET estado = 'disponible' WHERE id_equipo = ? AND estado = 'prestado'");

    if (!$stmt_update_detalle || !$stmt_update_equipo) {
        throw new Exception("Error preparando statements para quitar equipos: " . $conexion->error);
    }

    foreach ($items_to_remove as $id_detalle) {
        $id_equipo = null;
        $stmt_get_equipo_id = $conexion->prepare("SELECT id_equipo FROM prestamo_equipos_detalle WHERE id_prestamo_equipo_detalle = ? AND estado_item_prestamo = 'prestado'");
        if (!$stmt_get_equipo_id) { throw new Exception("Error preparando stmt_get_equipo_id: " . $conexion->error); }
        $stmt_get_equipo_id->bind_param("i", $id_detalle);
        $stmt_get_equipo_id->execute();
        $result_equipo_id = $stmt_get_equipo_id->get_result();
        $row_equipo_id = $result_equipo_id->fetch_assoc();
        $stmt_get_equipo_id->close();

        if ($row_equipo_id) {
            $id_equipo = $row_equipo_id['id_equipo'];

            $stmt_update_detalle->bind_param("i", $id_detalle); 
            $stmt_update_equipo->bind_param("i", $id_equipo);

            if ($stmt_update_detalle->execute()) {
                if ($stmt_update_detalle->affected_rows > 0) {
                    if ($stmt_update_equipo->execute()) {
                        if ($stmt_update_equipo->affected_rows > 0) {
                            $changes_made++;
                            $messages[] = "Equipo con detalle ID {$id_detalle} marcado como 'cancelado' y equipo maestro 'disponible'.";
                        } else {
                            $messages[] = "Advertencia: Detalle ID {$id_detalle} marcado como 'cancelado', pero el estado del equipo maestro ID {$id_equipo} no cambió (ya no estaba 'prestado').";
                        }
                    } else {
                         throw new Exception("Error al actualizar estado del equipo maestro ID {$id_equipo}.");
                    }
                } else {
                    $messages[] = "Advertencia: El ítem de detalle {$id_detalle} ya no estaba 'prestado' o no pudo ser actualizado.";
                }
            } else {
                throw new Exception("Error al quitar detalle de préstamo con ID {$id_detalle}.");
            }
        } else {
             $messages[] = "Advertencia: El ítem de detalle {$id_detalle} no se encontró como 'prestado' o ya fue procesado.";
        }
    }
    $stmt_update_detalle->close();
    $stmt_update_equipo->close();


    if (!empty($items_to_add)) {
        $stmt_add_detalle = $conexion->prepare("INSERT INTO prestamo_equipos_detalle (id_prestamo_equipo, id_equipo, estado_item_prestamo, fecha_vencimiento_item) VALUES (?, ?, 'prestado', DATE_ADD(CURDATE(), INTERVAL 7 DAY))");
        $stmt_update_equipo_status = $conexion->prepare("UPDATE equipos SET estado = 'prestado' WHERE id_equipo = ? AND estado = 'disponible'");

        if (!$stmt_add_detalle || !$stmt_update_equipo_status) {
            throw new Exception("Error preparando statements para añadir equipos: " . $conexion->error);
        }

        foreach ($items_to_add as $id_equipo_a_anadir) {
            // Verificar si el equipo está realmente disponible antes de añadirlo
            $check_stmt = $conexion->prepare("SELECT estado FROM equipos WHERE id_equipo = ?");
            if (!$check_stmt) { throw new Exception("Error preparando check_stmt: " . $conexion->error); }
            $check_stmt->bind_param("i", $id_equipo_a_anadir);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();

            if (!$check_result || (isset($check_result['estado']) ? $check_result['estado'] : '') !== 'disponible') { // Corrección para '??'
                $messages[] = "Advertencia: El equipo ID {$id_equipo_a_anadir} no está disponible para añadir. Estado actual: " . (isset($check_result['estado']) ? $check_result['estado'] : 'No encontrado') . "."; // Corrección para '??'
                continue; 
            }

            $stmt_add_detalle->bind_param("ii", $id_prestamo_equipo, $id_equipo_a_anadir);
            $stmt_update_equipo_status->bind_param("i", $id_equipo_a_anadir);

            if ($stmt_add_detalle->execute()) {
                if ($stmt_add_detalle->affected_rows > 0) {
                    if ($stmt_update_equipo_status->execute()) {
                        if ($stmt_update_equipo_status->affected_rows > 0) {
                            $changes_made++;
                            $messages[] = "Equipo ID {$id_equipo_a_anadir} añadido al préstamo y marcado como 'prestado'.";
                        } else {
                            $messages[] = "Advertencia: Equipo ID {$id_equipo_a_anadir} añadido al préstamo, pero el estado del equipo maestro no cambió (ya no estaba 'disponible').";
                        }
                    } else {
                         throw new Exception("Error al actualizar estado del equipo maestro ID {$id_equipo_a_anadir}.");
                    }
                } else {
                    $messages[] = "Advertencia: El detalle de préstamo para equipo ID {$id_equipo_a_anadir} no pudo ser creado.";
                }
            } else {
                throw new Exception("Error al añadir equipo ID {$id_equipo_a_anadir}.");
            }
        }
        $stmt_add_detalle->close();
        $stmt_update_equipo_status->close();
    }

    $stmt_check_pendientes = $conexion->prepare("SELECT COUNT(*) AS pendientes FROM prestamo_equipos_detalle WHERE id_prestamo_equipo = ? AND estado_item_prestamo = 'prestado'");
    if (!$stmt_check_pendientes) {
        throw new Exception("Error preparando conteo de pendientes para cabecera: " . $conexion->error);
    }
    $stmt_check_pendientes->bind_param("i", $id_prestamo_equipo);
    $stmt_check_pendientes->execute();
    $result_pendientes = $stmt_check_pendientes->get_result();
    $row_pendientes = $result_pendientes->fetch_assoc();
    $stmt_check_pendientes->close();

    if ($row_pendientes['pendientes'] == 0) {
        // Si no quedan ítems 'prestado', actualizar la cabecera a 'completamente_devuelto'
        $stmt_update_cabecera = $conexion->prepare("UPDATE prestamo_equipos SET estado_general_prestamo = 'completamente_devuelto' WHERE id_prestamo_equipo = ?");
        if (!$stmt_update_cabecera) {
            throw new Exception("Error preparando actualización de cabecera: " . $conexion->error);
        }
        $stmt_update_cabecera->bind_param("i", $id_prestamo_equipo);
        $stmt_update_cabecera->execute();
        if ($stmt_update_cabecera->affected_rows > 0) {
             $messages[] = "Estado del préstamo cabecera actualizado a 'completamente_devuelto'.";
        }
        $stmt_update_cabecera->close();
    } else {
        // Si aún quedan equipos prestados, asegurarse de que el estado sea 'prestado'
        $stmt_update_cabecera = $conexion->prepare("UPDATE prestamo_equipos SET estado_general_prestamo = 'prestado' WHERE id_prestamo_equipo = ? AND estado_general_prestamo != 'prestado'");
        if (!$stmt_update_cabecera) {
            throw new Exception("Error preparando actualización de cabecera (a prestado): " . $conexion->error);
        }
        $stmt_update_cabecera->bind_param("i", $id_prestamo_equipo);
        $stmt_update_cabecera->execute();
        if ($stmt_update_cabecera->affected_rows > 0) {
             $messages[] = "Estado del préstamo cabecera actualizado a 'prestado'.";
        }
        $stmt_update_cabecera->close();
    }


    if ($changes_made > 0) {
        $conexion->commit();
        $response['success'] = true;
        $response['message'] = '✅ Préstamo actualizado exitosamente. ' . implode(' ', $messages);
    } else {
        $conexion->rollback(); 
        $response['success'] = true;
        $response['message'] = '✅ No se realizaron cambios en el préstamo o los equipos ya estaban en el estado deseado.';
    }

} catch (Exception $e) {
    $conexion->rollback();
    $response['success'] = false;
    $response['message'] = '❌ Error al procesar la edición del préstamo: ' . $e->getMessage();
} finally {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    echo json_encode($response);
}

?>