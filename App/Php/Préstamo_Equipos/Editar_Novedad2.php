<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit;
}

// Aquí podrías añadir validación de rol (ej. solo administradores)
if ($_SESSION['rol'] !== 'administrador') {
     $response['message'] = 'No tiene permisos para editar.';
     echo json_encode($response);
     exit;
}

$id_novedad = $_POST['id_novedad2'] ?? 0;
$tipo = $_POST['tipo_novedad'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

if (empty($id_novedad) || empty($tipo) || empty($descripcion)) {
    $response['message'] = 'Todos los campos son requeridos.';
    echo json_encode($response);
    exit;
}

$stmt = $conexion->prepare("UPDATE novedades2 SET tipo_novedad = ?, descripcion = ? WHERE id_novedad2 = ?");
$stmt->bind_param("ssi", $tipo, $descripcion, $id_novedad);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Novedad actualizada correctamente.';
} else {
    $response['message'] = 'Error al actualizar la novedad: ' . $stmt->error;
}

$stmt->close();
$conexion->close();
echo json_encode($response);
?>