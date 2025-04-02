let index = 0;
const images = document.querySelectorAll(".carousel img");

function showNextImage() {
  images[index].classList.remove("active");
  index = (index + 1) % images.length;
  images[index].classList.add("active");
}
setInterval(showNextImage, 5000);




function toggleMenu() {
  document.getElementById("menuOverlay").classList.toggle("show");
  document.querySelector(".menu-button").classList.toggle("menu-open");
}

// Cierra el menú al hacer clic fuera de él
document.addEventListener("click", (event) => {
  if (!document.querySelector(".menu-list").contains(event.target) &&
      !document.querySelector(".menu-button").contains(event.target)) {
      document.getElementById("menuOverlay").classList.remove("show");
      document.querySelector(".menu-button").classList.remove("menu-open");
  }
});
