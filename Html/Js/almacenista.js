// Detectar si el usuario usó el botón "atrás"
if (performance.navigation.type === 2) {
location.reload(true); // Forzar recarga desde el servidor
}
// Si esta página está dentro de un iframe, redirige al nivel superior
if (window.top !== window.self) {
    window.top.location = window.location;
}

function cargarPagina(pagina) {
    const iframe = document.getElementById("contenido");
    iframe.style.opacity = 0; // Transición suave
    setTimeout(() => {
        iframe.src = pagina;
        iframe.onload = () => iframe.style.opacity = 1;
    }, 200);
}
window.addEventListener("pageshow", function (event) {
if (event.persisted || (window.performance && performance.navigation.type === 2)) {
    window.location.reload();
}
});