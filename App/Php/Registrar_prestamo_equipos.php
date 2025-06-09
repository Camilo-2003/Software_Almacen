<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipo_id = isset($_POST['equipo_id']) ? intval($_POST['equipo_id']) : 0;
    $instructor_id = isset($_POST['instructor']) ? intval($_POST['instructor']) : 0;
    
    $marca = isset($_POST['marca']) ? htmlspecialchars($_POST['marca']) : ''; 

    $id_responsable = isset($_POST['id_responsable']) ? intval($_POST['id_responsable']) : 0;
    $rol_responsable = isset($_POST['rol_responsable']) ? $_POST['rol_responsable'] : '';

    if ($equipo_id <= 0 || $instructor_id <= 0 || $id_responsable <= 0 || !in_array($rol_responsable, ['almacenista', 'administrador']) || empty($marca)) {
        echo "<script>alert('❌ Error: Datos incompletos o inválidos para el préstamo. Asegúrese de seleccionar equipo, instructor, que su sesión sea válida y que la marca del equipo esté presente.'); window.location.href='../Equipos.php';</script>";
        exit();
    }
    date_default_timezone_set('America/Bogota');
    $fecha_prestamo = date("Y-m-d h:i:s A");
    $estado_prestamo = 'pendiente';

    $conexion->begin_transaction();

    try {
        $check_disponibilidad_stmt = $conexion->prepare("SELECT estado FROM equipos WHERE id_equipo = ?");
        if (!$check_disponibilidad_stmt) {
            throw new Exception("Error preparando la consulta de disponibilidad: " . $conexion->error);
        }
        $check_disponibilidad_stmt->bind_param("i", $equipo_id);
        $check_disponibilidad_stmt->execute();
        $result_disponibilidad = $check_disponibilidad_stmt->get_result();
        if ($result_disponibilidad->num_rows === 0 || $result_disponibilidad->fetch_assoc()['estado'] !== 'disponible') {
            throw new Exception("El equipo seleccionado ya no está disponible o no existe.");
        }
        $check_disponibilidad_stmt->close();

        $insert_prestamo_stmt = $conexion->prepare("INSERT INTO prestamo_equipos (id_equipo, id_instructor, id_responsable, rol_responsable, marca, fecha_prestamo, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$insert_prestamo_stmt) {
            throw new Exception("Error preparando el INSERT de prestamo_equipos: " . $conexion->error);
        }
        $insert_prestamo_stmt->bind_param("iiissss", $equipo_id, $instructor_id, $id_responsable, $rol_responsable, $marca, $fecha_prestamo, $estado_prestamo);

        if (!$insert_prestamo_stmt->execute()) {
            throw new Exception("Error al registrar préstamo: " . $insert_prestamo_stmt->error);
        }
        $insert_prestamo_stmt->close();

        // 3. Actualizar el estado del equipo
        $update_equipo_stmt = $conexion->prepare("UPDATE equipos SET estado = 'prestado' WHERE id_equipo = ?");
        if (!$update_equipo_stmt) {
            throw new Exception("Error preparando el UPDATE de equipos: " . $conexion->error);
        }
        $update_equipo_stmt->bind_param("i", $equipo_id);
        if (!$update_equipo_stmt->execute()) {
            throw new Exception("Error al actualizar estado del equipo: " . $update_equipo_stmt->error);
        }
        $update_equipo_stmt->close();
        
        $conexion->commit();
        echo "<script>alert('✅ Préstamo de equipo registrado exitosamente.'); window.location.href='../Equipos.php';</script>"; 
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        echo "<script>alert('❌ Error en la transacción de préstamo: " . htmlspecialchars($e->getMessage()) . "'); window.location.href='../Equipos.php';</script>"; 
        exit();
    } finally {
        if ($conexion) {
            $conexion->close();
        }
    }
} else {
    echo "<script>alert('❌ Acceso no válido al script de préstamo.'); window.location.href='../Equipos.php';</script>";
    exit();
}
?>