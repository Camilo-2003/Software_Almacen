function validarFormulario() {
 document.getElementById("tipo").value = document.getElementById("tipo").value.trim();
 document.getElementById("instructor").value = document.getElementById("instructor").value.trim();
 document.getElementById("almacenista").value = document.getElementById("almacenista").value.trim();

return true;
}

// Prevenir espacios al inicio mientras escribe
document.getElementById("tipo").addEventListener("input", function(e) {
  this.value = this.value.replace(/^\s+/, '');
});

document.getElementById("instructor").addEventListener("input", function(e) {
  this.value = this.value.replace(/^\s+/, '');
});
  document.getElementById("almacenista").addEventListener("input", function(e) {
  this.value = this.value.replace(/^\s+/, '');
});



  