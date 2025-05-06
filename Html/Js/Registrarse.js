function validarFormulario() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm-password").value;
    var nombre = document.getElementById("nombre").value.trim();
    var apellido = document.getElementById("apellido").value.trim();
    var telefono = document.getElementById("telefono").value;

    // Validar longitud mínima de nombre y apellido
    if (nombre.length < 6 || apellido.length < 6) {
        alert("El nombre y el apellido deben tener al menos  letras.");
        return false;
    } 

    // Validar que nombre y apellido no sean solo espacios
    if (nombre === "" || apellido === "") {
        alert("El nombre y apellido no pueden estar vacíos o contener solo espacios.");
        return false; 
    }

    // Validar longitud del teléfono
    if (telefono.length !== 10) {
        alert("El teléfono debe tener exactamente 10 dígitos.");
        return false;
    }

    // Validar contraseñas
    if (password !== confirmPassword) {
        alert("Las contraseñas no coinciden. Inténtalo de nuevo.");
        return false;
    }

    return true;
}
// redirección 
function cancelarFormulario() {
    if (confirm("¿Estás seguro de que deseas cancelar el registro?")) {
        window.location.href = "login.html";   
    }
}


// Prevenir que el espacio sea el primer carácter en nombre
document.getElementById("nombre").addEventListener("input", function(e) {
    if (this.value.startsWith(" ")) {
        this.value = this.value.trimStart();
    }
});

// Prevenir que el espacio sea el primer carácter en el apellido
document.getElementById("apellido").addEventListener("input", function(e) {
    if (this.value.startsWith(" ")) {
        this.value = this.value.trimStart();
    }
});

// Restringir el campo teléfono a solo números
document.getElementById("telefono").addEventListener("input", function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});

//evita insertar espacios al crear la contraseña
document.getElementById("password").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

  document.getElementById("confirm-password").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });
//evita insertar espacios al ingresar el correo
  document.getElementById("correo").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

