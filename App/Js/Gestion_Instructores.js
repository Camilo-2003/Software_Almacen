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
