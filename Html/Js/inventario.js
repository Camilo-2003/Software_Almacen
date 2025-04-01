document.addEventListener("DOMContentLoaded", function() {
    cargarInventario();
});

function cargarInventario() {
    fetch("inventario.php")
        .then(response => response.json())
        .then(data => {
            let tbody = document.getElementById("inventario");
            tbody.innerHTML = "";

            data.forEach(material => {
                tbody.innerHTML += `
                    <tr>
                        <td>${material.nombre}</td>
                        <td>${material.tipo}</td>
                        <td>${material.stock}</td>
                    </tr>`;
            });
        })
        .catch(error => console.error("Error al cargar el inventario:", error));
}

function agregarInventario() {
    let nombre = document.getElementById("nombre").value;
    let tipo = document.getElementById("tipo").value;
    let stock = document.getElementById("stock").value;

    if (nombre === "" || stock <= 0) {
        alert("Por favor ingrese datos válidos.");
        return;
    }

    let formData = new FormData();
    formData.append("nombre", nombre);
    formData.append("tipo", tipo);
    formData.append("stock", stock);

    fetch("agregar_material.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        cargarInventario();
        document.getElementById("nombre").value = "";
        document.getElementById("stock").value = "1";
    })
    .catch(error => console.error("Error al agregar material:", error));
}
