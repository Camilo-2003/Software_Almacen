document.addEventListener('DOMContentLoaded', function() {

    const editForm = document.querySelector('div.edicion form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            fetch('Usuarios.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showFloatingMessage(data.message, data.type);
                if (data.type === 'success') {
                    setTimeout(() => {
                        window.location.href = 'Usuarios.php'; // Recargar para ver cambios
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFloatingMessage('🚨 Error de conexión al actualizar.', 'error');
            });
        });
    }

    const deleteForms = document.querySelectorAll('form.inline-form');
    deleteForms.forEach(deleteForm => {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (confirm("¿Estás seguro que deseas eliminar este usuario?")) {
                const formData = new FormData(deleteForm);
                fetch('Usuarios.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showFloatingMessage(data.message, data.type);
                    if (data.type === 'success') {
                        deleteForm.closest('tr').remove();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showFloatingMessage('🚨 Error de conexión al eliminar.', 'error');
                });
            }
        });
    });
});