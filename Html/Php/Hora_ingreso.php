<?php
function obtenerFechaOrganizada($fechaHora){
    date_default_timezone_set('America/Bogota');

    $dias = [
        "Sunday" => "Domingo",
        "Monday" => "Lunes",
        "Tuesday" => "Martes",
        "Wednesday" => "Miércoles",
        "Thursday" => "Jueves",
        "Friday" => "Viernes",
        "Saturday" => "Sábado"
    ];
    $meses = [
        "01" => "enero", "02" => "febrero", "03" => "marzo", "04" => "abril", 
        "05" => "mayo", "06" => "junio", "07" => "julio", "08" => "agosto",
        "09" => "septiembre", "10" => "octubre", "11" => "noviembre", "12" => "diciembre"  
    ];
    $timestamp = strtotime($fechaHora);
    $diaSemana = $dias[date("l", $timestamp)];
    $dia = date("d", $timestamp);
    $mes = $meses[date("m", $timestamp)];
    $año = date("Y", $timestamp);
    $hora = date("h:i A", $timestamp);

    return "<b class='fecha'> <i class='fa-solid fa-calendar-days' id='icon'></i> Fecha:</b><strong class='vv'> $diaSemana, $dia de $mes de $año.</strong>"."<b class='hora'><i class='fa-solid fa-clock' id='icon'></i> Hora de ingreso:</b> <b class='vvv'> $hora</b>";
}

$hora_ingreso = '';
$correo = $_SESSION["correo"] ?? null;
if ($correo) {
    $sql = "SELECT hora_ingreso FROM almacenistas WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->bind_result($hora_ingreso);
        $stmt->fetch();
        $stmt->close();
    } 
}else {                                  
    $sql = "SELECT hora_ingreso FROM administradores WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->bind_result($hora_ingreso);
        $stmt->fetch();
        $stmt->close();
    }
}
?>