<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/conexion.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombres = trim($_POST["nombre"]);
    $apellidos = trim($_POST["apellido"]);
    $correo = trim($_POST["correo"]);
    $telefono = trim($_POST["telefono"]);
    $password = trim($_POST["password"]);
    $password_hashed = password_hash($password, PASSWORD_DEFAULT); // Hasheamos la contraseña


    $sql_check = "SELECT id_almacenista FROM almacenistas WHERE correo = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('El correo ya está registrado. Usa otro.'); window.location.href='../registrarse.html';</script>";
        $stmt_check->close();
        $conexion->close();
        exit();
    }
    $stmt_check->close();

    // Insertar usuario en la BD
    $sql = "INSERT INTO almacenistas (nombres, apellidos, correo, telefono, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssss", $nombres, $apellidos, $correo, $telefono, $password_hashed);
    // si todos los campos se llenan correctamente se ejecuta y direcciona a login
    if ($stmt->execute()) {
        echo "<script>alert('Registro exitoso. Ahora puedes iniciar sesión.'); window.location.href='../login.html';</script>";
    } else {
        echo "<script>alert('Error al registrar. Inténtalo de nuevo.'); window.location.href='../registrarse.html';</script>";
    }

    $stmt->close();
    $conexion->close();
    exit();
}
?>


