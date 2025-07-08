$(document).ready(function() { 
//DECLARACIÓN DE CONSTANTES Y VARIABLES GLOBALES
    const UMBRAL_STOCK_BAJO = 5;
    const areaAlertasVencidas = $('#overdue-alerts-area');
    const contenidoAlertasVencidas = $('#contenido-alertas-vencidas');
    const areaAlertaStockBajo = $('#alert-area-stock');
    const contenidoAlertaStockBajo = $('#contenido-alerta-stock-bajo');
    

    //DEFINICIÓN DE FUNCIONES PRINCIPALES 
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
   
        //VALIDACIÓN PARA ESPACIOS AL INICIO EN EL CAMPO DE BÚSQUEDA DEL SELECT INSTRUCTORES Y MATERIALES
                $(this).on('select2:open', function() {
                    const searchInput = $('.select2-search__field');
                    if (searchInput.length) {
                        searchInput.off('input.trimLeadingSpace').on('input.trimLeadingSpace', function() {
                            this.value = this.value.replace(/^\s+/, '');
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

        // Guardar los valores actuales antes de vaciar
        const valoresActuales = {};
        cantidadFieldsContainer.find('input[name^="cantidad_prestamo"]').each(function() {
            const id = $(this).attr('id').replace('cantidad_prestamo_', '');
            valoresActuales[id] = $(this).val();
        });

        // Vaciar y recrear solo los campos seleccionados
        cantidadFieldsContainer.empty();
        materialesSeleccionados.forEach(function(materialId) {
            const option = $(`#material_id option[value="${materialId}"]`);
            const materialNombre = option.text().split(' (Tipo:')[0].trim();
            const materialStock = option.data('stock');
            if (materialNombre && materialStock !== undefined) {
                const valorGuardado = valoresActuales[materialId] || 1; // Usar valor previo o 1 por defecto
                const fieldHtml = `
                    <div class="form-group cantidad-material-group">
                        <label for="cantidad_prestamo_${materialId}">${materialNombre} Disponibles: (${materialStock}) || </label>
                        <input type="number" name="cantidad_prestamo[${materialId}]" id="cantidad_prestamo_${materialId}" class="form-control" value="${valorGuardado}" min="1" max="${materialStock}" required>
                    </div>`;
                cantidadFieldsContainer.append(fieldHtml);
            }
        });
    });
    $('#material_id').trigger('change'); // Iniciar con los valores seleccionados
}

    //FUNCIONES DE ALERTA 

    function verificarStockBajo() {
        $.ajax({
            url: 'Php/Préstamo_Materiales/Obtener_stock_total_materiales.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success,'success') {
                    const totalDisponible = response.total_disponible;
                    contenidoAlertaStockBajo.empty();

                    if (totalDisponible <= UMBRAL_STOCK_BAJO && totalDisponible > 0) {
                        contenidoAlertaStockBajo.append(`<div class="alert-item low-stock-item"><span class="alert-icon"><i class="fas fa-exclamation-circle" id="iii"></i></span><p><b>¡ATENCIÓN!</b> El stock de materiales no consumibles es bajo. Solo quedan <strong>${totalDisponible}</strong> unidades en total.</p></div>`);
                        areaAlertaStockBajo.show();
                    } else if (totalDisponible === 0) {
                        contenidoAlertaStockBajo.append(`<div class="alert-item low-stock-item"><span class="alert-iconn"><i class="fas fa-exclamation-circle" id="alt"></i></span><p class="advert"><b>¡ADVERTENCIA CRÍTICA!</b> No quedan materiales no consumibles disponibles para préstamo.</p></div>`);
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
                        const htmlAlerta = `<div class="alert-item"><span class="alert-icon"><i class="fas fa-bell" id="iconnn"></i></span><p class="pp">El material <strong>${item.nombre_material}</strong> prestado a <strong>${item.nombre_instructor}</strong> ha vencido. Fecha límite: <strong>${item.fecha_limite_devolucion}</strong>.</p></div>`;
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

    //MANEJADORES DE EVENTOS 
    const $document = $(document);

    $document.on('click', '.tab-button', function() {
        loadTabContent($(this).data('tab-content'));
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
    });

    $document.on('submit', '#formRegistrarPrestamoMaterial', function(e) {
        e.preventDefault();
        if (($('#material_id').val() || []).length === 0) {
            showFloatingMessage("Por favor, seleccione al menos un material.", 'error'); return;
        }
        if (!$('#instructor').val()) {
            showFloatingMessage("Por favor, seleccione un instructor.", 'error'); return;
        }
        $.ajax({
            url: 'Php/Préstamo_Materiales/Registrar_prestamo_materiales.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showFloatingMessage(response.message, response.success ? 'success' : 'error');
                if (response.success) {
                    loadTabContent('registrar-prestamo');
                    verificarStockBajo();
                }
            },
            error: function(xhr) {
                showFloatingMessage('Error al registrar el préstamo.','error'); console.error(xhr.responseText);
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
    
    //EDICIÓN DE PRÉSTAMO ---
     $document.on('click', '.open-editar-prestamo-modal', function() {
        const modal = $('#editarPrestamoCompletoModal');
        const instructorId = $(this).data('instructor-id');
        
        modal.find('#edit_modal_instructor_id').val(instructorId);
        modal.find('#edit_modal_instructor_info').text($(this).data('instructor-nombre'));
        modal.find('#edit_modal_fecha_prestamo_info').text($(this).data('fecha-prestamo'));
        modal.find('#editable_loan_items_container').html('<p>Cargando...</p>');
        let selectAdd = modal.find('#add_material_to_loan_list');
        if (selectAdd.hasClass("select2-hidden-accessible")) {
            selectAdd.select2('destroy');
        }
        selectAdd.empty();

        $.ajax({
            url: 'Php/Préstamo_Materiales/Obtener_datos_prestamo_edicion_materiales.php',
            type: 'GET',
            data: { id_prestamo_material: $(this).data('id-prestamo') },
            dataType: 'json',
            success: function(response) {
                if (!response.success) { showFloatingMessage(response.message || 'Error al cargar datos.','error'); return; }
                
                const itemsContainer = modal.find('#editable_loan_items_container').empty();
                let addSelect = modal.find('#add_material_to_loan_list').html('<option value="">Seleccione para añadir...</option>');
                const loanedIds = [];

                response.loan_details.forEach(item => {
                    loanedIds.push(String(item.id_material));
                    const stockTotalDisponible = parseInt(item.stock_en_bodega) + parseInt(item.cantidad);
                    const itemHtml = `
                        <div class="editable-item" data-id="${item.id_material}" data-nombre="${item.nombre}" data-stock-total="${stockTotalDisponible}">
                            <span>${item.nombre}</span>
                            <input type="number" class="item-qty" value="${item.cantidad}" min="1" max="${stockTotalDisponible}">
                            <button type="button" class="btn-remove-item"><i class="fa-solid fa-times"></i> Quitar del Préstamo</button>
                        </div>`;
                    itemsContainer.append(itemHtml);
                });

                response.available_materials.forEach(material => {
                    if (!loanedIds.includes(String(material.id_material))) {
                        const option = new Option(`${material.nombre} (Stock: ${material.stock})`, material.id_material);
                        $(option).data('nombre', material.nombre).data('stock', material.stock);
                        addSelect.append(option);
                    }
                });
                
                addSelect.select2({ theme: "default", width: 'style', placeholder: "Seleccione un Material", dropdownParent: modal.find('.modal-content') });
                modal.css('display', 'flex');
            }
        });
    });

    // Al hacer clic en el botón "Añadir"
    $document.on('click', '#btn_add_material_to_list', function() {
        const select = $('#add_material_to_loan_list');
        const selectedOption = select.find('option:selected');
        const materialId = selectedOption.val();
        if (!materialId) return;

        const nombre = selectedOption.data('nombre');
        const stock = selectedOption.data('stock');
        
        const itemHtml = `
            <div class="editable-item" data-id="${materialId}" data-nombre="${nombre}" data-stock-total="${stock}">
                <span>${nombre}</span>
                <input type="number" class="item-qty" value="1" min="1" max="${stock}">
                <button type="button" class="btn-remove-item"><i class="fa-solid fa-times"></i> Quitar del Préstamo</button>
            </div>`;
        $('#editable_loan_items_container').append(itemHtml);
        selectedOption.remove();
        select.val('').trigger('change');
    });

    // Al hacer clic en el botón "Quitar" de un ítem
    $document.on('click', '.btn-remove-item', function() {
        const itemDiv = $(this).closest('.editable-item');
        const id = itemDiv.data('id'), nombre = itemDiv.data('nombre'), stock = itemDiv.data('stock-total');
        const select = $('#add_material_to_loan_list');
        const option = new Option(`${nombre} (Stock: ${stock})`, id);
        $(option).data('nombre', nombre).data('stock', stock);
        select.append(option);
        itemDiv.remove();
    });

    // Al enviar el formulario de edición
    $document.on('submit', '#formEditarPrestamoCompleto', function(e) {
        e.preventDefault();
        const form = $(this);
        const instructorId = form.find('#edit_modal_instructor_id').val();
        let finalState = [], isValid = true;
        const errorDiv = form.find('#edit_loan_error_message').hide().empty();

        $('#editable_loan_items_container .editable-item').each(function() {
            const qty = parseInt($(this).find('.item-qty').val());
            const maxStock = parseInt($(this).find('.item-qty').attr('max'));
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            
            if (isNaN(qty) || qty < 1 || qty > maxStock) {
                errorDiv.html(`Cantidad inválida para "${nombre}". Debe ser entre 1 y ${maxStock}.`).show();
                isValid = false;
                return false; // Sale del loop .each
            }
            finalState.push({ id_material: id, cantidad: qty });
        });

        if (!isValid) return;

        $.ajax({
            url: 'Php/Préstamo_Materiales/Procesar_edicion_prestamo_materiales.php',
            type: 'POST',
            data: {
                instructor_id: instructorId,
                final_state: JSON.stringify(finalState),
                id_responsable: $('#id_responsable_session').val(),
            },
            dataType: 'json',
            success: response => {
                showFloatingMessage(response.message, response.success ? 'success' : 'error');
                if (response.success) {
                    form.closest('.modal').css('display', 'none');
                    loadTabContent('devoluciones-pendientes', instructorId); // Recarga la vista del instructor
                    verificarStockBajo();
                    cargarAlertasVencidas();
                }
            },
            error: () => showFloatingMessage('Error crítico al procesar la edición.','error')
        });
    });

    //MANEJADORES DE MODALES ---
 $document.on('click', '.open-devolver-modal', function() {
        const idPrestamo = $(this).data('id-prestamo');
        const cantidadPrestada = $(this).data('cantidad-prestada');

        // Llenar los campos del modal
        $('#modal_id_prestamo_material').val(idPrestamo);
        $('#modal_cantidad_devolver').val(cantidadPrestada); // Pone por defecto la cantidad total
        $('#modal_cantidad_devolver').prop('max', cantidadPrestada); // Establece el máximo a devolver
        $('#cantidad_maxima_info').text(`Cantidad prestada: ${cantidadPrestada}`); // Muestra info al usuario
        
        // Limpiar campos de estado y observaciones de usos anteriores
        $('#modal_estado_devolucion').val('');
        $('#modal_observaciones').val('');

        $('#devolverMaterialModal').css('display', 'flex');
    });

    $document.on('click', '#btnDevolverSeleccionados2', function() {
        const selectedIds = $('.prestamo-checkbox:checked').map((_, el) => $(el).data('id-prestamo')).get();
        if (selectedIds.length > 0) {
            $('#modal_id_prestamo_material_multiple').val(selectedIds.join(','));
            $('#devolverMaterialModalMultiple').css('display', 'flex');
        } else {
            showFloatingMessage('Por favor, selecciona al menos un préstamo.','error');
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
                showFloatingMessage(response.message, response.success ? 'success' : 'error');
                if (response.success) {
                    form.closest('.modal').css('display', 'none');
                    loadTabContent('devoluciones-pendientes', instructorId);
                    verificarStockBajo();
                    cargarAlertasVencidas();
                }
            },
            error: function(xhr) {
                showFloatingMessage('Error al procesar la devolución.','error'); console.error(xhr.responseText);
            }
        });
    });

    // CARGA INICIAL
    cargarAlertasVencidas();
    verificarStockBajo();
    loadTabContent($('.tab-button.active').data('tab-content') || 'registrar-prestamo');
});