<?php
// include("prohibirAcceso.php");
 include "Conexion.php";
?>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Novedades</title>
    <link rel="stylesheet" href="Css/HistorialNovedades.css">
    <h2>Historial de novedades</h2> 
    <br>
     <div>
        <a href="Novedades.php" class="regresar" title="Haz clic para volver ">Regresar</a>
    </div>

      <input type="text" id="busquedaNovedades" placeholder="🔍 Buscar Novedades..." onkeyup="filtrarTabla('busquedaNovedades', 'tablaNovedades')">
    <br><br>
<div class="container">
    <br>

<table>
          <thead>
        <tr> 
      <th>Id</th>
      <th>Tipo</th>
      <th>Descripcion</th>
      <th>Fecha</th>
      <th>Id_Responsable</th>
      <th>Rol</th>
      <th>Responsable</th>
      <th>Id_instructor</th>
      <th>Instructor</th>
      <th>Acciones</th>
      </tr>
        </thead>
         <tbody>
           
      <?php
     $novedades = $conexion->query( " SELECT * FROM novedades WHERE id_novedad;");

     if ($novedades->num_rows > 0) {
        while ($datos = $novedades->fetch_assoc()) { 
      echo "<tr>
        <td>{$datos['id_novedad']}</td>
        <td>{$datos['tipo']}</td>
        <td>{$datos['descripcion']}</td>
        <td>{$datos['fecha']}</td>
        <td>{$datos['id_responsable']}</td>
        <td>{$datos['rol_responsable']}</td>
        <td>{$datos['nombre_responsable']}</td>
        <td>{$datos['id_instructor']}</td>
        <td>{$datos['nombre_instructor']}</td>
        <td class='acciones'>
          <a class='btn-editar' href='Php/Editar_Novedad.php?id={$datos['id_novedad']}'>✏️Editar</a>
          <a class='btn-eliminar' href='Php/Eliminar_Novedad.php?id={$datos['id_novedad']}' onclick='return confirmarEliminacion()'>🗑️Eliminar</a> 
        </td>
      </tr>";
     }
    }  else {
        echo "<tr><td colspan='9'>No hay novedades registrados en este momento.</td></tr>";
    }
    ?> 
  </tbody> 
      </table>  
</div>
<br>
<script src="Js/Novedades.js"></script>