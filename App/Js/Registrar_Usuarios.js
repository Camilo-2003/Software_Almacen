function validarFormulario() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm-password").value;
    var nombre = document.getElementById("nombre").value.trim();
    var apellido = document.getElementById("apellido").value.trim();
    var telefono = document.getElementById("telefono").value;
    var correo = document.getElementById("correo").value.trim(); 
    var rol = document.querySelector('select[name="rol"]').value; 

    if (nombre.length < 2 || apellido.length < 2) {
        showFloatingMessage("El nombre y el apellido deben tener al menos 2 letras.", 'error');
        return false;
    }

    if (nombre === "" || apellido === "") {
        showFloatingMessage("El nombre y apellido no pueden estar vacíos o contener solo espacios.", 'error');
        return false;
    }

    if (telefono.length !== 10) {
        showFloatingMessage("El teléfono debe tener exactamente 10 dígitos.", 'error');
        return false;
    }
      if (password.length < 6 || confirmPassword.length < 6) {
        showFloatingMessage("La contraseña es muy corta. Debe ser minimo de 6 caracteres.", 'error');
        return false;
    }

    if (password !== confirmPassword) {
        showFloatingMessage("Las contraseñas no coinciden. Inténtalo de nuevo.", 'error');
        return false;
    }
    submitFormViaAjax(rol, nombre, apellido, correo, telefono, password);
    return false;
}

async function submitFormViaAjax(rol, nombre, apellido, correo, telefono, password) {
    const formData = new FormData();
    formData.append('rol', rol);
    formData.append('nombre', nombre);
    formData.append('apellido', apellido);
    formData.append('correo', correo);
    formData.append('telefono', telefono);
    formData.append('password', password);
    formData.append('confirm-password', password);

    try {
        const response = await fetch('Php/Guardar_Registrarse.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json(); 
        showFloatingMessage(result.message, result.type);
        if (result.type === 'success') { // LIMPIAR CAMPOS LUEGO DE ENVIAR 
            document.getElementById("nombre").value = '';
            document.getElementById("apellido").value = '';
            document.getElementById("correo").value = '';
            document.getElementById("telefono").value = '';
            document.getElementById("password").value = '';
            document.getElementById("confirm-password").value = '';
            document.querySelector('select[name="rol"]').value = ''; 
            setTimeout(() => {
           }, 5000); 
        }
    } catch (error) {
        console.error('Error al enviar el formulario:', error);
        showFloatingMessage('🚨 Error de conexión o servidor. Inténtalo de nuevo.', 'error');
    }
}
function cancelarFormulario() {
    if (confirm("¿Estás seguro de que deseas cancelar el registro?")) {
        window.location.href = "Usuarios.php";
    }
}
document.getElementById("nombre").addEventListener("input", function(e) {
    if (this.value.startsWith(" ")) {
        this.value = this.value.trimStart();
    }
});
document.getElementById("apellido").addEventListener("input", function(e) {
    if (this.value.startsWith(" ")) {
        this.value = this.value.trimStart();
    }
});
document.getElementById("telefono").addEventListener("input", function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});
document.getElementById("password").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+|\s+$/g, '');
});
document.getElementById("confirm-password").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+|\s+$/g, '');
});
document.getElementById("correo").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});