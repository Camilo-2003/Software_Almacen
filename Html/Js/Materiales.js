  function validarFormulario() {
            const tipo = document.getElementById("tipo_material").value;
            const material = document.getElementById("material").value.trim();
            const cantidad = document.getElementById("cantidad").value;
            const instructor = document.getElementById("instructor").value.trim();

            if (!tipo || !material || !cantidad || !instructor) {
                alert("Por favor, completa todos los campos.");
                return false;
            }

            if (cantidad <= 0) {
                alert("La cantidad debe ser mayor a 0.");
                return false;
            }

            return true;
}

function buscarTipoMaterial() {
    const material = document.getElementById("material").value;

    if (material.length === 0) {
        document.getElementById("tipo_material").value = "";
        return;
    }

    fetch(`Php/obtener_tipo_material.php?nombre=${encodeURIComponent(material)}`)
        .then(res => res.text())
        .then(tipo => {
            document.getElementById("tipo_material").value = tipo !== "no encontrado" ? tipo : "";
        });
}

;