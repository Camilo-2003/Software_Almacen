$(document).ready(function() {
    const editModal = $('#editNovedadModal');
    const deleteModal = $('#deleteNovedadModal');
    let novedadIdToDelete = null;

    // Abrir modal de edición
    $(document).on('click', '.btn-edit-novedad', function() {
        const id = $(this).data('id');
        const tipo = $(this).data('tipo');
        const descripcion = $(this).data('descripcion');
        
        $('#edit_id_novedad2').val(id);
        $('#edit_tipo_novedad').val(tipo);
        $('#edit_descripcion').val(descripcion);
        
        editModal.show();
    });

    // Enviar formulario de edición
    $('#formEditNovedad').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: '/Software_Almacen/App/Php/Novedades/Editar_Novedad2.php', 
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    location.reload(); 
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });

    // Abrir modal de eliminación
    $(document).on('click', '.btn-delete-novedad', function() {
        novedadIdToDelete = $(this).data('id');
        deleteModal.show();
    });

    // Confirmar eliminación
    $('#confirmDeleteBtn').on('click', function() {
        if (!novedadIdToDelete) return;

        $.ajax({
            url: '/Software_Almacen/App/Php/Novedades/Eliminar_Novedad2.php', 
            type: 'POST',
            data: { id_novedad2: novedadIdToDelete },
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    location.reload(); 
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });

    // Cerrar modales
    $('.close-button').on('click', function() {
        $(this).closest('.modal').hide();
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('.modal')) {
            $('.modal').hide();
        }
    });
});