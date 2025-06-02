<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

include("ProhibirAcceso.php");


// Consulta para obtener los instructores
$select = "SELECT * FROM instructores";
$result = $conexion->query($select);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial de Instructores</title>
<link rel="stylesheet" href="Css/Instructores.css">
</head>
<body>
<nav>
<div class="contain">
    <a href="Gestion_Usuarios.php" class="rgs" title="Haz clic para volver">Regresar</a>
</div>
</nav>
<h1>Lista de Instructores</h1>
<br><br>
<table>
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Ambiente</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($instructor = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $instructor['id_instructor'] . "</td>";
            echo "<td>" . $instructor['nombre'] . "</td>";
            echo "<td>" . $instructor['apellido'] . "</td>";
            echo "<td>" . $instructor['correo'] . "</td>";
            echo "<td>" . $instructor['telefono'] . "</td>";
            echo "<td>" . $instructor['ambiente'] . "</td>";
            echo "<td>
                    <a href='Php/Editar_Instructor.php?id=" . urlencode($instructor['id_instructor']) . "' class='button1'>✏️ Editar</a> | 
                    <form action='Php/Eliminar_Instructor.php' method='POST' style='display:inline-block;'>
                        <input type='hidden' name='id' value='" . $instructor['id_instructor'] . "'>
                        <button type='submit'class='button2' onclick='return confirm(\"¿Estás seguro que quieres eliminar este instructor?\")'>🗑️ Eliminar</button>
                    </form>
                    </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</body>
</html>
