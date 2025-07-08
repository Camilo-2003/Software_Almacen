<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}
header('Content-Type: application/json');

$response = ['success' => false, 'overdue_items' => []];

if ($conexion->connect_error) {
    $response['message'] = "Error de conexión a la base de datos: " . $conexion->connect_error;
    echo json_encode($response);
    exit();
}

try {
    $conexion->query("SET time_zone = 'America/Bogota';");

    $sql = "SELECT 
                m.nombre as nombre_material, 
                CONCAT(i.nombre, ' ', i.apellido) as nombre_instructor,
                pm.fecha_limite_devolucion
            FROM prestamo_materiales pm
            JOIN materiales m ON pm.id_material = m.id_material
            JOIN instructores i ON pm.id_instructor = i.id_instructor
            WHERE pm.estado = 'pendiente' AND pm.fecha_limite_devolucion IS NOT NULL AND pm.fecha_limite_devolucion < NOW()";

    $current_time = $conexion->query("SELECT NOW() AS now")->fetch_assoc()['now'];
    error_log("Current time for overdue check: " . $current_time);

    $resultado = $conexion->query($sql);
    $vencidos = [];
    if ($resultado) {
        while ($row = $resultado->fetch_assoc()) {
            $vencidos[] = $row;
            error_log("Overdue material found: {$row['nombre_material']}, Vencimiento: {$row['fecha_limite_devolucion']}");
        }
        $response['success'] = true;
        $response['overdue_items'] = $vencidos;
        $response['message'] = 'Se encontraron ' . count($vencidos) . ' materiales con devolución vencida.';
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $conexion->error);
    }
} catch (Exception $e) {
    $response['message'] = 'Error al obtener alertas de vencimiento: ' . $e->getMessage();
} finally {
    if ($conexion) $conexion->close();
    echo json_encode($response);
}
?>