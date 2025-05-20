// Definición del inventario inicial
const inventario = {
    Consumible: { "Papel": 100, "Tinta": 100, "Marcadores": 100 },
    No_Consumible: { "Hdmi": 100, "Proyector": 100, "Impresora": 100 }
};

// Cargar materiales según el tipo seleccionado
function cargarMateriales() {
    const tipo = document.getElementById("tipo").value;
    const materialSelect = document.getElementById("material");
    materialSelect.innerHTML = "<option value=''>Seleccionar</option>";

    if (tipo && inventario[tipo]) {
        for (const item in inventario[tipo]) {
            const option = document.createElement("option");
            option.value = item;
            option.textContent = `${item} (${inventario[tipo][item]})`;
            materialSelect.appendChild(option);
        }
    }
}

// Registrar un nuevo préstamo
function registrarPrestamo() {
    const tipo = document.getElementById('tipo').value;
    const material = document.getElementById('material').value;
    const cantidadInput = document.getElementById('cantidad').value;
    const cantidad = parseInt(cantidadInput, 10);
    const instructor = document.getElementById('instructor').value.trim();

    // Validación de campos
    if (!tipo || !material || !cantidad || !instructor) {
        alert("Por favor, completa todos los campos.");
        return;
    }

    // Validación de cantidad
    if (isNaN(cantidad) || cantidad <= 0) {
        alert("Por favor, ingresa una cantidad válida.");
        return;
    }

    // Validación de disponibilidad en el inventario
    if (inventario[tipo][material] < cantidad) {
        alert(`No hay suficiente ${material} disponible. Cantidad en inventario: ${inventario[tipo][material]}`);
        return;
    }

    const fecha = new Date();
    const id = Date.now().toString();

    let estado = 'prestado';
    let fechaDevolucion = null;

    if (tipo === 'Consumible') {
        estado = 'no devolutivo';
        fechaDevolucion = fecha.toLocaleTimeString();
    } else {
        inventario[tipo][material] -= cantidad;
    }

    const prestamo = {
        id,
        tipo,
        material,
        cantidad,
        instructor,
        fecha: fecha.toLocaleDateString(),
        hora: fecha.toLocaleTimeString(),
        estado,
        fechaDevolucion,
        novedad: null
    };

    const prestamos = JSON.parse(localStorage.getItem('prestamos')) || [];
    prestamos.push(prestamo);
    localStorage.setItem('prestamos', JSON.stringify(prestamos));

    cargarMateriales();
    mostrarHistorial();
    alert("Préstamo registrado correctamente.");
}

// Mostrar el historial de préstamos
function mostrarHistorial() {
    const tbody = document.getElementById('historial');
    if (!tbody) return; // Verifica si el elemento existe
    tbody.innerHTML = '';

    const prestamos = JSON.parse(localStorage.getItem('prestamos')) || [];

    prestamos.forEach(p => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${p.tipo}</td>
            <td>${p.material}</td>
            <td>${p.cantidad}</td>  
            <td>${p.instructor}</td>
            <td>${p.fecha}</td>
            <td>${p.hora}</td>
            <td style="color: white; background-color: ${p.estado === 'prestado' ? 'orange' : p.estado === 'devuelto' ? 'green' : 'gray'}">
                ${p.estado}
            </td>
            <td>
                ${p.estado === 'prestado' && p.tipo !== 'Consumible' ? `<button onclick="irADevolucion('${p.id}')">Devolver</button>` : ''}
            </td>
        `;
        tbody.appendChild(fila);
    });
}

// Redirigir a la página de devolución
function irADevolucion(id) {
    sessionStorage.setItem('prestamoADevolver', id);
    window.location.href = 'DevolucionMateriales.html';
}

// Confirmar devolución del material
function confirmarDevolucion() {
    const id = sessionStorage.getItem('prestamoADevolver');
    if (!id) {
        alert("No se encontró el préstamo a devolver.");
        return;
    }

    const prestamos = JSON.parse(localStorage.getItem('prestamos')) || [];
    const prestamoIndex = prestamos.findIndex(p => p.id === id);

    if (prestamoIndex === -1) {
        alert("Préstamo no encontrado.");
        return;
    }

    const prestamo = prestamos[prestamoIndex];

    // Actualizar estado y fecha de devolución
    prestamo.estado = 'devuelto';
    prestamo.fechaDevolucion = new Date().toLocaleTimeString();
    prestamo.novedad = document.getElementById('novedad') ? document.getElementById('novedad').value.trim() : '';

    // Actualizar inventario
    if (inventario[prestamo.tipo] && inventario[prestamo.tipo][prestamo.material] !== undefined) {
        inventario[prestamo.tipo][prestamo.material] += prestamo.cantidad;
    }

    // Guardar cambios
    prestamos[prestamoIndex] = prestamo;
    localStorage.setItem('prestamos', JSON.stringify(prestamos));

    // Limpiar sesión
    sessionStorage.removeItem('prestamoADevolver');

    // Actualizar historial
    mostrarHistorial();
    mostrarHistorialDevoluciones();

    alert("Devolución registrada correctamente.");
}

// Mostrar historial de devoluciones
function mostrarHistorialDevoluciones() {
    const tbody = document.getElementById('tablaDevoluciones');
    if (!tbody) return; // Verifica si el elemento existe
    tbody.innerHTML = '';

    const prestamos = JSON.parse(localStorage.getItem('prestamos')) || [];

    prestamos.forEach(p => {
        if (p.estado === 'devuelto' || p.estado === 'no devolutivo') {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${p.tipo}</td>
                <td>${p.material}</td>
                <td>${p.cantidad}</td>
                <td>${p.instructor}</td>
                <td>${p.fecha}</td>
                <td>${p.fechaDevolucion || '-'}</td>
                <td>${p.novedad || '-'}</td>
                <td style="color: white; background-color: ${p.estado === 'devuelto' ? 'green' : 'gray'}">
                    ${p.estado}
                </td>
            `;
            tbody.appendChild(fila);
        }
    });
}

// Validar el formulario antes de enviar
function validarFormulario() {
    const instructorInput = document.getElementById("instructor");
    instructorInput.value = instructorInput.value.trim();
    return true;
}

// Eliminar espacios al inicio del campo instructor mientras se escribe
document.addEventListener("DOMContentLoaded", function () {
    const instructorInput = document.getElementById("instructor");
    if (instructorInput) {
        instructorInput.addEventListener("input", function () {
            this.value = this.value.replace(/^\s+/, '');
        });
    }

    // Mostrar historial si existen las tablas correspondientes
    mostrarHistorial();
    mostrarHistorialDevoluciones();
});


