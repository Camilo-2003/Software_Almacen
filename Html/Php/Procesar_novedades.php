<?php

$conexion = new mysqli("localhost", "root", "", "almacen");
$conexion->set_charset("utf8");

if (!empty($_POST['btnIngresar'])) {
    $tipoNovedad = $_POST["tipoNovedad"];
    $descripcion = $_POST["descripcion"];
    $id_instructor = $_POST["id_instructor"];
    $instructor = $_POST["instructor"];
    $id_responsable = $_POST["id_responsable"];
    $rol_responsable = $_POST["rol_responsable"];
    $nombre_responsable = $_POST['nombre_responsable'];
    $observaciones = $_POST["observaciones"];
    $sql = $conexion->query(" INSERT INTO novedades(tipo, descripcion, id_instructor, nombre_instructor, id_responsable, rol_responsable, nombre_responsable, observaciones) VALUES
    ('$tipoNovedad', '$descripcion', $id_instructor, '$instructor',$id_responsable, '$rol_responsable', '$nombre_responsable', '$observaciones');");
    if ($sql == 1){
        echo "<script> alert('✅ Novedad Subida Con Exito'); window.location.href='../Novedades.php';</script>";
    } else {
        echo "<script> alert('❌ Error Al Subir La Novedad');</script>";
    }
}else {
    echo "<script> alert('❌ No se ha enviado el formulario');</script>";
}
?>