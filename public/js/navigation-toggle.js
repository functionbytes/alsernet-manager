/**
 * Navigation Toggle Script
 * Maneja la navegación entre secciones en el sidebar
 */

document.addEventListener('DOMContentLoaded', function () {
    const miniNavItems = document.querySelectorAll('.mini-nav-item');
    const menuSections = document.querySelectorAll('.sidebarmenu .sidebar-nav');

    miniNavItems.forEach((item, index) => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            // Remover clase d-none de todos
            menuSections.forEach(section => {
                section.classList.add('d-none');
            });

            // Agregar clase d-none a todos los items
            miniNavItems.forEach(navItem => {
                navItem.classList.remove('active');
            });

            // Mostrar sección correspondiente
            if (menuSections[index]) {
                menuSections[index].classList.remove('d-none');
                item.classList.add('active');
            }
        });
    });

    // Activar primer item por defecto
    if (miniNavItems.length > 0) {
        miniNavItems[0].classList.add('active');
    }
});
