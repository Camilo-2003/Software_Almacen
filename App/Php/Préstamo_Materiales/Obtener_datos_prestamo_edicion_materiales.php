<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json');
$response = ['success' => false, 'loan_details' => [], 'available_materials' => [], 'message' => ''];

if (!isset($_GET['id_prestamo_material']) || !filter_input(INPUT_GET, 'id_prestamo_material', FILTER_VALIDATE_INT)) {
    $response['message'] = 'ID de préstamo no válido.';
    echo json_encode($response);
    exit();
}

$id_prestamo_material_ref = $_GET['id_prestamo_material'];

try {
    // Obtener todos los préstamos pendientes para el instructor asociado con el id de referencia
    $sql_loan = "
        SELECT pm.id_prestamo_material, pm.id_material, m.nombre, pm.cantidad 
        FROM prestamo_materiales pm 
        JOIN materiales m ON pm.id_material = m.id_material 
        WHERE pm.id_instructor = (SELECT id_instructor FROM prestamo_materiales WHERE id_prestamo_material = ? LIMIT 1) 
        AND pm.estado = 'pendiente'
        AND m.tipo = 'no consumible'
    ";
    $stmt_loan = $conexion->prepare($sql_loan);
    $stmt_loan->bind_param("i", $id_prestamo_material_ref);
    $stmt_loan->execute();
    $result_loan = $stmt_loan->get_result();
    while ($row = $result_loan->fetch_assoc()) {
        $response['loan_details'][] = $row;
    }
    $stmt_loan->close();

    // Obtener todos los materiales no consumibles que estén disponibles para préstamo
    $sql_available = "SELECT id_material, nombre, stock FROM materiales WHERE stock > 0 AND estado_material = 'disponible' AND tipo = 'no consumible' ORDER BY nombre";
    $stmt_available = $conexion->prepare($sql_available);
    $stmt_available->execute();
    $result_available = $stmt_available->get_result();
    while ($row = $result_available->fetch_assoc()) {
        $response['available_materials'][] = $row;
    }
    $stmt_available->close();

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = 'Error de servidor: ' . $e->getMessage();
}

$conexion->close();
echo json_encode($response);
?>