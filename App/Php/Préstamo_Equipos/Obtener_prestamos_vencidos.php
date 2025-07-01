<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_errors.log');

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

$response = ['success' => false, 'message' => '', 'overdue_items' => []];

if ($conexion->connect_error) {
    $response['message'] = "Error de conexión a la base de datos: " . $conexion->connect_error;
    echo json_encode($response);
    exit();
}

try {
    $conexion->query("SET time_zone = 'America/Bogota';");

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
            AND ped.fecha_vencimiento_item < NOW()
        ORDER BY
            ped.fecha_vencimiento_item ASC;
    ";

    $current_time = $conexion->query("SELECT NOW() AS now")->fetch_assoc()['now'];

    $resultado = $conexion->query($sql);

    if ($resultado) {
        if ($resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $response['overdue_items'][] = $fila;
                error_log("Overdue item found: ID {$fila['id_prestamo_equipo_detalle']}, Equipo: {$fila['nombre_equipo']}, Vencimiento: {$fila['fecha_vencimiento_item']}");
            }
            $response['success'] = true;
            $response['message'] = 'Se encontraron ' . $resultado->num_rows . ' equipos con devolución vencida.';
        } else {
            $response['message'] = 'No hay equipos con devoluciones vencidas en este momento.';
        }
        $resultado->free();
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
}
?>