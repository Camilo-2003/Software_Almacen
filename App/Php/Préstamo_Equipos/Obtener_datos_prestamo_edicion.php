<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'loan_header' => null, 'loan_details' => [], 'available_equipment' => []];

if (!isset($conexion) || $conexion->connect_error) {
    $response['message'] = 'Error de conexión a la base de datos.';
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    $response['message'] = 'Acceso no válido (método incorrecto).';
    echo json_encode($response);
    exit();
}

$id_prestamo_equipo = isset($_GET['id_prestamo_equipo']) ? intval($_GET['id_prestamo_equipo']) : 0;

if ($id_prestamo_equipo <= 0) {
    $response['message'] = 'ID de préstamo no proporcionado o inválido.';
    echo json_encode($response);
    exit();
}

try {
    //Obtener datos de la cabecera del préstamo
    $stmt_header = $conexion->prepare("SELECT pe.id_prestamo_equipo, CONCAT(i.nombre, ' ', i.apellido) AS instructor_nombre, pe.fecha_prestamo FROM prestamo_equipos pe JOIN instructores i ON pe.id_instructor = i.id_instructor WHERE pe.id_prestamo_equipo = ?");
    if (!$stmt_header) { throw new Exception("Error preparando stmt_header: " . $conexion->error); }
    $stmt_header->bind_param("i", $id_prestamo_equipo);
    $stmt_header->execute();
    $result_header = $stmt_header->get_result();
    $response['loan_header'] = $result_header->fetch_assoc();
    $stmt_header->close();

    if (!$response['loan_header']) {
        throw new Exception("Préstamo no encontrado.");
    }

    // Obtener detalles del préstamo (equipos ya asignados a este préstamo)
    $stmt_details = $conexion->prepare("
        SELECT 
            ped.id_prestamo_equipo_detalle, 
            e.id_equipo,
            CONCAT(e.marca, ' - ', e.serial) AS equipo_marca_serial,
            ped.estado_item_prestamo
        FROM prestamo_equipos_detalle ped
        JOIN equipos e ON ped.id_equipo = e.id_equipo
        WHERE ped.id_prestamo_equipo = ?
        ORDER BY ped.estado_item_prestamo DESC, equipo_marca_serial ASC
    ");
    if (!$stmt_details) { throw new Exception("Error preparando stmt_details: " . $conexion->error); }
    $stmt_details->bind_param("i", $id_prestamo_equipo);
    $stmt_details->execute();
    $result_details = $stmt_details->get_result();
    while ($row = $result_details->fetch_assoc()) {
        $response['loan_details'][] = $row;
    }
    $stmt_details->close();

    // Obtener equipos disponibles para añadir
    $sql_available_equipment = "
        SELECT id_equipo, marca, serial 
        FROM equipos 
        WHERE estado = 'disponible' 
        ORDER BY marca, serial
    ";
    $stmt_available = $conexion->prepare($sql_available_equipment);
    if (!$stmt_available) { throw new Exception("Error preparando stmt_available: " . $conexion->error); }
    $stmt_available->execute();
    $result_available = $stmt_available->get_result();
        
    while ($row = $result_available->fetch_assoc()) {
        $response['available_equipment'][] = $row;
    }
    $stmt_available->close();
    
    $response['success'] = true;
    $response['message'] = 'Datos del préstamo obtenidos exitosamente.';

} catch (Exception $e) {
    $response['message'] = '❌ Error al obtener datos del préstamo: ' . $e->getMessage();
} finally {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    echo json_encode($response);
    exit();
}
?>
