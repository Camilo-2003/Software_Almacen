<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
include("ProhibirAcceso.php");

function sendJsonResponse($message, $type) {
    header('Content-Type: application/json');
    echo json_encode(['message' => $message, 'type' => $type]);
    exit();
}

$instructor_para_editar = null;
$es_modo_edicion = false;
// Procesar Eliminación
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = intval($_POST["id"]);
    if (!empty($id)) {
        $sql = "DELETE FROM instructores WHERE id_instructor = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
        sendJsonResponse('✅ Instructor eliminado correctamente.', 'success');
        } else {
        sendJsonResponse('⚠️ Error al eliminar. El instructor puede tener préstamos asociados.', 'error');
        }
        $stmt->close();
    } else {
        sendJsonResponse('⚠️ID de instructor no válido para eliminar.', 'error');
    }
    exit(); 
}

// Procesar Adición/Edición de Instructor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'])) {
    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $correo = trim($_POST["correo"]);
    $telefono = trim($_POST["telefono"]);
    $ambiente = trim($_POST["ambiente"]);
    $estado = trim($_POST["estado"]);
    $disponibilidad_prestamo = trim($_POST['disponibilidad_prestamo']);
    $id_instructor = isset($_POST['id_instructor']) ? intval($_POST['id_instructor']) : 0;

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($ambiente) || empty($estado) || empty($disponibilidad_prestamo)) {
        sendJsonResponse('⚠️ Todos los campos son obligatorios.', 'error');
        exit();
    }
    if ($id_instructor > 0) { 
        $update_query = "UPDATE instructores SET nombre = ?, apellido = ?, correo = ?, telefono = ?, ambiente = ?, estado_activo = ?, disponibilidad_prestamo = ? WHERE id_instructor = ?";
        $stmt = $conexion->prepare($update_query);
        $stmt->bind_param('sssssssi', $nombre, $apellido, $correo, $telefono, $ambiente, $estado, $disponibilidad_prestamo, $id_instructor);

        if ($stmt->execute()) {
            sendJsonResponse('✅ Instructor actualizado correctamente.', 'success');
        } else {
            sendJsonResponse('⚠️ Error al actualizar. Revisa los datos.', 'error');
        }
    } else { 
        $sql = "INSERT INTO instructores (nombre, apellido, correo, telefono, ambiente, estado_activo, disponibilidad_prestamo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssss", $nombre, $apellido, $correo, $telefono, $ambiente, $estado, $disponibilidad_prestamo);

        if ($stmt->execute()) {
            sendJsonResponse('✅ Registro de instructor exitoso.', 'success');
        } else {
            sendJsonResponse('⚠️ Error al registrar. El instructor ya podría existir.', 'error');
        }
    }
    $stmt->close();
    exit(); 
}
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_instructor_a_editar = intval($_GET['id']);
    if ($id_instructor_a_editar > 0) {
        $query = "SELECT * FROM instructores WHERE id_instructor = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param('i', $id_instructor_a_editar);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $instructor_para_editar = $result->fetch_assoc();
            $es_modo_edicion = true;
        } else {
            sendJsonResponse('⚠️ Instructor no encontrado para edición.', 'error');
            exit();
        }
        $stmt->close();
    } else {
        sendJsonResponse('⚠️ ID de instructor no válido para edición.', 'error');
        exit();
    }
}
$select = "SELECT * FROM instructores ORDER BY id_instructor DESC"; // Ordenar para ver los más recientes primero
$result_instructores = $conexion->query($select);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Instructores | SENA</title>
    <link rel="stylesheet" href="Css/Gestion_Instructores.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <script src="Js/Mensajes.js" defer></script>
</head>
<body>
    <header>
        <div class="header-left">
            <a href="<?php echo isset($pagina_regresar) ? htmlspecialchars($pagina_regresar) : '#'; ?>" class="rgs" title="Haz clic para volver"><i class="fas fa-reply"></i> Regresar</a>
        </div>
        <div class="header-center">
            <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
            <h1>Gestión de Instructores</h1>
        </div>
        <div class="header-right"></div>
    </header>
<main class="main-content">
    <div class="tabs-container" style="display:none;"> 
        <button class="tab-button active">Instructores</button>
        </div>

    <div class="form-and-stats-block">
        <h2 class="block-title"><?php echo $es_modo_edicion ? 'Modificar Instructor' : 'Registrar Instructor'; ?></h2>

        <form action="Gestion_Instructores.php" method="POST" onsubmit="return validarFormulario()" class="form-container">
            <?php if ($es_modo_edicion): ?>
                <input type="hidden" name="id_instructor" value="<?php echo htmlspecialchars($instructor_para_editar['id_instructor']); ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group flex-item">
                    <label for="nombre">Nombres:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre" value="<?php echo $es_modo_edicion ? htmlspecialchars($instructor_para_editar['nombre']) : ''; ?>" required>
                </div>
                <div class="form-group flex-item">
                    <label for="apellido">Apellidos:</label>
                    <input type="text" id="apellido" name="apellido" placeholder="Apellido" value="<?php echo $es_modo_edicion ? htmlspecialchars($instructor_para_editar['apellido']) : ''; ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group flex-item">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" value="<?php echo $es_modo_edicion ? htmlspecialchars($instructor_para_editar['correo']) : ''; ?>" required>
                </div>
                <div class="form-group flex-item">
                    <label for="telefono">Teléfono:</label>
                    <input type="number" id="telefono" name="telefono" placeholder="Teléfono" value="<?php echo $es_modo_edicion ? htmlspecialchars($instructor_para_editar['telefono']) : ''; ?>" required pattern="[0-9]{10}" maxlength="10" title="Debe ser un número de 10 dígitos">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group flex-item">
                    <label for="ambiente">Ambiente:</label>
                    <input type="text" id="ambiente" name="ambiente" placeholder="Ambiente" value="<?php echo $es_modo_edicion ? htmlspecialchars($instructor_para_editar['ambiente']) : ''; ?>" required>
                </div>
            <div class="form-group flex-item">
                <label for="estado">Estado:</label>
                <select name="estado">
                    <option>Seleccione una opción</option>
            <option value="activo" <?php echo ($es_modo_edicion && $instructor_para_editar['estado_activo'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
            <option value="inactivo" <?php echo ($es_modo_edicion && $instructor_para_editar['estado_activo'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="form-group flex-item">
                <label for="disponibilidad">Disponibilidad:</label>
                <select name="disponibilidad_prestamo">
                    <option>Seleccione una opción</option>
            <option value="disponible" <?php echo ($es_modo_edicion && $instructor_para_editar['disponibilidad_prestamo'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
            <option value="no_disponible" <?php echo ($es_modo_edicion && $instructor_para_editar['disponibilidad_prestamo'] == 'no_disponible') ? 'selected' : ''; ?>>No Disponible</option>
                </select>
            </div>
            </div>
            
            <div class="button-group">
                <button type="submit" name="submit_instructor" class="btn btn-success">
                    <i class="fas <?php echo $es_modo_edicion ? 'fa-save' : 'fa-plus-circle'; ?>"></i>
                    <?php echo $es_modo_edicion ? 'Actualizar Instructor' : 'Agregar Instructor'; ?>
                </button>
                <?php if ($es_modo_edicion): ?>
                    <a href="Gestion_Instructores.php" class="btn btn-secondary"><i class="fas fa-times-circle"></i> Cancelar Edición</a>
                <?php else: ?>
                    <button type="reset" class="btn btn-secondary"><i class="fas fa-eraser"></i> Limpiar Campos</button>
                <?php endif; ?>
            </div>
        </form>

        <div class="stats-container">
            <?php
            $total_instructores = $conexion->query("SELECT COUNT(*) FROM instructores")->fetch_assoc()['COUNT(*)'];
            $instructores_activos = $conexion->query("SELECT COUNT(*) FROM instructores WHERE estado_activo = 'activo'")->fetch_assoc()['COUNT(*)'];
            $instructores_inactivos = $conexion->query("SELECT COUNT(*) FROM instructores WHERE estado_activo = 'inactivo'")->fetch_assoc()['COUNT(*)'];
            ?>
            <div class="stat-item">
                <span class="stat-number"><?php echo $total_instructores; ?></span> Total Instructores
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $instructores_activos; ?></span> Instructores Activos
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $instructores_inactivos; ?></span> Instructores Inactivos
            </div>
        </div>
    </div> <div class="table-block">
        <h2 class="block-title">Lista de Instructores</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <!-- <th>ID</th> -->
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Ambiente</th>
                        <th>Estado</th>
                        <th>Disponibilidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_instructores->num_rows > 0) {
                        while ($instructor = $result_instructores->fetch_assoc()) {
                            echo "<tr>";
                            // echo "<td class='text-center'>" . htmlspecialchars($instructor['id_instructor']) . "</td>";
                            echo "<td class='text-left'>" . htmlspecialchars($instructor['nombre']) . "</td>";
                            echo "<td class='text-left'>" . htmlspecialchars($instructor['apellido']) . "</td>";
                            echo "<td class='text-left'>" . htmlspecialchars($instructor['correo']) . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($instructor['telefono']) . "</td>";
                            echo "<td class='text-left'>" . htmlspecialchars($instructor['ambiente']) . "</td>";
                            echo "<td class='text-left'>" . htmlspecialchars($instructor['estado_activo']) . "</td>";
                            echo "<td class='text-left'>" . htmlspecialchars($instructor['disponibilidad_prestamo']) . "</td>";
                            echo "<td class='action-buttons'>
                                    <a href='Gestion_Instructores.php?action=edit&id=" . urlencode($instructor['id_instructor']) . "' class='btn btn-edit' title='Editar'><i class='fas fa-edit'></i> Editar</a>
                                    <form action='Gestion_Instructores.php' method='POST' class='delete-form' style='display:inline-block; margin-left: 5px;'>
                                        <input type='hidden' name='action' value='delete'>
                                        <input type='hidden' name='id' value='" . htmlspecialchars($instructor['id_instructor']) . "'>
                                        <button type='submit' class='btn btn-delete' title='Eliminar'><i class='fas fa-trash-alt'></i> Eliminar</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No hay instructores registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div> </main>
    <script src="Js/Gestion_Instructores.js"></script>
    <script>
        <?php if ($es_modo_edicion): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Scroll suave al formulario de edición cuando se activa
                const formContainer = document.querySelector('.form-container');
                if (formContainer) {
                    formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    document.getElementById('nombre').focus();
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>