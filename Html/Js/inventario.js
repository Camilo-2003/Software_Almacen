document.addEventListener("DOMContentLoaded", function () {
    cargarInventario();

    // Manejar el envío del formulario
    document.getElementById("formularioInventario").addEventListener("submit", function (event) {
        event.preventDefault(); // Evitar el envío predeterminado del formulario
        if (validarFormulario()) {
            agregarInventario(); // Procesar los datos solo si la validación pasa
        }
    });
});

function agregarInventario() {
    let tipoRegistro = document.getElementById("tipo_registro").value;

    let formData = new FormData();
    formData.append("tipo_registro", tipoRegistro);

    if (tipoRegistro === "Material") {
        let nombre = document.getElementById("nombre_material").value.trim();
        let tipo = document.getElementById("tipo_material").value;
        let stock = document.getElementById("stock_material").value;

        formData.append("nombre_material", nombre);
        formData.append("tipo_material", tipo);
        formData.append("stock_material", stock);

    } else if (tipoRegistro === "Equipo") {
        let marca = document.getElementById("marca").value.trim();
        let serial = document.getElementById("serial").value.trim();
        let estado = document.getElementById("estado").value;

        formData.append("marca", marca);
        formData.append("serial", serial);
        formData.append("estado", estado);
    }

    console.log([...formData]); // Para depuración

    fetch("Php/agregar_material.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes("obligatorios") || data.includes("no válido")) {
            alert("⚠️ Error: " + data);
        } else if (data.includes("Error")) {
            alert("⚠️ Ocurrió un error al agregar el equipo, serial ya existente .");
        } else {
            alert("✅ Registro agregado correctamente.");
            cargarInventario(); // Recarga la tabla
            limpiarFormulario(); // Limpia el formulario
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("⚠️ Error al conectar con el servidor.");
    });
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

    if (tipoRegistro === "Material") {
        materialFields.style.display = "block";
        equipoFields.style.display = "none";
        nombreMaterial.setAttribute("required", "required");
    } else if (tipoRegistro === "Equipo") { 
        materialFields.style.display = "none";
        equipoFields.style.display = "block";
        nombreMaterial.removeAttribute("required");
    } else {
        materialFields.style.display = "none";
        equipoFields.style.display = "none";
        nombreMaterial.removeAttribute("required");
    }
}

function limpiarFormulario() {
    document.getElementById("nombre_material").value = "";
    document.getElementById("stock_material").value = "1";
    document.getElementById("marca").value = "";
    document.getElementById("serial").value = "";
    document.getElementById("tipo_registro").value = "";
    mostrarCampos(); // Actualizar visibilidad de campos
}

function validarFormulario() {
    let tipoRegistro = document.getElementById("tipo_registro").value;

    if (tipoRegistro === "") {
        alert("⚠️ Selecciona un tipo de registro.");
        return false;
    }

    if (tipoRegistro === "Material") {
        let nombreMaterial = document.getElementById("nombre_material").value.trim();
        let stockMaterial = document.getElementById("stock_material").value;

        // Limpiar espacios iniciales
        document.getElementById("nombre_material").value = nombreMaterial;

        if (nombreMaterial === "") {
            alert("⚠️ El nombre del material es obligatorio.");
            return false;
        }
        if (stockMaterial <= 0) {
            alert("⚠️ La cantidad debe ser mayor a 0.");
            return false;
        }
    } else if (tipoRegistro === "Equipo") {
        let marca = document.getElementById("marca").value.trim();
        let serial = document.getElementById("serial").value.trim();

        // Limpiar espacios iniciales
        document.getElementById("serial").value = serial;

        if (marca === "") {
            alert("⚠️ La marca es obligatoria.");
            return false;
        }
        if (serial === "") {
            alert("⚠️ El serial es obligatorio.");
            return false;
        }
    }

    return true;
}

// Prevenir espacios al inicio mientras se escribe en los inputs
document.getElementById("nombre_material").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

document.getElementById("serial").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
});

  