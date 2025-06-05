// Detectar si el usuario usó el botón "atrás"
if (performance.navigation.type === 2) {
location.reload(true); // Forzar recarga desde el servidor
}
window.addEventListener("pageshow", function (event) {
if (event.persisted || (window.performance && performance.navigation.type === 2)) {
    window.location.reload();
}
});
 