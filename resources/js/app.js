import './bootstrap';
import './notifications';

// Toggle Sidebar - Manejo de sidebar en móvil
function handleSidebar() {
    const mainWrapper = document.getElementById("main-wrapper");
    if (!mainWrapper) return;

    const isMobileView = () => window.innerWidth < 1300;

    // Inicializar: Sidebar siempre cerrado al cargar (remover show-sidebar)
    mainWrapper.classList.remove("show-sidebar");

    function closeSidebarOnMobile() {
        // Solo cerrar en vista móvil (< 1300px)
        if (isMobileView()) {
            mainWrapper.classList.remove("show-sidebar");
        }
    }

    // Evento para el botón hamburger
    document.querySelectorAll(".sidebartoggler").forEach((element) => {
        element.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation(); // Prevenir que el evento suba al document listener
            mainWrapper.classList.toggle("show-sidebar");
            const dataTheme = document.body.getAttribute("data-sidebartype");
            if (dataTheme === "full") {
                document.body.setAttribute("data-sidebartype", "mini-sidebar");
            } else {
                document.body.setAttribute("data-sidebartype", "full");
            }
        });
    });

    // Cerrar sidebar al hacer clic en un enlace del menú (en móvil)
    document.querySelectorAll(".sidebar-link").forEach((link) => {
        link.addEventListener("click", function () {
            closeSidebarOnMobile();
        });
    });

    // Cerrar sidebar al hacer clic fuera (en la zona oscura)
    document.addEventListener("click", function (e) {
        const sidebar = document.querySelector(".side-mini-panel");
        const hamburger = document.querySelector(".sidebartoggler");

        // Si el clic NO es en el sidebar y NO es en el hamburger
        if (sidebar && hamburger && !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            closeSidebarOnMobile();
        }
    });

    // Cerrar sidebar cuando se redimensiona la ventana (cambio de breakpoint)
    let resizeTimer;
    window.addEventListener("resize", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (!isMobileView() && mainWrapper.classList.contains("show-sidebar")) {
                mainWrapper.classList.remove("show-sidebar");
            }
        }, 250);
    });
}

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', handleSidebar);
