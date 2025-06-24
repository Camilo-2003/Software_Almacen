<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if (!isset($conexion) || $conexion->connect_error) {
    $response['message'] = 'Error de conexión a la base de datos.';
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $response['message'] = 'Acceso no válido al script de actualización (método incorrecto).';
    echo json_encode($response);
    exit();
}
$id_prestamo_equipo_detalle = isset($_POST['id_prestamo_equipo_detalle']) ? intval($_POST['id_prestamo_equipo_detalle']) : 0;
$estado_item_prestamo = isset($_POST['estado_item_prestamo']) ? $_POST['estado_item_prestamo'] : '';
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

if ($id_prestamo_equipo_detalle <= 0) {
    $response['message'] = 'ID de detalle de préstamo inválido.';
    echo json_encode($response);
    exit();
}
if (empty($estado_item_prestamo)) {
    $response['message'] = 'El estado del ítem no puede estar vacío.';
    echo json_encode($response);
    exit();
}

$conexion->begin_transaction();

try {
    // Actualizar el estado y observaciones en prestamo_equipos_detalle
    $stmt = $conexion->prepare("UPDATE prestamo_equipos_detalle SET estado_item_prestamo = ?, observaciones = ? WHERE id_prestamo_equipo_detalle = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar la sentencia de actualización: " . $conexion->error);
    }
    
    // Asegurar que las variables pasadas a bind_param sean referencias explícitas
    $temp_estado = $estado_item_prestamo;
    $temp_obs = $observaciones;
    $temp_id = $id_prestamo_equipo_detalle;

    $stmt->bind_param("ssi", $temp_estado, $temp_obs, $temp_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0 && $stmt->errno === 0) {
        $response['success'] = true;
        $response['message'] = '✅ No se realizaron cambios, los datos son los mismos o el ítem no existe.';
    } else if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = '✅ Detalles del préstamo actualizados exitosamente.';
    } else {
        throw new Exception("Error desconocido al actualizar el detalle: " . $stmt->error);
    }
    
    $stmt->close();
    $conexion->commit();

} catch (Exception $e) {
    $conexion->rollback();
    $response['message'] = '❌ Error al actualizar los detalles del préstamo: ' . $e->getMessage();
} finally {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    echo json_encode($response);
    exit();
}
?>
