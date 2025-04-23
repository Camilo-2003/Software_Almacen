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