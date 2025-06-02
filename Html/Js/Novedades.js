function validarFormulario() {
    document.getElementById("descripcion").value = document.getElementById("descripcion").value.trim();
    document.getElementById("id_instructor").value = document.getElementById("id_instructor").value.trim();
    document.getElementById("instructor").value = document.getElementById("instructor").value.trim();
    document.getElementById("id_almacenista").value = document.getElementById("id_almacenista").value.trim();
    document.getElementById("almacenista").value = document.getElementById("almacenista").value.trim();
    document.getElementById("observaciones").value = document.getElementById("observaciones").value.trim();

    return true;
}
// Prevenir que el espacio sea el primer carácter en descripcion
document.getElementById("descripcion").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

// Prevenir que el espacio sea el primer carácter y tambien al final en el id_instructor
document.getElementById("id_instructor").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+|\s+$/g, ''); 
});

//evita insertar espacios al ingresar al campo instructor
document.getElementById("instructor").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

// Prevenir que el espacio sea el primer carácter y evita tambien al final en el id_almacenista
document.getElementById("id_almacenista").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+|\s+$/g, '');
  });
//evita insertar espacios al ingresar al campo almacenista
  document.getElementById("almacenista").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });
//evita insertar espacios al ingresar al campo observaciones
  document.getElementById("observaciones").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

function confirmarEliminacion() {
  return confirm('¿Estás seguro de que deseas eliminar esta novedad?');
}
