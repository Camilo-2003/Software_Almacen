<?php
// ProhibirAcceso.php, Conexion.php y resto de código de sesión
include("ProhibirAcceso.php");
include "Conexion.php";

$id_responsable_logueado = $_SESSION["id_almacenista"] ?? ($_SESSION["id_administrador"] ?? '');
$rol_responsable_logueado = $_SESSION["rol"] ?? '';
$nombre_responsable_logueado = (isset($_SESSION["nombres"]) ? $_SESSION["nombres"] : '') . ' ' . (isset($_SESSION["apellidos"]) ? $_SESSION["apellidos"] : '');

$action = $_GET['action'] ?? 'novedades_form';

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
    <a href="?action=novedades_form" class="btn-header-register" title="Haz clic para registrar nueva novedad"><i class="fas fa-plus"></i> Registrar Novedad</a>
<?php endif; ?>
<?php if ($action !== 'historial') : ?>
    <a href="?action=historial" class="rgs" title="Haz clic para ver el historial de novedades"><i class="fas fa-history"></i> Historial de Novedades</a>
<?php endif; ?>
</div>
</header>

<div class="main-content">
<?php
switch ($action) {
case 'novedades_form':
$instructor_options_html = "<option value=''>Selecciona un instructor</option>";
$sql_instructores = "SELECT id_instructor, nombre, apellido FROM instructores ORDER BY nombre ASC";
$resultado_instructores = $conexion->query($sql_instructores);

if ($resultado_instructores) {
    if ($resultado_instructores->num_rows > 0) {
        while ($fila_instructor = $resultado_instructores->fetch_assoc()) {
            $instructor_options_html .= "<option value='" . htmlspecialchars($fila_instructor["id_instructor"]) . "'>";
            $instructor_options_html .= htmlspecialchars($fila_instructor["nombre"]) . " " . htmlspecialchars($fila_instructor["apellido"]);
            $instructor_options_html .= "</option>";
        }
    } else {
        $instructor_options_html = "<option value=''>No hay instructores disponibles</option>";
    }
    $resultado_instructores->free();
} else {
    $instructor_options_html = "<option value=''>Error al cargar instructores: " . $conexion->error . "</option>";
    error_log("Error en la consulta de instructores (form): " . $conexion->error);
}
?>
<div class="container">
    <form action="?action=procesar_novedad" method="post" onsubmit="return validarFormulario()">
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
        <input type="text" placeholder="Descripción" name="descripcion" id="descripcion" autocomplete="off" required>

        <label for="instructor">Instructor</label>
        <select name="instructor_id" id="instructor" required>
            <?= $instructor_options_html; ?>
        </select>

        <label for="observaciones">Observaciones Adicionales</label>
        <input type="text" placeholder="Observaciones" name="observaciones" id="observaciones" autocomplete="off" required>

        <label>Responsable De Registrar Novedad</label>
        <input type="text" name="nombre_responsable" value="<?= htmlspecialchars($nombre_responsable_logueado) ?>" readonly>

        <br>
        <button type="submit" name="btnIngresar" value="Ok"><i class="fas fa-plus-circle"></i> Registrar Novedad</button>
    </form>
</div>
<?php
break;

case 'historial':         ?>
<h2 class="historial-title">Historial de novedades</h2>
<input type="text" id="busquedaNovedades" placeholder="🔍 Buscar Novedades..." onkeyup="filtrarTabla('busquedaNovedades', 'tablaNovedades')">
<br>
<div class="container historial-container">
    <div class="table-wrapper">
        <table id="tablaNovedades">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Rol</th>
                    <th>Responsable</th>
                    <th>Instructor</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $novedades = $conexion->query("SELECT * FROM novedades ORDER BY fecha DESC");
                if ($novedades->num_rows > 0) {
                    while ($datos = $novedades->fetch_assoc()) {
                        echo "<tr>
                                    <td>" . htmlspecialchars($datos['id_novedad']) . "</td>
                                    <td>" . htmlspecialchars($datos['tipo']) . "</td>
                                    <td>" . htmlspecialchars($datos['descripcion']) . "</td>
                                    <td>" . htmlspecialchars($datos['fecha']) . "</td>
                                    <td>" . htmlspecialchars($datos['rol_responsable']) . "</td>
                                    <td>" . htmlspecialchars($datos['nombre_responsable']) . "</td>
                                    <td>" . htmlspecialchars($datos['nombre_instructor']) . "</td>
                                    <td class='obs'>" . htmlspecialchars($datos['observaciones']) . "</td>
                                    <td class='acciones'>
                                        <a class='btn-editar' href='?action=editar_novedad_form&id=" . htmlspecialchars($datos['id_novedad']) ."'><i class='fas fa-edit' id='ii'></i> Editar</a>
                                        <a class='btn-eliminar' href='?action=eliminar_novedad&id=" . htmlspecialchars($datos['id_novedad']) . "' onclick='return confirmarEliminacion()'><i class='fas fa-trash-alt' id='ii'></i> Eliminar</a>
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
<?php
break;

case 'procesar_novedad':
if (!empty($_POST['btnIngresar'])) {
    $tipoNovedad        = $_POST["tipoNovedad"] ?? '';
    $descripcion        = $_POST["descripcion"] ?? '';
    $id_instructor      = $_POST["instructor_id"] ?? '';
    $observaciones      = $_POST["observaciones"] ?? '';
    
    $id_responsable     = $_POST["id_responsable"] ?? '';
    $rol_responsable    = $_POST["rol_responsable"] ?? '';
    $nombre_responsable = $_POST['nombre_responsable'] ?? '';

    date_default_timezone_set('America/Bogota');
    $fecha_novedad = date("Y-m-d H:i:s");

    $nombre_completo_instructor = 'Instructor No Seleccionado';
    if (!empty($id_instructor)) {
        $sql_get_name = "SELECT nombre, apellido FROM instructores WHERE id_instructor = ?";
        $stmt_name = $conexion->prepare($sql_get_name);
        if ($stmt_name) {
            $stmt_name->bind_param("i", $id_instructor);
            $stmt_name->execute();
            $result_name = $stmt_name->get_result();
            if ($result_name && $result_name->num_rows == 1) {
                $row_name = $result_name->fetch_assoc();
                $nombre_completo_instructor = $row_name["nombre"] . " " . $row_name["apellido"];
            }
            $stmt_name->close();
        } else {
            error_log("Error al preparar consulta de nombre de instructor (procesar): " . $conexion->error);
        }
    }
    $sql_insert = "INSERT INTO novedades(tipo, descripcion, fecha, id_instructor, nombre_instructor, id_responsable, rol_responsable, nombre_responsable, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql_insert);
    if ($stmt) {
        $stmt->bind_param(
            "sssisssss",
            $tipoNovedad, $descripcion, $fecha_novedad, $id_instructor, $nombre_completo_instructor,
            $id_responsable, $rol_responsable, $nombre_responsable, $observaciones
        );
        if ($stmt->execute()) {
        header("Location: Novedades.php?action=historial&mensaje=creada");
        exit();
        } else {
        error_log("Error al subir la novedad: " . $stmt->error);
        header("Location: Novedades.php?action=novedades_form&mensaje=error_insercion");
        exit();        }
        $stmt->close();
    } else {
        error_log("Error en la preparación de la consulta de inserción: " . $conexion->error);
    header("Location: Novedades.php?action=novedades_form&mensaje=error2");
    exit();      }
} else {
    header("Location: Novedades.php?action=novedades_form&mensaje=error3");
    exit();  }
exit();
break;

case 'editar_novedad_form':
$id = intval($_GET['id'] ?? 0);
$novedad = null;
$instructor_options_html = "<option value=''>Selecciona un instructor</option>";

if ($id > 0) {
    $select_novedad = "SELECT * FROM novedades WHERE id_novedad = ?";
    $stmt_novedad = $conexion->prepare($select_novedad);
    if ($stmt_novedad) {
        $stmt_novedad->bind_param("i", $id);
        $stmt_novedad->execute();
        $result_novedad = $stmt_novedad->get_result();
        if ($result_novedad->num_rows > 0) {
            $novedad = $result_novedad->fetch_assoc();
        } else {
            echo "<p class='error-message'>No se encontró la novedad.</p>";
        }
        $stmt_novedad->close();
    } else {
        echo "<p class='error-message'>Error al preparar consulta de novedad (editar): " . $conexion->error . "</p>";
    }
} else {
    echo "<p class='error-message'>ID de novedad no válido.</p>";
}
$sql_instructores = "SELECT id_instructor, nombre, apellido FROM instructores ORDER BY nombre ASC";
$resultado_instructores = $conexion->query($sql_instructores);
if ($resultado_instructores) {
    if ($resultado_instructores->num_rows > 0) {
        while ($fila_instructor = $resultado_instructores->fetch_assoc()) {
            $selected = ($novedad && $novedad['id_instructor'] == $fila_instructor["id_instructor"]) ? 'selected' : '';
            $instructor_options_html .= "<option value='" . htmlspecialchars($fila_instructor["id_instructor"]) . "' $selected>";
            $instructor_options_html .= htmlspecialchars($fila_instructor["nombre"]) . " " . htmlspecialchars($fila_instructor["apellido"]);
            $instructor_options_html .= "</option>";
        }
    } else {
        $instructor_options_html = "<option value=''>No hay instructores disponibles</option>";
    }
    $resultado_instructores->free();
} else {
    error_log("Error en la consulta de instructores (editar): " . $conexion->error);
    $instructor_options_html = "<option value=''>Error al cargar instructores</option>";
}
if ($novedad) :
?>
<div class="container2">
    <form action="?action=actualizar_novedad" method="post">
        <h2>Actualizar Novedad</h2>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
        
        <input type="hidden" name="id_responsable_original" value="<?php echo htmlspecialchars($novedad['id_responsable']); ?>">
        <input type="hidden" name="rol_responsable_original" value="<?php echo htmlspecialchars($novedad['rol_responsable']); ?>">
        <input type="hidden" name="nombre_responsable_original" value="<?php echo htmlspecialchars($novedad['nombre_responsable']); ?>">
        
        <label>Tipo de novedad</label><br>
        <select name="tipoNovedad" required>
            <option value="">Seleccione</option>
            <option value="devolucion_material" <?php if ($novedad['tipo'] == 'devolucion_material') echo 'selected'; ?>>Novedad Material</option>
            <option value="devolucion_equipo" <?php if ($novedad['tipo'] == 'devolucion_equipo') echo 'selected'; ?>>Novedad Equipos</option>
        </select><br>
        <label>Descripción</label><br>
        <input type="text" placeholder="Descripción" name="descripcion" id="descripcion" value="<?php echo htmlspecialchars($novedad['descripcion']); ?>" required><br>

        <label for="instructor_id">Instructor</label><br>
        <select name="instructor_id" id="instructor_id" required>
            <?= $instructor_options_html; ?>
        </select><br>
        <label>Rol</label><br>
        <input type="text" placeholder="Rol Responsable" value="<?php echo htmlspecialchars($novedad['rol_responsable']); ?>" required readonly><br>
        <label>Responsable</label><br>
        <input type="text" placeholder="Nombre Responsable" value="<?php echo htmlspecialchars($novedad['nombre_responsable']); ?>" required readonly><br>
        <label>Observaciones adicionales</label><br>
        <input type="text" placeholder="Observaciones" name="observaciones" id="observaciones" value="<?php echo htmlspecialchars($novedad['observaciones']); ?>" required autocomplete="off"><br>
        <br>
        <button type="submit" name="btnIngresar" value="Ok"><i class="fa-regular fa-circle-check"></i> Actualizar Novedad</button>
    </form>
</div>
<?php
endif;
break;

case 'actualizar_novedad':
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $tipo = $_POST['tipoNovedad'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $id_instructor = $_POST['instructor_id'] ?? ''; 
        $observaciones = $_POST['observaciones'] ?? '';
        
        $id_responsable_novedad = $_POST['id_responsable_original'] ?? '';
        $rol_responsable_novedad = $_POST['rol_responsable_original'] ?? '';
        $nombre_responsable_novedad = $_POST['nombre_responsable_original'] ?? '';

        $nombre_instructor_db = ''; 
        if (!empty($id_instructor)) {
            $sql_get_name = "SELECT nombre, apellido FROM instructores WHERE id_instructor = ?";
            $stmt_name = $conexion->prepare($sql_get_name);
            if ($stmt_name) {
                $stmt_name->bind_param("i", $id_instructor);
                $stmt_name->execute();
                $stmt_name->bind_result($nombre_instr, $apellido_instr);
                if ($stmt_name->fetch()) {
                    $nombre_instructor_db = $nombre_instr . " " . $apellido_instr;
                }
                $stmt_name->close();
            } else {
                error_log("Error al preparar consulta de nombre de instructor (actualizar): " . $conexion->error);
            }
        }
        date_default_timezone_set('America/Bogota'); 
        $fecha_actualizacion = date("Y-m-d H:i:s");

        $sql_update = "UPDATE novedades SET tipo=?, descripcion=?, fecha=?, id_instructor=?, nombre_instructor=?, id_responsable=?, rol_responsable=?, nombre_responsable=?, observaciones=? WHERE id_novedad=?";
        $stmt = $conexion->prepare($sql_update);
        
        if ($stmt === false) {
        header("Location: Novedades.php?action=historial&mensaje=error_actualizacion");
        exit();
        }
        $stmt->bind_param(
            "sssisssssi",
            $tipo,
            $descripcion,
            $fecha_actualizacion,
            $id_instructor,
            $nombre_instructor_db,
            $id_responsable_novedad,
            $rol_responsable_novedad,
            $nombre_responsable_novedad,
            $observaciones,
            $id 
        );
        if ($stmt->execute()) {
        header("Location: Novedades.php?action=historial&mensaje=actualizada");
        exit();
        } else {
            error_log("Error al actualizar la novedad: " . $stmt->error);
        header("Location: Novedades.php?action=historial&mensaje=error_actualizacion");
        exit();
        }
        $stmt->close();
    } else {
    header("Location: Novedades.php?action=historial&mensaje=error_id");
    exit();    
}
} else {
    header("Location: Novedades.php?action=historial&mensaje=acceso");
    exit();  }
exit();
break;

case 'eliminar_novedad':
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $delete = "DELETE FROM novedades WHERE id_novedad = ?";
    $stmt = $conexion->prepare($delete);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
        header("Location: Novedades.php?action=historial&mensaje=eliminada");
        exit();
        } else {
        header("Location: Novedades.php?action=historial&mensaje=error_actualizacion");
        exit();
        }
        $stmt->close();
    } else {
    header("Location: Novedades.php?action=historial&mensaje=error_preparar_consulta");
    exit(); 
    }
} else {
    header("Location: Novedades.php?action=historial&mensaje=error_actualizacion");
    exit();
}
exit();
break;

default:
echo "<script> window.location.href='?action=novedades_form';</script>";
exit();
break;
}
?>
</div>
<script src="Js/Novedades.js"></script>
</body>
</html>
