document.addEventListener('DOMContentLoaded', () => {
    const modalEditarNovedad = document.getElementById('modalEditarNovedad');
    const closeButton = modalEditarNovedad.querySelector('.close-button');
    const tablaNovedades = document.getElementById('tablaNovedades');
    const imagenPreviaContainer = document.getElementById('imagen-previa-container');

    // Función para abrir el modal de edición y llenar el formulario
    tablaNovedades.addEventListener('click', (event) => {
        const editButton = event.target.closest('.btn-editar');
        if (editButton) {
            // Limpiar previsualización de imagen anterior
            imagenPreviaContainer.innerHTML = '';

            // Recuperar todos los datos del botón
            const id = editButton.dataset.id;
            const tipoElemento = editButton.dataset.tipoElemento;
            const descripcion = editButton.dataset.descripcion;
            const fechaNovedad = editButton.dataset.fechaNovedad;
            const rolResponsable = editButton.dataset.rolResponsable;
            const nombreResponsable = editButton.dataset.nombreResponsable;
            const nombreInstructor = editButton.dataset.nombreInstructor;
            const tipoNovedad = editButton.dataset.tipoNovedad;
            const imagen = editButton.dataset.imagen; // Ruta de la imagen 

            // Rellenar el formulario del modal
            document.getElementById('edit_id_novedad').value = id;
            document.getElementById('edit_tipo_elemento').value = tipoElemento;
            document.getElementById('edit_descripcion').value = descripcion;
            
            // Formatear la fecha para el input datetime-local
            const fecha = new Date(fechaNovedad);
            const formattedDate = fecha.getFullYear() + '-' +
                                  String(fecha.getMonth() + 1).padStart(2, '0') + '-' +
                                  String(fecha.getDate()).padStart(2, '0') + 'T' +
                                  String(fecha.getHours()).padStart(2, '0') + ':' +
                                  String(fecha.getMinutes()).padStart(2, '0');
            document.getElementById('edit_fecha_novedad').value = formattedDate;

            document.getElementById('edit_rol_responsable').value = rolResponsable;
            document.getElementById('edit_nombre_responsable').value = nombreResponsable;
            document.getElementById('edit_nombre_instructor').value = nombreInstructor;
            document.getElementById('edit_tipo_novedad').value = tipoNovedad;
            
            document.getElementById('imagen_existente').value = imagen;

            if (imagen) {
                imagenPreviaContainer.innerHTML = `<p style="font-weight: bold; margin-bottom: 5px; color: #555;">Imagen Actual:</p><img src="${imagen}" id="imagen-previa" alt="Imagen Actual">`;
            }

            modalEditarNovedad.style.display = 'flex'; 
        }

        const deleteButton = event.target.closest('.btn-eliminar');
        if (deleteButton) {
            const deleteUrl = deleteButton.dataset.deleteUrl; 
            if (confirm('¿Estás seguro de que quieres eliminar esta novedad? Esta acción no se puede deshacer.')) {
                window.location.href = deleteUrl; 
            }
        }
    });

    // Cerrar el modal al hacer clic en la x
    closeButton.addEventListener('click', () => {
        modalEditarNovedad.style.display = 'none';
    });

    // // Cerrar el modal si se hace clic fuera de él
    // window.addEventListener('click', (event) => {
    //     if (event.target === modalEditarNovedad) {
    //         modalEditarNovedad.style.display = 'none';
    //     }
    // });
});

function validarFormulario() {
    if (document.getElementById("descripcion")) {
        document.getElementById("descripcion").value = document.getElementById("descripcion").value.trim();
    }
    if (document.getElementById("observaciones")) {
        document.getElementById("observaciones").value = document.getElementById("observaciones").value.trim();
    }
    return true;
}

function confirmarEliminacion() {
  return confirm('¿Estás seguro de que deseas eliminar esta novedad?');
}