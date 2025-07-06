<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
    $correo = trim($_POST['correo']);
    $roles = [
        "administradores" => "id_administrador",
        "almacenistas" => "id_almacenista"
    ];

    foreach ($roles as $tabla => $campo_id) {
        $sql = "SELECT * FROM $tabla WHERE correo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $_SESSION['correo_recuperar'] = $correo;
            $_SESSION['tabla'] = $tabla;
            $_SESSION['campo_id'] = $campo_id;

            echo "<script>alert('✅ Enlace generado correctamente.'); window.location.href='../restablecer.html';</script>";
            exit;
        }
    }

    echo "<script>alert('❌ Correo no registrado.'); window.location.href='../olvidaste.html';</script>";
}
?>
