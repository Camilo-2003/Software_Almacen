<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once "../../ProhibirAcceso.php";

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

$instructor_id_filtro = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;

if ($instructor_id_filtro > 0) {
    //obtener todos los préstamos pendientes del instructor
    $sql = "
        SELECT
            pm.id_prestamo_material,
            m.nombre AS nombre_material,
            m.tipo AS tipo_material,
            pm.cantidad,
            CONCAT(i.nombre, ' ', i.apellido) AS instructor_nombre,
            i.id_instructor,
            pm.fecha_prestamo,
            pm.fecha_limite_devolucion,
            pm.estado,
            pm.id_responsable,
            pm.rol_responsable,
            pm.responsable
        FROM prestamo_materiales pm
        JOIN materiales m ON pm.id_material = m.id_material
        JOIN instructores i ON pm.id_instructor = i.id_instructor
        WHERE pm.id_instructor = ?
        AND pm.estado = 'pendiente'
        AND m.tipo = 'no consumible'
        ORDER BY pm.fecha_prestamo ASC;
    ";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo "<p style='text-align: center; color: #dc3545; padding: 20px;'>Error preparando la consulta: " . htmlspecialchars($conexion->error) . "</p>";
        exit();
    }
    $stmt->bind_param("i", $instructor_id_filtro);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $prestamos = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // echo "<div class='instructor-detail-view'>";
    echo "<div class='instructor-detail-view' data-instructor-id='" . htmlspecialchars($instructor_id_filtro) . "'>";
    if (count($prestamos) > 0) {
        $primer_prestamo = $prestamos[0];
        echo "<button id='backToInstructorList' class='btn-back-list'><i class='fas fa-arrow-left'></i> Volver a la lista de instructores</button>";
        echo "<h2 class='txttt'>Préstamos Pendientes para " . htmlspecialchars($primer_prestamo['instructor_nombre']) . "</h2>";
        echo "<p>Aquí puedes ver y gestionar los materiales que el instructor tiene pendientes por devolver.</p>";
     
        echo "<div class='loan-group'>";
        // Botón para editar el préstamo completo (usa el ID del primer ítem como referencia del grupo)
        echo "<h3 class='txtt'>Préstamo # " . htmlspecialchars($primer_prestamo['id_prestamo_material']) . " (Fecha: " . htmlspecialchars($primer_prestamo['fecha_prestamo']) . ")</h3>";
        echo "<button class='btn-editar open-editar-prestamo-modal' 
                data-id-prestamo='" . htmlspecialchars($primer_prestamo['id_prestamo_material']) . "'
                data-instructor-id='" . htmlspecialchars($primer_prestamo['id_instructor']) . "'
                data-instructor-nombre='" . htmlspecialchars($primer_prestamo['instructor_nombre']) . "'
                data-fecha-prestamo='" . htmlspecialchars($primer_prestamo['fecha_prestamo']) . "'
                title='Editar este grupo de préstamos'>
                <i class='fa-solid fa-edit'></i> Editar Préstamo Completo
              </button>";
        echo "<div class='table-responsive'>";
        echo "<table>";
        echo "<thead><tr>
                <th><input type='checkbox' id='selectAllPrestamos'></th>
                <th>Material</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th>Cantidad</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Vencimiento</th>
                <th>Acciones</th>
              </tr></thead>";
        echo "<tbody>";

        foreach ($prestamos as $row) {
            echo "<tr>";
            echo "<td><input type='checkbox' class='prestamo-checkbox' data-id-prestamo='" . htmlspecialchars($row['id_prestamo_material']) . "'></td>";
            echo "<td>" . htmlspecialchars($row['nombre_material']) . "</td>";
            echo "<td class='pendiente'>" . htmlspecialchars($row['estado']) . "</td>";
            echo "<td>" . htmlspecialchars($row['responsable']) . " (" . htmlspecialchars($row['rol_responsable']) . ")</td>";
            echo "<td>" . htmlspecialchars($row['cantidad']) . "</td>";
            echo "<td>" . htmlspecialchars(date("Y-m-d H:i:s", strtotime($row['fecha_prestamo']))) . "</td>";
            echo "<td>" . htmlspecialchars(date("Y-m-d H:i:s", strtotime($row['fecha_limite_devolucion']))) . "</td>";
            echo "<td class='actions-cell'>
            <button class='btn-devolver-small open-devolver-modal' data-id-prestamo='" . htmlspecialchars($row['id_prestamo_material']) . "' data-cantidad-prestada='" . htmlspecialchars($row['cantidad']) . "'><i class='fa-solid fa-rotate'></i> Devolver</button>
            <button class='btn btn-warning btn-abrir-novedad-material-modal'
                    data-id-prestamo-material='" . htmlspecialchars($row['id_prestamo_material']) . "'
                    data-nombre-material='" . htmlspecialchars($row['nombre_material']) . "'
                    data-nombre-instructor='" . htmlspecialchars($row['instructor_nombre']) . "'>
                <i class='fa-solid fa-exclamation-triangle'></i> Novedad
            </button> 
           </td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
        echo "<button class='btn-devolver-seleccionados' id='btnDevolverSeleccionados2'><i class='fas fa-check-double'></i> Devolver Seleccionados</button>";
        // echo "<button class='btn-novedad-general' id='btnNovedadMaterialGeneral'><i class='fas fa-exclamation-triangle'></i> Registrar Novedad General</button>";
        echo "</div>";

    } else {
        echo "<button id='backToInstructorList' class='btn-back-list'><i class='fas fa-arrow-left'></i> Volver a la lista de instructores</button>";
        echo "<h2 class='txt'>Préstamos Pendientes para el Instructor</h2>";
        echo "<p style='text-align: center; color: var(--neutral-text-medium); padding: 20px;'>No hay materiales pendientes de devolución para este instructor.</p>";
    }
    echo "</div>"; 

} else {
    $sql = "
        SELECT
            i.id_instructor,
            CONCAT(i.nombre, ' ', i.apellido) AS instructor_nombre,
            COUNT(pm.id_prestamo_material) AS total_materiales_pendientes
        FROM instructores i
        JOIN prestamo_materiales pm ON i.id_instructor = pm.id_instructor
        JOIN materiales m ON pm.id_material = m.id_material
        WHERE pm.estado = 'pendiente'
        AND m.tipo = 'no consumible'
        GROUP BY i.id_instructor, instructor_nombre
        ORDER BY instructor_nombre ASC;
    ";

    $resultado = $conexion->query($sql);

    echo "<h2 class='txt'>Instructores con Devoluciones Pendientes</h2>";
    echo "<p>Haz clic en el nombre de un instructor para ver sus préstamos pendientes:</p>";
    
    if ($resultado->num_rows > 0) {
        echo "<ul class='instructor-list'>";
        while ($fila = $resultado->fetch_assoc()) {
            echo "<li>";
            echo "<button class='instructor-item-button' data-instructor-id='" . htmlspecialchars($fila['id_instructor']) . "'>";
            echo htmlspecialchars($fila['instructor_nombre']) . "<span class='pending-count'>" . htmlspecialchars($fila['total_materiales_pendientes']) . " material(es) pendiente(s)</span>";
            echo "</button>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='text-align: center; color: var(--neutral-text-medium); padding: 20px;'>No hay instructores con materiales pendientes de devolución en este momento.</p>";
    }
}

$conexion->close();
?>