<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json');
// Añadir 'total_equipos_disponibles' a la respuesta
$response = ['success' => false, 'message' => '', 'equipos' => [], 'instructores' => [], 'total_equipos_disponibles' => 0];

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

if ($conexion->connect_error) {
    $response['message'] = "Error de conexión a la base de datos: " . $conexion->connect_error;
    echo json_encode($response);
    exit();
}

try {
    // 1. Obtener equipos actualmente disponibles
    $sqlEquiposDisponibles = "SELECT id_equipo, marca, serial FROM equipos WHERE estado = 'disponible' ORDER BY marca, serial";
    $resultadoEquiposDropdown = $conexion->query($sqlEquiposDisponibles);
    
    if ($resultadoEquiposDropdown) {
        while ($equipo = $resultadoEquiposDropdown->fetch_assoc()) {
            $response['equipos'][] = $equipo;
        }
        // Contar el número de equipos disponibles
        $response['total_equipos_disponibles'] = count($response['equipos']);
        $resultadoEquiposDropdown->free(); // Liberar el conjunto de resultados
    } else {
        error_log("Error al obtener equipos disponibles: " . $conexion->error);
    }

    //Obtener instructores activos y disponibles para préstamo
    $sqlInstructores = "SELECT id_instructor, nombre, apellido FROM instructores WHERE estado_activo = 'activo' AND disponibilidad_prestamo = 'disponible' ORDER BY nombre, apellido";
    $resultadoInstructores = $conexion->query($sqlInstructores);
    if ($resultadoInstructores) {
        while ($instructor = $resultadoInstructores->fetch_assoc()) {
            $response['instructores'][] = $instructor;
        }
        $resultadoInstructores->free(); 
    } else {
        error_log("Error al obtener instructores disponibles: " . $conexion->error);
    }

    $response['success'] = true;
    $response['message'] = 'Datos de formulario cargados exitosamente.';
} catch (Exception $e) {
    $response['message'] = 'Error al cargar datos del formulario: ' . $e->getMessage();
} finally {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    echo json_encode($response);
    exit();
}
?>
