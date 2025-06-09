<?php

$conexion = new mysqli("localhost", "root", "", "almacen");
$conexion->set_charset("utf8");

if (!empty($_POST['btnIngresar'])) {
    $tipoNovedad      = $_POST["tipoNovedad"] ?? '';
    $descripcion      = $_POST["descripcion"] ?? '';
    $id_instructor    = $_POST["instructor_id"] ?? ''; 
    $observaciones    = $_POST["observaciones"] ?? '';
    $id_responsable   = $_POST["id_responsable"] ?? '';
    $rol_responsable  = $_POST["rol_responsable"] ?? '';
    $nombre_responsable = $_POST['nombre_responsable'] ?? '';

    date_default_timezone_set('America/Bogota');
    $fecha_novedad = date("Y-m-d h:i:s A");
    $nombre_completo_instructor = '';
    if (!empty($id_instructor)) {
        $sql_get_instructor_name = "SELECT nombre, apellido FROM instructores WHERE id_instructor = ?";
        $stmt_get_name = $conexion->prepare($sql_get_instructor_name);

        if ($stmt_get_name) {
            $stmt_get_name->bind_param("i", $id_instructor);
            $stmt_get_name->execute();
            $result_name = $stmt_get_name->get_result();

            if ($result_name && $result_name->num_rows == 1) {
                $row_name = $result_name->fetch_assoc();
                $nombre_completo_instructor = $row_name["nombre"] . " " . $row_name["apellido"];
            }
            $stmt_get_name->close();
        } else {
            error_log("Error preparing instructor name query: " . $conexion->error);
            // Optionally, set a default or show an error to the user
            $nombre_completo_instructor = 'Instructor Desconocido';
        }
    } else {
        // If instructor ID is empty, set a default name
        $nombre_completo_instructor = 'Instructor No Seleccionado';
    }
    $sql_insert = "INSERT INTO novedades(tipo, descripcion, fecha, id_instructor, nombre_instructor, id_responsable, rol_responsable, nombre_responsable, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql_insert);
    if ($stmt) {
        $stmt->bind_param(
            "sssisssss", // String, String, String, Integer, String, Integer, String, String, String
            $tipoNovedad,
            $descripcion,
            $fecha_novedad,
            $id_instructor,
            $nombre_completo_instructor, // Use the fetched full name here
            $id_responsable,
            $rol_responsable,
            $nombre_responsable,
            $observaciones
        );
        if ($stmt->execute()) {
            echo "<script> alert('✅ Novedad Subida Con Exito'); window.location.href='../Novedades.php';</script>";
        } else {
            // Log the error for debugging, don't show internal error to user
            error_log("Error al subir la novedad: " . $stmt->error);
            echo "<script> alert('❌ Error Al Subir La Novedad. Inténtalo de nuevo.'); window.location.href='../Novedades.php';</script>";
        }
        $stmt->close();
    } else {
        error_log("Error en la preparación de la consulta: " . $conexion->error);
        echo "<script> alert('❌ Error interno del sistema. Inténtalo de nuevo.'); window.location.href='../Novedades.php';</script>";
    }
} else {
    echo "<script> alert('❌ No se ha enviado el formulario correctamente.'); window.location.href='../Novedades.php';</script>";
}
$conexion->close();
?>
