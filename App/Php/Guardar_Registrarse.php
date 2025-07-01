<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

function sendJsonResponse($message, $type) {
    echo json_encode(['message' => $message, 'type' => $type]);
    exit(); 
}
//Garantiza que las variables tengan un valor 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rol = $_POST['rol'] ?? ''; 
    $nombres = trim($_POST["nombre"] ?? '');
    $apellidos = trim($_POST["apellido"] ?? '');
    $correo = trim($_POST["correo"] ?? '');
    $telefono = trim($_POST["telefono"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $password_hashed = password_hash($password, PASSWORD_DEFAULT); // Se encriptar la contraseña

    if (!in_array($rol, ['almacenista', 'administrador'])) {
        sendJsonResponse('🚨 Rol no válido.', 'error');
        exit();
    }
    $nombre_tabla = '';
    $id_columna = '';

    if ($rol === 'almacenista') {
        $nombre_tabla = 'almacenistas';
        $id_columna = 'id_almacenista';
    } elseif ($rol === 'administrador') {
        $nombre_tabla = 'administradores';
        $id_columna = 'id_administrador';
    }
    $sql_check = "SELECT $id_columna FROM $nombre_tabla WHERE correo = ?";
    $stmt_check = $conexion->prepare($sql_check);
    if (!$stmt_check) {
        sendJsonResponse('🚨 Error en la preparación de la consulta de verificación: ' . $conexion->error, 'error');
        $conexion->close();
        exit();
    }
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        sendJsonResponse('⚠️ El correo ya está registrado. Usa otro.', 'error');
        $stmt_check->close();
        $conexion->close();
        exit();
    }
    $stmt_check->close();

    $sql_insertar = "INSERT INTO $nombre_tabla (nombres, apellidos, correo, telefono, password) VALUES (?, ?, ?, ?, ?)";
    $stmt_insertar = $conexion->prepare($sql_insertar);
    if (!$stmt_insertar) {
        sendJsonResponse('🚨 Error en la preparación de la consulta de inserción: ' . $conexion->error, 'error');
        $conexion->close();
        exit();
    }
    $stmt_insertar->bind_param("sssss", $nombres, $apellidos, $correo, $telefono, $password_hashed);

    if ($stmt_insertar->execute()) {
        sendJsonResponse('✅ Registro exitoso. Este usuario ya puede acceder al sistema.', 'success');
    } else {
        sendJsonResponse('🚨 Error al registrar. Inténtalo de nuevo: ' . $stmt_insertar->error, 'error');
    }

    $stmt_insertar->close();
    $conexion->close();
    exit();
} else {
    sendJsonResponse('Acceso no autorizado. Por favor, envía el formulario correctamente.', 'error');
    exit();
}
?>