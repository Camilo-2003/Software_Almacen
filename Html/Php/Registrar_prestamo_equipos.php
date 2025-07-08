<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y validar entradas
    $id_equipo = isset($_POST['tipo']) ? intval($_POST['tipo']) : 0;
    $id_instructor = isset($_POST['instructor']) ? intval($_POST['instructor']) : 0;
    $id_almacenista = isset($_POST['almacenista']) ? intval($_POST['almacenista']) : 0;
    $fecha = date("Y-m-d H:i:s");

    // Validar que los IDs sean positivos y que la marca no esté vacía
    if ($id_equipo <= 0 || $id_instructor <= 0 || $id_almacenista <= 0 || empty($marca)) {
        echo "❌ Error: Datos inválidos.";
        exit;
    }

    // Verificar que el equipo exista y esté disponible
    $query = $conexion->prepare("SELECT estado FROM equipos WHERE id_equipo = ?");
    $query->bind_param("i", $id_equipo);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 0) {
        echo "❌ Error: No se encontró el equipo con ID $id_equipo.";
        $query->close();
        $conexion->close();
        exit;
    }

    $row = $resultado->fetch_assoc();
    if ($row['estado'] !== 'disponible') {
        echo "❌ Error: El equipo con ID $id_equipo no está disponible para préstamo. Estado actual: " . $row['estado'];
        $query->close();
        $conexion->close();
        exit;
    }
    $query->close();

    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // Registrar el préstamo con estado 'pendiente'
        $sql = "INSERT INTO prestamo_equipos (id_equipo, id_instructor, id_almacenista, fecha_prestamo, estado)
                VALUES (?, ?, ?, ?, 'pendiente')";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiiss", $id_equipo, $id_instructor, $id_almacenista, $fecha);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar el préstamo: " . $stmt->error);
        }

        // Actualizar el estado del equipo a 'prestado'
        $update_sql = "UPDATE equipos SET estado = 'prestado' WHERE id_equipo = ?";
        $update_stmt = $conexion->prepare($update_sql);
        $update_stmt->bind_param("i", $id_equipo);
        if (!$update_stmt->execute()) {
            throw new Exception("Error al actualizar el estado del equipo: " . $update_stmt->error);
        }

        // Confirmar transacción
        $conexion->commit();
        echo "✅ Préstamo registrado exitosamente.";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conexion->rollback();
        echo "❌ Transacción fallida: " . $e->getMessage();
    }

    $conexion->close();
}
?>
