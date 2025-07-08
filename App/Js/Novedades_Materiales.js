$(document).ready(function() {
    const individualModal = $('#modalRegistrarNovedadMaterial');
    const multipleModal = $('#modalRegistrarNovedadesMaterialesMultiples');

    // --- Lógica para mostrar/ocultar botones de acción múltiple ---
    function toggleBulkActionButtons() {
        const anyChecked = $('.prestamo-checkbox:checked').length > 0;
        $('#btnDevolverSeleccionados2').toggle(anyChecked);
        $('#btnNovedadMaterialGeneral').toggle(anyChecked);
    }

    // Listener para checkboxes
    $(document).on('change', '.prestamo-checkbox, #selectAllMateriales', function() {
        toggleBulkActionButtons();
    });

    // --- Lógica para Novedad Individual ---
    $(document).on('click', '.btn-abrir-novedad-material-modal', function() {
        const id = $(this).data('id-prestamo-material');
        const material = $(this).data('nombre-material');
        const instructor = $(this).data('nombre-instructor');

        console.log('Abriendo modal de novedad individual:', { id, material, instructor });

        // Limpiar formulario
        $('#formRegistrarNovedadMaterial')[0].reset();
        $('#novedad_id_prestamo_material').val(id);
        $('#novedad_nombre_material').val(material);
        $('#novedad_nombre_instructor').val(instructor);
        
        individualModal.css('display', 'block');
    });

    $('#formRegistrarNovedadMaterial').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        console.log('Enviando novedad individual:', {
            id_prestamo_material: $('#novedad_id_prestamo_material').val(),
            tipo_novedad: $('#novedad_tipo_material').val(),
            descripcion: $('#novedad_descripcion_material').val(),
            hasFile: !!$('#novedad_file_material')[0].files[0]
        });

        $.ajax({
            url: '/Software_Almacen/App/Php/Préstamo_Materiales/Procesar_Devolucion_Material_Con_Novedad.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(responseText) {
                console.log('Respuesta cruda del servidor (novedad individual):', responseText);
                let response;
                try {
                    response = typeof responseText === 'string' ? JSON.parse(responseText) : responseText;
                    console.log('Respuesta parseada del servidor (novedad individual):', response);
                    if (response.success) {
                        showFloatingMessage(response.message, 'success');
                        individualModal.css('display', 'none');
                        const instructorId = $('.instructor-detail-view').data('instructor-id');
                        if (instructorId) {
                            // Fetch updated content dynamically
                            $.ajax({
                                url: '/Software_Almacen/App/Php/Préstamo_Materiales/Get_devoluciones_pendientes_materiales.php',
                                type: 'GET',
                                data: { instructor_id: instructorId },
                                success: function(updatedContent) {
                                    $('#tab-content-area').html(updatedContent);
                                    console.log('Contenido de la pestaña actualizado');
                                    toggleBulkActionButtons(); // Reinitialize event listeners
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error al actualizar el contenido:', xhr, status, error);
                                    showFloatingMessage('Error al actualizar la vista.', 'error');
                                }
                            });
                        } else {
                            location.reload(); // Fallback to full reload
                        }
                    } else {
                        showFloatingMessage('Error: ' + response.message, 'error');
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e, 'Respuesta cruda:', responseText);
                    showFloatingMessage('Error en la comunicación con el servidor. Respuesta: ' + responseText, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX (novedad individual):', xhr.responseText, status, error);
                showFloatingMessage('Error en la comunicación con el servidor. Detalle: ' + xhr.responseText, 'error');
            }
        });
    });

    // --- Lógica para Novedad Múltiple ---
    $(document).on('click', '#btnNovedadMaterialGeneral', function() {
        let selectedItems = [];
        $('.prestamo-checkbox:checked').each(function() {
            selectedItems.push({
                id_prestamo_material: $(this).data('id-prestamo')
            });
        });

        if (selectedItems.length === 0) {
            showFloatingMessage('Debe seleccionar al menos un material.', 'warning');
            return;
        }

        console.log('Abriendo modal de novedad múltiple:', selectedItems);

        $('#formRegistrarNovedadesMaterialesMultiples')[0].reset();
        $('#selectedMaterialItems').val(JSON.stringify(selectedItems));
        multipleModal.css('display', 'block');
    });

    $('#formRegistrarNovedadesMaterialesMultiples').on('submit', function(e) {
        e.preventDefault();
        const payload = {
            tipo_novedad: $('#novedadMultipleTipoMaterial').val(),
            descripcion: $('#novedadMultipleDescripcionMaterial').val(),
            items: JSON.parse($('#selectedMaterialItems').val())
        };

        console.log('Enviando novedad múltiple:', payload);

        $.ajax({
            url: '/Software_Almacen/App/Php/Préstamo_Materiales/Procesar_Devolucion_Material_Con_Novedad_Multiple.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
                console.log('Respuesta del servidor (novedad múltiple):', response);
                if (response.success) {
                        showFloatingMessage(response.message, 'success');
                        individualModal.css('display', 'none');
                        const instructorId = $('.instructor-detail-view').data('instructor-id');
                        if (instructorId) {
                            // Fetch updated content dynamically
                            $.ajax({
                                url: '/Software_Almacen/App/Php/Préstamo_Materiales/Get_devoluciones_pendientes_materiales.php',
                                type: 'GET',
                                data: { instructor_id: instructorId },
                                success: function(updatedContent) {
                                    $('#tab-content-area').html(updatedContent);
                                    console.log('Contenido de la pestaña actualizado');
                                    toggleBulkActionButtons(); // Reinitialize event listeners
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error al actualizar el contenido:', xhr, status, error);
                                    showFloatingMessage('Error al actualizar la vista.', 'error');
                                }
                            });
                        } else {
                            location.reload(); // Fallback to full reload
                        }
                    } else {
                    showFloatingMessage('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX (novedad múltiple):', xhr, status, error);
                showFloatingMessage('Error en la comunicación con el servidor.', 'error');
            }
        });
    });

    // --- Cerrar Modales ---
    $('.close-button').on('click', function() {
        $(this).closest('.modal').css('display', 'none');
    });
});
