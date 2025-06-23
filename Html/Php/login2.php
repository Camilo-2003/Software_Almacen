<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);
    date_default_timezone_set('America/Bogota');
    $fecha = date("Y-m-d h:i:s A");
    $_SESSION["hora_ingreso"] = $fecha;

    $usuarios = [
        "administradores" => [
            "id" => "id_administrador",
            "redirect" => "../Administrador.php",
            "rol" => "administrador"
        ],
        "almacenistas" => [
            "id" => "id_almacenista",
            "redirect" => "../Almacenista.php",
            "rol" => "almacenista"
        ]
    ];

    foreach ($usuarios as $tabla => $datos) {
        $sql = "SELECT * FROM $tabla WHERE correo = ?";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conexion->error);
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row["password"])) {
                // Guardamos los datos de sesión
                $_SESSION[$datos["id"]] = $row[$datos["id"]];
                $_SESSION["nombres"] = $row["nombres"];
                $_SESSION["apellidos"] = $row["apellidos"];
                $_SESSION["correo"] = $row["correo"];
                $_SESSION["rol"] = $datos["rol"]; // ✅ Guardamos el rol
                $_SESSION["hora_ingreso"] = $fecha;

                // Guardamos la hora de ingreso
                $update_sql = "UPDATE $tabla SET hora_ingreso = ? WHERE correo = ?";
                $update_stmt = $conexion->prepare($update_sql);
                if ($update_stmt) {
                    $update_stmt->bind_param("ss", $fecha, $correo);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                $stmt->close();
                $conexion->close();
                header("Location: " . $datos["redirect"]);
                exit();
            } else {
                $stmt->close();
                $conexion->close();
                echo "<script>alert('🚨 Contraseña incorrecta.'); window.location.href='../Login.php';</script>";
                exit();
            }
        }

        $stmt->close();
    }

    $conexion->close();
    echo "<script>alert('🚨 No estás registrado. Por favor, regístrate.'); window.location.href='/Software_Almacen/Html/Registrarse.html';</script>";
    exit();
}
?>
