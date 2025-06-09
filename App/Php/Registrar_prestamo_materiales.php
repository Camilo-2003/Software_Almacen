<?php
session_start();
// Habilitar la visualización de errores para depuración (descomentar en desarrollo, comentar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

// Redireccionar si el usuario no tiene los roles permitidos
if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: ../Error.php"); // Asegúrate de que la ruta sea correcta
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recopilar y sanear las entradas del formulario
    $id_material = filter_input(INPUT_POST, 'material_id', FILTER_VALIDATE_INT);
    $id_instructor = filter_input(INPUT_POST, 'instructor', FILTER_VALIDATE_INT);
    $cantidad_prestamo = filter_input(INPUT_POST, 'cantidad_prestamo', FILTER_VALIDATE_INT);
    $id_responsable = filter_input(INPUT_POST, 'id_responsable', FILTER_VALIDATE_INT);
    $rol_responsable = filter_input(INPUT_POST, 'rol_responsable', FILTER_SANITIZE_STRING);

    $material_tipo = filter_input(INPUT_POST, 'material_tipo', FILTER_SANITIZE_STRING);
    $material_stock_actual = filter_input(INPUT_POST, 'material_stock_actual', FILTER_VALIDATE_INT);

    date_default_timezone_set('America/Bogota');
    $fecha_prestamo = date("Y-m-d h:i:s A");

    // CRITICAL CHANGE HERE: Set loan state to 'consumido' for consumable items
    $estado_prestamo = ($material_tipo === 'consumible') ? 'consumido' : 'pendiente'; // <--- CHANGED FROM 'devuelto' TO 'consumido'

    // Validaciones básicas de los datos
    if (!$id_material || !$id_instructor || !$cantidad_prestamo || $cantidad_prestamo <= 0 || !$id_responsable || empty($rol_responsable) || empty($material_tipo) || $material_stock_actual === false) {
        echo "<script>alert('❌ Error: Todos los campos requeridos deben ser válidos. Verifique el rol del responsable.'); window.location.href='../Materiales.php';</script>";
        exit();
    }

    // Validación de stock antes de proceder (para ambos tipos)
    if ($cantidad_prestamo > $material_stock_actual) {
        echo "<script>alert('❌ Error: La cantidad a prestar excede el stock disponible.'); window.location.href='../Materiales.php';</script>";
        exit();
    }

    $conexion->begin_transaction(); // Iniciar una transacción para asegurar la consistencia

    try {
        // 1. Insertar el registro del préstamo en la tabla 'prestamo_materiales'
        $insert_prestamo_sql = "INSERT INTO prestamo_materiales (id_material, id_instructor, id_responsable, rol_responsable, cantidad, fecha_prestamo, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_prestamo = $conexion->prepare($insert_prestamo_sql);

        if (!$stmt_insert_prestamo) {
            throw new Exception("Error al preparar la consulta de inserción de préstamo: " . $conexion->error);
        }

        $stmt_insert_prestamo->bind_param("iiissss", $id_material, $id_instructor, $id_responsable, $rol_responsable, $cantidad_prestamo, $fecha_prestamo, $estado_prestamo);

        if (!$stmt_insert_prestamo->execute()) {
            throw new Exception("Error al ejecutar la inserción del préstamo: " . $stmt_insert_prestamo->error);
        }
        $prestamo_id = $conexion->insert_id; // Get the ID of the newly inserted loan
        $stmt_insert_prestamo->close();

        // 2. Actualizar el stock del material para AMBOS tipos (consumible y no_consumible)
        $new_stock = $material_stock_actual - $cantidad_prestamo;
        $update_stock_sql = "UPDATE materiales SET stock = ? WHERE id_material = ?";
        $stmt_update_stock = $conexion->prepare($update_stock_sql);

        if (!$stmt_update_stock) {
            throw new Exception("Error al preparar la consulta de actualización de stock: " . $conexion->error);
        }

        $stmt_update_stock->bind_param("ii", $new_stock, $id_material);

        if (!$stmt_update_stock->execute()) {
            throw new Exception("Error al ejecutar la actualización de stock: " . $stmt_update_stock->error);
        }
        $stmt_update_stock->close();

        // 3. Si el material es 'consumible', registrar una devolución implícita
        // Esto crea un registro en devolucion_materiales para el historial
        // Se usará el mismo estado 'consumido' para estado_devolucion
        if ($material_tipo === 'consumible') {
            $estado_devolucion_consumible = 'consumido'; // Set state for implicit return
            $observaciones_consumible = 'Material consumible. No se espera devolución física.';

            $insert_devolucion_sql = "INSERT INTO devolucion_materiales (id_prestamo_material, id_responsable, rol_responsable, estado_devolucion, fecha_devolucion, observaciones) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert_devolucion = $conexion->prepare($insert_devolucion_sql);
            if (!$stmt_insert_devolucion) {
                throw new Exception("Error preparando la inserción de devolución para consumible: " . $conexion->error);
            }
            $stmt_insert_devolucion->bind_param("iissss", $prestamo_id, $id_responsable, $rol_responsable, $estado_devolucion_consumible, $fecha_prestamo, $observaciones_consumible);
            if (!$stmt_insert_devolucion->execute()) {
                throw new Exception("Error al registrar la devolución para material consumible: " . $stmt_insert_devolucion->error);
            }
            $stmt_insert_devolucion->close();
        }

        $conexion->commit(); // Confirmar la transacción si todo fue exitoso
        echo "<script>alert('✅ Préstamo de material registrado y stock actualizado exitosamente.'); window.location.href='../Materiales.php';</script>";
        exit();

    } catch (Exception $e) {
        $conexion->rollback(); // Revertir la transacción en caso de error
        $errorMessage = "Error al registrar el préstamo: " . $e->getMessage();
        echo "<script>alert('❌ " . htmlspecialchars($errorMessage) . "'); window.location.href='../Materiales.php';</script>";
        exit();
    } finally {
        if ($conexion) {
            $conexion->close();
        }
    }
} else {
    // Si la solicitud no es POST, redirigir o mostrar un error
    header("Location: ../Error.php");
    exit();
}
?>