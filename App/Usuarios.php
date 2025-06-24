<?php
include("ProhibirAcceso.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if ($_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $id = intval($_POST['id_to_delete']);
    $rol = $_POST['rol_to_delete'];

    if ($rol === 'administrador') {
        $stmt = $conexion->prepare("DELETE FROM administradores WHERE id_administrador = ?");
    } elseif ($rol === 'almacenista') {
        $stmt = $conexion->prepare("DELETE FROM almacenistas WHERE id_almacenista = ?");
    }

    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: Usuarios.php");
    exit();
}

// Procesar edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {
    $id = intval($_POST['id']);
    $rol = $_POST['rol'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];

    if ($rol === 'administrador') {
        $stmt = $conexion->prepare("UPDATE administradores SET nombres=?, apellidos=?, correo=?, telefono=? WHERE id_administrador=?");
    } elseif ($rol === 'almacenista') {
        $stmt = $conexion->prepare("UPDATE almacenistas SET nombres=?, apellidos=?, correo=?, telefono=? WHERE id_almacenista=?");
    }

    if ($stmt) {
        $stmt->bind_param("ssssi", $nombres, $apellidos, $correo, $telefono, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: Usuarios.php");
    exit();
}

// Obtener usuario a editar si hay parámetro ?edit
$editar_usuario = null;
if (isset($_GET['edit']) && isset($_GET['rol'])) {
    $id = intval($_GET['edit']);
    $rol = $_GET['rol'];

    if ($rol === 'administrador') {
        $stmt = $conexion->prepare("SELECT id_administrador, nombres, apellidos, correo, telefono FROM administradores WHERE id_administrador = ?");
    } elseif ($rol === 'almacenista') {
        $stmt = $conexion->prepare("SELECT id_almacenista, nombres, apellidos, correo, telefono FROM almacenistas WHERE id_almacenista = ?");
    }

    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $editar_usuario = $resultado->fetch_assoc();
            $editar_usuario['rol'] = $rol;
        }
        $stmt->close();
    }
}

// Obtener administradores
$administradores = [];
$sql_admin = "SELECT id_administrador, nombres, apellidos, correo, telefono FROM administradores";
$result_admin = $conexion->query($sql_admin);
while ($row = $result_admin->fetch_assoc()) {
    $administradores[] = $row;
}

// Obtener almacenistas
$almacenistas = [];
$sql_almacenista = "SELECT id_almacenista, nombres, apellidos, correo, telefono FROM almacenistas";
$result_almacenista = $conexion->query($sql_almacenista);
while ($row = $result_almacenista->fetch_assoc()) {
    $almacenistas[] = $row;
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Usuarios | SENA</title>
<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="Css/Usuarios.css"> 
</head>
<body>
<header>
    <div class="header-left">
        <a href="Administrador.php" class="rgs"><i class="fas fa-reply"></i> Regresar</a>
    </div>
    <div class="header-center">
        <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Gestión de Usuarios</h1>
    </div>
    <div class="header-right">
        <a href="Registrar_Usuarios.php" class="btn btn-header-register"><i class="fas fa-user-plus"></i> Registrar Usuario</a>
    </div>
</header>

<div class="cc">
<div class="edicion">
    <?php if ($editar_usuario): ?>
    <h3>Editando Usuario: <span class="user"><?php echo ucfirst($editar_usuario['rol']); ?></span></h3>
    <form action="Usuarios.php" method="POST">
        <input type="hidden" name="action" value="update_user">
        <input type="hidden" name="id" value="<?php echo $editar_usuario['id_' . $editar_usuario['rol']]; ?>">
        <input type="hidden" name="rol" value="<?php echo $editar_usuario['rol']; ?>">

        <label>Nombres</label>
        <input type="text" name="nombres" value="<?php echo htmlspecialchars($editar_usuario['nombres']); ?>" required>
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="<?php echo htmlspecialchars($editar_usuario['apellidos']); ?>" required>
        <label>Correo</label>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($editar_usuario['correo']); ?>" required>
        <label>Teléfono</label>
        <input type="number" name="telefono" value="<?php echo htmlspecialchars($editar_usuario['telefono']); ?>" required>

        <button type="submit" id=""><i class="fas fa-save"></i> Actualizar</button>
        <a href="Usuarios.php" class="btn btn-cancel"><i class="fas fa-times"></i> Cancelar Edición</a>
    </form>
    <?php else: ?>
    <p>Selecciona un usuario para editar.</p>
    <?php endif; ?>
</div>
</div>

<hr class="separator"> 
<div class="tables-container">
    <div class="table-block">
        <h2 class="block-title">Administradores Registrados</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($administradores as $admin): ?>
                    <tr>
                        <td class='text-center'><?php echo htmlspecialchars($admin['id_administrador']); ?></td>
                        <td class='text-left'><?php echo htmlspecialchars($admin['nombres']); ?></td>
                        <td class='text-left'><?php echo htmlspecialchars($admin['apellidos']); ?></td>
                        <td class='text-left'><?php echo htmlspecialchars($admin['correo']); ?></td>
                        <td class='text-center'><?php echo htmlspecialchars($admin['telefono']); ?></td>
                        <td class='action-buttons'>
                            <a href='Usuarios.php?edit=<?php echo $admin['id_administrador']; ?>&rol=administrador' class='btn btn-edit'><i class='fas fa-edit'></i> Editar</a>
                            <form action='Usuarios.php' method='POST' class="inline-form" onsubmit='return confirm("¿Estás seguro que deseas eliminar este administrador?");'>
                                <input type='hidden' name='action' value='delete_user'>
                                <input type='hidden' name='id_to_delete' value='<?php echo $admin['id_administrador']; ?>'>
                                <input type='hidden' name='rol_to_delete' value='administrador'>
                                <button type='submit' class='btn btn-delete'><i class='fas fa-trash-alt'></i> Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($administradores)): ?>
                    <tr><td colspan='6' class='text-center'>No hay administradores registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-block" style="margin-top: var(--spacing-large);">
        <h2 class="block-title">Almacenistas Registrados</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($almacenistas as $almacenista): ?>
                    <tr>
                        <td class='text-center'><?php echo htmlspecialchars($almacenista['id_almacenista']); ?></td>
                        <td class='text-left'><?php echo htmlspecialchars($almacenista['nombres']); ?></td>
                        <td class='text-left'><?php echo htmlspecialchars($almacenista['apellidos']); ?></td>
                        <td class='text-left'><?php echo htmlspecialchars($almacenista['correo']); ?></td>
                        <td class='text-center'><?php echo htmlspecialchars($almacenista['telefono']); ?></td>
                        <td class='action-buttons'>
                            <a href='Usuarios.php?edit=<?php echo $almacenista['id_almacenista']; ?>&rol=almacenista' class='btn btn-edit'><i class='fas fa-edit'></i> Editar</a>
                            <form action='Usuarios.php' method='POST' class="inline-form" onsubmit='return confirm("¿Estás seguro que deseas eliminar este almacenista?");'>
                                <input type='hidden' name='action' value='delete_user'>
                                <input type='hidden' name='id_to_delete' value='<?php echo $almacenista['id_almacenista']; ?>'>
                                <input type='hidden' name='rol_to_delete' value='almacenista'>
                                <button type='submit' class='btn btn-delete'><i class='fas fa-trash-alt'></i> Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($almacenistas)): ?>
                    <tr><td colspan='6' class='text-center'>No hay almacenistas registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
