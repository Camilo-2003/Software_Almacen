<?php
include '/Software_Almacen/Html/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestamo_id = $_POST['prestamo_id'];
    $novedad = $_POST['novedad'];
    $fecha_devolucion = date('Y-m-d H:i:s');

    // Actualizar estado del préstamo
    $sql = "UPDATE prestamo_equipos SET estado = 'devuelto' WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$prestamo_id]);

    // Registrar devolución
    $sql = "INSERT INTO devolucion_equipos (prestamo_id, fecha_devolucion, novedad) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$prestamo_id, $fecha_devolucion, $novedad]);

    echo "Devolución registrada exitosamente.";
}
?>

