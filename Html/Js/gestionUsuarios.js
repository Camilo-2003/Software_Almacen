function obtenerInstructores() {
    return JSON.parse(localStorage.getItem("instructores")) || [];
}

function actualizarInstructores(instructores) {
    localStorage.setItem("instructores", JSON.stringify(instructores));
}

function mostrarInstructores() {
    let lista = document.getElementById("listaInstructores");
    lista.innerHTML = "";
    let instructores = obtenerInstructores();
    instructores.forEach((inst, index) => {
        lista.innerHTML += `
            <tr>
                <td contenteditable="true" onblur="editarInstructor(${index}, 'nombre', this.textContent)">${inst.nombre}</td>
                <td contenteditable="true" onblur="editarInstructor(${index}, 'correo', this.textContent)">${inst.correo}</td>
                <td contenteditable="true" onblur="editarInstructor(${index}, 'telefono', this.textContent)">${inst.telefono}</td>
                <td contenteditable="true" onblur="editarInstructor(${index}, 'especialidad', this.textContent)">${inst.especialidad}</td>
                <td><button onclick="eliminarInstructor(${index})" class="button2">Eliminar</button></td>
            </tr>`;
    });
}

function agregarInstructor() {
    let nombre = document.getElementById("nombre").value;
    let correo = document.getElementById("correo").value;
    let telefono = document.getElementById("telefono").value;
    let especialidad = document.getElementById("especialidad").value;
    
    if (!nombre || !correo || !telefono || !especialidad) {
        alert("Todos los campos son obligatorios");
        return;
    }
    
    let instructores = obtenerInstructores();
    instructores.push({ nombre, correo, telefono, especialidad });
    actualizarInstructores(instructores);
    mostrarInstructores();
}

function editarInstructor(index, campo, valor) {
    let instructores = obtenerInstructores();
    instructores[index][campo] = valor;
    actualizarInstructores(instructores);
}

function eliminarInstructor(index) {
    let instructores = obtenerInstructores();
    instructores.splice(index, 1);
    actualizarInstructores(instructores);
    mostrarInstructores();
}

document.addEventListener("DOMContentLoaded", mostrarInstructores);
