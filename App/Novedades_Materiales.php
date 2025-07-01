<?php
include "ProhibirAcceso.php";
include "Conexion.php";

$id_responsable_logueado = $_SESSION["id_almacenista"] ?? ($_SESSION["id_administrador"] ?? '');
$rol_responsable_logueado = $_SESSION["rol"] ?? '';
$nombre_responsable_logueado = (isset($_SESSION["nombres"]) ? $_SESSION["nombres"] : '') . ' ' . (isset($_SESSION["apellidos"]) ? $_SESSION["apellidos"] : '');

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Novedades | SENA</title>
<link rel="stylesheet" href="Css/Novedades.css">
<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
<script src="Js/Mensajes.js" defer></script>
</head>
<body>
<header>
<div class="header-left"> 
<div class="regresar">
    <a href="<?php echo $pagina_regresar; ?>" class="rgs"><i class="fas fa-reply"></i> Regresar</a>
</div>
</div>
<div class="header-center">
<img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
<h1>Gestión de Novedades</h1>
</div>
</div>
</header>

<div class="main-content">
<div class="container historial-container">
    <div class="table-wrapper">
        <table id="tablaNovedades">
            <thead>
                <tr>
                    <!-- <th>Id Material</th> -->
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Rol</th>
                    <th>Responsable</th>
                    <th>Instructor</th>
                    <th>Tipo Novedad</th>
                    <th>Imagen</th> 
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $novedades_materiales = $conexion->query("SELECT * FROM novedades2 WHERE tipo_elemento = 'material' ORDER BY fecha_novedad DESC");  
                    if ($novedades_materiales->num_rows > 0) {
                    while ($datos = $novedades_materiales->fetch_assoc()) {
                        echo "<tr>
                                    <td>" . htmlspecialchars($datos['tipo_elemento']) . "</td>
                                    <td>" . htmlspecialchars($datos['nombre_material']) . "</td>
                                    <td>" . htmlspecialchars($datos['descripcion']) . "</td>
                                    <td>" . htmlspecialchars($datos['fecha_novedad']) . "</td>
                                    <td>" . htmlspecialchars($datos['rol_responsable_registro']) . "</td>
                                    <td>" . htmlspecialchars($datos['nombre_responsable_registro']) . "</td>
                                    <td>" . htmlspecialchars($datos['nombre_instructor']) . "</td>
                                    <td>" . htmlspecialchars($datos['tipo_novedad']) . "</td>
                                    <td>";
                                    if (!empty($datos['ruta_imagen'])) {
                                        echo "<img src=\"" . htmlspecialchars($datos['ruta_imagen']) . "\" alt=\"Miniatura de Novedad\" class=\"thumbnail-novedad\">";
                                    } else {
                                        echo "<i class=\"fa-regular fa-image\" style=\"font-size: 2em; color: var(--neutral-text-medium);\"></i>";
                                    }
                                    echo "</td>
                                    <td class='acciones'>
                                        <button class='btn-editar' data-id='" . htmlspecialchars($datos['id_novedad2']) . "' 
                                                data-tipo-elemento='" . htmlspecialchars($datos['tipo_elemento']) . "'
                                                data-descripcion='" . htmlspecialchars($datos['descripcion']) . "'
                                                data-fecha-novedad='" . htmlspecialchars($datos['fecha_novedad']) . "'
                                                data-rol-responsable='" . htmlspecialchars($datos['rol_responsable_registro']) . "'
                                                data-nombre-responsable='" . htmlspecialchars($datos['nombre_responsable_registro']) . "'
                                                data-nombre-instructor='" . htmlspecialchars($datos['nombre_instructor']) . "'
                                                data-tipo-novedad='" . htmlspecialchars($datos['tipo_novedad']) . "'
                                                data-imagen='" . htmlspecialchars($datos['ruta_imagen']) . "'
                                                ><i class='fas fa-edit' id='ii'></i> Editar</button>
                                        <button class='btn-eliminar' data-id='" . htmlspecialchars($datos['id_novedad2']) . "' data-delete-url='Php/Novedades/Eliminar_Novedad_Material.php?id=" . htmlspecialchars($datos['id_novedad2']) . "'><i class='fas fa-trash-alt' id='ii'></i> Eliminar</button>
                                    </td>
                                </tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No hay novedades registradas en este momento.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<div id="modalEditarNovedad" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Editar Novedad de Material</h2>
        <form id="formEditarNovedadMaterial" method="POST" action="/Software_Almacen/App/Php/Novedades/Actualizar_Novedad_Material.php" enctype="multipart/form-data">
            <input type="hidden" id="edit_id_novedad" name="id_novedad2">
            
                <label for="edit_tipo_elemento">Tipo de Elemento:</label>
                <input type="text" id="edit_tipo_elemento" name="tipo_elemento" value="material" readonly>

                <label for="edit_descripcion">Descripción:</label>
                <textarea id="edit_descripcion" name="descripcion" required></textarea>

                <label for="edit_fecha_novedad">Fecha de Novedad:</label>
                <input type="datetime-local" id="edit_fecha_novedad" name="fecha_novedad" required>

                <label for="edit_rol_responsable">Rol del Responsable:</label>
                <input type="text" id="edit_rol_responsable" name="rol_responsable_registro" readonly>

                <label for="edit_nombre_responsable">Nombre del Responsable:</label>
                <input type="text" id="edit_nombre_responsable" name="nombre_responsable_registro" readonly>

                <label for="edit_nombre_instructor">Instructor:</label>
                <input type="text" id="edit_nombre_instructor" name="nombre_instructor" required>

                <label for="edit_tipo_novedad">Tipo de Novedad:</label>
                <select id="edit_tipo_novedad" name="tipo_novedad" required>
                    <option value="">Seleccione el tipo</option>
                    <option value="regular">Regular</option>
                    <option value="malo">Malo</option>
                    <option value="malo">Otro</option>
                </select>

              <input type="hidden" id="imagen_existente" name="imagen_existente" value="<?= htmlspecialchars($current_image_path_from_db ?? '') ?>">
              <label for="edit_imagen">Imagen:</label>
              <input type="file" id="edit_imagen" name="imagen">
              <div id="imagen-previa-container"></div>
            
            <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
        </form>
    </div>
</div>


<script src="Js/Novedades.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const mensaje = params.get("mensaje");

  if (mensaje === "creada") {
    showFloatingMessage("✅ Novedad registrada con éxito");
  } else if (mensaje === "actualizada") {
    showFloatingMessage("✅ Novedad actualizada correctamente");
  } else if (mensaje === "eliminada") {
    showFloatingMessage("✅ Novedad eliminada correctamente");
  } else if (mensaje === "error_insercion") {
    showFloatingMessage("❌ Error al registrar la novedad", true);
  } else if(mensaje === "error_preparar_consulta"){ 
    showFloatingMessage("❌ Error al preparar la consulta de eliminación", true); 
  }  else if (mensaje === "error2"){
    showFloatingMessage("❌ Error interno del sistema. Inténtalo de nuevo", true);   
  } else if (mensaje === "error3"){
    showFloatingMessage("❌ Error al enviar el formulario. Por favor, completa todos los campos", true);
  } else if (mensaje === "acceso"){
    showFloatingMessage("❌ Acceso no permitido para actualizar", true);   
  }else if (mensaje === "error_actualizacion") {
    showFloatingMessage("❌ Error al actualizar la novedad", true);
  } else if (mensaje === "error_id") {
    showFloatingMessage("❌ ID de novedad no válido", true);
  }
  // ✅ Limpia el parámetro para que no se repita al recargar
  if (mensaje) {
    const nuevaUrl = window.location.origin + window.location.pathname + window.location.search.replace(/&?mensaje=[^&]*/, "");
    window.history.replaceState({}, document.title, nuevaUrl);
  }
});
</script>
</body>
</html>