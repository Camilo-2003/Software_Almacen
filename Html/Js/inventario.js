document.addEventListener("DOMContentLoaded", function () {
    cargarInventario();
});

function agregarInventario() {
    let tipoRegistro = document.getElementById("tipo_registro").value;

    let formData = new FormData();
    formData.append("tipo_registro", tipoRegistro);

    if (tipoRegistro === "Material") {
        let nombre = document.getElementById("nombre_material").value.trim();
        let tipo = document.getElementById("tipo_material").value;
        let stock = document.getElementById("stock_material").value;

        if (nombre === "" || stock <= 0) {
            alert("⚠️ Ingresa datos válidos.");
            return;
        }

        formData.append("nombre", nombre);
        formData.append("tipo", tipo);
        formData.append("stock", stock);

    } else if (tipoRegistro === "Equipo") {
        let marca = document.getElementById("marca").value.trim();
        let serial = document.getElementById("serial").value.trim();
        let estado = document.getElementById("estado").value;

        if (marca === "" || serial === "") {
            alert("⚠️ Ingresa datos válidos.");
            return;
        }

        formData.append("marca", marca);
        formData.append("serial", serial);
        formData.append("estado", estado);
    } else {
        alert("⚠️ Selecciona un tipo de registro.");
        return;
    }
    console.log([...formData]); // Verificar si los datos están correctos antes de enviarlos

    fetch("Php/agregar_material.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Muestra mensaje de éxito o error
        cargarInventario(); // Recarga la tabla
        limpiarFormulario();
    })
    .catch(error => console.error("Error:", error));
}

function cargarInventario() {
    fetch("Php/cargar_inventario.php")
    .then(response => response.json())
    .then(data => {
        let tbody = document.getElementById("inventario");
        tbody.innerHTML = "";

        data.forEach(item => {
            let row = `<tr>
                        <td>${item.tipo_registro === "Material" ? item.nombre : item.marca}</td>
                        <td>${item.tipo_registro === "Material" ? item.tipo : item.serial}</td>
                        <td>${item.tipo_registro === "Material" ? item.stock : item.estado}</td>
                      </tr>`;
            tbody.innerHTML += row;
        });
    })
    .catch(error => console.error("Error:", error));
}

function mostrarCampos() {
    let tipoRegistro = document.getElementById("tipo_registro").value;
    let materialFields = document.getElementById("materialFields");
    let equipoFields = document.getElementById("equipoFields");
    let nombreMaterial = document.getElementById("nombre_material");
   //si selecciono material solo aparecen opciones de material
    if (tipoRegistro === "Material") {
        materialFields.style.display = "block";
        equipoFields.style.display = "none";
        nombreMaterial.setAttribute("required", "required");
    //si selecciono equipo solo aparecen opciones de equipo
    } else if (tipoRegistro === "Equipo") { 
        materialFields.style.display = "none";
        equipoFields.style.display = "block";
        nombreMaterial.removeAttribute("required");
    // si o si debes seleccionar una de las 2 opciones
    } else {
        materialFields.style.display = "none";
        equipoFields.style.display = "none";
        nombreMaterial.removeAttribute("required");
    }
}
//refrescar y limpiar informacion que ya se ha registrado 
function limpiarFormulario() {
    document.getElementById("nombre_material").value = "";
    document.getElementById("stock_material").value = "1";
    document.getElementById("marca").value = "";
    document.getElementById("serial").value = "";
}

//solo para el input de nombre de material
function validarFormulario() {
    var nombreMaterialInput = document.getElementById("nombre_material");
    var nombreEquipoInput = document.getElementById("serial");
// Limpiar espacios iniciales
nombreMaterialInput.value = nombreMaterialInput.value.replace(/^\s+/, '');
nombreEquipoInput.value = nombreEquipoInput.value.replace(/^\s+/, '');
    return true;
}
// Prevenir espacios al inicio mientras se escribe en el input 
document.getElementById("nombre_material").addEventListener("input", function(e) {
this.value = this.value.replace(/^\s+/, '');
});

document.getElementById("serial").addEventListener("input", function(e) {
this.value = this.value.replace(/^\s+/, '');
});

