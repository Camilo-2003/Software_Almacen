<?php
$host = "localhost";
$user = "root";
$password = "1000191455mm.";
$db = "almacen";

$conexion = new mysqli($host, $user, $password, $db);
if ($conexion->connect_errno) {
    die("Conexión Fallida: " . $conexion->connect_error);
}

// Verificamos si vienen datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $material = isset($_POST['material']) ? $_POST['material'] : '';
    $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : '';
    $instructor = isset($_POST['instructor']) ? $_POST['instructor'] : '';

    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $estado = 'Prestado';

    if ($tipo != '' && $material != '' && $cantidad != '' && $instructor != '') {
        if ($tipo == 'Consumible') {
            // Insertar en prestamo_materiales
            $sql = "INSERT INTO prestamo_materiales (material, cantidad, instructor, fecha, hora, estado)
                    VALUES ('$material', '$cantidad', '$instructor', '$fecha', '$hora', '$estado')";
        } elseif ($tipo == 'No_Consumible') {
            // Insertar en prestamo_equipos
            $sql = "INSERT INTO prestamo_equipos (equipo, cantidad, instructor, fecha, hora, estado)
                    VALUES ('$material', '$cantidad', '$instructor', '$fecha', '$hora', '$estado')";
        }

        if ($conexion->query($sql) === TRUE) {
            echo "✅ Préstamo registrado correctamente. <a href='../index.html'>Volver</a>";
        } else {
            echo "❌ Error al guardar préstamo: " . $conexion->error;
        }
    } else {
        echo "⚠️ Todos los campos son obligatorios.";
    }
}
?>
