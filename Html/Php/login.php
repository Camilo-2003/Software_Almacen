<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);

    // Verificar si hay usuarios en la tabla
    $sql_count = "SELECT COUNT(*) AS total FROM almacenistas";
    $result_count = $conexion->query($sql_count);

    if (!$result_count) {
        die("Error en la consulta: " . $conexion->error);
    }

    $row_count = $result_count->fetch_assoc();

    if ($row_count["total"] == 0) {
        // Si no esta registrado, redirigir a registrarse.html de inmediato
        header("Location: ../Registrarse.html");
        exit();
    }

    // Verificar si el usuario ya está registrado en la base de datos
    $sql = "SELECT * FROM almacenistas WHERE correo = ?";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Error al ejecutar la consulta: " . $stmt->error);
    }

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $row["password"])) {
            $_SESSION["id_almacenista"] = $row["id_almacenista"];
            $_SESSION["nombres"] = $row["nombres"];
            $_SESSION["apellidos"] = $row["apellidos"]; 
            $_SESSION["correo"] = $row["correo"];

            $stmt->close();
            $conexion->close();
            header("Location: ../Almacenista.php");
            exit();
        } else {
            $stmt->close();
            $conexion->close();
            header("Location: ../Login.php?error=incorrect_password");
            exit();
        }
    } else {
        // Si el correo no está registrado, redirigir al registro
        $stmt->close();
        $conexion->close();
        echo "<script>alert('🚨 No estás registrado. Por favor, regístrate.'); window.location.href='/Software_Almacen/Html/Registrarse.html';</script>";
        //header("Location: ../registrarse.html");
        exit();
    }
}
?>

