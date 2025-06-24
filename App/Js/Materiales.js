$(document).ready(function() {
    
    // --- DECLARACIÓN DE CONSTANTES Y VARIABLES GLOBALES ---
    const UMBRAL_STOCK_BAJO = 5; // Ajustado a 5 como solicitaste
    const areaAlertasVencidas = $('#alert-area-vencidos');
    const contenidoAlertasVencidas = $('#contenido-alertas-vencidas');
    const areaAlertaStockBajo = $('#alert-area-stock');
    const contenidoAlertaStockBajo = $('#contenido-alerta-stock-bajo');

    // --- DEFINICIÓN DE FUNCIONES PRINCIPALES ---

    function loadTabContent(tab, instructorId = 0) {
        const tabToFileMap = {
            'registrar-prestamo': 'registrar_prestamo',
            'devoluciones-pendientes': 'devoluciones_pendientes_materiales',
            'historial-prestamos': 'historial_prestamos_materiales',
            'equipos-disponibles': 'materiales_disponibles',
            'total-equipos': 'total_materiales',
            'observaciones': 'observaciones_materiales'
        };

        const fileName = tabToFileMap[tab] || tab;
        let ajaxUrl = `/Software_Almacen/App/Php/Préstamo_Materiales/Get_${fileName}.php`;

        if (tab === 'devoluciones-pendientes' && instructorId > 0) {
            ajaxUrl += `?instructor_id=${instructorId}`;
        }

        $.ajax({
            url: ajaxUrl,
            type: 'GET',
            success: function(response) {
                $('#tab-content-area').html(response);
                initializeDynamicComponents(tab, instructorId);
            },
            error: function(xhr) {
                $('#tab-content-area').html('<p style="color: red;">Error al cargar contenido. Detalles en la consola.</p>');
                console.error("Error en AJAX:", xhr.responseText);
            }
        });
    }

    function initializeDynamicComponents(tab, instructorId) {
        $('.select2-enhanced').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    theme: "default",
                    width: '100%',
                    placeholder: "Seleccione una opción",
                    allowClear: true
                });
            }
        });

        if (tab === 'registrar-prestamo') {
            initializeRegistrarPrestamo();
        } else if (tab === 'devoluciones-pendientes') {
            if (instructorId > 0) {
                $('#btnDevolverSeleccionados2').hide();
            }
        }
    }

    function initializeRegistrarPrestamo() {
        $('#material_id').on('change', function() {
            const cantidadFieldsContainer = $('#cantidad_fields');
            const materialesSeleccionados = $(this).val() || [];
            cantidadFieldsContainer.empty();

            materialesSeleccionados.forEach(function(materialId) {
                const option = $(`#material_id option[value="${materialId}"]`);
                const materialNombre = option.text().split(' (Tipo:')[0].trim();
                const materialStock = option.data('stock');
                if (materialNombre && materialStock !== undefined) {
                    const fieldHtml = `
                        <div class="form-group cantidad-material-group">
                            <label for="cantidad_prestamo_${materialId}">${materialNombre} (Disponibles: ${materialStock})</label>
                            <input type="number" name="cantidad_prestamo[${materialId}]" id="cantidad_prestamo_${materialId}" class="form-control" value="1" min="1" max="${materialStock}" required>
                        </div>`;
                    cantidadFieldsContainer.append(fieldHtml);
                }
            });
        });
        $('#material_id').trigger('change');
    }

    // --- FUNCIONES DE ALERTA ---

    function verificarStockBajo() {
        $.ajax({
            url: 'Php/Préstamo_Materiales/Obtener_stock_total_materiales.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const totalDisponible = response.total_disponible;
                    contenidoAlertaStockBajo.empty();

                    if (totalDisponible <= UMBRAL_STOCK_BAJO && totalDisponible > 0) {
                        contenidoAlertaStockBajo.append(`<div class="alert-item low-stock-item"><span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span><p>¡ATENCIÓN! El stock de materiales no consumibles es bajo. Solo quedan <strong>${totalDisponible}</strong> unidades en total.</p></div>`);
                        areaAlertaStockBajo.show();
                    } else if (totalDisponible === 0) {
                        contenidoAlertaStockBajo.append(`<div class="alert-item low-stock-item"><span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span><p>¡ADVERTENCIA CRÍTICA! No quedan materiales no consumibles disponibles para préstamo.</p></div>`);
                        areaAlertaStockBajo.show();
                    } else {
                        areaAlertaStockBajo.hide();
                    }
                }
            }
        });
    }

    function cargarAlertasVencidas() {
        $.ajax({
            url: 'Php/Préstamo_Materiales/Obtener_prestamos_vencidos.php',
            type: 'GET',
            dataType: 'json',
            success: function(respuesta) {
                contenidoAlertasVencidas.empty();
                if (respuesta.success && respuesta.overdue_items.length > 0) {
                    respuesta.overdue_items.forEach(function(item) {
                        const htmlAlerta = `<div class="alert-item"><span class="alert-icon"><i class="fas fa-bell"></i></span><p>El material <strong>${item.nombre_material}</strong> prestado a <strong>${item.nombre_instructor}</strong> ha vencido. Fecha límite: <strong>${item.fecha_limite_devolucion}</strong>.</p></div>`;
                        contenidoAlertasVencidas.append(htmlAlerta);
                    });
                    areaAlertasVencidas.show();
                } else {
                    areaAlertasVencidas.hide();
                }
            },
            error: function(xhr) {
                console.error(`Error al obtener alertas de vencimiento: ${xhr.responseText}`);
            }
        });
    }

    // --- MANEJADORES DE EVENTOS DELEGADOS ---
    const $document = $(document);

    $document.on('click', '.tab-button', function() {
        loadTabContent($(this).data('tab-content'));
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
    });

    $document.on('submit', '#formRegistrarPrestamoMaterial', function(e) {
        e.preventDefault();
        if (($('#material_id').val() || []).length === 0) {
            alert("Por favor, seleccione al menos un material."); return;
        }
        if (!$('#instructor').val()) {
            alert("Por favor, seleccione un instructor."); return;
        }
        $.ajax({
            url: 'Php/Préstamo_Materiales/Registrar_prestamo_materiales.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    loadTabContent('registrar-prestamo');
                    verificarStockBajo();
                }
            },
            error: function(xhr) {
                alert('Error al registrar el préstamo.'); console.error(xhr.responseText);
            }
        });
    });

    $document.on('click', '.instructor-item-button', function() {
        loadTabContent('devoluciones-pendientes', $(this).data('instructor-id'));
    });

    $document.on('click', '#backToInstructorList', function() {
        loadTabContent('devoluciones-pendientes');
    });

    $document.on('change', '#selectAllPrestamos, .prestamo-checkbox', function() {
        if ($(this).is('#selectAllPrestamos')) {
            $('.prestamo-checkbox').prop('checked', this.checked);
        }
        $('#btnDevolverSeleccionados2').toggle($('.prestamo-checkbox:checked').length > 0);
    });
    
    // --- LÓGICA DE EDICIÓN DE PRÉSTAMO ---
    $document.on('click', '.open-editar-prestamo-modal', function() {
        const modal = $('#editarPrestamoCompletoModal');
        const prestamoId = $(this).data('id-prestamo');
        const instructorId = $(this).data('instructor-id');
        
        modal.find('form').data('instructor-id', instructorId);
        modal.find('#edit_modal_id_prestamo_material_completo').val(prestamoId);
        modal.find('#edit_modal_instructor_info').text($(this).data('instructor-nombre'));
        modal.find('#edit_modal_fecha_prestamo_info').text($(this).data('fecha-prestamo'));
        modal.find('#current_loaned_equipment').html('<p>Cargando...</p>');
        
        const availableItemsSelect = modal.find('#add_material_id_to_loan');
        if (availableItemsSelect.hasClass("select2-hidden-accessible")) {
            availableItemsSelect.select2('destroy');
        }
        availableItemsSelect.empty();
    
        $.ajax({
            url: 'Php/Préstamo_Materiales/Obtener_datos_prestamo_edicion_materiales.php',
            type: 'GET',
            data: { id_prestamo_material: prestamoId },
            dataType: 'json', 
            success: function(response) {
                if (response.success) {
                    const currentItemsContainer = modal.find('#current_loaned_equipment').empty();
                    const loanedMaterialIds = response.loan_details.map(item => String(item.id_material));
    
                    response.loan_details.forEach(item => {
                        currentItemsContainer.append(`<div class="equipment-item" data-material-id="${item.id_material}"><span>${item.nombre} (Cantidad: ${item.cantidad})</span><button type="button" class="remove-from-loan-btn">Quitar</button></div>`);
                    });
    
                    response.available_materials.forEach(material => {
                        if (!loanedMaterialIds.includes(String(material.id_material))) {
                            availableItemsSelect.append(new Option(`${material.nombre} (Stock: ${material.stock})`, material.id_material, false, false));
                        }
                    });
    
                    availableItemsSelect.select2({
                        theme: "default",
                        width: '100%',
                        placeholder: "Seleccione para añadir",
                        dropdownParent: modal.find('.modal-content')
                    });
                    modal.css('display', 'flex');
                } else {
                   alert(response.message || 'Error al cargar datos de edición.');
                }
            },
            error: function(xhr) {
                alert('Error crítico al cargar los datos del préstamo.'); console.error(xhr.responseText);
            }
        });
    });

    $document.on('click', '.remove-from-loan-btn', function() {
        $(this).closest('.equipment-item').remove();
    });

    $document.on('submit', '#formEditarPrestamoCompleto', function(e) {
        e.preventDefault();
        const instructorId = $(this).data('instructor-id');
        let finalItems = {};
        $('#current_loaned_equipment .equipment-item').each(function() {
            finalItems[$(this).data('material-id')] = 'EXISTING';
        });
        const formData = {
            id_prestamo_material: $('#edit_modal_id_prestamo_material_completo').val(),
            final_items: JSON.stringify(finalItems),
            items_to_add: JSON.stringify(($('#add_material_id_to_loan').val() || []).map(id => ({ id_material: id, cantidad: 1 }))),
            id_responsable: $('#id_responsable_session').val(),
            rol_responsable: $('#rol_responsable_session').val()
        };
        $.ajax({
            url: 'Php/Préstamo_Materiales/Procesar_edicion_prestamo_materiales.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    $('#editarPrestamoCompletoModal').css('display', 'none');
                    loadTabContent('devoluciones-pendientes', instructorId);
                    verificarStockBajo();
                    cargarAlertasVencidas();
                }
            },
            error: function(xhr) {
                alert('Error al guardar los cambios.'); console.error(xhr.responseText);
            }
        });
    });

    // --- MANEJADORES DE MODALES ---
    $document.on('click', '.open-devolver-modal', function() {
        $('#modal_id_prestamo_material').val($(this).data('id-prestamo'));
        $('#devolverMaterialModal').css('display', 'flex');
    });

    $document.on('click', '#btnDevolverSeleccionados2', function() {
        const selectedIds = $('.prestamo-checkbox:checked').map((_, el) => $(el).data('id-prestamo')).get();
        if (selectedIds.length > 0) {
            $('#modal_id_prestamo_material_multiple').val(selectedIds.join(','));
            $('#devolverMaterialModalMultiple').css('display', 'flex');
        } else {
            alert('Por favor, selecciona al menos un préstamo.');
        }
    });

    $document.on('click', '.close-button', function() {
        $(this).closest('.modal').css('display', 'none');
    });

    $document.on('submit', '#formDevolverMaterial, #formDevolverMaterialMultiple', function(e) {
        e.preventDefault();
        const form = $(this);
        const instructorId = $('#devoluciones-pendientes .instructor-item-button.active').data('instructor-id') || 0;
        $.ajax({
            url: '/Software_Almacen/App/Php/Préstamo_Materiales/Registrar_Devolucion_Materiales.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    form.closest('.modal').css('display', 'none');
                    loadTabContent('devoluciones-pendientes', instructorId);
                    verificarStockBajo();
                    cargarAlertasVencidas();
                }
            },
            error: function(xhr) {
                alert('Error al procesar la devolución.'); console.error(xhr.responseText);
            }
        });
    });

    // --- CARGA INICIAL ---
    cargarAlertasVencidas();
    verificarStockBajo();
    loadTabContent($('.tab-button.active').data('tab-content') || 'registrar-prestamo');
});