// Lógica para el login y cambio de roles
document.getElementById("loginForm").addEventListener("submit", function(event) {
  event.preventDefault();
  
  const role = document.getElementById("role").value;
  if (role) {
    showDashboard(role);
  } else {
    alert("Por favor, selecciona un rol.");
  }
});

function showDashboard(role) {
  // Ocultar todas las secciones de los paneles
  document.getElementById("admin-dashboard").classList.add("hidden");
  document.getElementById("almacenista-dashboard").classList.add("hidden");
  document.getElementById("instructor-dashboard").classList.add("hidden");
  
  // Mostrar la sección correspondiente al rol
  switch(role) {
    case "admin":
      document.getElementById("admin-dashboard").classList.remove("hidden");
      break;
    case "almacenista":
      document.getElementById("almacenista-dashboard").classList.remove("hidden");
      break;
    case "instructor":
      document.getElementById("instructor-dashboard").classList.remove("hidden");
      break;
  }
}

// Funciones para las acciones en los paneles de usuario
function viewLoans() {
  alert("Aquí puedes ver los préstamos.");
}

function manageUsers() {
  alert("Aquí puedes gestionar los usuarios.");
}

function manageInventory() {
  alert("Aquí puedes gestionar el inventario.");
}

function requestLoan() {
  alert("Aquí puedes solicitar un préstamo.");
}
