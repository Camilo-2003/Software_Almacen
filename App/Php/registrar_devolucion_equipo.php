<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_prestamo_equipo = $_POST['id_prestamo_equipo'];
    $fecha_devolucion = date("Y-m-d H:i:s");

    // Verificar si el préstamo existe y está pendiente
    $query = $conexion->prepare("SELECT id_equipo FROM prestamo_equipos WHERE id_prestamo_equipo = ? AND estado = 'pendiente'");
    $query->bind_param("i", $id_prestamo_equipo);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 0) {
        echo "❌ Error: No se encontró el préstamo con ID $id_prestamo_equipo o ya ha sido devuelto.";
        exit;
    }

    $row = $resultado->fetch_assoc();
    $id_equipo = $row['id_equipo'];

    // Registrar la devolución
    $stmt = $conexion->prepare("UPDATE prestamo_equipos SET fecha_devolucion = ?, estado = 'devuelto' WHERE id_prestamo_equipo = ?");
    $stmt->bind_param("si", $fecha_devolucion, $id_prestamo_equipo);

    if ($stmt->execute()) {
        // Actualizar el estado del equipo a 'disponible'
        $update_stmt = $conexion->prepare("UPDATE equipos SET estado = 'disponible' WHERE id_equipo = ?");
        $update_stmt->bind_param("i", $id_equipo);
        $update_stmt->execute();

        // Redirigir de nuevo a la página de préstamos
        header("Location: ver_prestamos.php");
        exit;
    } else {
        echo "❌ Error al registrar la devolución: " . $stmt->error;
    }
}
?>
