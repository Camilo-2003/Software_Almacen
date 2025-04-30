const inventario = {
    No_Consumible: { "HP": 100, "ASUS": 100, "DELL": 100, "LENOVO": 100 }
};

function cargarEquipos() {
    let tipo = document.getElementById("tipo").value;
    let equipoSelect = document.getElementById("equipo");
    let serial = document.getElementById("serial");
    equipoSelect.innerHTML = "<option value=''>Seleccionar</option>";
    
    if (tipo && inventario[tipo]) {
        for (let item in inventario[tipo]) {
            let option = document.createElement("option");
            option.value = item;
            option.textContent = `${item} (${inventario[tipo][item]})`;
            equipoSelect.appendChild(option);
        }
    }
}

function registrarPrestamo() {
    let tipo = document.getElementById("tipo").value;
    let equipo = document.getElementById("equipo").value;
    let serial = document.getElementById("serial").value;
    let cantidad = parseInt(document.getElementById("cantidad").value);
    let instructor = document.getElementById("instructor").value;
    let historial = document.getElementById("historial");
    
    if (!tipo || !equipo || !serial || !instructor || cantidad < 1) {
        alert("Por favor, complete todos los campos correctamente");
        return;
    }
    
    if (inventario[tipo][equipo] < cantidad) {
        alert("No hay suficiente stock");
        return;
    }
    inventario[tipo][equipo] -= cantidad;
    cargarEquipos();
    
    let fecha = new Date();
    let fechaStr = fecha.toLocaleDateString();
    let horaStr = fecha.toLocaleTimeString();
    
    let fila = `<tr>
        <td>${tipo}</td>
        <td>${equipo}</td>
        <td>${serial}</td>
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
    let equipo = fila.cells[1].textContent;
    let cantidad = parseInt(fila.cells[2].textContent);
    
    let confirmacion = confirm("¿Hubo algún problema con la devolución?");
    if (confirmacion) {
        let evidencia = prompt("Describe el problema que se presento con la devolución:");
        alert("Evidencia registrada: " + evidencia);
    } else {
        inventario["No_Consumible"][equipo] += cantidad;
        cargarEquipos();
    }
    
    fila.cells[6].textContent = "Devuelto";
    fila.cells[6].style.color = "black";
    fila.cells[6].style.backgroundColor = "green";
    boton.remove();
}

function validarFormulario() {
var serialInput = document.getElementById("serial");
var instructorInput = document.getElementById("instructor");

serialInput.value = serialInput.value.replace(/^\s+/, '');
instructorInput.value = instructorInput.value.replace(/^\s+/, '');

return true;
}

  // Prevenir espacios al inicio mientras escribe
  document.getElementById("serial").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

  document.getElementById("instructor").addEventListener("input", function(e) {
    this.value = this.value.replace(/^\s+/, '');
  });

  function validarCantidad(input) {
    if (input.value > 1) {
      input.value = 1;
    } else if (input.value < 1) {
      input.value = 1;
    }
  }