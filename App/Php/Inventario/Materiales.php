<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "almacen");
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetMateriales($conn);
        break;
    case 'POST':
        handlePostMaterial($conn);
        break;
    case 'PUT':
        handlePutMaterial($conn);
        break;
    case 'DELETE':
        handleDeleteMaterial($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not supported']);
        exit;
}
function handleGetMateriales($conn) {
    $sql = "SELECT id_material, nombre, tipo, stock, estado_material FROM materiales";
    $result = $conn->query($sql);

    $materiales = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $materiales[] = $row;
        }
    } elseif ($result === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al obtener materiales: ' . $conn->error]);
        exit;
    }
    echo json_encode($materiales);
    exit; 
}
function handlePostMaterial($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = trim($data['nombre']);
    $tipo = $data['tipo'];
    $stock = $data['stock'];
    $estado_material = $data['estado_material'];

    $check_sql = "SELECT COUNT(*) FROM materiales WHERE nombre = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de verificación: ' . $conn->error]);
        exit;
    }
    $check_stmt->bind_param("s", $nombre);
    $check_stmt->execute();
    $count = 0;
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        http_response_code(409); 
        echo json_encode(['message' => 'Error: Ya existe un material con este nombre.']);
        exit;
    }
    $sql = "INSERT INTO materiales (nombre, tipo, stock, estado_material) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de inserción: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssis", $nombre, $tipo, $stock, $estado_material);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Material creado con éxito', 'id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al crear material: ' . $stmt->error]);
    }
    $stmt->close();
    exit; 
}
function handlePutMaterial($conn) {
    $id_material = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_material === 0) {
        http_response_code(400); 
        echo json_encode(['message' => 'ID de material no proporcionado para la actualización.']);
        exit; 
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = trim($data['nombre']); 
    $tipo = $data['tipo'];
    $stock = $data['stock'];
    $estado_material = $data['estado_material'];

    $check_sql = "SELECT COUNT(*) FROM materiales WHERE nombre = ? AND id_material != ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de verificación: ' . $conn->error]);
        exit;
    }
    $check_stmt->bind_param("si", $nombre, $id_material);
    $check_stmt->execute();
    $count = 0;
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        http_response_code(409); 
        echo json_encode(['message' => 'Error: Ya existe otro material con este nombre.']);
        exit;
    }
    $sql = "UPDATE materiales SET nombre = ?, tipo = ?, stock = ?, estado_material = ? WHERE id_material = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de actualización: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssisi", $nombre, $tipo, $stock, $estado_material, $id_material);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Material actualizado con éxito']);
        } else {
            echo json_encode(['message' => 'Material no encontrado o no se realizaron cambios.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al actualizar material: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}
function handleDeleteMaterial($conn) {
    $id_material = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_material === 0) {
        http_response_code(400); 
        echo json_encode(['message' => 'ID de material no proporcionado para la eliminación.']);
        exit; 
    }

    $sql = "DELETE FROM materiales WHERE id_material = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de eliminación: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id_material);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Material eliminado con éxito']);
        } else {
            echo json_encode(['message' => 'Material no encontrado.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al eliminar material: ' . $stmt->error]);
    }
    $stmt->close();
    exit; 
}
$conn->close(); 
?>