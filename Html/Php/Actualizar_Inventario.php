<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "almacen");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener datos del formulario
$tipo = $_POST['tipo'];
$id = intval($_POST['id']);  // Seguridad: convertir el id a entero

if ($tipo === 'material') {
    $nombre = $_POST['nombre'];
    $tipoMaterial = $_POST['tipo_material'];  // Cambiar a tipo_material
    $stock = $_POST['stock'];

    // Preparar consulta SQL para actualizar los datos del material
    $sql = "UPDATE materiales SET nombre=?, tipo=?, stock=? WHERE id_material=?";
    $stmt = $conexion->prepare($sql);

    // Verificar si la sentencia se preparó correctamente
    if ($stmt === false) {
        echo "Error al preparar la consulta: " . $conexion->error;
        exit();
    }

    // Vincular parámetros
    $stmt->bind_param("ssii", $nombre, $tipoMaterial, $stock, $id);

    // Ejecutar la sentencia
    if ($stmt->execute()) {
        // Redirigir al historial después de actualizar
       echo "<script> alert('✅ Material actualizado con éxito'); window.location.href='Historial_Inventario.php'; </script>";
        exit();
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    // Cerrar la sentencia
    $stmt->close();
} elseif ($tipo === 'equipo') {
    $marca = $_POST['marca'];
    $serial = $_POST['serial'];
    $estado = $_POST['estado'];
    $stock = $_POST['stock'];

    // Preparar consulta SQL para actualizar los datos del equipo
    $sql = "UPDATE equipos SET marca=?, serial=?, estado=?, stock=? WHERE id_equipo=?";
    $stmt = $conexion->prepare($sql);

    // Verificar si la sentencia se preparó correctamente
    if ($stmt === false) {
        echo "Error al preparar la consulta: " . $conexion->error;
        exit();
    }

    // Vincular parámetros
    $stmt->bind_param("sssii", $marca, $serial, $estado, $stock, $id);

    // Ejecutar la sentencia
    if ($stmt->execute()) {
        // Redirigir al historial después de actualizar
        echo "<script> alert('✅ Equipo actualizado con éxito'); window.location.href='Historial_Inventario.php'; </script>";
        exit();
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    // Cerrar la sentencia
    $stmt->close();
}

// Cerrar la conexión
$conexion->close();
?>
