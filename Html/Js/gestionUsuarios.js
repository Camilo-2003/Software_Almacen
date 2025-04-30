function validarFormulario() {
    var nombreInput = document.getElementById("nombre");
    var apellidoInput = document.getElementById("apellido");
    var correoInput = document.getElementById("correo");
    var telefonoInput = document.getElementById("telefono");
    var ambienteInput = document.getElementById("ambiente");
    var nombrematerialInput = document.getElementById("nombre_material");

    // Limpiar espacios iniciales
    nombreInput.value = nombreInput.value.replace(/^\s+/, '');
    apellidoInput.value = apellidoInput.value.replace(/^\s+/, '');
    correoInput.value = correoInput.value.replace(/^\s+/, '');
    telefonoInput.value = telefonoInput.value.replace(/^\s+/, '');
    ambienteInput.value = ambienteInput.value.replace(/^\s+/, '');
    nombrematerialInput.value = nombrematerialInput.value.replace(/^\s+/, '');

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
    this.value = this.value.replace(/^\s+/, '');
  });
  document.getElementById("ambiente").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });
  document.getElementById("nombre_material").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

