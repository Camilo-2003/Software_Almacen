<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_material = $_POST['tipo_material'];
    $material = $_POST['material'];
    $cantidad = $_POST['cantidad'];
    $instructor = $_POST['instructor'];
    $fecha_prestamo = $_POST['fecha_prestamo'];
    $hora_devolucion = $_POST['hora_devolucion'];
    $estado = $_POST['estado'];

    $sql = "INSERT INTO prestamo_materiales (tipo_material, material, cantidad, instructor, fecha_prestamo, hora_devolucion, estado)
            VALUES ('$tipo_material', '$material', '$cantidad', '$instructor', '$fecha_prestamo', '$hora_devolucion', '$estado')";

    if ($conn->query($sql) === TRUE) {
        echo "Préstamo registrado exitosamente.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>


