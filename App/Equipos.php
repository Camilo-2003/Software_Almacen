<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php'; 

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: Error.php");
    exit();
}
$id_responsable_session = 0;
$rol_responsable_session = '';
$nombre_responsable_completo = '';

if (isset($_SESSION['rol'])) {
    $rol_responsable_session = htmlspecialchars($_SESSION['rol']);
    $nombre_responsable_completo = htmlspecialchars($_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]);

    if ($rol_responsable_session === 'almacenista' && isset($_SESSION['id_almacenista'])) {
        $id_responsable_session = intval($_SESSION['id_almacenista']);
    } elseif ($rol_responsable_session === 'administrador' && isset($_SESSION['id_administrador'])) {
        $id_responsable_session = intval($_SESSION['id_administrador']);
    }
}
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
    <button type="submit"><i class="fa-solid fa-handshake" id="icon"></i> Prestar Equipo</button>
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
    <script src="Js/jquery-3.7.1.min.js"></script>
    <link href="Css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="Css/Equipos.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="regresar">
        <a href="Préstamos.php" class="rgs" title="Haz clic para volver"><i class="fas fa-reply"></i> Regresar</a>
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
                <button class="tab-button" data-tab-content="observaciones">Observaciones</button>
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
            <button type="submit"><i class="fa-regular fa-circle-check"></i> Confirmar Devolución</button>
        </form>
    </div>
</div>
<script src="Js/Equipos.js"></script>
<script src="Js/select2.min.js"></script>

<script>
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
                
                // Inicializar Select2 para ambos selectores
                const $equipoSelect = $('#equipo_id').select2({
                    theme: "default",
                    width: '100%',
                    placeholder: "Seleccione el equipo",
                    allowClear: true,
                    matcher: function(params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }
                        if (data.id === '') {
                            return null;
                        }
                        const term = $.trim(params.term).toLowerCase();
                        const text = $.trim(data.text).toLowerCase();
                        if (text.indexOf(term) > -1) {
                            return data;
                        }
                        return null;
                    }
                });

                const $instructorSelect = $('#instructor_id').select2({
                    theme: "default",
                    width: '100%',
                    placeholder: "Seleccione un instructor",
                    allowClear: true,
                    matcher: function(params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }
                        if (data.id === '') {
                            return null;
                        }
                        const term = $.trim(params.term).toLowerCase();
                        const text = $.trim(data.text).toLowerCase();
                        if (text.indexOf(term) > -1) {
                            return data;
                        }
                        return null;
                    }
                });

                // *** AÑADIDO: Lógica para eliminar espacios al inicio en el campo de búsqueda de Select2 para EQUIPO ***
                $equipoSelect.on('select2:open', function() {
                    const selectId = $(this).attr('id');
                    // select2-equipo_id-container es el id del span que contiene el texto seleccionado.
                    // El campo de búsqueda real tiene un aria-controls que apunta a 'select2-IDDELSELECT-results'
                    const $searchField = $(`[aria-controls="select2-${selectId}-results"]`);
                    
                    // Asegúrate de que el evento se adjunte una sola vez para evitar duplicados
                    $searchField.off('input.trimspaces').on('input.trimspaces', function() {
                        const oldValue = $(this).val();
                        const newValue = oldValue.replace(/^\s+/, ''); // Elimina solo los espacios al inicio
                        if (oldValue !== newValue) {
                            $(this).val(newValue).trigger('change'); // trigger('change') para que Select2 re-evalúe la búsqueda
                            // console.log('Campo de búsqueda de Equipo recortado:', newValue); // Para depuración
                        }
                    });
                });

                // *** AÑADIDO: Lógica para eliminar espacios al inicio en el campo de búsqueda de Select2 para INSTRUCTOR ***
                $instructorSelect.on('select2:open', function() {
                    const selectId = $(this).attr('id');
                    const $searchField = $(`[aria-controls="select2-${selectId}-results"]`);
                    
                    // Asegúrate de que el evento se adjunte una sola vez para evitar duplicados
                    $searchField.off('input.trimspaces').on('input.trimspaces', function() {
                        const oldValue = $(this).val();
                        const newValue = oldValue.replace(/^\s+/, ''); // Elimina solo los espacios al inicio
                        if (oldValue !== newValue) {
                            $(this).val(newValue).trigger('change'); // trigger('change') para que Select2 re-evalúe la búsqueda
                            // console.log('Campo de búsqueda de Instructor recortado:', newValue); // Para depuración
                        }
                    });
                });


                $('#equipo_id').on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const marca = selectedOption.data('marca');
                    $('#equipo_marca_hidden').val(marca);
                });
                $('#equipo_id').trigger('change');
            } else {
                // Destruir Select2 antes de cargar nuevo contenido para evitar duplicados
                // y asegurar que se inicializa correctamente la próxima vez.
                if ($('#equipo_id').data('select2')) {
                    $('#equipo_id').select2('destroy');
                }
                if ($('#instructor_id').data('select2')) {
                    $('#instructor_id').select2('destroy');
                }

                if (tabName === 'devoluciones-pendientes') {
                    url = 'Php/Get_devoluciones_pendientes_equipos.php';
                } else if (tabName === 'historial-prestamos') {
                    url = 'Php/Get_historial_prestamos_equipos.php';
                } else if (tabName === 'equipos-disponibles') {
                    url = 'Php/Get_equipos_disponibles.php';
                } else if (tabName === 'total-equipos') {
                    url = 'Php/Get_total_equipos.php';
                } else if (tabName === 'observaciones') {
                    url = 'Php/Get_observaciones_equipos.php'
                }

                if (url) {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(data) {
                            $('#tab-content-area').html(data);
                            attachDevolverButtonListeners(); 
                        },
                        error: function() {
                            $('#tab-content-area').html('<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar el contenido.</p>');
                        }
                    });
                }
            }
        }
        function attachDevolverButtonListeners() {
            $('#tab-content-area').off('click', '.open-devolver-modal').on('click', '.open-devolver-modal', function() {
                const prestamoId = $(this).data('id-prestamo');
                modalPrestamoId.val(prestamoId);
                modalEstadoDevolucion.val(''); 
                modalObservaciones.val(''); 
                devolverModal.css('display', 'flex');
            });
        }
        closeButton.on('click', function() {
            devolverModal.css('display', 'none');
            // Al cerrar el modal, recargar la pestaña de devoluciones pendientes
            loadTabContent('devoluciones-pendientes');
            $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
        });

        $(window).on('click', function(event) {
            if ($(event.target).is(devolverModal)) {
                devolverModal.css('display', 'none');
                // Al cerrar el modal, recargar la pestaña de devoluciones pendientes
                loadTabContent('devoluciones-pendientes');
                $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
            }
        });

        // Initial load: Check URL parameter for active tab
        const urlParams = new URLSearchParams(window.location.search);
        const requestedTab = urlParams.get('tab');

        if (requestedTab) {
            $('.tab-button[data-tab-content="' + requestedTab + '"]').addClass('active').siblings().removeClass('active');
            loadTabContent(requestedTab);
        } else {
            loadTabContent('registrar-prestamo');
        }

        $('.tab-button').on('click', function() {
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            const tabName = $(this).data('tab-content');
            loadTabContent(tabName);
        });
    });

    function validarFormulario() {
        const equipoId = document.getElementById('equipo_id').value;
        const instructorId = document.getElementById('instructor_id').value;
        const equipoMarca = document.getElementById('equipo_marca_hidden').value;

        // Asegúrate de que las opciones vacías no se consideren válidas
        if (equipoId === "" || instructorId === "" || equipoMarca === "") {
            alert("Por favor, complete todos los campos requeridos para el préstamo.");
            return false;
        }
        return true;
    }
</script>

</body>
</html>