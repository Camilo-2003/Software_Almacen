function validarFormulario() {
    var correoInput = document.getElementById("correo");
    var passwordInput = document.getElementById("password");

     // .replace Método que reemplaza parte del texto
    // Limpiar espacios iniciales
    correoInput.value = correoInput.value.replace(/^\s+/, '');
    passwordInput.value = passwordInput.value.replace(/^\s+/, '');

    // Verificar si el correo quedó vacío después de limpiar
    if (correoInput.value === "") {
      alert("El correo no puede estar vacío o contener espacios al inicio.");
      return false;
    }

    // Verificar si la contraseña quedó vacía después de limpiar 
    if (passwordInput.value === "") {
      alert("La contraseña no puede estar vacía o contener espacios al inicio.");
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
    alert("✅¡Has cerrado sesión correctamente!");
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