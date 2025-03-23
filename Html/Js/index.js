let index = 0;
const images = document.querySelectorAll(".carousel img");

function showNextImage() {
  images[index].classList.remove("active");
  index = (index + 1) % images.length;
  images[index].classList.add("active");
}
setInterval(showNextImage, 5000);