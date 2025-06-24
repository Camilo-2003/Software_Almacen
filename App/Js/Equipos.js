function validarFormulario() {
const equipoIds = $('#equipo_id').val(); 
const instructorId = $('#instructor_id').val(); 

if (!equipoIds || equipoIds.length === 0) {
    alert("Por favor, seleccione al menos un equipo para el préstamo.");
    return false;
}

if (!instructorId || instructorId === "" || instructorId === "0") {
    alert("Por favor, seleccione un instructor.");
    return false;
}

return true;
}
$(document).ready(function() {
// Modales y formularios de devolución
const modalDevolverEquipoIndividual = $('#devolverEquipoModal');
const campoIdPrestamoIndividual = $('#modal_id_prestamo_equipo');
const campoEstadoDevolucionIndividual = $('#modal_estado_devolucion');
const campoObservacionesIndividual = $('#modal_observaciones');
const formularioDevolucionIndividual = $('#formRegistrarDevolucionIndividual');
const botonCerrarModalIndividual = modalDevolverEquipoIndividual.find('.close-button');

const modalDevolverMultiple = $('#devolverModal');
const formularioDevolucionMultiple = $('#formDevolverEquipoMultiple');
const botonCerrarModalMultiple = modalDevolverMultiple.find('.close-button');

// Modal de edición de préstamo completo
const modalEditarPrestamoCompleto = $('#editarPrestamoCompletoModal');
const formularioEditarPrestamoCompleto = $('#formEditarPrestamoCompleto');
const botonCerrarModalEditarCompleto = modalEditarPrestamoCompleto.find('.close-button');
const campoIdPrestamoEditarCompleto = $('#edit_modal_id_prestamo_equipo_completo');
const infoInstructorModalEditar = $('#edit_modal_instructor_info');
const infoFechaPrestamoModalEditar = $('#edit_modal_fecha_prestamo_info');
const contenedorEquiposPrestadosActuales = $('#current_loaned_equipment');
const selectAnadirEquipoAPrestamo = $('#add_equipo_id_to_loan');

// Contenedores de contenido dinámico y alertas
const areaContenidoPestanas = $('#tab-content-area');
const areaAlertasVencidas = $('#overdue-alerts-area');
const contenidoAlertasVencidas = $('#overdue-alerts-content');
const areaAlertaStockBajo = $('#low-stock-alert-area');
const contenidoAlertaStockBajo = $('#low-stock-alert-content');

//alerta de stock bajo
const UMBRAL_STOCK_BAJO = 5; 

/**
 * Carga el contenido de la pestaña seleccionada dinámicamente.
 * @param {string} nombrePestana - El nombre de la pestaña a cargar (ej. 'registrar-prestamo').
 * @param {number} [idInstructor=0] - Opcional. ID de un instructor para filtrar resultados.
 */
function cargarContenidoPestana(nombrePestana, idInstructor = 0) {
    areaContenidoPestanas.html('<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">Cargando...</p>');

    let url = '';
    if (nombrePestana === 'registrar-prestamo') {
        // Renderiza la estructura HTML básica del formulario de préstamo
        areaContenidoPestanas.html(`
            <form id="formRegistrarPrestamo" method="post">
                <h2>Registrar Nuevo Préstamo de Equipo</h2>
                <div class="form-group">
                    <label for="equipo_id">Equipos:</label>
                    <select name="equipo_ids[]" id="equipo_id" class="select2-enhanced" multiple="multiple" required>
                    </select>
                </div>

                <div class="form-group">
                    <label for="instructor_id">Instructor:</label>
                    <select name="instructor" id="instructor_id" class="select2-enhanced" required>
                    </select>
                </div>
                <div class="form-group">
                    <label>Responsable del Préstamo:</label>
                    <input type="text" value="${nombreResponsableCompletoJs} (Rol: ${rolResponsableSessionJs})" readonly>
                    <input type="hidden" name="id_responsable" value="${idResponsableSessionJs}">
                    <input type="hidden" name="rol_responsable" value="${rolResponsableSessionJs}">
                    <input type="hidden" name="responsable" value="${nombreResponsableCompletoJs}">

                </div>
                <button type="submit"><i class="fa-solid fa-handshake" id="icon"></i> Prestar Equipo</button>
            </form>
        `);

        // Carga dinámica de datos para los selects del formulario
        $.ajax({
            url: 'Php/Préstamo_Equipos/Obtener_datos_formulario_prestamo.php', 
            type: 'GET',
            dataType: 'json',
            success: function(respuesta) {
                if (respuesta.success) {
                    const $selectEquipo = $('#equipo_id');
                    const $selectInstructor = $('#instructor_id');

                    // Destruir instancias existentes de Select2 antes de mostrar
                    if ($selectEquipo.data('select2')) { $selectEquipo.select2('destroy'); }
                    if ($selectInstructor.data('select2')) { $selectInstructor.select2('destroy'); }

                    // Limpiar opciones previas
                    $selectEquipo.empty();
                    $selectInstructor.empty();

                    // Añadir opciones por defecto
                    $selectEquipo.append('<option value="">Seleccione el equipo</option>');
                    $selectInstructor.append('<option value="">Seleccione un instructor</option>');

                    // Mostrar Select2 de equipos
                    if (respuesta.equipos && respuesta.equipos.length > 0) {
                        respuesta.equipos.forEach(function(equipo) {
                            $selectEquipo.append(new Option(`${equipo.marca} - ${equipo.serial}`, equipo.id_equipo, false, false));
                        });
                    } else {
                        $selectEquipo.append('<option value="" disabled>No hay equipos disponibles</option>');
                    }

                    // Mostrar Select2 de instructores
                    if (respuesta.instructores && respuesta.instructores.length > 0) {
                        respuesta.instructores.forEach(function(instructor) {
                            $selectInstructor.append(new Option(`${instructor.nombre} ${instructor.apellido}`, instructor.id_instructor, false, false));
                        });
                    } else {
                        $selectInstructor.append('<option value="" disabled>No hay instructores disponibles</option>');
                    }

                    // Reinicializar Select2
                    $selectEquipo.select2({
                        theme: "default", width: '100%', placeholder: "Seleccione uno o más equipos", allowClear: true, multiple: true,
                        matcher: function(params, data) {
                            if ($.trim(params.term) === '') { return data; }
                            if (data.id === '') { return null; }
                            const term = $.trim(params.term).toLowerCase();
                            const text = $.trim(data.text).toLowerCase();
                            if (text.indexOf(term) > -1) { return data; }
                            return null;
                        }
                    });
                    $selectInstructor.select2({
                        theme: "default", width: '100%', placeholder: "Seleccione un instructor", allowClear: true,
                        matcher: function(params, data) {
                            if ($.trim(params.term) === '') { return data; }
                            if (data.id === '') { return null; }
                            const term = $.trim(params.term).toLowerCase();
                            const text = $.trim(data.text).toLowerCase();
                            if (text.indexOf(term) > -1) { return data; }
                            return null;
                        }
                    });

                    // Adjuntar manejadores de eventos 'open' para limpiar espacios
                    $selectEquipo.on('select2:open', function() {
                        const idSelect = $(this).attr('id');
                        const $campoBusqueda = $(`[aria-controls="select2-${idSelect}-results"]`);
                        $campoBusqueda.off('input.trimspaces').on('input.trimspaces', function() {
                            const valorAnterior = $(this).val();
                            const nuevoValor = valorAnterior.replace(/^\s+/, '');
                            if (valorAnterior !== nuevoValor) { $(this).val(nuevoValor).trigger('change'); }
                        });
                    });
                    $selectInstructor.on('select2:open', function() {
                        const idSelect = $(this).attr('id');
                        const $campoBusqueda = $(`[aria-controls="select2-${idSelect}-results"]`);
                        $campoBusqueda.off('input.trimspaces').on('input.trimspaces', function() {
                            const valorAnterior = $(this).val();
                            const nuevoValor = valorAnterior.replace(/^\s+/, '');
                            if (valorAnterior !== nuevoValor) { $(this).val(nuevoValor).trigger('change'); }
                        });
                    });

                    // Adjuntar manejador de submit del formulario de préstamo
                    $('#formRegistrarPrestamo').off('submit').on('submit', manejarEnvioFormularioPrestamo);

                    // Después de cargar los datos exitosamente, verificar stock bajo
                    verificarStockBajo(respuesta.total_equipos_disponibles);

                } else {
                    areaContenidoPestanas.html(`<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar los datos del formulario de préstamo: ${respuesta.message}</p>`);
                    // Ocultar alerta de stock bajo si la carga de datos falla
                    areaAlertaStockBajo.hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let mensajeError = `Error al cargar los datos del formulario de préstamo: ${textStatus} - ${errorThrown}. Respuesta: ${jqXHR.responseText}`;
                console.error(mensajeError);
                areaContenidoPestanas.html(`<p style="text-align: center; color: #dc3545; padding: 20px;">${mensajeError}</p>`);
                // Ocultar alerta de stock bajo en caso de error AJAX
                areaAlertaStockBajo.hide();
            }
        });

    } else {
        if ($('#equipo_id').data('select2')) { $('#equipo_id').select2('destroy'); }
        if ($('#instructor_id').data('select2')) { $('#instructor_id').select2('destroy'); }
        
        // Determinar la URL para otras pestañas
        if (nombrePestana === 'devoluciones-pendientes') {
            url = `Php/Préstamo_Equipos/Get_devoluciones_pendientes_equipos.php${idInstructor > 0 ? '?instructor_id=' + idInstructor : ''}`;
        } else if (nombrePestana === 'historial-prestamos') {
            url = 'Php/Préstamo_Equipos/Get_historial_prestamos_equipos.php';
        } else if (nombrePestana === 'equipos-disponibles') {
            url = 'Php/Préstamo_Equipos/Get_equipos_disponibles.php';
        } else if (nombrePestana === 'total-equipos') {
            url = 'Php/Préstamo_Equipos/Get_total_equipos.php';
        } else if (nombrePestana === 'observaciones') {
            url = 'Php/Préstamo_Equipos/Get_observaciones_equipos.php';
        }

        if (url) {
            $.ajax({
                url: url,
                type: 'GET',
                success: function(datos) {
                    areaContenidoPestanas.html(datos);
                    
                    if (nombrePestana === 'devoluciones-pendientes') {
                        if (idInstructor > 0) {
                            adjuntarManejadoresBotonesDevolver();
                            adjuntarManejadoresBotonesEditarPrestamo();
                            configurarDevolucionMultiple(); 
                            $('#backToInstructorList').off('click').on('click', function() {
                                cargarContenidoPestana('devoluciones-pendientes', 0);
                            });
                        } else {
                            $('.instructor-item-button').off('click').on('click', function() {
                                const idInstructorSeleccionado = $(this).data('instructor-id');
                                cargarContenidoPestana('devoluciones-pendientes', idInstructorSeleccionado);
                            });
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) { 
                    console.error(`Error en solicitud GET para ${url}: ${textStatus}, ${errorThrown}, ${jqXHR.responseText}`);
                    areaContenidoPestanas.html('<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar el contenido.</p>');
                }
            });
        }
        // Ocultar alerta de stock bajo cuando no está en la pestaña 'registrar-prestamo'
        areaAlertaStockBajo.hide();
    }
    // Siempre cargar las alertas de vencimiento al cambiar de pestaña o al iniciar
    cargarAlertasVencidas();
}
function manejarEnvioFormularioPrestamo(e) {
    e.preventDefault();

    const equipoIds = $('#equipo_id').val();
    const instructorId = $('#instructor_id').val();

    if (!equipoIds || equipoIds.length === 0) {
        alert("Por favor, seleccione al menos un equipo para el préstamo.");
        console.warn("Validación fallida: No se seleccionaron equipos.");
        return false;
    }
    if (instructorId === "" || instructorId === "0") {
        alert("Por favor, seleccione un instructor.");
        console.warn("Validación fallida: No se seleccionó instructor.");
        return false;
    }

    // Recopilar datos incluyendo campos ocultos para el responsable
    const datosFormularioArray = $(this).serializeArray();
    const datosAEnviar = {};
    datosFormularioArray.forEach(item => {
        if (item.name.endsWith('[]')) { 
            const clave = item.name.slice(0, -2);
            if (!datosAEnviar[clave]) {
                datosAEnviar[clave] = [];
            }
            datosAEnviar[clave].push(item.value);
        } else {
            datosAEnviar[item.name] = item.value;
        }
    });
    $.ajax({
        url: 'Php/Préstamo_Equipos/Registrar_prestamo_equipos.php',
        type: 'POST',
        contentType: 'application/json', 
        data: JSON.stringify(datosAEnviar),
        dataType: 'json',
        success: function(respuesta) {
            console.log("Respuesta AJAX de registro de préstamo:", respuesta);
            if (respuesta.success) {
                alert(respuesta.message);
                // Recargar la pestaña 'registrar-prestamo' para obtener listas actualizadas
                cargarContenidoPestana('registrar-prestamo');
            } else {
                alert('❌ ' + respuesta.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            let mensajeError = 'Error al procesar la solicitud.';
            try {
                const respuestaJson = JSON.parse(jqXHR.responseText);
                mensajeError = respuestaJson.message || 'Error desconocido del servidor.';
            } catch (e) {
                mensajeError = `Error en la comunicación con el servidor: ${textStatus} - ${errorThrown}. Respuesta: ${jqXHR.responseText}`;
            }
            console.error("Error AJAX en registro de préstamo:", mensajeError);
            alert('❌ ' + mensajeError);
        }
    });
}

function adjuntarManejadoresBotonesDevolver() {
    console.log("adjuntarManejadoresBotonesDevolver: Adjuntando eventos a botones individuales.");
    areaContenidoPestanas.off('click', '.open-devolver-modal').on('click', '.open-devolver-modal', function() {
        const idPrestamoDetalle = $(this).data('id-prestamo-detalle');
        campoIdPrestamoIndividual.val(idPrestamoDetalle);
        campoEstadoDevolucionIndividual.val('');
        campoObservacionesIndividual.val('');
        modalDevolverEquipoIndividual.css('display', 'flex'); 
    });
}

function adjuntarManejadoresBotonesEditarPrestamo() {
    areaContenidoPestanas.off('click', '.open-editar-prestamo-modal').on('click', '.open-editar-prestamo-modal', function() {
        const idPrestamo = $(this).data('id-prestamo');
        const nombreInstructor = $(this).data('instructor-nombre');
        const fechaPrestamo = $(this).data('fecha-prestamo');

        campoIdPrestamoEditarCompleto.val(idPrestamo);
        infoInstructorModalEditar.text(nombreInstructor);
        infoFechaPrestamoModalEditar.text(fechaPrestamo);
        
        if (selectAnadirEquipoAPrestamo.data('select2')) {
            selectAnadirEquipoAPrestamo.select2('destroy');
            console.log("DEBUG: Select2 destruido para selectAnadirEquipoAPrestamo.");
        }
        
        // Limpiar las opciones del elemento <select>
        selectAnadirEquipoAPrestamo.empty(); 
        // Añadir la opción de placeholder inicial
        selectAnadirEquipoAPrestamo.append('<option value="">Seleccione equipos para añadir</option>'); 


        // Cargar datos completos del préstamo y equipos disponibles vía AJAX
        $.ajax({
            url: 'Php/Préstamo_Equipos/Obtener_datos_prestamo_edicion.php',
            type: 'GET',
            dataType: 'json',
            data: { id_prestamo_equipo: idPrestamo },
            success: function(datos) {

                // Poblar equipos actualmente prestados
                if (datos.loan_details && datos.loan_details.length > 0) {
                    contenedorEquiposPrestadosActuales.empty(); // Limpiar contenido previo
                    datos.loan_details.forEach(function(item) {
                        let htmlItem = ``;
                        if (item.estado_item_prestamo !== 'prestado') { // Si ya no está prestado
                            htmlItem = `
                                <div class="equipment-item-edit equipment-item-returned" data-id-detalle="${item.id_prestamo_equipo_detalle}">
                                    <span>${item.equipo_marca_serial}</span>
                                    <span>Estado: ${item.estado_item_prestamo}</span>
                                    <span class="status-info"> Ya devuelto/inactivo</span>
                                </div>
                            `;
                        } else {
                            htmlItem = `
                                <div class="equipment-item-edit" data-id-detalle="${item.id_prestamo_equipo_detalle}">
                                    <span>${item.equipo_marca_serial}</span>
                                    <span>Estado: ${item.estado_item_prestamo}</span>
                                    <button type="button" class="btn-remove-item" 
                                        data-id-detalle="${item.id_prestamo_equipo_detalle}"
                                        title="Marcar como devuelto/cancelado">
                                        <i class="fa-solid fa-times"></i> Quitar del Préstamo
                                    </button>
                                </div>
                            `;
                        }
                        contenedorEquiposPrestadosActuales.append(htmlItem);
                    });
                } else {
                    contenedorEquiposPrestadosActuales.html('<p>No hay equipos activos en este préstamo.</p>');
                }

                // Poblar Select2 con equipos disponibles para añadir
                if (Array.isArray(datos.available_equipment) && datos.available_equipment.length > 0) {
                    datos.available_equipment.forEach(function(equipo) {
                        selectAnadirEquipoAPrestamo.append(new Option(`${equipo.marca} - ${equipo.serial}`, equipo.id_equipo, false, false));
                    });
                } else {
                    selectAnadirEquipoAPrestamo.empty(); 
                    selectAnadirEquipoAPrestamo.append('<option value="">No hay equipos disponibles para añadir.</option>'); 
                }

                // Reinicializar Select2 en el modal DESPUÉS de añadir todas las opciones
                selectAnadirEquipoAPrestamo.select2({
                    theme: "default", width: '100%', placeholder: "Seleccione equipos para añadir",
                    allowClear: true, multiple: true,
                    dropdownParent: modalEditarPrestamoCompleto 
                });                    
                selectAnadirEquipoAPrestamo.val(null).trigger('change'); 
                
                // Manejar clic en "Quitar del Préstamo" (delegación)
                contenedorEquiposPrestadosActuales.off('click', '.btn-remove-item').on('click', '.btn-remove-item', function() {
                    const idItemARemover = $(this).data('id-detalle');
                    if (confirm(`¿Estás seguro de que quieres quitar este equipo (ID de detalle: ${idItemARemover}) del préstamo? Esto lo marcará como CANCELADO.`)) {
                        $(this).closest('.equipment-item-edit').addClass('marked-for-removal');
                        $(this).prop('disabled', true).text('Marcado para Quitar');
                        formularioEditarPrestamoCompleto.append(`<input type="hidden" name="items_to_remove[]" value="${idItemARemover}">`);
                    }
                });

                modalEditarPrestamoCompleto.css('display', 'flex'); // Mostrar el modal
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let mensajeError = 'Error al procesar la solicitud.';
                try {
                    const respuestaJson = JSON.parse(jqXHR.responseText);
                    mensajeError = respuestaJson.message || 'Error desconocido del servidor.';
                } catch (e) {
                    mensajeError = `Error en la comunicación con el servidor: ${textStatus} - ${errorThrown}. Respuesta: ${jqXHR.responseText}`;
                }
                console.error("Error al cargar datos del préstamo para edición:", mensajeError);
                alert('❌ Error al cargar los datos del préstamo: ' + mensajeError);
            }
        });
    });
}
function configurarDevolucionMultiple() {
    const $areaContenidoPestana = $('#tab-content-area');
    
    const $seleccionarTodosPrestamos = $areaContenidoPestana.find('#selectAllPrestamos');
    const $checkboxesPrestamo = $areaContenidoPestana.find('.prestamo-checkbox'); 
    const $botonDevolverSeleccionados = $areaContenidoPestana.find('#btnDevolverSeleccionados');

    console.log("DEBUG: Elementos de devolución múltiple encontrados:", {
        $seleccionarTodosPrestamosLongitud: $seleccionarTodosPrestamos.length,
        $checkboxesPrestamoLongitud: $checkboxesPrestamo.length,
        $botonDevolverSeleccionadosLongitud: $botonDevolverSeleccionados.length
    });

    $seleccionarTodosPrestamos.off('change').on('change', function() {
        $checkboxesPrestamo.not(':disabled').prop('checked', this.checked).trigger('change');
    });

    $checkboxesPrestamo.off('change').on('change', function() {
        const conteoSeleccionado = $checkboxesPrestamo.filter(':checked').length;
        if (conteoSeleccionado > 0) {
            $botonDevolverSeleccionados.show();
        } else {
            $botonDevolverSeleccionados.hide();
        }
        if (!this.checked) {
            $seleccionarTodosPrestamos.prop('checked', false);
        } else if (conteoSeleccionado === $checkboxesPrestamo.not(':disabled').length && $checkboxesPrestamo.not(':disabled').length > 0) {
            $seleccionarTodosPrestamos.prop('checked', true);
        }
    });

    $botonDevolverSeleccionados.off('click').on('click', function() {
        const idsPrestamoSeleccionados = [];
        
        $checkboxesPrestamo.filter(':checked').not(':disabled').each(function() {
            const id = $(this).data('id-prestamo-detalle');
            if (id) {
                idsPrestamoSeleccionados.push(id);
            } else {
                console.warn("DEBUG: Checkbox sin 'data-id-prestamo-detalle' encontrado.");
            }
        });

        if (idsPrestamoSeleccionados.length === 0) {
            alert('Por favor, selecciona al menos un equipo para devolver.');
            console.warn("No se seleccionaron equipos para devolución múltiple.");
            return;
        }

        formularioDevolucionMultiple[0].reset();
        formularioDevolucionMultiple.find('#modal_estado_devolucion_multiple').val('');
        formularioDevolucionMultiple.find('#modal_observaciones_multiple').val('');
        formularioDevolucionMultiple.find('#devolver_error_message').hide();

        modalDevolverMultiple.css('display', 'flex'); 
    });

    formularioDevolucionMultiple.off('submit').on('submit', function(e) {
        e.preventDefault();

        const estadoDevolucion = $(this).find('#modal_estado_devolucion_multiple').val();
        const observaciones = $(this).find('#modal_observaciones_multiple').val();
        
        const idsPrestamoSeleccionados = [];
        $('#tab-content-area').find('.prestamo-checkbox:checked').not(':disabled').each(function() {
            idsPrestamoSeleccionados.push($(this).data('id-prestamo-detalle'));
        });

        if (!estadoDevolucion) {
            alert('Por favor, selecciona el estado de devolución para los equipos seleccionados.');
            console.warn("Estado de devolución múltiple no seleccionado.");
            return;
        }
        if (idsPrestamoSeleccionados.length === 0) { 
            alert('No se encontraron equipos seleccionados para devolver. Por favor, selecciona al menos uno.');
            console.warn("No se recopilaron IDs al momento del envío del formulario JSON.");
            return;
        }

        const datosAEnviar = {
            id_prestamo_equipo_detalle: idsPrestamoSeleccionados,
            id_responsable: idResponsableSessionJs, // Usar la variable JS
            rol_responsable: rolResponsableSessionJs, // Usar la variable JS
            responsable: nombreResponsableCompletoJs, // Usar la variable JS
            estado_devolucion: estadoDevolucion,
            observaciones: observaciones
        };

        $.ajax({
            url: 'Php/Préstamo_Equipos/Registrar_Devolucion_Equipos.php',
            type: 'POST',
            contentType: 'application/json', 
            data: JSON.stringify(datosAEnviar), 
            dataType: 'json',
            success: function(respuesta) {
                if (respuesta.success) {
                    alert(respuesta.message);
                    modalDevolverMultiple.css('display', 'none');
                    const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
                    cargarContenidoPestana('devoluciones-pendientes', idInstructorActual); 
                    $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
                } else {
                    $('#devolver_error_message').text('❌ ' + (respuesta.message || 'Error desconocido.')).show();
                    console.error("Error en respuesta AJAX (backend - JSON):", respuesta.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let mensajeError = 'Error al procesar la solicitud.';
                try {
                    mensajeError = JSON.parse(jqXHR.responseText).message || 'Error desconocido del servidor.';
                } catch (e) {
                    mensajeError = `Error en la comunicación con el servidor: ${textStatus} - ${errorThrown}. Respuesta: ${jqXHR.responseText}`;
                }
                console.error("Respuesta AJAX de devolución múltiple (ERROR - JSON):", mensajeError, jqXHR.responseText);
                $('#devolver_error_message').text('❌ ' + mensajeError).show();
            }
        });
    });
} // Fin de configurarDevolucionMultiple
function cargarAlertasVencidas() {
    $.ajax({
        url: 'Php/Préstamo_Equipos/Obtener_prestamos_vencidos.php', 
        type: 'GET',
        dataType: 'json',
        success: function(respuesta) {
            contenidoAlertasVencidas.empty(); // Limpiar alertas previas

            if (respuesta.success && respuesta.overdue_items.length > 0) {
                contenidoAlertasVencidas.removeClass('no-alerts-message').addClass('has-alerts');
                respuesta.overdue_items.forEach(function(item) {
                    const htmlAlerta = `
                        <div class="alert-item">
                            <span class="alert-icon"><i class="fas fa-bell"></i></span>
                            <p>El equipo <strong>${item.nombre_equipo}</strong> prestado al instructor <strong>${item.nombre_instructor}</strong> ha vencido su plazo de devolución. Debía ser devuelto antes de: <strong>${item.fecha_vencimiento_item}</strong>.</p>
                        </div>
                    `;
                    contenidoAlertasVencidas.append(htmlAlerta);
                });
                areaAlertasVencidas.show(); // Mostrar el contenedor general de alertas
            } else {
                contenidoAlertasVencidas.html('<p class="no-alerts-message">No hay alertas de equipos vencidos en este momento.</p>');
                contenidoAlertasVencidas.removeClass('has-alerts').addClass('no-alerts-message');
                areaAlertasVencidas.hide(); // Ocultar si no hay alertas
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error(`Error al obtener alertas de vencimiento: ${textStatus}, ${errorThrown}, ${jqXHR.responseText}`);
            contenidoAlertasVencidas.html('<p class="error-message">Error al cargar las alertas de vencimiento.</p>');
            contenidoAlertasVencidas.removeClass('has-alerts').addClass('no-alerts-message');
            areaAlertasVencidas.show(); // Mostrar error, así que mantener contenedor visible
        }
    });
}

/**
 * Verifica la cantidad total de equipos disponibles y muestra una alerta si el stock es bajo.
 * @param {number} totalDisponible - El número total de equipos disponibles.
 */
function verificarStockBajo(totalDisponible) {
    contenidoAlertaStockBajo.empty(); // Limpiar contenido previo

    if (totalDisponible <= UMBRAL_STOCK_BAJO && totalDisponible > 0) {
        contenidoAlertaStockBajo.append(`
            <div class="alert-item low-stock-item">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <p>¡ATENCIÓN! El stock de equipos disponibles es bajo. Solo quedan <strong>${totalDisponible}</strong> equipos disponibles para préstamo.</p>
            </div>
        `);
        areaAlertaStockBajo.show();
        areaAlertaStockBajo.removeClass('no-alerts-message').addClass('has-alerts');
        console.warn(`ALERTA DE STOCK BAJO: ¡Solo quedan ${totalDisponible} ítems!`);
    } else if (totalDisponible === 0) {
            contenidoAlertaStockBajo.append(`
            <div class="alert-item low-stock-item">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <p>¡ADVERTENCIA CRÍTICA! No quedan equipos disponibles para préstamo.</p>
            </div>
        `);
        areaAlertaStockBajo.show();
        areaAlertaStockBajo.removeClass('no-alerts-message').addClass('has-alerts');
        console.error("ALERTA CRÍTICA DE STOCK: ¡No quedan ítems!");
    }
    else {
        areaAlertaStockBajo.hide(); // Ocultar la alerta si el stock es suficiente
    }
}

// Cierre del modal de devolución individual
botonCerrarModalIndividual.on('click', function() {
    modalDevolverEquipoIndividual.css('display', 'none');
    const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
    cargarContenidoPestana('devoluciones-pendientes', idInstructorActual); 
    $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
});

$(window).off('click.devolverIndividual').on('click.devoluciones-individual', function(evento) {
    if ($(evento.target).is(modalDevolverEquipoIndividual)) {
        modalDevolverEquipoIndividual.css('display', 'none');
        const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
        cargarContenidoPestana('devoluciones-pendientes', idInstructorActual); 
        $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
    }
});

// Cierre del modal de devolución múltiple
botonCerrarModalMultiple.off('click').on('click', function() {
    modalDevolverMultiple.css('display', 'none');
    formularioDevolucionMultiple[0].reset();
    $('#devolver_error_message').hide();
    const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
    cargarContenidoPestana('devoluciones-pendientes', idInstructorActual);
    $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
});

$(window).off('click.devolverMultiple').on('click.devolverMultiple', function(evento) {
    if ($(evento.target).is(modalDevolverMultiple)) {
        modalDevolverMultiple.css('display', 'none');
        formularioDevolucionMultiple[0].reset();
        $('#devolver_error_message').hide();
        const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
        cargarContenidoPestana('devoluciones-pendientes', idInstructorActual);
        $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
    }
});

// Cierre del modal de edición de préstamo completo
botonCerrarModalEditarCompleto.on('click', function() {
    modalEditarPrestamoCompleto.css('display', 'none');
    const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
    cargarContenidoPestana('devoluciones-pendientes', idInstructorActual); 
    if (selectAnadirEquipoAPrestamo.data('select2')) {
        selectAnadirEquipoAPrestamo.select2('destroy');
    }
});

$(window).off('click.editarPrestamoCompleto').on('click.editarPrestamoCompleto', function(evento) {
    if ($(evento.target).is(modalEditarPrestamoCompleto)) {
        modalEditarPrestamoCompleto.css('display', 'none');
        const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
        cargarContenidoPestana('devoluciones-pendientes', idInstructorActual);
        if (selectAnadirEquipoAPrestamo.data('select2')) {
            selectAnadirEquipoAPrestamo.select2('destroy');
        }
    }
});
// Envío del formulario de devolución individual
formularioDevolucionIndividual.off('submit').on('submit', function(e) {
    e.preventDefault();

    const datosFormulario = $(this).serializeArray(); 
    
    // Añadir datos de responsable desde las variables JavaScript globales
    datosFormulario.push({name: 'id_responsable', value: idResponsableSessionJs});
    datosFormulario.push({name: 'rol_responsable', value: rolResponsableSessionJs});

    const estadoDevolucion = $(this).find('#modal_estado_devolucion').val();

    if (!estadoDevolucion) {
        alert('Por favor, seleccione el estado de devolución.');
        console.warn("Estado de devolución individual no seleccionado.");
        return;
    }

    // Convertir array serializado a objeto JSON para envío
    const datosAEnviar = {};
    datosFormulario.forEach(item => {
        if (item.name.endsWith('[]')) { 
            const clave = item.name.slice(0, -2);
            if (!datosAEnviar[clave]) {
                datosAEnviar[clave] = [];
            }
            datosAEnviar[clave].push(item.value);
        } else {
            datosAEnviar[item.name] = item.value;
        }
    });
    // Asegurarse de que id_prestamo_equipo_detalle es un array para el backend PHP
    datosAEnviar['id_prestamo_equipo_detalle'] = [parseInt(datosAEnviar['id_prestamo_equipo_detalle'])];

    $.ajax({
        url: 'Php/Préstamo_Equipos/Registrar_Devolucion_Equipos.php',
        type: 'POST',
        contentType: 'application/json', 
        data: JSON.stringify(datosAEnviar), 
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.success) {
                alert(respuesta.message);
                modalDevolverEquipoIndividual.css('display', 'none');
                const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
                cargarContenidoPestana('devoluciones-pendientes', idInstructorActual); 
                $('.tab-button[data-tab-content="devoluciones-pendientes"]').addClass('active').siblings().removeClass('active');
            } else {
                alert('❌ ' + respuesta.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            let mensajeError = 'Error al procesar la solicitud.';
            try {
                const respuestaJson = JSON.parse(jqXHR.responseText);
                mensajeError = respuestaJson.message || 'Error desconocido del servidor.';
            } catch (e) {
                mensajeError = `Error en la comunicación con el servidor: ${textStatus} - ${errorThrown}. Respuesta: ${jqXHR.responseText}`;
            }
            console.error("Error AJAX en devolución individual:", mensajeError);
            alert('❌ ' + mensajeError);
        }
    });
});

// Envío del formulario de edición de préstamo completo
formularioEditarPrestamoCompleto.off('submit').on('submit', function(e) {
    e.preventDefault();

    const idPrestamo = campoIdPrestamoEditarCompleto.val();
    const itemsAAnadir = selectAnadirEquipoAPrestamo.val(); 
    const itemsARemover = []; 
    
    $('.equipment-item-edit.marked-for-removal').each(function() {
        itemsARemover.push($(this).data('id-detalle'));
    });

    const datosAEnviar = {
        id_prestamo_equipo: idPrestamo,
        items_to_add: itemsAAnadir || [], 
        items_to_remove: itemsARemover || [], 
        id_responsable: idResponsableSessionJs, // Usar la variable JS
        rol_responsable: rolResponsableSessionJs // Usar la variable JS
    };

    $.ajax({
        url: 'Php/Préstamo_Equipos/Procesar_edicion_prestamo.php', 
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(datosAEnviar),
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.success) {
                alert(respuesta.message);
                modalEditarPrestamoCompleto.css('display', 'none');
                const idInstructorActual = new URLSearchParams(window.location.search).get('instructor_id') || 0;
                cargarContenidoPestana('devoluciones-pendientes', idInstructorActual); 
            } else {
                $('#edit_loan_error_message').text('❌ ' + (respuesta.message || 'Error desconocido.')).show();
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            let mensajeError = 'Error al procesar la solicitud.';
            try {
                const respuestaJson = JSON.parse(jqXHR.responseText);
                mensajeError = respuestaJson.message || 'Error desconocido del servidor.';
            } catch (e) {
                mensajeError = `Error en la comunicación con el servidor: ${textStatus} - ${errorThrown}. Respuesta: ${jqXHR.responseText}`;
            }
            console.error("Error AJAX en edición de préstamo completo:", mensajeError);
            $('#edit_loan_error_message').text('❌ ' + mensajeError).show();
        }
    });
});


// Lógica de manejo de pestañas (inicialización)
const paramsURL = new URLSearchParams(window.location.search);
const pestanaSolicitada = paramsURL.get('tab');
const idInstructorInicial = paramsURL.get('instructor_id') || 0;

if (pestanaSolicitada) {
    $(`.tab-button[data-tab-content="${pestanaSolicitada}"]`).addClass('active').siblings().removeClass('active');
    cargarContenidoPestana(pestanaSolicitada, idInstructorInicial);
} else {
    cargarContenidoPestana('registrar-prestamo');  
}

// Manejador de clic para los botones de pestaña
$('.tab-button').on('click', function() {
    const nombrePestana = $(this).data('tab-content');
    $('.tab-button').removeClass('active');
    $(this).addClass('active');
    // Cuando se cambia de pestaña, ocultar la alerta de stock bajo
    areaAlertaStockBajo.hide(); 
    if (nombrePestana === 'devoluciones-pendientes') {
            cargarContenidoPestana(nombrePestana, idInstructorInicial); 
    } else {
            cargarContenidoPestana(nombrePestana); 
    }
});
// Carga inicial de alertas de vencimiento cuando la página está lista
cargarAlertasVencidas();
}); 
