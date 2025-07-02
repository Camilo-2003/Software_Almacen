<?php
include "ProhibirAcceso.php";
include "Conexion.php";

$id_responsable_logueado = $_SESSION["id_almacenista"] ?? ($_SESSION["id_administrador"] ?? '');
$rol_responsable_logueado = $_SESSION["rol"] ?? '';
$nombre_responsable_logueado = trim(($_SESSION["nombres"] ?? '') . ' ' . ($_SESSION["apellidos"] ?? ''));

$action = $_GET['action'] ?? 'novedades_form';

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="Img/logo_sena.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Novedades | SENA</title>
  <link rel="stylesheet" href="Css/Novedades.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="Css/Mensajes.css">
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
  <div class="header-right">
    <?php if ($action !== 'novedades_form') : ?>
      <a href="?action=novedades_form" class="btn-header-register"><i class="fas fa-plus"></i> Registrar Novedad</a>
    <?php endif; ?>
    <?php if ($action !== 'historial') : ?>
      <a href="?action=historial" class="rgs"><i class="fas fa-history"></i> Historial de Novedades</a>
    <?php endif; ?>
  </div>
</header>

<div class="main-content">
<?php
switch ($action) {

  case 'novedades_form':
    $instructor_options = "<option value=''>Selecciona un instructor</option>";
    $res = $conexion->query("SELECT id_instructor, nombre, apellido FROM instructores ORDER BY nombre ASC");
    if ($res) {
      while ($f = $res->fetch_assoc()) {
        $instructor_options .= "<option value='{$f['id_instructor']}'>" . htmlspecialchars($f['nombre'].' '.$f['apellido']) . "</option>";
      }
      $res->free();
    }
    ?>
    <div class="container">
      <form action="?action=procesar_novedad" method="post" enctype="multipart/form-data" onsubmit="return validarFormulario()">
        <h2>Registrar Novedad</h2>
        <input type="hidden" name="id_responsable" value="<?= htmlspecialchars($id_responsable_logueado) ?>">
        <input type="hidden" name="rol_responsable" value="<?= htmlspecialchars($rol_responsable_logueado) ?>">
        <label for="tipoNovedad">Tipo de novedad</label>
        <select name="tipoNovedad" id="tipoNovedad" required>
          <option value="">Seleccione</option>
          <option value="devolucion_material">Novedad Material</option>
          <option value="devolucion_equipo">Novedad Equipo</option>
        </select>
        <label for="descripcion">Descripción</label>
        <input type="text" name="descripcion" placeholder="Descripción" id="descripcion" required autocomplete="off">
        <label for="instructor">Instructor</label>
        <select name="instructor_id" id="instructor" required><?= $instructor_options ?></select>
        <label for="observaciones">Observaciones Adicionales</label>
        <input type="text" name="observaciones" placeholder="Observación Adicionales" id="observaciones" required autocomplete="off">
        <label>Responsable</label>
        <input type="text" value="<?= htmlspecialchars($nombre_responsable_logueado) ?>" readonly>
        <label for="imagen">Adjuntar imagen (opcional)</label>
        <input type="file" name="imagen" id="imagen" accept="image/*">
        <br><br>
        <button type="submit" name="btnIngresar" value="Ok"><i class="fas fa-plus-circle"></i> Registrar Novedad</button>
      </form>
    </div>
    <?php
    break;

  case 'procesar_novedad':
    if (!empty($_POST['btnIngresar'])) {
      $tipoNovedad       = $_POST["tipoNovedad"];
      $descripcion       = $_POST["descripcion"];
      $id_instructor     = $_POST["instructor_id"];
      $observaciones     = $_POST["observaciones"];
      $id_responsable    = $_POST["id_responsable"];
      $rol_responsable   = $_POST["rol_responsable"];
      $nombre_responsable= $nombre_responsable_logueado;
      date_default_timezone_set('America/Bogota');
      $fecha_novedad = date("Y-m-d H:i:s");

      $nombre_instructor = 'Instructor No Seleccionado';
      if (!empty($id_instructor)) {
        $stmt = $conexion->prepare("SELECT nombre, apellido FROM instructores WHERE id_instructor = ?");
        $stmt->bind_param("i", $id_instructor);
        $stmt->execute();
        if ($r = $stmt->get_result()->fetch_assoc()) {
          $nombre_instructor = $r['nombre'].' '.$r['apellido'];
        }
        $stmt->close();
      }

      $imagen_ruta = null;
      if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['imagen']['tmp_name'];
        $nombreArchivo = preg_replace('/[^a-zA-Z0-9\.\-_]/','_', basename($_FILES['imagen']['name']));
        $destino = 'uploads/' . uniqid() . '_' . $nombreArchivo;
        if (!is_dir('uploads')) mkdir('uploads', 0755, true);
        if (move_uploaded_file($tmp, $destino)) {
          $imagen_ruta = $destino;
        } else {
          error_log("Error al mover imagen");
        }
      }

      $sql = "INSERT INTO novedades
        (tipo, descripcion, fecha, id_instructor, nombre_instructor, id_responsable, rol_responsable, nombre_responsable, observaciones, imagen)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $conexion->prepare($sql);
      $stmt->bind_param(
        "sssissssss",
        $tipoNovedad, $descripcion, $fecha_novedad,
        $id_instructor, $nombre_instructor,
        $id_responsable, $rol_responsable,
        $nombre_responsable, $observaciones,
        $imagen_ruta
      );
      if ($stmt->execute()) {
        header("Location: ?action=historial&mensaje=creada");
      } else {
        error_log("Error insertar novedad: ".$stmt->error);
        header("Location: ?action=novedades_form&mensaje=error_insercion");
      }
      exit;
    }
    break;

  case 'historial':
    ?>
    <h2 class="historial-title">Historial de novedades</h2>
    <input type="text" id="busquedaNovedades" placeholder="🔍 Buscar Novedades..." onkeyup="filtrarTabla('busquedaNovedades','tablaNovedades')">
    <div class="container historial-container">
      <div class="table-wrapper">
        <table id="tablaNovedades">
          <thead>
            <tr>
              <th>Id</th><th>Tipo</th><th>Descripción</th><th>Fecha</th>
              <th>Rol</th><th>Responsable</th><th>Instructor</th>
              <th>Observaciones</th><th>Imagen</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $novedades = $conexion->query("SELECT * FROM novedades ORDER BY fecha DESC");
            if ($novedades && $novedades->num_rows > 0) {
              while ($d = $novedades->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".htmlspecialchars($d['id_novedad'])."</td>";
                echo "<td>".htmlspecialchars($d['tipo'])."</td>";
                echo "<td>".htmlspecialchars($d['descripcion'])."</td>";
                echo "<td>".htmlspecialchars($d['fecha'])."</td>";
                echo "<td>".htmlspecialchars($d['rol_responsable'])."</td>";
                echo "<td>".htmlspecialchars($d['nombre_responsable'])."</td>";
                echo "<td>".htmlspecialchars($d['nombre_instructor'])."</td>";
                echo "<td class='obs'>".htmlspecialchars($d['observaciones'])."</td>";
                echo "<td>";
                if (!empty($d['imagen'])) {
                  echo "<img src='".htmlspecialchars($d['imagen'])."' alt='Imagen' style='max-width:80px; height:auto; border-radius:4px;'>";
                } else {
                  echo "—";
                }
                echo "</td>";
                echo "<td class='acciones'>
                        <a href='?action=editar_novedad_form&id=".htmlspecialchars($d['id_novedad'])."' class='btn-editar'><i class='fas fa-edit'></i> Editar</a>
                        <a href='?action=eliminar_novedad&id=".htmlspecialchars($d['id_novedad'])."' class='btn-eliminar' onclick='return confirmarEliminacion()'><i class='fas fa-trash-alt'></i> Eliminar</a>
                      </td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='10'>No hay novedades registradas en este momento.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    break;

  case 'eliminar_novedad':
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
      // Obtener ruta de imagen
      $stmt = $conexion->prepare("SELECT imagen FROM novedades WHERE id_novedad = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->bind_result($ruta_img);
      $stmt->fetch();
      $stmt->close();

      // Eliminar registro
      $stmt = $conexion->prepare("DELETE FROM novedades WHERE id_novedad = ?");
      $stmt->bind_param("i", $id);
      if ($stmt->execute()) {
        // Eliminar archivo
        if (!empty($ruta_img) && is_file($ruta_img)) {
          @unlink($ruta_img);  // elimina si existe :contentReference[oaicite:0]{index=0}
        }
        header("Location: ?action=historial&mensaje=eliminada");
        exit();
      } else {
        error_log("Error eliminar novedad: ".$stmt->error);
        header("Location: ?action=historial&mensaje=error_actualizacion");
        exit();
      }
    } else {
      header("Location: ?action=historial&mensaje=error_actualizacion");
      exit();
    }
    break;

  // Incluye aquí editar_novedad_form y actualizar_novedad tal cual los tienes

  default:
    header("Location: ?action=novedades_form");
    exit;
}
?>
</div>
<script src="Js/Novedades.js"></script>
</body>
</html>
