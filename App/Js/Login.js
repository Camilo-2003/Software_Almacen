function showFloatingMessage(message, isError = false) {
    let messageDiv = document.getElementById('floatingMessage');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'floatingMessage';
        document.body.appendChild(messageDiv);
    }

    messageDiv.textContent = message;
    messageDiv.className = 'floating-message';
    messageDiv.classList.add(isError ? 'error' : 'success');

    messageDiv.style.display = 'block';
    messageDiv.style.opacity = '1';

    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 400);
    }, 4000);
}

function validarFormulario() {
    var correoInput = document.getElementById("correo");
    var passwordInput = document.getElementById("password");

    correoInput.value = correoInput.value.replace(/^\s+/, '');
    passwordInput.value = passwordInput.value.replace(/^\s+/, '');

    // Verificar si el correo quedó vacío después de limpiar
    if (correoInput.value === "") {
      showFloatingMessage("El correo no puede estar vacío o contener espacios al inicio.", true);
      return false;
    }

    // Verificar si la contraseña quedó vacía después de limpiar 
    if (passwordInput.value === "") {
      showFloatingMessage("La contraseña no puede estar vacía o contener espacios al inicio.", true);
      return false;
    }

    return true;
  }
  // Prevenir espacios al inicio mientras escribe
  document.getElementById("correo").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

  document.getElementById("password").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

// Obtener parámetros de la URL
const params = new URLSearchParams(window.location.search);

// Verificar si viene el mensaje de cierre
if (params.get("mensaje") === "cierre") {
    showFloatingMessage("✅ ¡Has cerrado sesión correctamente!");
    const nuevaUrl = window.location.origin + window.location.pathname;
    window.history.replaceState({}, document.title, nuevaUrl);
}else if (params.get("mensaje") === "error") {
    showFloatingMessage("❌No estás registrado. Por favor, comunicate con el administrador del sistema.", true);
    const nuevaUrl = window.location.origin + window.location.pathname;
    window.history.replaceState({}, document.title, nuevaUrl);
}else if (params.get("mensaje") === "password") {
    showFloatingMessage("❌La contraseña es incorrecta. Por favor, intentalo de nuevo.", true);
    const nuevaUrl = window.location.origin + window.location.pathname;
    window.history.replaceState({}, document.title, nuevaUrl);
}
 
// ver y ocultar password
let clave = document.getElementById("password")
let ver = document.getElementById("ver");
let icono = document.getElementById("icono")
let con = true

ver.addEventListener("click", function () {
if (con) {
  clave.type = "text";
  icono.classList.remove("fa-eye");
  icono.classList.add("fa-eye-slash");
  con = false;
} else {
  clave.type = "password";
  icono.classList.remove("fa-eye-slash");
  icono.classList.add("fa-eye");
  con = true;
}
});