function validarFormulario() {
var serialInput = document.getElementById("serial");
var instructorInput = document.getElementById("instructor");

serialInput.value = serialInput.value.replace(/^\s+/, '');
instructorInput.value = instructorInput.value.replace(/^\s+/, '');

return true;
}

  // Prevenir espacios al inicio mientras escribe
  document.getElementById("serial").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

  document.getElementById("instructor").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

