<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);



header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'overdue_items' => []];

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if ($conexion->connect_error) {
    $response['message'] = "Error de conexión a la base de datos: " . $conexion->connect_error;
    echo json_encode($response);
    exit();
}

try {
    $sql = "
        SELECT
            ped.id_prestamo_equipo_detalle,
            ped.id_prestamo_equipo,
            ped.fecha_vencimiento_item,
            pe.fecha_prestamo,
            CONCAT(e.marca, ' - ', e.serial) AS nombre_equipo,
            CONCAT(i.nombre, ' ', i.apellido) AS nombre_instructor
        FROM
            prestamo_equipos_detalle ped
        JOIN
            prestamo_equipos pe ON ped.id_prestamo_equipo = pe.id_prestamo_equipo
        JOIN
            equipos e ON ped.id_equipo = e.id_equipo
        JOIN
            instructores i ON pe.id_instructor = i.id_instructor
        WHERE
            ped.estado_item_prestamo = 'prestado' 
            AND ped.fecha_vencimiento_item < DATE_FORMAT(NOW(), '%Y-%m-%d %h:%i:%s %p')
        ORDER BY
            ped.fecha_vencimiento_item ASC;
    ";

    $resultado = $conexion->query($sql);

    if ($resultado) {
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $response['overdue_items'][] = $fila;
            }
            $response['message'] = 'Se encontraron ' . $resultado->num_rows . ' equipos con devolución vencida.';
        } else {
            $response['message'] = 'No hay equipos con devoluciones vencidas en este momento.';
        }
        $resultado->free(); 
        $response['success'] = true;
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $conexion->error);
    }

} catch (Exception $e) {
    $response['message'] = 'Error al obtener alertas de vencimiento: ' . $e->getMessage();
} finally {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    echo json_encode($response);
    exit();
}
?>