$(document).ready(function() {
    //MODAL DE NOVEDAD INDIVIDUAL
    const modalRegistrarNovedad2 = $('#modalRegistrarNovedad2');
    const formRegistrarNovedad2 = $('#formRegistrarNovedad2');
    const cerrarNovedad2ModalBtn = $('#cerrarNovedad2ModalBtn');
    const novedad2IdPrestamoEquipoDetalle = $('#novedad2_id_prestamo_equipo_detalle');
    const novedad2IdPrestamoEquipo = $('#novedad2_id_prestamo_equipo');
    const novedad2IdEquipo = $('#novedad2_id_equipo');
    const novedad2NombreEquipo = $('#novedad2_nombre_equipo');
    const novedad2IdInstructor = $('#novedad2_id_instructor');
    const novedad2NombreInstructor = $('#novedad2_nombre_instructor');
    const novedad2Tipo = $('#novedad2_tipo');
    const novedad2Descripcion = $('#novedad2_descripcion');
    //MODAL DE NOVEDAD MULTIPLE 
    const modalNovedadesMultiples = $('#modalRegistrarNovedadesMultiples');
    const formNovedadesMultiples = $('#formRegistrarNovedadesMultiples');
    const cerrarNovedadesMultiplesModalBtn = $('#cerrarNovedadesMultiplesModalBtn');

    $(document).on('click', '.btn-abrir-novedad2-modal', function() {
        const idPrestamoDetalle = $(this).data('id_prestamo_equipo_detalle');
        const idPrestamo = $(this).data('id_prestamo_equipo');
        const idEquipo = $(this).data('id_equipo');
        const nombreEquipo = $(this).data('nombre_equipo');
        const idInstructor = $(this).data('id_instructor');
        const nombreInstructor = $(this).data('nombre_instructor');

        novedad2IdPrestamoEquipoDetalle.val(idPrestamoDetalle);
        novedad2IdPrestamoEquipo.val(idPrestamo);
        novedad2IdEquipo.val(idEquipo);
        novedad2NombreEquipo.val(nombreEquipo);
        novedad2IdInstructor.val(idInstructor);
        novedad2NombreInstructor.val(nombreInstructor);
        novedad2Descripcion.val('');
        novedad2Tipo.val('');
        modalRegistrarNovedad2.show();
    });

    cerrarNovedad2ModalBtn.on('click', () => modalRegistrarNovedad2.hide());

    
formRegistrarNovedad2.on('submit', function(e) {
    e.preventDefault();
    if (novedad2Tipo.val() === "" || novedad2Descripcion.val().trim() === "") {
        showFloatingMessage("Debe seleccionar un tipo y escribir una descripción.", 'error');
        return;
    }

    const formData = new FormData(this); 

    $.ajax({
        url: '/Software_Almacen/App/Php/Préstamo_Equipos/Procesar_Devolucion_Con_Novedad2.php',
        type: 'POST',
        data: formData, 
        contentType: false, 
        processData: false, 
        beforeSend: () => formRegistrarNovedad2.find('button[type="submit"]').prop('disabled', true).text('Procesando...'),
        success: function(response) {
            if (response.success) {
                showFloatingMessage(response.message, 'success');
                location.reload(); 
                modalRegistrarNovedad2.hide();
                
                // Refresco mejorado
                setTimeout(() => {
                    const instructorId = $('.instructor-detail-view').data('instructor-id');
                    if (instructorId) {
                        cargarContenidoPestana('devoluciones-pendientes', instructorId);
                    } else {
                        cargarContenidoPestana('devoluciones-pendientes');
                    }
                }, 300);

            } else {
                showFloatingMessage('❌ ' + (response.message || 'Error desconocido'), 'error');
            }
        },
        error: (jqXHR) => showFloatingMessage('❌ Error de comunicación: ' .concat(jqXHR.statusText), 'error'),
        complete: () => formRegistrarNovedad2.find('button[type="submit"]').prop('disabled', false).html('<i class="fa-solid fa-save"></i> Registrar Novedad y Devolver')
    });
});

    function actualizarBotonesSeleccion() {
        const haySeleccionados = $('.prestamo-checkbox:checked').length > 0;
        $('#btnDevolverSeleccionados').prop('disabled', !haySeleccionados);
        $('#btnNovedadSeleccionados').prop('disabled', !haySeleccionados);
    }

    // Listener para los checkboxes para actualizar los botones
    $(document).on('change', '.prestamo-checkbox, #selectAllPrestamos', function() {
        actualizarBotonesSeleccion();
    });
    
    // Listener para abrir el modal de novedad múltiple
    $(document).on('click', '#btnNovedadSeleccionados', function() {
        let selectedItems = [];
        $('.prestamo-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            selectedItems.push({
                id_prestamo_equipo_detalle: row.data('id-detalle')
            });
        });

        if (selectedItems.length === 0) {
            showFloatingMessage('Debe seleccionar al menos un equipo.', 'error');
            return;
        }
        
        // Limpiar y poblar el modal
        formNovedadesMultiples[0].reset();
        $('#selectedItems').val(JSON.stringify(selectedItems));
        modalNovedadesMultiples.show();
    });

    // Listener para cerrar el modal de novedad múltiple
    cerrarNovedadesMultiplesModalBtn.on('click', () => modalNovedadesMultiples.hide());

    // Listener para enviar el formulario de novedad múltiple
    formNovedadesMultiples.on('submit', function(e) {
        e.preventDefault();

        const tipoNovedad = $('#novedadMultipleTipo').val();
        const descripcion = $('#novedadMultipleDescripcion').val().trim();
        const itemsJson = $('#selectedItems').val();

        if (!tipoNovedad || !descripcion) {
            showFloatingMessage('Debe seleccionar un tipo de novedad y escribir una descripción.', 'error');
            return;
        }

        const payload = {
            tipo_novedad: tipoNovedad,
            descripcion: descripcion,
            items: JSON.parse(itemsJson)
        };

        $.ajax({
            url: '/Software_Almacen/App/Php/Préstamo_Equipos/Procesar_Devolucion_Con_Novedad_Multiple.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            beforeSend: () => formNovedadesMultiples.find('button[type="submit"]').prop('disabled', true).text('Procesando...'),
            success: function(response) {
                if (response.success) {
                    showFloatingMessage(response.message, 'success');
                    modalNovedadesMultiples.hide();
                    
                    setTimeout(() => {
                        const instructorId = $('.instructor-detail-view').data('instructor-id');
                        if (instructorId) {
                            cargarContenidoPestana('devoluciones-pendientes', instructorId);
                        } else {
                            cargarContenidoPestana('devoluciones-pendientes');
                        }
                    }, 300); // 300ms de espera

                } else {
                    showFloatingMessage('❌ ' + (response.message || 'Error desconocido'), 'error');
                }
            },
        });
    });
    // Cierre genérico de modales al hacer clic fuera
    $(window).on('click', function(event) {
        if ($(event.target).is(modalRegistrarNovedad2)) modalRegistrarNovedad2.hide();
        if ($(event.target).is(modalNovedadesMultiples)) modalNovedadesMultiples.hide();
    });

    // Llamada inicial para asegurar que los botones estén en el estado correcto al cargar.
    actualizarBotonesSeleccion();
});