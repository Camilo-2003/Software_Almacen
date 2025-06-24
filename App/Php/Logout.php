<?php
session_start(); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
//INICIO REGISTRAR SALIDA EN HISTORIAL
if (isset($_SESSION['current_session_log_id'])) {
    $id_registro = $_SESSION['current_session_log_id'];
    date_default_timezone_set('America/Bogota');
    $fecha_salida_historial = date("Y-m-d h:i:s A"); 

    $stmt_update = $conexion->prepare("UPDATE historial_sesiones SET hora_salida = ? WHERE id_registro = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("si", $fecha_salida_historial, $id_registro);
        if (!$stmt_update->execute()) {
            error_log("Error al actualizar hora_salida en historial_sesiones para registro $id_registro: " . $stmt_update->error);
        }
        $stmt_update->close();
    } else {
        error_log("Error en la preparación de la consulta de actualización de historial de sesión: " . $conexion->error);
    }
    unset($_SESSION['current_session_log_id']); // Limpia el ID de sesión del historial
}
// FIN DE REGISTRAR SALIDA EN HISTORIAL 
if (isset($_SESSION["correo"]) && isset($_SESSION["rol"])) {
    $correo = $_SESSION["correo"];
    $rol = $_SESSION["rol"];
    date_default_timezone_set('America/Bogota');
    $fecha_salida = date("Y-m-d h:i:s A");

    $tabla = '';
    if ($rol === 'administrador') {
        $tabla = 'administradores';
    } elseif ($rol === 'almacenista') {
        $tabla = 'almacenistas';
    }
    if (!empty($tabla)) {
        $update_sql = "UPDATE $tabla SET hora_salida = ? WHERE correo = ?";
        $update_stmt = $conexion->prepare($update_sql);

        if ($update_stmt) {
            $update_stmt->bind_param("ss", $fecha_salida, $correo);
            if (!$update_stmt->execute()) {
                error_log("Error al actualizar hora_salida para $correo en $tabla: " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            error_log("Error en la preparación de la consulta de actualización de hora_salida: " . $conexion->error);
        }
    }
}
session_unset(); 
session_destroy();
// Prevenir caché
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Location: ../Login.php?mensaje=cierre"); //mensaje de cierre esta en login.js
exit(); 
?> 
  