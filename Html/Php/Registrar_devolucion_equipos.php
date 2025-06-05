<?php
session_start();
// The error reporting lines should be removed or commented out for production
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

// Check if POST data is even received and if id_prestamo_equipo is set
// This initial check helps prevent direct access to the script without form submission
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['id_prestamo_equipo'])) {
    echo "<script>alert('❌ Acceso no válido al script de devolución.'); window.location.href='../Equipos.php';</script>";
    exit(); // Always use exit() after header/redirect to stop script execution
}

// Collect and sanitize inputs
$id_prestamo_equipo = intval($_POST['id_prestamo_equipo']);
$estado_devolucion = isset($_POST['estado_devolucion']) ? $_POST['estado_devolucion'] : 'bueno';
$observaciones_raw = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : ''; 
$observaciones = !empty($observaciones_raw) ? $observaciones_raw : 'Devolución normal'; 
$fecha_devolucion = date("Y-m-d H:i:s");

$id_responsable = isset($_POST['id_responsable']) ? intval($_POST['id_responsable']) : 0;
$rol_responsable = isset($_POST['rol_responsable']) ? $_POST['rol_responsable'] : '';

// --- VALIDACIONES INICIALES ---
// Validar que el ID del préstamo sea válido
if ($id_prestamo_equipo <= 0) {
    echo "<script>alert('❌ Error: ID de préstamo de equipo no válido.'); window.location.href='../Equipos.php';</script>";
    exit();
}

// Validar que el ID y el ROL del responsable sean válidos
if ($id_responsable <= 0 || !in_array($rol_responsable, ['almacenista', 'administrador'])) {
    echo "<script>alert('❌ Error: Información del responsable de la devolución no válida. Por favor, intente iniciar sesión nuevamente.'); window.location.href='../Equipos.php';</script>";
    exit();
}
// --- FIN VALIDACIONES INICIALES ---

$conexion->begin_transaction(); // Iniciar una transacción

try {
    // 1. Obtener id_equipo del préstamo (se necesita para actualizar el estado del equipo)
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

    // 2. Actualizar el estado del préstamo a 'devuelto' y registrar fecha de devolución
    $update_prestamo_stmt = $conexion->prepare("UPDATE prestamo_equipos SET estado = 'devuelto', fecha_devolucion = ? WHERE id_prestamo_equipo = ?");
    if (!$update_prestamo_stmt) {
        throw new Exception("Error preparando la actualización de préstamo: " . $conexion->error);
    }
    $update_prestamo_stmt->bind_param("si", $fecha_devolucion, $id_prestamo_equipo);
    if (!$update_prestamo_stmt->execute()) {
        throw new Exception("Error al actualizar el préstamo a 'devuelto': " . $update_prestamo_stmt->error);
    }
    $update_prestamo_stmt->close();

    // 3. Actualizar el estado del equipo
    $update_equipo_stmt = $conexion->prepare("UPDATE equipos SET estado = ? WHERE id_equipo = ?");
    if (!$update_equipo_stmt) {
        throw new Exception("Error preparando la actualización de equipo: " . $conexion->error);
    }
    $update_equipo_stmt->bind_param("si", $estado_devolucion, $equipo_id);
    if (!$update_equipo_stmt->execute()) {
        throw new Exception("Error al actualizar el estado del equipo: " . $update_equipo_stmt->error);
    }
    $update_equipo_stmt->close();

    // 4. Registrar la devolución en la tabla devolucion_equipos
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
    exit(); // Important to exit after redirect

} catch (Exception $e) {
    $conexion->rollback(); // Revertir la transacción si algo falla
    // Only display specific error message for debugging, usually a generic message for users
    $errorMessage = "Error en la transacción de devolución: " . $e->getMessage();
    echo "<script>alert('❌ " . htmlspecialchars($errorMessage) . "'); window.location.href='../Equipos.php';</script>";
    exit(); // Important to exit after redirect
} finally {
    // Ensure connection is closed even if an error occurs outside of try-catch or after commit
    if ($conexion) {
        $conexion->close();
    }
}
?>