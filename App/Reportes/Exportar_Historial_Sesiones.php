<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

session_start();

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    die("Location: Error.php");
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Historial_Sesiones_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');

// Nombres de las columnas en la hoja de cálculo
fputcsv($output, [
    'ID Registro',
    'ID Usuario',
    'Tipo Usuario',
    'Nombre Completo',
    'Correo Electronico', 
    'Hora Ingreso',
    'Hora Salida'
]);
$sql = "SELECT 
            hs.id_registro,
            hs.id_usuario,
            hs.tipo_usuario,
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
            END AS correo_usuario,
            hs.hora_ingreso,
            hs.hora_salida
        FROM historial_sesiones hs
        LEFT JOIN almacenistas a ON hs.id_usuario = a.id_almacenista AND hs.tipo_usuario = 'almacenista'
        LEFT JOIN administradores ad ON hs.id_usuario = ad.id_administrador AND hs.tipo_usuario = 'administrador'
        ORDER BY hs.hora_ingreso DESC"; 

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $nombre_completo = $fila['nombres_usuario'] . ' ' . $fila['apellidos_usuario'];
        $hora_salida = $fila['hora_salida'];
        if ($hora_salida === NULL || $hora_salida === '') {
            $hora_salida = 'Activo';
        }
        
        fputcsv($output, [
            $fila['id_registro'],
            $fila['id_usuario'],
            ucfirst($fila['tipo_usuario']), 
            $nombre_completo,
            $fila['correo_usuario'],
            $fila['hora_ingreso'],
            $hora_salida
        ]);
    }
}
fclose($output);
$conexion->close();
exit; 
?>