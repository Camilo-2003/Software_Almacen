<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

if (isset($_GET['id'])) {
    $id_instructor = intval($_GET['id']);

    $query = "SELECT * FROM instructores WHERE id_instructor = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $id_instructor);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $instructor = $result->fetch_assoc();
    } else {
        echo "Instructor no encontrado.";
        exit;
    }
} else {
    echo "ID de instructor no proporcionado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $ambiente = $_POST['ambiente'];

    $update_query = "UPDATE instructores SET nombre = ?, apellido = ?, correo = ?, telefono = ?, ambiente = ? WHERE id_instructor = ?";
    $stmt_update = $conexion->prepare($update_query);
    $stmt_update->bind_param('sssssi', $nombre, $apellido, $correo, $telefono, $ambiente, $id_instructor);

    if ($stmt_update->execute()) {
        echo "<script>alert('✅Instructor actualizado correctamente.'); window.location.href='/Software_Almacen/Html/Instructores.php';</script>";
    } else {
        echo "⚠️Error al actualizar, otro instructor ya tiene esta información.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Instructor</title>
</head>
<body>
    <h1>Editar Instructor</h1>
    <form method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($instructor['nombre']); ?>" required><br><br>

        <label>Apellido:</label>
        <input type="text" name="apellido" value="<?php echo htmlspecialchars($instructor['apellido']); ?>" required><br><br>

        <label>Correo:</label>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($instructor['correo']); ?>" required><br><br>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo htmlspecialchars($instructor['telefono']); ?>" required><br><br>

        <label>Ambiente:</label>
        <input type="text" name="ambiente" value="<?php echo htmlspecialchars($instructor['ambiente']); ?>" required><br><br>

        <input type="submit" value="Actualizar Instructor">
    </form>
</body>
</html>
