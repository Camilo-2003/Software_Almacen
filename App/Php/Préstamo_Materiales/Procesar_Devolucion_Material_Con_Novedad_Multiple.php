<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

$response = ['success' => false, 'message' => ''];

//Validación de Sesión 
$id_responsable_registro = 0;
$rol_responsable_registro = '';
$nombre_responsable_registro = '';
if (isset($_SESSION['rol'])) {
    $rol_responsable_registro = $_SESSION['rol'];
    $nombre_responsable_registro = trim((isset($_SESSION["nombres"]) ? $_SESSION["nombres"] : '') . ' ' . (isset($_SESSION["apellidos"]) ? $_SESSION["apellidos"] : ''));
    if ($rol_responsable_registro === 'almacenista' && isset($_SESSION['id_almacenista'])) {
        $id_responsable_registro = $_SESSION['id_almacenista'];
    } elseif ($rol_responsable_registro === 'administrador' && isset($_SESSION['id_administrador'])) {
        $id_responsable_registro = $_SESSION['id_administrador'];
    }
}
if ($id_responsable_registro === 0) {
    $response['message'] = 'Acceso denegado. Su sesión ha expirado.';
    echo json_encode($response);
    exit();
}

$conexion->begin_transaction();

try {
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!isset($datos['items']) || !is_array($datos['items']) || empty($datos['items']) || !isset($datos['tipo_novedad']) || !isset($datos['descripcion'])) {
        throw new Exception('Datos de entrada inválidos.');
    }

    $tipo_novedad_general = $conexion->real_escape_string($datos['tipo_novedad']);
    $descripcion_general = $conexion->real_escape_string($datos['descripcion']);
    $items_a_procesar = $datos['items'];
    $condicion_devolucion = ($tipo_novedad_general === 'malo') ? 'malo' : 'deteriorado';

foreach ($items_a_procesar as $item) {
        $id_prestamo_material = intval($item['id_prestamo_material']);
        
        $stmt_info = $conexion->prepare("SELECT pm.id_material, pm.id_instructor, pm.cantidad, m.nombre AS nombre_material, CONCAT(i.nombre, ' ', i.apellido) AS nombre_instructor FROM prestamo_materiales pm JOIN materiales m ON pm.id_material = m.id_material JOIN instructores i ON pm.id_instructor = i.id_instructor WHERE pm.id_prestamo_material = ? AND pm.estado = 'pendiente'");
        $stmt_info->bind_param("i", $id_prestamo_material);
        $stmt_info->execute();
        $info = $stmt_info->get_result()->fetch_assoc();
        if (!$info) continue;
        $stmt_info->close();
        
        $cantidad_a_devolver = $info['cantidad'];

        // Insertar en novedades2 
        $stmt_novedad = $conexion->prepare("INSERT INTO novedades2 (tipo_elemento, id_prestamo_material, id_material, nombre_material, id_instructor, nombre_instructor, tipo_novedad, descripcion, id_responsable_registro, nombre_responsable_registro, rol_responsable_registro) VALUES ('material', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_novedad->bind_param("iisssssiss", $id_prestamo_material, $info['id_material'], $info['nombre_material'], $info['id_instructor'], $info['nombre_instructor'], $tipo_novedad_general, $descripcion_general, $id_responsable_registro, $nombre_responsable_registro, $rol_responsable_registro);
        $stmt_novedad->execute();
        $stmt_novedad->close();

        //Registrar en devolucion_materiales 
        $stmt_devolucion = $conexion->prepare("INSERT INTO devolucion_materiales (id_prestamo_material, fecha_devolucion, observaciones, estado_devolucion, id_responsable, rol_responsable, responsable, cantidad) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)");
        $stmt_devolucion->bind_param("issisisi", $id_prestamo_material, $descripcion_general, $condicion_devolucion, $id_responsable_registro, $rol_responsable_registro, $nombre_responsable_registro, $cantidad_a_devolver);
        $stmt_devolucion->execute();
        $stmt_devolucion->close();

        //Actualizar el préstamo original 
        //Se marca como 'devuelto' y la cantidad se pone a 0.
        $stmt_update_prestamo = $conexion->prepare("UPDATE prestamo_materiales SET estado = 'devuelto', cantidad = 0 WHERE id_prestamo_material = ?");
        $stmt_update_prestamo->bind_param("i", $id_prestamo_material);
        $stmt_update_prestamo->execute();
        $stmt_update_prestamo->close();

        // Lógica de Stock 
        if ($condicion_devolucion === 'malo' || $condicion_devolucion === 'deteriorado') {
            $stmt_stock = $conexion->prepare("UPDATE materiales SET stock = stock + ?, estado_material = 'en_revision' WHERE id_material = ?");
            $stmt_stock->bind_param("ii", $cantidad_a_devolver, $info['id_material']);
        } else {
            $stmt_stock = $conexion->prepare("UPDATE materiales SET stock = stock + ? WHERE id_material = ?");
            $stmt_stock->bind_param("ii", $cantidad_a_devolver, $info['id_material']);
        }
        $stmt_stock->execute();
        $stmt_stock->close();
    }

    $conexion->commit();
    $response['success'] = true;
    $response['message'] = '✅ Novedades de materiales registradas y devueltas exitosamente.';

} catch (Exception $e) {
    $conexion->rollback();
    $response['message'] = '❌ Error: ' . $e->getMessage();
}

echo json_encode($response);
$conexion->close();
?>