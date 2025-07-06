<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['correo']);
    $tablas = ['administradores', 'almacenistas'];
    $usuario_encontrado = false;

    foreach ($tablas as $tabla) {
        $stmt = $conexion->prepare("SELECT correo FROM $tabla WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario_encontrado = true;
            break;
        }
    }

    if (!$usuario_encontrado) {
        echo "<script>alert('🚨 Correo no registrado.'); window.location.href='../olvidaste_contraseña.html';</script>";
        exit;
    }

    $token = bin2hex(random_bytes(50));
    $_SESSION['token'] = $token;
    $_SESSION['correo_recup'] = $correo;

    header("Location: ../restablecer_contraseña.php?token=$token");
    exit;
}
?>
