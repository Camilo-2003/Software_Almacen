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
     $response['message'] = 'No tiene permisos para eliminar.';
     echo json_encode($response);
     exit;
}

$id_novedad = $_POST['id_novedad2'] ?? 0;
if (empty($id_novedad)) {
    $response['message'] = 'ID de novedad no proporcionado.';
    echo json_encode($response);
    exit;
}

$conexion->begin_transaction();

try {
    // 1. Obtener la ruta de la imagen antes de borrar el registro
    $stmt_select = $conexion->prepare("SELECT ruta_imagen FROM novedades2 WHERE id_novedad2 = ?");
    $stmt_select->bind_param("i", $id_novedad);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $novedad = $result->fetch_assoc();
    $stmt_select->close();

    // 2. Eliminar el registro de la base de datos
    $stmt_delete = $conexion->prepare("DELETE FROM novedades2 WHERE id_novedad2 = ?");
    $stmt_delete->bind_param("i", $id_novedad);
    $stmt_delete->execute();

    if ($stmt_delete->affected_rows > 0) {
        // 3. Si se borró de la BD, eliminar el archivo físico
        if ($novedad && !empty($novedad['ruta_imagen'])) {
            $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $novedad['ruta_imagen'];
            if (file_exists($ruta_fisica)) {
                unlink($ruta_fisica);
            }
        }
        $conexion->commit();
        $response['success'] = true;
        $response['message'] = 'Novedad eliminada correctamente.';
    } else {
        throw new Exception('No se encontró la novedad o ya fue eliminada.');
    }
    $stmt_delete->close();

} catch (Exception $e) {
    $conexion->rollback();
    $response['message'] = 'Error: ' . $e->getMessage();
}

$conexion->close();
echo json_encode($response);
?>