<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"]); // Asegura que sea un número entero

    if (empty($id)) {
        echo "<script>alert('⚠️ID de instructor no válido.'); window.location.href='../Gestion_Usuarios.php';</script>";
        exit();
    }

    // Prepara la eliminación de forma segura
    $sql = "DELETE FROM instructores WHERE id_instructor = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('✅Instructor eliminado correctamente.'); window.location.href='../Instructores.php';</script>";
    } else {
        echo "<script>alert('⚠️Error al eliminar instructor. Inténtalo de nuevo.'); window.location.href='../Instructores.php';</script>";
    }

    $stmt->close();
    $conexion->close();
    exit();
} else {
    // Si alguien intenta entrar directo a este archivo
    echo "<script>alert('⚠️Acceso no permitido.'); window.location.href='../Gestion_Usuarios.php';</script>";
    exit();
}
?>
