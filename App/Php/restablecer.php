<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if (isset($_SESSION['correo_recuperar'], $_SESSION['tabla'], $_SESSION['campo_id'])) {
    $correo = $_SESSION['correo_recuperar'];
    $tabla = $_SESSION['tabla'];
    $campo_id = $_SESSION['campo_id'];

    $nueva = $_POST['nueva'];
    $confirmar = $_POST['confirmar'];

    if ($nueva === $confirmar && strlen($nueva) >= 6) {
        $passwordHash = password_hash($nueva, PASSWORD_DEFAULT);
        $sql = "UPDATE $tabla SET password = ? WHERE correo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $passwordHash, $correo);
        $stmt->execute();

        unset($_SESSION['correo_recuperar'], $_SESSION['tabla'], $_SESSION['campo_id']);

        echo "<script>alert('✅ Contraseña restablecida correctamente.'); window.location.href='../Login.html';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Las contraseñas no coinciden o son muy cortas.'); window.location.href='../restablecer.html';</script>";
        exit;
    }
} else {
    echo "<script>alert('❌ Solicitud no válida.'); window.location.href='../olvidaste.html';</script>";
    exit;
}
?>
