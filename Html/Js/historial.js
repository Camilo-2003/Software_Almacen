function mostrarTotalesEquipos() {
  const titulo = document.getElementById('tituloTotales');
  const tabla = document.getElementById('contenedorTabla');
  
  const visible = titulo.style.display === 'block';

  titulo.style.display = visible ? 'none' : 'block';
  tabla.style.display = visible ? 'none' : 'block';
}

function mostrarTotalesMateriales() {
  const titulo = document.getElementById('tituloTotalesMateriales');
  const tabla = document.getElementById('contenedorTablaMateriales');
  
  const visible = titulo.style.display === 'block';

  titulo.style.display = visible ? 'none' : 'block';
  tabla.style.display = visible ? 'none' : 'block';
}

function filtrarTabla(inputId, tablaId) {
  const input = document.getElementById(inputId);
  const filtro = input.value.toLowerCase();
  const tabla = document.getElementById(tablaId);
  const filas = tabla.getElementsByTagName("tr");

  for (let i = 1; i < filas.length; i++) {
      const celdas = filas[i].getElementsByTagName("td");
      let coincide = false;
      for (let j = 0; j < celdas.length; j++) {
          const texto = celdas[j].textContent.toLowerCase();
          if (texto.includes(filtro)) {
              coincide = true;
              break;
          }
      }
      filas[i].style.display = coincide ? "" : "none";
  }
}

function confirmarEliminacion() {
  return confirm('¿Estás seguro de que deseas eliminar este registro?');
}

