<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

if (!isset($conexion) || $conexion->connect_error) {
    echo "<p style='text-align: center; color: #dc3545; padding: 20px;'>Error: No se pudo conectar a la base de datos. Por favor, revise su archivo Conexion.php.</p>";
    exit(); 
}

$instructor_id_filtro = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;

if ($instructor_id_filtro > 0) {
    $sql = "SELECT
                ped.id_prestamo_equipo_detalle,
                ped.id_prestamo_equipo,
                ped.estado_item_prestamo AS estado_detalle,
                ped.fecha_vencimiento_item,
                ped.fecha_devolucion_item,
                e.id_equipo,
                e.marca AS equipo_marca,
                e.serial AS equipo_serial,
                CONCAT(e.marca, ' - ', e.serial) AS equipo_marca_serial,
                CONCAT(i.nombre, ' ', i.apellido) AS instructor_nombre,
                pe.fecha_prestamo,
                pe.rol_responsable,
                pe.responsable,
                pe.estado_general_prestamo AS estado_cabecera
            FROM prestamo_equipos pe
            JOIN prestamo_equipos_detalle ped ON pe.id_prestamo_equipo = ped.id_prestamo_equipo
            JOIN equipos e ON ped.id_equipo = e.id_equipo
            JOIN instructores i ON pe.id_instructor = i.id_instructor
            WHERE pe.id_instructor = ? 
            AND ped.estado_item_prestamo = 'prestado' 
            ORDER BY pe.fecha_prestamo ASC, ped.fecha_vencimiento_item ASC";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo "<p style='text-align: center; color: #dc3545; padding: 20px;'>Error preparando la consulta: " . htmlspecialchars($conexion->error) . "</p>";
        exit();
    }
    $stmt->bind_param("i", $instructor_id_filtro);
    $stmt->execute();
    $resultado = $stmt->get_result();

    echo "<div class='instructor-detail-view' data-instructor-id='" . htmlspecialchars($instructor_id_filtro) . "'>";
    // echo "<div class='instructor-detail-view'>";
    if ($resultado->num_rows > 0) {
        $primera_fila = $resultado->fetch_assoc();
        echo "<button id='backToInstructorList' class='btn-back-list'><i class='fas fa-arrow-left'></i> Volver a la lista de instructores</button>";
        echo "<h2 class='txt'>Préstamos Pendientes para " . htmlspecialchars($primera_fila['instructor_nombre']) . "</h2>";
        echo "<p>Aquí puedes ver los equipos que aún no ha devuelto este instructor.</p>";
        
        $resultado->data_seek(0); 

        // Agrupar por id_prestamo_equipo
        $prestamos_agrupados = [];
        while ($fila = $resultado->fetch_assoc()) {
            $prestamos_agrupados[$fila['id_prestamo_equipo']]['cabecera'] = [
                'id_prestamo_equipo' => $fila['id_prestamo_equipo'],
                'instructor_nombre' => $fila['instructor_nombre'],
                'fecha_prestamo' => $fila['fecha_prestamo'],
                'estado_general_prestamo' => $fila['estado_cabecera']
            ];
            $prestamos_agrupados[$fila['id_prestamo_equipo']]['detalles'][] = $fila;
        }

        foreach ($prestamos_agrupados as $id_cabecera => $prestamo) {
            echo "<div class='loan-group'>";
            echo "<div class='loan-header'>";
            echo "<h3>Préstamo # " . htmlspecialchars($prestamo['cabecera']['id_prestamo_equipo']) . " (Fecha: " . htmlspecialchars($prestamo['cabecera']['fecha_prestamo']) . ")</h3>";
            echo "<button class='btn-editar-small open-editar-prestamo-modal' 
                    data-id-prestamo='" . htmlspecialchars($prestamo['cabecera']['id_prestamo_equipo']) . "'
                    data-instructor-id='" . htmlspecialchars($instructor_id_filtro) . "'
                    data-instructor-nombre='" . htmlspecialchars($prestamo['cabecera']['instructor_nombre']) . "'
                    data-fecha-prestamo='" . htmlspecialchars($prestamo['cabecera']['fecha_prestamo']) . "'
                    title='Editar préstamo completo'>
                    <i class='fa-solid fa-edit'></i> Editar Préstamo Completo
                </button>";
            echo "</div>";

            echo "<div class='table-responsiveee'>";
            echo "<table>";
            echo "<thead>";
            echo "<tr>";
            echo "<th><input type='checkbox' id='selectAllPrestamos' data-prestamo-id-cabecera='" . htmlspecialchars($id_cabecera) . "'></th>"; // Checkbox para seleccionar todos los de este préstamo
            // echo "<th>ID DETALLE PRÉSTAMO</th>";
            echo "<th>Equipo</th>";
            echo "<th>Estado</th>";
            echo "<th>Responsable</th>";
            echo "<th>Fecha préstamo</th>";
            echo "<th>Fecha vencimiento</th>";
            echo "<th>Acciones</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            foreach ($prestamo['detalles'] as $detalle) {
                $estado_clase = str_replace('_', '-', $detalle['estado_detalle']);
                echo "<tr class='item-estado-" . htmlspecialchars($estado_clase) . "' 
                          data-id-detalle='" . htmlspecialchars($detalle['id_prestamo_equipo_detalle']) . "'
                          data-id-prestamo='" . htmlspecialchars($detalle['id_prestamo_equipo']) . "'
                          data-id-equipo='" . htmlspecialchars($detalle['id_equipo']) . "'
                          data-id-instructor='" . htmlspecialchars($instructor_id_filtro) . "'>";
                echo "<td>";
                // Deshabilitar checkbox si el estado no es 'prestado'
                $checkbox_disabled = ($detalle['estado_detalle'] !== 'prestado') ? 'disabled' : '';
                echo "<input type='checkbox' class='prestamo-checkbox' data-id-prestamo-detalle='" . htmlspecialchars($detalle['id_prestamo_equipo_detalle']) . "' " . $checkbox_disabled . ">";
                echo "</td>";
                // echo "<td>" . htmlspecialchars($detalle['id_prestamo_equipo_detalle']) . "</td>";
                echo "<td>" . htmlspecialchars($detalle['equipo_marca_serial']) . "</td>";
                echo "<td class='activo'>"  .  htmlspecialchars($detalle['estado_cabecera']) . "</>";
                echo "<td>" . htmlspecialchars($detalle['responsable']) . " (" . htmlspecialchars($detalle['rol_responsable']) . ")</td>";
                echo "<td>" . htmlspecialchars($detalle['fecha_prestamo']) . "</td>";
                echo "<td>" . htmlspecialchars($detalle['fecha_vencimiento_item']) . "</td>";
                echo "<td>";
                if ($detalle['estado_detalle'] === 'prestado') {
                    echo "<button class='btn-devolver-small open-devolver-modal' data-id-prestamo-detalle='" . htmlspecialchars($detalle['id_prestamo_equipo_detalle']) . "' title='Registrar devolución de este equipo'><i class='fa-solid fa-rotate'></i> Devolver</button>";
                    //Novedades
                    echo "<button class='btn btn-warning btn-abrir-novedad2-modal' ";
                    echo "data-id_prestamo_equipo_detalle='" . htmlspecialchars($detalle['id_prestamo_equipo_detalle']) . "' ";
                    echo "data-id_prestamo_equipo='" . htmlspecialchars($detalle['id_prestamo_equipo']) . "' ";
                    echo "data-id_equipo='" . htmlspecialchars($detalle['id_equipo']) . "' ";
                    echo "data-nombre_equipo='" . htmlspecialchars($detalle['equipo_marca_serial']) . "' "; 
                    echo "data-nombre_instructor='" . htmlspecialchars($detalle['instructor_nombre']) . "' ";
                    echo "data-id_instructor='" . htmlspecialchars($instructor_id_filtro) . "' "; 
                    echo "data-fecha_vencimiento_item='" . htmlspecialchars($detalle['fecha_vencimiento_item']) . "'>"; 
                    echo "<i class='fa-solid fa-exclamation-triangle'></i> Novedad"; 
                    echo "</button>";
                } else {
                    echo "<span class='status-info'>" . htmlspecialchars(ucwords(str_replace('_', ' ', $detalle['estado_detalle']))) . "</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "<button class='btn-devolver-seleccionados' id='btnDevolverSeleccionados'><i class='fas fa-check-double'></i> Devolver Seleccionados</button>";
            // echo "<button class='btn-novedad-seleccionados' id='btnNovedadSeleccionados' disabled ><i class='fas fa-exclamation-triangle'></i> Registrar Novedad General</button>";
            echo "</div>"; 
        }

    } else {
        echo "<button id='backToInstructorList' class='btn-back-list'><i class='fas fa-arrow-left'></i> Volver a la lista de instructores</button>";
        echo "<h2 class='txt'>Préstamos Pendientes para el Instructor</h2>";
        echo "<p class='txt' style='text-align: center; color: var(--neutral-text-medium); padding: 20px;'>No hay equipos pendientes de devolución para este instructor.</p>";
    }
    echo "</div>";

    $stmt->close();

} else {
    $sql = "SELECT
                i.id_instructor,
                CONCAT(i.nombre, ' ', i.apellido) AS instructor_nombre,
                COUNT(ped.id_prestamo_equipo_detalle) AS total_equipos_pendientes
            FROM instructores i
            JOIN prestamo_equipos pe ON i.id_instructor = pe.id_instructor
            JOIN prestamo_equipos_detalle ped ON pe.id_prestamo_equipo = ped.id_prestamo_equipo
            WHERE ped.estado_item_prestamo = 'prestado' 
            GROUP BY i.id_instructor, instructor_nombre
            ORDER BY instructor_nombre ASC";

    $resultado = $conexion->query($sql);

    echo "<h2 class='txt'>Instructores con Devoluciones Pendientes</h2>";
    echo "<p>Haz clic en el nombre de un instructor para ver sus préstamos pendientes:</p>";
    
    if ($resultado->num_rows > 0) {
        echo "<ul class='instructor-list'>";
        while ($fila = $resultado->fetch_assoc()) {
            echo "<li>";
            echo "<button class='instructor-item-button' data-instructor-id='" . htmlspecialchars($fila['id_instructor']) . "'>";
            echo htmlspecialchars($fila['instructor_nombre']) . "<span class='pending-count'>" . htmlspecialchars($fila['total_equipos_pendientes']) . " equipo(s) pendiente(s)</span>";
            echo "</button>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='txt' style='text-align: center; color: var(--neutral-text-medium); padding: 20px;'>No hay instructores con equipos pendientes de devolución en este momento.</p>";
    }
}
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>

