document.addEventListener("DOMContentLoaded", () => {
    const scrollContainer = document.querySelector(".scroll-container");
    const leftButton = document.querySelector(".scroll-button.left");
    const rightButton = document.querySelector(".scroll-button.right");

    if (scrollContainer && leftButton && rightButton) {
        const scrollAmount = 300; // Cantidad de desplazamiento

        leftButton.addEventListener("click", () => {
            scrollContainer.scrollBy({ left: -scrollAmount, behavior: "smooth" });
        });

        rightButton.addEventListener("click", () => {
            scrollContainer.scrollBy({ left: scrollAmount, behavior: "smooth" });
        });
    } else {
        console.error("No se encontraron los elementos del carrusel.");
    }
});
