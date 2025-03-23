<?php
// Incluimos la conexión existente
include 'conexion.php';

// Recibimos datos del formulario
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$fechaNacimiento = $_POST['fechaNacimiento'];
$password = $_POST['password'];

// Hashear la contraseña antes de guardarla (buena práctica)
$hash = password_hash($password, PASSWORD_DEFAULT);

// Preparamos la consulta (evitamos SQL Injection)
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, correo, telefono, fechaNacimiento, password) VALUES (?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssss", $nombre, $apellido, $correo, $telefono, $fechaNacimiento, $hash);

// Ejecutamos la consulta
if ($stmt->execute()) {
    echo "¡Usuario registrado exitosamente!";
    // Opcionalmente redireccionar a otra página:
    // header("Location: login.html");
} else {
    echo "Error: " . $stmt->error;
}

// Cerramos la consulta y conexión
$stmt->close();
$conexion->close();
?>
