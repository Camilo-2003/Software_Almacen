// Funcionalidad de pestañas principales
function openTab(event, tabId) {
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');

    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    event.currentTarget.classList.add('active');
}
function showFloatingMessage(message, isError = false) {
    let messageDiv = document.getElementById('floatingMessage');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'floatingMessage';
        document.body.appendChild(messageDiv);
    }
    messageDiv.textContent = message;
    messageDiv.className = 'floating-message';
    if (isError) {
        messageDiv.classList.add('error');
    } else {
        messageDiv.classList.add('success');
    }
    messageDiv.style.display = 'block';
    messageDiv.style.opacity = '1';
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 500); 
    }, 5000);
}
async function loadEquipos() {
    try {
        const response = await fetch('../../Php/Inventario/Equipos.php');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const equipos = await response.json();

        const equiposTableBody = document.getElementById('equiposTableBody');
        equiposTableBody.innerHTML = '';

        equipos.forEach(equipo => {
            const row = equiposTableBody.insertRow();

            row.insertCell().textContent = equipo.id_equipo;
            row.insertCell().textContent = equipo.marca;
            row.insertCell().textContent = equipo.serial;
            row.insertCell().textContent = equipo.estado;

            // Celda para los botones de acción
            const actionsCell = row.insertCell();
            actionsCell.classList.add('action-buttons');

            // Botón Editar
            const editButton = document.createElement('button');
            editButton.innerHTML = '<i class="fa-solid fa-pen-to-square" id="ii"></i>' + ' Editar';
            editButton.onclick = () => editEquipo(equipo);
            actionsCell.appendChild(editButton);

            // Botón Eliminar
            const deleteButton = document.createElement('button');
            deleteButton.innerHTML = '<i class="fa-solid fa-trash" id="ii"></i>' + ' Eliminar';
            deleteButton.classList.add('delete-button');
            deleteButton.onclick = () => deleteEquipo(equipo.id_equipo);
            actionsCell.appendChild(deleteButton);
        });

    } catch (error) {
        console.error('Error al cargar equipos:', error);
        showFloatingMessage('Error al cargar equipos: ' + error.message, true);
    }
}

// Guardar Equipo (Registrar/Modificar)
async function saveEquipo() {
    const id = document.getElementById('equipoId').value;
    const marca = document.getElementById('equipoMarca').value;
    const serial = document.getElementById('equipoSerial').value.trim();
    const estado = document.getElementById('equipoEstado').value;

    if (!marca || marca === 'default' || !serial) {
        showFloatingMessage('Por favor selecciona una marca válida y completa el serial.', true);
        return;
    }

    const data = { marca, serial, estado };
    let url = '../../Php/Inventario/Equipos.php';
    let method = 'POST';

    if (id) { // Si hay un ID, es una modificación
        url = `../../Php/Inventario/Equipos.php?id=${id}`;
        method = 'PUT';
    }

    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });
        const result = await response.json();

        if (response.ok) {
            showFloatingMessage(result.message);
            loadEquipos();
            clearEquipoForm();
        } else {
            if (response.status === 409) {
                showFloatingMessage(result.message, true);
            } else {
                showFloatingMessage(result.message || 'Error desconocido al guardar equipo.', true);
            }
        }
    } catch (error) {
        console.error('Error en la conexión al guardar equipo:', error);
        showFloatingMessage('Error de conexión o servidor al guardar equipo.', true);
    }
}
// Editar equipo
function editEquipo(equipo) {
    document.getElementById('equipoId').value = equipo.id_equipo;
    document.getElementById('equipoMarca').value = equipo.marca;  
    document.getElementById('equipoSerial').value = equipo.serial;
    document.getElementById('equipoEstado').value = equipo.estado;
}

// Eliminar equipo
async function deleteEquipo(id) {
    if (confirm(`¿Estás seguro de que quieres eliminar el equipo con ID ${id}?`)) {
        try {
            // Envía el ID como parámetro de consulta para DELETE
            const response = await fetch(`../../Php/Inventario/Equipos.php?id=${id}`, {
                method: 'DELETE',
            });

            const result = await response.json();

            if (response.ok) {
                showFloatingMessage(result.message);
                loadEquipos();
            } else {
                showFloatingMessage(result.message || 'Error desconocido al eliminar equipo.', true);
            }
        } catch (error) {
            console.error('Error en la conexión al eliminar equipo:', error);
            showFloatingMessage('Error de conexión o servidor al eliminar equipo.', true);
        }
    }
}

// Limpiar formulario de equipo
function clearEquipoForm() {
    document.getElementById('equipoId').value = '';
    document.getElementById('equipoMarca').value = 'default';  // Valor por defecto del select
    document.getElementById('equipoSerial').value = '';
    document.getElementById('equipoEstado').value = 'disponible';
}
// Cargar materiales
async function loadMateriales() {
    try {
        const response = await fetch('/Software_Almacen/App/Php/Inventario/Materiales.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const materiales = await response.json();

        const materialesTableBody = document.getElementById('materialesTableBody');
        materialesTableBody.innerHTML = '';

        materiales.forEach(material => {
            const row = materialesTableBody.insertRow();
            row.insertCell().textContent = material.id_material;
            row.insertCell().textContent = material.nombre;
            row.insertCell().textContent = material.tipo;
            row.insertCell().textContent = material.stock;
            // row.insertCell().textContent = ''; // Columna vacía para "Descripción"
            const actionsCell = row.insertCell();
            actionsCell.classList.add('action-buttons');

            const editButton = document.createElement('button');
            editButton.innerHTML = '<i class="fa-solid fa-pen-to-square" id="ii"></i>' + ' Editar';
            editButton.onclick = () => editMaterial(material);
            actionsCell.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.innerHTML = '<i class="fa-solid fa-trash" id="ii"></i>' + ' Eliminar';
            deleteButton.classList.add('delete-button');
            deleteButton.onclick = () => deleteMaterial(material.id_material, material.nombre);
            actionsCell.appendChild(deleteButton);
        });
    } catch (error) {
        console.error('Error al cargar materiales:', error);
        showFloatingMessage('Error al cargar materiales.', true);
    }
}

// Guardar Material (Registrar/Modificar)
async function saveMaterial() {
    const id = document.getElementById('materialId').value;
    const nombre = document.getElementById('materialNombre').value.trim(); // Trim whitespace
    const tipo = document.getElementById('materialTipo').value;
    const stock = document.getElementById('materialStock').value;

    if (!nombre || stock === '') {
        showFloatingMessage('Nombre y Stock son campos obligatorios para materiales.', true);
        return;
    }
    if (parseInt(stock) < 0) {
        showFloatingMessage('El stock no puede ser negativo.', true);
        return;
    }

    const data = { nombre, tipo, stock: parseInt(stock) };
    let url = '../../Php/Inventario/Materiales.php';
    let method = 'POST';

    if (id) {
        url = `../../Php/Inventario/Materiales.php?id=${id}`;
        method = 'PUT';
    }

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (response.ok) {
            showFloatingMessage(result.message);
            loadMateriales();
            clearMaterialForm();
        } else {
            if (response.status === 409) { 
                showFloatingMessage(result.message, true);
            } else {
                showFloatingMessage(result.message || 'Error desconocido al guardar material.', true);
            }
        }
    } catch (error) {
        console.error('Error en la conexión al guardar material:', error);
        showFloatingMessage('Error de conexión o servidor al guardar material.', true);
    }
}

// Editar material
function editMaterial(material) {
    document.getElementById('materialId').value = material.id_material;
    document.getElementById('materialNombre').value = material.nombre;
    document.getElementById('materialTipo').value = material.tipo;
    document.getElementById('materialStock').value = material.stock;
}

// Eliminar material
async function deleteMaterial(id, nombre) { 
    if (confirm(`¿Estás seguro de que quieres eliminar el material '${nombre}' con ID ${id}?`)) {
        try {
            const response = await fetch(`../../Php/Inventario/Materiales.php?id=${id}`, {
                method: 'DELETE',
            });

            const result = await response.json();

            if (response.ok) {
                showFloatingMessage(result.message);
                loadMateriales();
            } else {
                showFloatingMessage(result.message || 'Error desconocido al eliminar material.', true);
            }
        } catch (error) {
            console.error('Error en la conexión al eliminar material:', error);
            showFloatingMessage('Error de conexión o servidor al eliminar material.', true);
        }
    }
}

// Limpiar formulario de material
function clearMaterialForm() {
    document.getElementById('materialId').value = '';
    document.getElementById('materialNombre').value = '';
    document.getElementById('materialTipo').value = 'consumible';
    document.getElementById('materialStock').value = '0';
}

// Inicializar la aplicación
document.addEventListener('DOMContentLoaded', () => {
    loadEquipos();
    loadMateriales();
    document.querySelector('.tab-button.active').click();
});  