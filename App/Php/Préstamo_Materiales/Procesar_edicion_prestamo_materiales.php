<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

session_start();
if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id_prestamo_ref = filter_input(INPUT_POST, 'id_prestamo_material', FILTER_VALIDATE_INT);
$final_items_json = $_POST['final_items'] ?? '[]';
$items_to_add_json = $_POST['items_to_add'] ?? '[]';
$id_responsable = filter_input(INPUT_POST, 'id_responsable', FILTER_VALIDATE_INT);
$rol_responsable = htmlspecialchars($_POST['rol_responsable'] ?? '');

$final_items = json_decode($final_items_json, true);
$items_to_add = json_decode($items_to_add_json, true);

if (!$id_prestamo_ref || !$id_responsable || empty($rol_responsable)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para procesar la edición.']);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    if (!$conexion) {
        throw new Exception("Error de conexión a la base de datos.");
    }
    $conexion->begin_transaction();

    //Obtener datos originales y el ID del instructor
    $sql_get_original = "SELECT id_prestamo_material, id_material, cantidad, id_instructor FROM prestamo_materiales WHERE id_instructor = (SELECT id_instructor FROM prestamo_materiales WHERE id_prestamo_material = ? LIMIT 1) AND estado = 'pendiente'";
    $stmt_get_original = $conexion->prepare($sql_get_original);
    $stmt_get_original->bind_param("i", $id_prestamo_ref);
    $stmt_get_original->execute();
    $result_original = $stmt_get_original->get_result();
    $original_loans = [];
    $id_instructor = 0;
    while($row = $result_original->fetch_assoc()) {
        $original_loans[$row['id_material']] = $row;
        if($id_instructor == 0) $id_instructor = $row['id_instructor'];
    }
    $stmt_get_original->close();

    if ($id_instructor === 0) {
        throw new Exception("No se pudo determinar el instructor del préstamo.");
    }
    
    $responsable_nombre = $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'];

    //Procesar eliminaciones
    foreach ($original_loans as $material_id => $loan_data) {
        if (!array_key_exists($material_id, $final_items)) {
            // Se elimina la fila del préstamo
            $stmt_delete = $conexion->prepare("DELETE FROM prestamo_materiales WHERE id_prestamo_material = ?");
            $stmt_delete->bind_param("i", $loan_data['id_prestamo_material']);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            // Se devuelve el stock
            $stmt_update_stock = $conexion->prepare("UPDATE materiales SET stock = stock + ? WHERE id_material = ?");
            $stmt_update_stock->bind_param("ii", $loan_data['cantidad'], $material_id);
            $stmt_update_stock->execute();
            $stmt_update_stock->close();
        }
    }

    //Procesar adiciones
    $hay_nuevos_no_consumibles = false;
    foreach ($items_to_add as $item) {
        $material_id = intval($item['id_material']);
        $cantidad = intval($item['cantidad']);

        $stmt_stock = $conexion->prepare("SELECT stock, tipo, estado_material FROM materiales WHERE id_material = ?");
        $stmt_stock->bind_param("i", $material_id);
        $stmt_stock->execute();
        $material_info = $stmt_stock->get_result()->fetch_assoc();
        $stmt_stock->close();

        if (!$material_info || $material_info['stock'] < $cantidad) throw new Exception("Stock insuficiente para el material ID: $material_id");
        if ($material_info['estado_material'] !== 'disponible') throw new Exception("El material ID $material_id no está disponible para préstamo.");
        
        $estado = ($material_info['tipo'] === 'no consumible') ? 'pendiente' : 'consumido';
        $fecha_limite = ($material_info['tipo'] === 'no consumible') ? date('Y-m-d H:i:s', strtotime('+6 hours')) : null;
        if ($material_info['tipo'] === 'no consumible') $hay_nuevos_no_consumibles = true;

        $sql_insert = "INSERT INTO prestamo_materiales (id_material, id_instructor, id_responsable, rol_responsable, responsable, cantidad, fecha_prestamo, fecha_limite_devolucion, estado) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        $stmt_insert = $conexion->prepare($sql_insert);
        $stmt_insert->bind_param("iiississ", $material_id, $id_instructor, $id_responsable, $rol_responsable, $responsable_nombre, $cantidad, $fecha_limite, $estado);
        $stmt_insert->execute();
        $stmt_insert->close();

        $stmt_reduce_stock = $conexion->prepare("UPDATE materiales SET stock = stock - ? WHERE id_material = ?");
        $stmt_reduce_stock->bind_param("ii", $cantidad, $material_id);
        $stmt_reduce_stock->execute();
        $stmt_reduce_stock->close();
    }
    
    //disponibilidad del instructor
    $sql_check_final = "SELECT COUNT(*) as total_pendientes FROM prestamo_materiales WHERE id_instructor = ? AND estado = 'pendiente'";
    $stmt_check_final = $conexion->prepare($sql_check_final);
    $stmt_check_final->bind_param("i", $id_instructor);
    $stmt_check_final->execute();
    $conteo_final = $stmt_check_final->get_result()->fetch_assoc()['total_pendientes'];
    $stmt_check_final->close();
    
    $nuevo_estado_instructor = 'disponible';
    if ($conteo_final > 0) {
        $nuevo_estado_instructor = 'no_disponible';
    }

    $stmt_update_instructor = $conexion->prepare("UPDATE instructores SET disponibilidad_prestamo = ? WHERE id_instructor = ?");
    $stmt_update_instructor->bind_param("si", $nuevo_estado_instructor, $id_instructor);
    $stmt_update_instructor->execute();
    $stmt_update_instructor->close();

    $conexion->commit();
    $response['success'] = true;
    $response['message'] = '✅ Préstamo actualizado exitosamente.';

} catch (Exception $e) {
    if ($conexion && $conexion->in_transaction) $conexion->rollback();
    $response['message'] = '❌ Error al actualizar el préstamo: ' . $e->getMessage();
} finally {
    if ($conexion) $conexion->close();
}

echo json_encode($response);
?>