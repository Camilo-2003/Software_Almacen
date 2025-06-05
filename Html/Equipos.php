<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php'; // Asegúrate de que esta ruta sea correcta

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: Error.php");
    exit();
}

// --- CÓDIGO ACTUALIZADO: Obtener el ID y el ROL del usuario logueado ---
$id_responsable_session = 0;
$rol_responsable_session = '';
$nombre_responsable_completo = ''; // Para mostrar en el formulario

if (isset($_SESSION['rol'])) {
    $rol_responsable_session = htmlspecialchars($_SESSION['rol']);
    $nombre_responsable_completo = htmlspecialchars($_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]);

    if ($rol_responsable_session === 'almacenista' && isset($_SESSION['id_almacenista'])) {
        $id_responsable_session = intval($_SESSION['id_almacenista']);
    } elseif ($rol_responsable_session === 'administrador' && isset($_SESSION['id_administrador'])) {
        $id_responsable_session = intval($_SESSION['id_administrador']);
    }
}
// --- FIN CÓDIGO ACTUALIZADO ---


$sqlEquiposDisponiblesForm = "SELECT id_equipo, marca, serial FROM equipos WHERE estado = 'disponible' ORDER BY marca, serial";
$resultadoEquiposDropdown = $conexion->query($sqlEquiposDisponiblesForm);
$equiposData = [];
if ($resultadoEquiposDropdown) {
    while ($equipo = $resultadoEquiposDropdown->fetch_assoc()) {
        $equiposData[] = $equipo;
    }
}

$sqlInstructores = "SELECT id_instructor, nombre, apellido FROM instructores ORDER BY nombre, apellido";
$resultadoInstructores = $conexion->query($sqlInstructores);
$instructoresData = [];
if ($resultadoInstructores) {
    while ($instructor = $resultadoInstructores->fetch_assoc()) {
        $instructoresData[] = $instructor;
    }
}
ob_start(); 
?>
<form action="Php/Registrar_prestamo_equipos.php" method="post" onsubmit="return validarFormulario()">
    <h2>Registrar Nuevo Préstamo de Equipo</h2>
    <div class="form-group">
        <label for="equipo_id">Equipo:</label>
        <select name="equipo_id" id="equipo_id" class="select2-enhanced" required>
            <option value="">Seleccione el equipo</option>
            <?php foreach ($equiposData as $equipo): ?>
                <option value='<?= htmlspecialchars($equipo['id_equipo']) ?>' data-marca="<?= htmlspecialchars($equipo['marca']) ?>">
                    <?= htmlspecialchars($equipo['marca']) ?> - <?= htmlspecialchars($equipo['serial']) ?> (ID: <?= htmlspecialchars($equipo['id_equipo']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <input type="hidden" name="marca" id="equipo_marca_hidden" value="">

    <div class="form-group">
        <label for="instructor_id">Instructor:</label>
        <select name="instructor" id="instructor_id" class="select2-enhanced" required>
            <option value="">Seleccione un instructor</option>
            <?php foreach ($instructoresData as $instructor): ?>
                <option value='<?= htmlspecialchars($instructor['id_instructor']) ?>'>
                    <?= htmlspecialchars($instructor['nombre']) ?> <?= htmlspecialchars($instructor['apellido']) ?> (ID: <?= htmlspecialchars($instructor['id_instructor']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Responsable del Préstamo:</label>
        <input type="text" value="<?php echo $nombre_responsable_completo . ' (Rol: ' . $rol_responsable_session . ')'; ?>" readonly>
        <input type="hidden" name="id_responsable" value="<?= $id_responsable_session ?>">
        <input type="hidden" name="rol_responsable" value="<?= $rol_responsable_session ?>">
    </div>

    <button type="submit">Prestar Equipo</button>
</form>
<?php
$formHtml = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="Css/Equipos.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="regresar">
        <a href="Préstamos.php" class="rgs" title="Haz clic para volver">Regresar</a>
    </div>
    <div class="header-center-content">
        <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Gestión de Equipos</h1>
    </div>
</header>

<div class="container">
    <div class="content-area"> 
        <div class="tabs-container">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab-content="registrar-prestamo">Registrar Préstamo</button>
                <button class="tab-button" data-tab-content="devoluciones-pendientes">Devoluciones Pendientes</button>
                <button class="tab-button" data-tab-content="historial-prestamos">Historial de Préstamos</button>
                <button class="tab-button" data-tab-content="equipos-disponibles">Equipos Disponibles</button>
                <button class="tab-button" data-tab-content="total-equipos">Total Equipos</button>
            </div>

            <div id="tab-content-area" class="tab-content-area">
                <p>Cargando...</p>
            </div>
        </div>
    </div>
</div>

<div id="devolverEquipoModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Registrar Devolución</h2>
        <form id="formDevolverEquipo" action="Php/Registrar_Devolucion_Equipos.php" method="POST">
            <input type="hidden" id="modal_id_prestamo_equipo" name="id_prestamo_equipo">
            
            <input type="hidden" name="id_responsable" value="<?= $id_responsable_session ?>">
            <input type="hidden" name="rol_responsable" value="<?= $rol_responsable_session ?>">

            <div class="form-group">
                <label for="modal_estado_devolucion">Estado del Equipo:</label>
                <select id="modal_estado_devolucion" name="estado_devolucion" required>
                    <option value="">Seleccione el estado</option>
                    <option value="bueno">Bueno</option>
                    <option value="regular">Regular</option>
                    <option value="malo">Malo</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="modal_observaciones">Observaciones (Opcional):</label>
                <textarea id="modal_observaciones" name="observaciones" rows="4"></textarea>
            </div>
            
            <button type="submit">Confirmar Devolución</button>
        </form>
    </div>
</div>

<script src="Js/Equipos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Asegúrate de que el contenido HTML del formulario se escape correctamente
    const formHtmlContent = `<?= addslashes($formHtml) ?>`;

    $(document).ready(function() {
        const devolverModal = $('#devolverEquipoModal');
        const closeButton = devolverModal.find('.close-button');
        const modalPrestamoId = $('#modal_id_prestamo_equipo');
        const modalEstadoDevolucion = $('#modal_estado_devolucion');
        const modalObservaciones = $('#modal_observaciones');

        function loadTabContent(tabName) {
            $('#tab-content-area').html('<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">Cargando...</p>');

            let url = '';
            if (tabName === 'registrar-prestamo') {
                $('#tab-content-area').html(formHtmlContent);
                
                $('.select2-enhanced').select2({
                    theme: "default",
                    width: '100%',
                    placeholder: "Seleccione una opción",
                    allowClear: true
                });

                $('#equipo_id').on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const marca = selectedOption.data('marca');
                    $('#equipo_marca_hidden').val(marca);
                });
                $('#equipo_id').trigger('change');
            } else {
                // Destroy Select2 instances when switching away from the form tab
                // (Para evitar problemas de inicialización múltiple si se vuelve a la pestaña de formulario)
                if ($('.select2-enhanced').data('select2')) {
                    $('.select2-enhanced').select2('destroy');
                }

                if (tabName === 'devoluciones-pendientes') {
                    url = 'Php/Get_devoluciones_pendientes_equipos.php';
                } else if (tabName === 'historial-prestamos') {
                    url = 'Php/Get_historial_prestamos.php';
                } else if (tabName === 'equipos-disponibles') {
                    url = 'Php/Get_equipos_disponibles.php';
                } else if (tabName === 'total-equipos') {
                    url = 'Php/Get_total_equipos.php';
                }

                if (url) {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(data) {
                            $('#tab-content-area').html(data);
                            // IMPORTANT: Re-attach event listeners for dynamically loaded content
                            attachDevolverButtonListeners(); 
                        },
                        error: function() {
                            $('#tab-content-area').html('<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar el contenido.</p>');
                        }
                    });
                }
            }
        }

        // Function to attach listeners to the 'Devolver' buttons
        function attachDevolverButtonListeners() {
            // Use event delegation for dynamically loaded buttons
            $('#tab-content-area').off('click', '.open-devolver-modal').on('click', '.open-devolver-modal', function() {
                const prestamoId = $(this).data('id-prestamo');
                modalPrestamoId.val(prestamoId); // Set the hidden input with the loan ID
                modalEstadoDevolucion.val(''); // Reset select
                modalObservaciones.val(''); // Reset textarea
                devolverModal.css('display', 'flex'); // Show the modal (using flex for centering)
            });
        }

        // Close modal when clicking on <span> (x)
        closeButton.on('click', function() {
            devolverModal.css('display', 'none');
        });

        // Close modal when clicking outside of it
        $(window).on('click', function(event) {
            if ($(event.target).is(devolverModal)) {
                devolverModal.css('display', 'none');
            }
        });

        // Load the "Registrar Préstamo" tab by default on page load
        loadTabContent('registrar-prestamo');

        $('.tab-button').on('click', function() {
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            const tabName = $(this).data('tab-content');
            loadTabContent(tabName);
        });
    });

    // This function must be globally accessible for onsubmit
    function validarFormulario() {
        const equipoId = document.getElementById('equipo_id').value;
        const instructorId = document.getElementById('instructor_id').value;
        const equipoMarca = document.getElementById('equipo_marca_hidden').value;

        if (equipoId === "" || instructorId === "" || equipoMarca === "") {
            alert("Por favor, complete todos los campos requeridos para el préstamo.");
            return false;
        }
        return true;
    }
</script>

</body>
</html>