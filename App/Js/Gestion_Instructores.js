function validarFormulario() {
   document.getElementById("nombre").value = document.getElementById("nombre").value.trim();
   document.getElementById("apellido").value = document.getElementById("apellido").value.trim();
   document.getElementById("correo").value = document.getElementById("correo").value.trim();
   document.getElementById("telefono").value = document.getElementById("telefono").value.trim();
   document.getElementById("ambiente").value = document.getElementById("ambiente").value.trim();

    return true;
  }
  // Prevenir espacios al inicio mientras se escribe en los input  

  document.getElementById("nombre").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

  document.getElementById("apellido").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  }); 
  document.getElementById("correo").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });
 document.getElementById("telefono").addEventListener("input", function(e) {
     this.value = this.value.replace(/[^0-9]/g, '');
     if (this.value.length > 10) {
         this.value = this.value.slice(0, 10);
     }
 });
  document.getElementById("ambiente").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

document.addEventListener('DOMContentLoaded', function() {

    // 1. Manejar el envío del formulario de AGREGAR y EDITAR
    // Esta parte ya estaba bien, apunta a '.form-container' y no causa conflicto.
    const form = document.querySelector('.form-container');
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Evitar el envío tradicional

        const formData = new FormData(form);

        fetch('Gestion_Instructores.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showFloatingMessage(data.message, data.type);
            if (data.type === 'success') {
                // Esperar 2 segundos para que el usuario vea el mensaje y luego recargar la página
                setTimeout(() => {
                    window.location.href = 'Gestion_Instructores.php';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showFloatingMessage('🚨 Error al Registrar Instructor.', 'error');
        });
    });

    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(deleteForm => {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Evitar el envío tradicional

            if (confirm("¿Estás seguro que quieres eliminar este instructor?")) {
                const formData = new FormData(deleteForm);
                
                fetch('Gestion_Instructores.php', {
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
                    showFloatingMessage('🚨 Error al Registrar Instructor', 'error');
                });
            }
        });
    });
});