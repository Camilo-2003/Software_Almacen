<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Bogota'); 

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

session_start();

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

$filename = 'Historial_Sesiones_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

$delimiter = ';'; 

fputcsv($output, [
    'ID Registro',
    'ID Usuario',
    'Tipo Usuario',
    'Nombre Completo',
    'Correo Electronico', 
    'Fecha Ingreso',  
    'Hora Ingreso',    
    'Fecha Salida',    
    'Hora Salida'       
], $delimiter); 

$sql = "SELECT 
            hs.id_registro,
            hs.id_usuario,
            hs.tipo_usuario,
            DATE_FORMAT(hs.hora_ingreso, '%Y-%m-%d') AS fecha_ingreso_formateada,   
            DATE_FORMAT(hs.hora_ingreso, '%H:%i:%s') AS hora_ingreso_formateada,   
            DATE_FORMAT(hs.hora_salida, '%Y-%m-%d') AS fecha_salida_formateada,    
            DATE_FORMAT(hs.hora_salida, '%H:%i:%s') AS hora_salida_formateada,      
            CASE 
                WHEN hs.tipo_usuario = 'almacenista' THEN a.nombres
                WHEN hs.tipo_usuario = 'administrador' THEN ad.nombres
                ELSE 'Usuario Desconocido'
            END AS nombres_usuario,
            CASE 
                WHEN hs.tipo_usuario = 'almacenista' THEN a.apellidos
                WHEN hs.tipo_usuario = 'administrador' THEN ad.apellidos
                ELSE ''
            END AS apellidos_usuario,
            CASE
                WHEN hs.tipo_usuario = 'almacenista' THEN a.correo
                WHEN hs.tipo_usuario = 'administrador' THEN ad.correo
                ELSE ''
            END AS correo_usuario
        FROM historial_sesiones hs
        LEFT JOIN almacenistas a ON hs.id_usuario = a.id_almacenista AND hs.tipo_usuario = 'almacenista'
        LEFT JOIN administradores ad ON hs.id_usuario = ad.id_administrador AND hs.tipo_usuario = 'administrador'
        ORDER BY hs.hora_ingreso DESC"; 

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $nombre_completo = $fila['nombres_usuario'] . ' ' . $fila['apellidos_usuario'];
        
        $fecha_salida_csv = $fila['fecha_salida_formateada'];
        $hora_salida_csv = $fila['hora_salida_formateada'];

        if (empty($fila['fecha_salida_formateada'])) {
            $fecha_salida_csv = 'Activo'; 
            $hora_salida_csv = 'Activo'; 
        }
        
        fputcsv($output, [
            $fila['id_registro'],
            $fila['id_usuario'],
            ucfirst($fila['tipo_usuario']), 
            $nombre_completo,
            $fila['correo_usuario'],
            $fila['fecha_ingreso_formateada'], 
            $fila['hora_ingreso_formateada'],  
            $fecha_salida_csv,             
            $hora_salida_csv                   
        ], $delimiter); 
    }
}
fclose($output);
$conexion->close();
exit; 
?>