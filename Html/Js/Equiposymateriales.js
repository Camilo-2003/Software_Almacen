const inventario = {
    Consumible: { "Papel": 100, "Tinta": 100, "Marcadores": 100 },
    No_Consumible: { "Laptop": 100, "Proyector": 100, "Impresora": 100 }
};

function cargarMateriales() {
    let tipo = document.getElementById("tipo").value;
    let materialSelect = document.getElementById("material");
    materialSelect.innerHTML = "<option value=''>Seleccionar</option>";
    
    if (tipo && inventario[tipo]) {
        for (let item in inventario[tipo]) {
            let option = document.createElement("option");
            option.value = item;
            option.textContent = `${item} (${inventario[tipo][item]})`;
            materialSelect.appendChild(option);
        }
    }
}

function registrarPrestamo() {
    let tipo = document.getElementById("tipo").value;
    let material = document.getElementById("material").value;
    let cantidad = parseInt(document.getElementById("cantidad").value);
    let instructor = document.getElementById("instructor").value;
    let historial = document.getElementById("historial");
    
    if (!tipo || !material || !instructor || cantidad < 1) {
        alert("Por favor, complete todos los campos correctamente");
        return;
    }
    
    if (inventario[tipo][material] < cantidad) {
        alert("No hay suficiente stock");
        return;
    }
    inventario[tipo][material] -= cantidad;
    cargarMateriales();
    
    let fecha = new Date();
    let fechaStr = fecha.toLocaleDateString();
    let horaStr = fecha.toLocaleTimeString();
    
    let fila = `<tr>
        <td>${tipo}</td>
        <td>${material}</td>
        <td>${cantidad}</td>
        <td>${instructor}</td>
        <td>${fechaStr}</td>
        <td>${horaStr}</td>
        <td style="color: black;background-color:red">Prestado</td>
        ${tipo === 'No_Consumible' ? '<td><button onclick="devolver(this)">Devolver</button></td>' : '<td>-</td>'}
    </tr>`;
    
    historial.innerHTML += fila;
}

function devolver(boton) {
    let fila = boton.parentElement.parentElement;
    let material = fila.cells[1].textContent;
    let cantidad = parseInt(fila.cells[2].textContent);
    
    let confirmacion = confirm("¿Hubo algún problema con la devolución?");
    if (confirmacion) {
        let evidencia = prompt("Describe el problema que se presento con la devolución:");
        alert("Evidencia registrada: " + evidencia);
    } else {
        inventario["No_Consumible"][material] += cantidad;
        cargarMateriales();
    }
    
    fila.cells[6].textContent = "Devuelto";
    fila.cells[6].style.color = "black";
    fila.cells[6].style.backgroundColor = "green";
    boton.remove();
}