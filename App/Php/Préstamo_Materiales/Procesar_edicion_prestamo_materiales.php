<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}
$instructor_id = filter_input(INPUT_POST, 'instructor_id', FILTER_VALIDATE_INT);
$final_state_json = $_POST['final_state'] ?? '[]';
$final_state = json_decode($final_state_json, true);
$id_responsable = filter_input(INPUT_POST, 'id_responsable', FILTER_VALIDATE_INT);

if (!$instructor_id || !is_array($final_state) || !$id_responsable) {
    echo json_encode(['success' => false, 'message' => 'Datos de entrada inválidos para procesar la edición.']);
    exit();
}
try {
    $conexion->begin_transaction();
   //Obtener estado original de los préstamos pendientes NO CONSUMIBLES del instructor
    $stmt_orig = $conexion->prepare("SELECT pm.id_material, pm.cantidad FROM prestamo_materiales pm JOIN materiales m ON pm.id_material = m.id_material WHERE pm.id_instructor = ? AND pm.estado = 'pendiente' AND m.tipo = 'no consumible'");
    $stmt_orig->bind_param("i", $instructor_id);
    $stmt_orig->execute();
    $original_loans_raw = $stmt_orig->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_orig->close();
    
    $original_state = [];
    foreach($original_loans_raw as $loan) {
        $original_state[$loan['id_material']] = $loan['cantidad'];
    }

    //Borrar todos los préstamos pendientes NO CONSUMIBLES ANTERIORES del instructor
    $stmt_delete = $conexion->prepare("DELETE pm FROM prestamo_materiales pm JOIN materiales m ON pm.id_material = m.id_material WHERE pm.id_instructor = ? AND pm.estado = 'pendiente' AND m.tipo = 'no consumible'");
    $stmt_delete->bind_param("i", $instructor_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    
    //Procesar el nuevo estado y calcular el stock
    $stock_deltas = [];
    $new_state_map = [];
    foreach ($final_state as $item) {
        $new_state_map[intval($item['id_material'])] = intval($item['cantidad']);
    }

    $all_material_ids = array_unique(array_merge(array_keys($original_state), array_keys($new_state_map)));

    foreach($all_material_ids as $id) {
        $old_qty = $original_state[$id] ?? 0;
        $new_qty = $new_state_map[$id] ?? 0;
        $delta = $old_qty - $new_qty; // Positivo si se devuelve al stock, negativo si se saca del stock.
        if ($delta != 0) {
            $stock_deltas[$id] = $delta;
        }
    }
    
    //Validar stock y Re-insertar los préstamos según el estado final
    foreach ($final_state as $item) {
        $id = intval($item['id_material']);
        $new_qty = intval($item['cantidad']);
        
        if ($new_qty > 0) {
            // Validar stock antes de insertar
            $qty_needed = $new_qty - ($original_state[$id] ?? 0);
            if ($qty_needed > 0) {
                 $stmt_check_stock = $conexion->prepare("SELECT nombre, stock FROM materiales WHERE id_material = ?");
                 $stmt_check_stock->bind_param("i", $id);
                 $stmt_check_stock->execute();
                 $material_info = $stmt_check_stock->get_result()->fetch_assoc();
                 $stmt_check_stock->close();
                 if (!$material_info || $material_info['stock'] < $qty_needed) {
                    throw new Exception("Stock insuficiente para '{$material_info['nombre']}'. Se necesitan {$qty_needed} y solo hay {$material_info['stock']}.");
                 }
            }

            // Insertar la nueva línea de préstamo
            $stmt_insert = $conexion->prepare("INSERT INTO prestamo_materiales (id_material, id_instructor, id_responsable, rol_responsable, responsable, cantidad, fecha_prestamo, fecha_limite_devolucion, estado) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 'pendiente')");
            $fecha_limite = date('Y-m-d h:i:s A', strtotime('+6 hours'));
            $responsable_nombre = $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'];
            $stmt_insert->bind_param("iiissis", $id, $instructor_id, $id_responsable, $_SESSION['rol'], $responsable_nombre, $new_qty, $fecha_limite);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
    }

    //ajustes de stock
    foreach($stock_deltas as $id => $delta) {
        $stmt_stock = $conexion->prepare("UPDATE materiales SET stock = stock + ? WHERE id_material = ?");
        $stmt_stock->bind_param("ii", $delta, $id);
        $stmt_stock->execute();
        $stmt_stock->close();
    }
    //Actualizar estado final de disponibilidad del instructor
    $nuevo_estado_instructor = empty($final_state) ? 'disponible' : 'no_disponible';
    $stmt_update_inst = $conexion->prepare("UPDATE instructores SET disponibilidad_prestamo = ? WHERE id_instructor = ?");
    $stmt_update_inst->bind_param("si", $nuevo_estado_instructor, $instructor_id);
    $stmt_update_inst->execute();
    $stmt_update_inst->close();

    $conexion->commit();
    echo json_encode(['success' => true, 'message' => '✅ Préstamo actualizado correctamente.']);

} catch (Exception $e) {
    if ($conexion->in_transaction) {
        $conexion->rollback();
    }
    echo json_encode(['success' => false, 'message' => '❌ Error: ' . $e->getMessage()]);
} finally {
    if (isset($conexion)) {
        $conexion->close();
    }
}
?>