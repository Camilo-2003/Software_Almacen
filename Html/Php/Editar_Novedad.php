<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

$id = intval($_GET['id']); 
   if($id){
    $select = "SELECT * FROM novedades WHERE id_novedad = $id";
    $resultado = $conexion->query($select);
    $novedad = $resultado->fetch_assoc();
    $stmt = $conexion->prepare($select);
} else {
    echo "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<form action="Actualizar_Novedad.php" method="post"> 
     <h2>Actualizar Novedad</h2>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <label>Tipo de novedad</label><br>
    <select name="tipoNovedad">
        <option>Seleccione</option>
        <option value="devolucion_material" <?php if ($novedad['tipo'] == 'devolucion_material') echo 'selected'; ?>>Novedad Material</option>
        <option value="devolucion_equipo" <?php if ($novedad['tipo'] == 'devolucion_equipo') echo 'selected'; ?>>Novedad Equipos</option>
    </select><br>
    <label>Descripcion</label><br>
    <input type="text" placeholder="Descripcion" name="descripcion" id="descripcion" value="<?php echo isset($novedad['descripcion']) ? $novedad['descripcion'] : ''; ?>" required><br>
    <label>Id instructor</label><br>
    <input type="text" placeholder="Id instructor" name="id_instructor" id="id_instructor" value="<?php echo isset($novedad['id_instructor']) ? $novedad['id_instructor'] : ''; ?>" required><br>
    <label>Instructor</label><br>
    <input type="text" placeholder="Nombre instructor" name="instructor" id="instructor" value="<?php echo isset($novedad['nombre_instructor']) ? $novedad['nombre_instructor'] : ''; ?>" required><br>
    <label>Id almacenista</label><br>
    <input type="text" placeholder="Id almacenista" name="id_almacenista" id="id_almacenista" value="<?php echo isset($novedad['id_almacenista']) ? $novedad['id_almacenista'] : ''; ?>" required><br>
    <label>Almacenista</label><br>
    <input type="text" placeholder="Nombre almacenista" name="almacenista" id="almacenista" value="<?php echo isset($novedad['nombre_almacenista']) ? $novedad['nombre_almacenista'] : ''; ?>" required><br>
    <label>Observaciones adicionales</label><br>
    <input type="text" placeholder="Observaciones" name="observaciones" id="observaciones" value="<?php echo isset($novedad['observaciones']) ? $novedad['observaciones'] : '' ?>" required><br>
    <br>
    <button type="submit" name="btnIngresar" value="Ok">Enviar</button>
    </form>

</body>
</html> 