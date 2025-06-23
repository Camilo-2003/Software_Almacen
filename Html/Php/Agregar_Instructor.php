<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';
session_start();

//echo "Conexion exitosa"

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $correo = trim($_POST["correo"]);
    $telefono = trim($_POST["telefono"]);
    $ambiente = trim($_POST["ambiente"]);

      // Validación adicional por seguridad
      if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($ambiente)) {
        echo "<script>alert('⚠️Todos los campos son obligatorios.'); window.location.href='../GestionUsuarios.php';</script>";
        exit();
    }


    $sql = "INSERT INTO instructores (nombre, apellido, correo, telefono, ambiente) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $apellido, $correo, $telefono, $ambiente);
    
    // si todos los campos se llenan correctamente se ejecuta y direcciona a la pagina de gestion de usuarios
    if ($stmt->execute()) {
        echo "<script>alert('✅Registro de instructor exitoso.'); window.location.href='../Gestion_Usuarios.php';</script>";
    } else {
        echo "<script>alert('⚠️Error al registrar instructor. ¡Instructor ya registrado!.'); window.location.href='../Gestion_Usuarios.php';</script>";
    }
    $stmt->close();
    $conexion->close();
    exit();
}

?>