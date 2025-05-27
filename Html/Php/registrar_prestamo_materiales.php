<?php
require_once '/xampp/htdocs/Software_Almacen/Html/conexion.php';

// Recoger datos del formulario
$material_nombre = $_POST['Material'];
$tipo_material = $_POST['Tipo'];
$instructor_nombre = $_POST['Instructor'];
$cantidad = $_POST['Cantidad'];
$id_almacenista = 1; 

// Convertir todo a minúsculas para evitar problemas por mayúsculas/minúsculas
$material_nombre = strtolower(trim($material_nombre));
$tipo_material = strtolower(trim($tipo_material));
// $instructor_nombre = strtolower(trim($instructor_nombre));

// Buscar id_material
$sql_material = "SELECT id_material, stock FROM materiales WHERE LOWER(nombre) = ? AND tipo = ?";
$stmt_material = $conexion->prepare($sql_material);
$stmt_material->bind_param("ss", $material_nombre, $tipo_material);
$stmt_material->execute();
$result_material = $stmt_material->get_result();

if ($result_material->num_rows === 0) {
    die("Error: material no encontrado con ese tipo.");
}

$row_material = $result_material->fetch_assoc();
$id_material = $row_material['id_material'];
$stock_disponible = $row_material['stock'];
$stmt_material->close();

// Validar stock
if ($cantidad > $stock_disponible) {
    die("Error: cantidad solicitada excede el stock disponible.");
}

// Buscar id_instructor
$sql_instructor = "SELECT id_instructor FROM instructores WHERE LOWER(nombre) = ?";
$stmt_instructor = $conn->prepare($sql_instructor);
$stmt_instructor->bind_param("s", $instructor_nombre);
$stmt_instructor->execute();
$result_instructor = $stmt_instructor->get_result();

if ($result_instructor->num_rows === 0) {
    die("Error: instructor no encontrado.");
}
$id_instructor = $result_instructor->fetch_assoc()['id_instructor'];
$stmt_instructor->close();

// Insertar préstamo con estado y fecha_prestamo
$sql_insert = "INSERT INTO prestamo_materiales (
    id_material, id_instructor, id_almacenista, cantidad, fecha_prestamo, fecha_devolucion, estado
) VALUES (?, ?, ?, ?, NOW(), NOW(), 'pendiente')";

$stmt_insert = $conexion->prepare($sql_insert);
$stmt_insert->bind_param("iiii", $id_material, $id_instructor, $id_almacenista, $cantidad);

if ($stmt_insert->execute()) {
    // Actualizar stock
    $nuevo_stock = $stock_disponible - $cantidad;
    $sql_update_stock = "UPDATE materiales SET stock = ? WHERE id_material = ?";
    $stmt_update = $conn->prepare($sql_update_stock);
    $stmt_update->bind_param("ii", $nuevo_stock, $id_material);
    $stmt_update->execute();
    $stmt_update->close();

    echo "<script>alert('Préstamo registrado correctamente'); window.location.href='../Materiales.html';</script>";
} else {
    echo "Error al registrar el préstamo: " . $stmt_insert->error;
}

$stmt_insert->close();
$conn->close();
?>
