<?php
$conexion = new mysqli("localhost", "root", "", "almacen");

$tipo = $_GET['tipo'];
$id = intval($_GET['id']); // seguridad

if ($tipo === 'material') {
    // Obtener datos del material
    $sql = "SELECT * FROM materiales WHERE id_material = $id";
    $result = $conexion->query($sql);
    $data = $result->fetch_assoc();
    ?>
    <h2>Editar Material</h2>
    <form method="post" action="actualizar.php">
        <input type="hidden" name="tipo" value="material">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo $data['nombre']; ?>"><br>
        
        <label>Tipo:</label>
        <select name="tipo_material">
            <option value="Consumible" <?php if ($data['tipo'] == 'Consumible') echo 'selected'; ?>>Consumible</option>
            <option value="No Consumible" <?php if ($data['tipo'] == 'No Consumible') echo 'selected'; ?>>No Consumible</option>
        </select><br>

        <label>Stock:</label>
        <input type="number" name="stock" value="<?php echo $data['stock']; ?>"><br>
        
        <button type="submit">Guardar Cambios</button>
    </form>
    <?php
} elseif ($tipo === 'equipo') {
    // Obtener datos del equipo
    $sql = "SELECT * FROM equipos WHERE id_equipo = $id";
    $result = $conexion->query($sql);
    $data = $result->fetch_assoc();
    ?>
    <h2>Editar Equipo</h2>
    <form method="post" action="actualizar.php">
        <input type="hidden" name="tipo" value="equipo">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <label>Marca:</label>
        <input type="text" name="marca" value="<?php echo $data['marca']; ?>"><br>
        
        <label>Serial:</label>
        <input type="text" name="serial" value="<?php echo $data['serial']; ?>"><br>

        <label>Estado:</label><br>
        <select name="estado" required>
            <option value="disponible" <?php if ($data['estado'] == 'disponible') echo 'selected'; ?>>Disponible</option>
            <option value="prestado" <?php if ($data['estado'] == 'prestado') echo 'selected'; ?>>Prestado</option>
            <option value="deteriorado" <?php if ($data['estado'] == 'deteriorado') echo 'selected'; ?>>Deteriorado</option>
        </select><br><br>

        <button type="submit">Guardar Cambios</button>
    </form>
    <?php
}
?>


