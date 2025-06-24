function validarFormulario() {
    document.getElementById("descripcion").value = document.getElementById("descripcion").value.trim();
    document.getElementById("observaciones").value = document.getElementById("observaciones").value.trim();
    document.getElementById("busquedaNovedades").value = document.getElementById("busquedaNovedades").value.trim();

    return true;
}
// Prevenir que el espacio sea el primer carácter en descripcion
document.getElementById("descripcion").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

// Prevenir que el espacio sea el primer carácter en observaciones
document.getElementById("observaciones").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

//evita insertar espacios al ingresar al campo instructor
document.getElementById("busquedaNovedades").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

function confirmarEliminacion() {
  return confirm('¿Estás seguro de que deseas eliminar esta novedad?');
}