<?php
session_start();
// Habilitar la visualización de errores para depuración (descomentar en desarrollo, comentar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['id_prestamo_equipo'])) {
    echo "<script>alert('❌ Acceso no válido al script de devolución.'); window.location.href='../Equipos.php';</script>";
    exit();
}

$id_prestamo_equipo = intval($_POST['id_prestamo_equipo']);
// This is the condition of the *returned item*, to be stored in devolucion_equipos
$estado_devolucion = isset($_POST['estado_devolucion']) ? $_POST['estado_devolucion'] : 'bueno';
$observaciones_raw = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
$observaciones = !empty($observaciones_raw) ? $observaciones_raw : 'Devolución normal';
date_default_timezone_set('America/Bogota');
$fecha_devolucion = date("Y-m-d h:i:s A");

$id_responsable = isset($_POST['id_responsable']) ? intval($_POST['id_responsable']) : 0;
$rol_responsable = isset($_POST['rol_responsable']) ? $_POST['rol_responsable'] : '';

if ($id_prestamo_equipo <= 0) {
    echo "<script>alert('❌ Error: ID de préstamo de equipo no válido.'); window.location.href='../Equipos.php';</script>";
    exit();
}

if ($id_responsable <= 0 || !in_array($rol_responsable, ['almacenista', 'administrador'])) {
    echo "<script>alert('❌ Error: Información del responsable de la devolución no válida. Por favor, intente iniciar sesión nuevamente.'); window.location.href='../Equipos.php';</script>";
    exit();
}

$conexion->begin_transaction();

try {
    // 1. Obtener id_equipo del préstamo
    $get_equipo_id_stmt = $conexion->prepare("SELECT id_equipo FROM prestamo_equipos WHERE id_prestamo_equipo = ? AND estado = 'pendiente'");
    if (!$get_equipo_id_stmt) {
        throw new Exception("Error preparando la consulta de id_equipo: " . $conexion->error);
    }
    $get_equipo_id_stmt->bind_param("i", $id_prestamo_equipo);
    $get_equipo_id_stmt->execute();
    $result = $get_equipo_id_stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Préstamo no encontrado o ya devuelto.");
    }
    $equipo_data = $result->fetch_assoc();
    $equipo_id = $equipo_data['id_equipo'];
    $get_equipo_id_stmt->close();

    // 2. Actualizar el estado del préstamo a 'devuelto' en prestamo_equipos
    $update_prestamo_stmt = $conexion->prepare("UPDATE prestamo_equipos SET estado = 'devuelto', fecha_devolucion = ? WHERE id_prestamo_equipo = ?");
    if (!$update_prestamo_stmt) {
        throw new Exception("Error preparando la actualización de préstamo: " . $conexion->error);
    }
    $update_prestamo_stmt->bind_param("si", $fecha_devolucion, $id_prestamo_equipo);
    if (!$update_prestamo_stmt->execute()) {
        throw new Exception("Error al actualizar el préstamo a 'devuelto': " . $update_prestamo_stmt->error);
    }
    $update_prestamo_stmt->close();

    // 3. Actualizar el estado del equipo en la tabla 'equipos' a 'disponible'
    // *** CAMBIO CRÍTICO AQUÍ ***
    $new_equipo_status_in_inventory = 'disponible';
    if ($estado_devolucion === 'deteriorado') { // If the returned status is 'deteriorado', then the equipment itself should be marked as 'deteriorado'
        $new_equipo_status_in_inventory = 'deteriorado';
    }

    $update_equipo_stmt = $conexion->prepare("UPDATE equipos SET estado = ? WHERE id_equipo = ?");
    if (!$update_equipo_stmt) {
        throw new Exception("Error preparando la actualización de equipo: " . $conexion->error);
    }
    // Bind the correct status for the 'equipos' table
    $update_equipo_stmt->bind_param("si", $new_equipo_status_in_inventory, $equipo_id); // <--- HERE IS THE FIX
    if (!$update_equipo_stmt->execute()) {
        throw new Exception("Error al actualizar el estado del equipo: " . $update_equipo_stmt->error);
    }
    $update_equipo_stmt->close();

    // 4. Registrar la devolución en la tabla devolucion_equipos
    // Here, we store the actual condition ('bueno', 'regular', 'malo', 'deteriorado') of the returned item
    $insert_devolucion_stmt = $conexion->prepare("INSERT INTO devolucion_equipos (id_prestamo_equipo, id_responsable, rol_responsable, estado_devolucion, fecha_devolucion, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$insert_devolucion_stmt) {
        throw new Exception("Error preparando la inserción en devolucion_equipos: " . $conexion->error);
    }
    $insert_devolucion_stmt->bind_param("iissss", $id_prestamo_equipo, $id_responsable, $rol_responsable, $estado_devolucion, $fecha_devolucion, $observaciones);
    if (!$insert_devolucion_stmt->execute()) {
        throw new Exception("Error al registrar la devolución en la tabla devolucion_equipos: " . $insert_devolucion_stmt->error);
    }
    $insert_devolucion_stmt->close();

    $conexion->commit(); // Confirmar la transacción
    echo "<script>alert('✅ Devolución registrada exitosamente.'); window.location.href='../Equipos.php';</script>";
    exit();

} catch (Exception $e) {
    $conexion->rollback(); // Revertir la transacción si algo falla
    $errorMessage = "Error en la transacción de devolución: " . $e->getMessage();
    echo "<script>alert('❌ " . htmlspecialchars($errorMessage) . "'); window.location.href='../Equipos.php';</script>";
    exit();
} finally {
    if ($conexion) {
        $conexion->close();
    }
}
?>