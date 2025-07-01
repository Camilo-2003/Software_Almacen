<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

header('Content-Type: application/json');
$response = ['success' => false, 'loan_details' => [], 'available_materials' => []];

$id_prestamo_ref = filter_input(INPUT_GET, 'id_prestamo_material', FILTER_VALIDATE_INT);
if (!$id_prestamo_ref) {
    $response['message'] = 'ID de préstamo de referencia no válido.';
    echo json_encode($response);
    exit();
}

try {
    // 1. Obtener detalles de todos los préstamos pendientes para el instructor asociado a este préstamo
    $sql_loan = "
        SELECT 
            pm.id_material, 
            m.nombre, 
            pm.cantidad, 
            m.stock AS stock_en_bodega
        FROM prestamo_materiales pm 
        JOIN materiales m ON pm.id_material = m.id_material 
        WHERE pm.id_instructor = (SELECT id_instructor FROM prestamo_materiales WHERE id_prestamo_material = ? LIMIT 1) 
        AND pm.estado = 'pendiente' AND m.tipo = 'no consumible'";
    
    $stmt_loan = $conexion->prepare($sql_loan);
    $stmt_loan->bind_param("i", $id_prestamo_ref);
    $stmt_loan->execute();
    $result_loan = $stmt_loan->get_result();
    while ($row = $result_loan->fetch_assoc()) {
        $response['loan_details'][] = $row;
    }
    $stmt_loan->close();

    // 2. Obtener TODOS los materiales no consumibles que se pueden prestar (con stock > 0)
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

echo json_encode($response);
?>