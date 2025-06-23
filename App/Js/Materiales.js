function validarFormulario() {
 document.getElementById("material_id").value = document.getElementById("material_id").value.trim();
 document.getElementById("cantidad").value = document.getElementById("cantidad").value.trim();
 document.getElementById("instructor").value = document.getElementById("instructor").value.trim();

return true;
}
document.getElementById("material_id").addEventListener("input", function(e) {
this.value = this.value.replace(/^\s+/, '');
});

document.getElementById("cantidad").addEventListener("input", function(e) {
this.value = this.value.replace(/^\s+/, '');
});
document.getElementById("instructor").addEventListener("input", function(e) {
this.value = this.value.replace(/^\s+/, '');
});

function buscarTipoMaterial() {
const material = document.getElementById("material").value;

if (material.length === 0) {
    document.getElementById("tipo_material").value = "";
    return;
}

// fetch(`Php/Obtener_tipo_material.php?nombre=${encodeURIComponent(material)}`)
//     .then(res => res.text())
//     .then(tipo => {
//         document.getElementById("tipo_material").value = tipo !== "no encontrado" ? tipo : "";
//     });
}
