<?php
include("ProhibirAcceso.php");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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
$sqlMaterialesDisponiblesForm = "SELECT * FROM materiales";
$resultadoMaterialesDropdown = $conexion->query($sqlMaterialesDisponiblesForm);
$materialesData = [];
if ($resultadoMaterialesDropdown) {
    while ($material = $resultadoMaterialesDropdown->fetch_assoc()) {
        $materialesData[] = $material;
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
<form action="Php/Registrar_prestamo_materiales.php" method="post" onsubmit="return validarFormulario()">
    <h2>Registrar Nuevo Préstamo de Material</h2>
    <div class="form-group">
        <label for="material_id">Material:</label>
        <select name="material_id" id="material_id" class="select2-enhanced" required>
            <option value="">Seleccione el material</option>
            <?php foreach ($materialesData as $material): ?>
                <option 
                    value='<?= htmlspecialchars($material['id_material']) ?>' 
                    data-nombre="<?= htmlspecialchars($material['nombre']) ?>"
                    data-tipo="<?= htmlspecialchars($material['tipo']) ?>"
                    data-stock-actual="<?= htmlspecialchars($material['stock']) ?>"
                >
                    <?= htmlspecialchars($material['nombre']) ?> - (TIPO: <?= htmlspecialchars($material['tipo']) ?> ) - (ID: <?= htmlspecialchars($material['id_material']) ?>) - (STOCK: <?= htmlspecialchars($material['stock']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <input type="hidden" name="material_tipo" id="material_tipo_hidden">
    <input type="hidden" name="material_stock_actual" id="material_stock_actual_hidden">

    <label>Cantidad a Prestar:</label> <input type="number" name="cantidad_prestamo" id="cantidad_prestamo" placeholder="Cantidad a Prestar" min="1" required>
    <br><br>
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
    <button type="submit"><i class="fa-solid fa-handshake" id="icon"></i> Prestar Material</button>
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
    <title>Gestión de Materiales</title>
    <script src="Js/jquery-3.7.1.min.js"></script>
    <link href="Css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="Css/Materiales.css">
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
        <h1>Gestión de Materiales</h1>
    </div>
</header>
<div class="container">
    <div class="content-area"> 
        <div class="tabs-container">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab-content="registrar-prestamo">Registrar Préstamo</button>
                <button class="tab-button" data-tab-content="devoluciones-pendientes">Devoluciones Pendientes</button>
                <button class="tab-button" data-tab-content="historial-prestamos">Historial de Préstamos</button>
                <button class="tab-button" data-tab-content="equipos-disponibles">Materiales Disponibles</button>
                <button class="tab-button" data-tab-content="total-equipos">Total Materiales</button>
                <button class="tab-button" data-tab-content="observaciones">Observaciones</button>
            </div>
            <div id="tab-content-area" class="tab-content-area">
                <p>Cargando...</p>
            </div>
        </div>
    </div>
</div>
<div id="devolverMaterialModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Registrar Devolución</h2>
        <form id="formDevolverMaterial" action="Php/Registrar_Devolucion_Materiales.php" method="POST">
            <input type="hidden" id="modal_id_prestamo_material" name="id_prestamo_material">
            
            <input type="hidden" name="id_responsable" value="<?= $id_responsable_session ?>">
            <input type="hidden" name="rol_responsable" value="<?= $rol_responsable_session ?>">

            <div class="form-group">
                <label for="modal_estado_devolucion">Estado del Material:</label>
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
<script src="Js/Materiales.js"></script>
<script src="Js/select2.min.js"></script>

<script>
    const formHtmlContent = `<?= addslashes($formHtml) ?>`;

    $(document).ready(function() {
        const devolverModal = $('#devolverMaterialModal');
        const closeButton = devolverModal.find('.close-button');
        const modalPrestamoId = $('#modal_id_prestamo_material');
        const modalEstadoDevolucion = $('#modal_estado_devolucion');
        const modalObservaciones = $('#modal_observaciones');

        function loadTabContent(tabName) {
            $('#tab-content-area').html('<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">Cargando...</p>');

            let url = '';
            if (tabName === 'registrar-prestamo') {
                $('#tab-content-area').html(formHtmlContent);

                // Inicializar Select2 para ambos selectores
                const $materialSelect = $('#material_id').select2({
                    theme: "default",
                    width: '100%',
                    placeholder: "Seleccione el material",
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


                // *** MEJORA PARA ATACAR EL INPUT DE BÚSQUEDA DE SELECT2 ***
                // Select2 crea su campo de búsqueda dinámicamente. 
                // Debemos apuntar al campo de búsqueda dentro del contenedor de Select2.
                // Usamos .on('select2:open') para adjuntar el evento cuando el dropdown se abre
                // y el campo de búsqueda ya existe.
                $materialSelect.on('select2:open', function() {
                    $('.select2-search__field').on('input', function() {
                        const oldValue = $(this).val();
                        const newValue = oldValue.replace(/^\s+/, '');
                        if (oldValue !== newValue) {
                            $(this).val(newValue).trigger('change');
                            // console.log('Material search field trimmed:', newValue); // Depuración
                        }
                    });
                });

                $instructorSelect.on('select2:open', function() {
                    $('.select2-search__field').on('input', function() {
                        const oldValue = $(this).val();
                        const newValue = oldValue.replace(/^\s+/, '');
                        if (oldValue !== newValue) {
                            $(this).val(newValue).trigger('change');
                            // console.log('Instructor search field trimmed:', newValue); // Depuración
                        }
                    });
                });
                // *** FIN DE LA MEJORA ***


                $('#material_id').on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const tipo = selectedOption.data('tipo');
                    const stockActual = selectedOption.data('stock-actual');

                    $('#material_tipo_hidden').val(tipo);
                    $('#material_stock_actual_hidden').val(stockActual);

                    const cantidadInput = $('#cantidad_prestamo');
                    cantidadInput.attr({
                        "max": stockActual,
                        "placeholder": `Cantidad a Prestar (Max: ${stockActual})`
                    });
                    if (parseInt(cantidadInput.val()) > stockActual) {
                        cantidadInput.val(stockActual);
                    }
                });
                $('#material_id').trigger('change');

            } else {
                // Destruir Select2 antes de cargar nuevo contenido para evitar duplicados
                // y asegurar que se inicializa correctamente la próxima vez.
                if ($('#material_id').data('select2')) {
                    $('#material_id').select2('destroy');
                }
                if ($('#instructor_id').data('select2')) {
                    $('#instructor_id').select2('destroy');
                }


                if (tabName === 'devoluciones-pendientes') {
                    url = 'Php/Get_devoluciones_pendientes_materiales.php';
                } else if (tabName === 'historial-prestamos') {
                    url = 'Php/Get_historial_prestamos_materiales.php';
                } else if (tabName === 'equipos-disponibles') {
                    url = 'Php/Get_materiales_disponibles.php';
                } else if (tabName === 'total-equipos') {
                    url = 'Php/Get_total_materiales.php';
                } else if (tabName === 'observaciones') {
                    url = 'Php/Get_observaciones_materiales.php';
                }

                if (url) {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(data) {
                            $('#tab-content-area').html(data);
                            attachDevolverButtonListeners();
                        },
                        error: function(xhr, status, error) {
                            $('#tab-content-area').html('<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar el contenido: ' + error + '</p>');
                            console.error("AJAX Error: ", status, error, xhr.responseText); // Log error
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
            loadTabContent('devoluciones-pendientes');
            $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
        });

        $(window).on('click', function(event) {
            if ($(event.target).is(devolverModal)) {
                devolverModal.css('display', 'none');
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
        const materialId = document.getElementById('material_id').value;
        const instructorId = document.getElementById('instructor_id').value;
        const cantidadPrestamo = parseInt(document.getElementById('cantidad_prestamo').value);
        const materialStockActual = parseInt(document.getElementById('material_stock_actual_hidden').value);

        if (materialId === "" || instructorId === "" || isNaN(cantidadPrestamo) || cantidadPrestamo <= 0) {
            alert("Por favor, complete todos los campos requeridos para el préstamo y asegúrese de que la cantidad sea un número válido y mayor a cero.");
            return false;
        }

        if (cantidadPrestamo > materialStockActual) {
            alert("La cantidad a prestar excede el stock disponible (" + materialStockActual + ").");
            return false;
        }
        
        return true;
    }
</script>

</body>
</html>