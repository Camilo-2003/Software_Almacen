<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "almacen");
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetEquipos($conn);
        break;
    case 'POST':
        handlePostEquipo($conn); 
        break;
    case 'PUT':
        handlePutEquipo($conn);
        break;
    case 'DELETE':
        handleDeleteEquipo($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not supported']);
        exit;
}
function handleGetEquipos($conn) {
    $sql = "SELECT * FROM equipos";
    $result = $conn->query($sql);

    $equipos = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $equipos[] = $row;
        }
    }

    echo json_encode($equipos);
    exit; 
}
function handlePostEquipo($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $marca = $data['marca'];
    $serial = $data['serial'];
    $estado = $data['estado'];

    $check_sql = "SELECT COUNT(*) FROM equipos WHERE serial = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de verificación: ' . $conn->error]);
        exit;
    }
    $check_stmt->bind_param("s", $serial);
    $check_stmt->execute();
    $count = 0;
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        http_response_code(409);
        echo json_encode(['message' => 'Error: Ya existe un equipo con este número de serie.']);
        exit;
    }

    $sql = "INSERT INTO equipos (marca, serial, estado) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de inserción: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("sss", $marca, $serial, $estado);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Equipo creado con éxito', 'id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        if ($conn->errno == 1062) {
            echo json_encode(['message' => 'Error: El número de serie ya existe.']);
        } else {
            echo json_encode(['message' => 'Error al crear equipo: ' . $stmt->error]);
        }
    }
    $stmt->close();
    exit; 
}
function handlePutEquipo($conn) {
    $id_equipo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_equipo === 0) {
        http_response_code(400);
        echo json_encode(['message' => 'ID de equipo no proporcionado para la actualización.']);
        exit; 
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $marca = $data['marca'];
    $serial = $data['serial'];
    $estado = $data['estado'];

    $check_sql = "SELECT COUNT(*) FROM equipos WHERE serial = ? AND id_equipo != ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de verificación: ' . $conn->error]);
        exit;
    }
    $check_stmt->bind_param("si", $serial, $id_equipo);
    $check_stmt->execute();
    $count = 0;
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        http_response_code(409);
        echo json_encode(['message' => 'Error: Ya existe otro equipo con este número de serie.']);
        exit; 
    }

    $sql = "UPDATE equipos SET marca = ?, serial = ?, estado = ? WHERE id_equipo = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de actualización: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("sssi", $marca, $serial, $estado, $id_equipo);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Equipo actualizado con éxito']);
        } else {
            echo json_encode(['message' => 'Equipo no encontrado o no se realizaron cambios.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al actualizar equipo: ' . $stmt->error]);
    }
    $stmt->close();
    exit; 
}

function handleDeleteEquipo($conn) {
    $id_equipo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_equipo === 0) {
        http_response_code(400);
        echo json_encode(['message' => 'ID de equipo no proporcionado para la eliminación.']);
        exit; 
    }

    $sql = "DELETE FROM equipos WHERE id_equipo = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al preparar la consulta de eliminación: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id_equipo);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['message' => 'Equipo eliminado con éxito']);
        } else {
            echo json_encode(['message' => 'Equipo no encontrado.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al eliminar equipo: ' . $stmt->error]);
    }
    $stmt->close();
    exit; 
}

$conn->close(); 
?>