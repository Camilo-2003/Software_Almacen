<?php
session_start();
// Habilitar la visualización de errores para depuración (descomentar en desarrollo, comentar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

// Redireccionar si el usuario no tiene los roles permitidos
if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: ../Error.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_prestamo_material = filter_input(INPUT_POST, 'id_prestamo_material', FILTER_VALIDATE_INT);
    $estado_devolucion = filter_input(INPUT_POST, 'estado_devolucion', FILTER_SANITIZE_STRING);
    $observaciones = filter_input(INPUT_POST, 'observaciones', FILTER_SANITIZE_STRING);
    if (empty($observaciones)) {
        $observaciones = 'Sin observaciones adicionales.'; // Default message
    }

    $id_responsable = filter_input(INPUT_POST, 'id_responsable', FILTER_VALIDATE_INT);
    $rol_responsable = filter_input(INPUT_POST, 'rol_responsable', FILTER_SANITIZE_STRING);
   
    date_default_timezone_set('America/Bogota');
    $fecha_devolucion = date("Y-m-d h:i:s A");

    if (!$id_prestamo_material || empty($estado_devolucion) || !$id_responsable || empty($rol_responsable)) {
        $message = "❌ Error: Datos de devolución incompletos o inválidos.";
        echo "<script>alert(" . json_encode($message) . "); window.location.href='../Materiales.php?tab=devoluciones-pendientes';</script>";
        exit();
    }

    $conexion->begin_transaction();

    try {
        // 1. Obtener id_material, cantidad_prestada y tipo de material del préstamo original
        // No necesitamos el stock actual del material si es consumible, pero lo mantenemos para no_consumible
        $get_material_info_sql = "
            SELECT
                pm.id_material,
                pm.cantidad,
                m.tipo,
                m.stock as current_material_stock
            FROM prestamo_materiales pm
            JOIN materiales m ON pm.id_material = m.id_material
            WHERE pm.id_prestamo_material = ? AND pm.estado = 'pendiente'
        ";
        $stmt_get_material_info = $conexion->prepare($get_material_info_sql);
        if (!$stmt_get_material_info) {
            throw new Exception("Error preparando la consulta de información de material: " . $conexion->error);
        }
        $stmt_get_material_info->bind_param("i", $id_prestamo_material);
        $stmt_get_material_info->execute();
        $result = $stmt_get_material_info->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Préstamo no encontrado o ya ha sido devuelto.");
        }
        $material_data = $result->fetch_assoc();
        $material_id = $material_data['id_material'];
        $cantidad_prestada = $material_data['cantidad'];
        $material_tipo = $material_data['tipo'];
        $current_material_stock = $material_data['current_material_stock']; // Only relevant for no_consumible
        $stmt_get_material_info->close();

        // 2. Actualizar el estado del préstamo a 'devuelto' en prestamo_materiales
        $update_prestamo_sql = "UPDATE prestamo_materiales SET estado = 'devuelto', fecha_devolucion = ? WHERE id_prestamo_material = ?";
        $stmt_update_prestamo = $conexion->prepare($update_prestamo_sql);
        if (!$stmt_update_prestamo) {
            throw new Exception("Error preparando la actualización de préstamo: " . $conexion->error);
        }
        $stmt_update_prestamo->bind_param("si", $fecha_devolucion, $id_prestamo_material);
        if (!$stmt_update_prestamo->execute()) {
            throw new Exception("Error al actualizar el préstamo a 'devuelto': " . $stmt_update_prestamo->error);
        }
        $stmt_update_prestamo->close();

        // 3. Actualizar el stock en la tabla 'materiales' SOLO para materiales 'no_consumible'
        // *** ESTE ES EL CAMBIO CLAVE: Solo se suma al stock si el material es 'no_consumible' ***
        if ($material_tipo === 'no_consumible') {
            $new_stock = $current_material_stock + $cantidad_prestada;
            $update_material_stock_sql = "UPDATE materiales SET stock = ? WHERE id_material = ?";
            $stmt_update_material_stock = $conexion->prepare($update_material_stock_sql);
            if (!$stmt_update_material_stock) {
                throw new Exception("Error preparando la actualización de stock de material (no consumible): " . $conexion->error);
            }
            $stmt_update_material_stock->bind_param("ii", $new_stock, $material_id);
            if (!$stmt_update_material_stock->execute()) {
                throw new Exception("Error al actualizar stock de material (no consumible): " . $stmt_update_material_stock->error);
            }
            $stmt_update_material_stock->close();
        }
        // Si es 'consumible', no se hace ninguna actualización de stock aquí.

        // 4. Registrar la devolución en la tabla 'devolucion_materiales'
        // Asegúrate que las columnas 'id_responsable', 'rol_responsable' y 'estado_devolucion' existen en tu DB.
        $insert_devolucion_sql = "INSERT INTO devolucion_materiales (id_prestamo_material, id_responsable, rol_responsable, estado_devolucion, fecha_devolucion, observaciones) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert_devolucion = $conexion->prepare($insert_devolucion_sql);
        if (!$stmt_insert_devolucion) {
            throw new Exception("Error preparando la inserción en devolucion_materiales: " . $conexion->error);
        }
        $stmt_insert_devolucion->bind_param("iissss", $id_prestamo_material, $id_responsable, $rol_responsable, $estado_devolucion, $fecha_devolucion, $observaciones);
        if (!$stmt_insert_devolucion->execute()) {
            throw new Exception("Error al registrar la devolución en la tabla devolucion_materiales: " . $stmt_insert_devolucion->error);
        }
        $stmt_insert_devolucion->close();

        $conexion->commit();
        $message = "✅ Devolución de material registrada exitosamente.";
        echo "<script>alert(" . json_encode($message) . "); window.location.href='../Materiales.php?tab=devoluciones-pendientes';</script>";
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        $errorMessage = "❌ Error en la transacción de devolución: " . $e->getMessage();
        echo "<script>alert(" . json_encode($errorMessage) . "); window.location.href='../Materiales.php?tab=devoluciones-pendientes';</script>";
        exit();
    } finally {
        if ($conexion) {
            $conexion->close();
        }
    }
} else {
    header("Location: ../Error.php");
    exit();
}
?>