$(function(){
    initJs($('body'));

    // Main menu js
    initMainMenu();

    // Default jQuery Exception
    $( document ).ajaxError(function( event, request, settings ) {
        if(typeof(settings.globalError) != 'undefined' || settings.globalError == false) {
            return;
        }

        // abort ajax
        if (request.statusText =='abort') {
            console.log('User abort!');
            return;
        }

        if (request.responseText) {
            alert(request.responseText);
        } else {
            console.log(request);
        }
    });

    // Top quota button
    $(document).on('click', '.top-quota-button', function(e) {
        e.preventDefault();
        var url = $(this).attr("data-url");
        console.log(url);


        var quotaPopup = new Popup({
            url: url
        });

        quotaPopup.load();
    });



// Theme Sidebar
    function handleSidebarToggle() {
        function setSidebarType(sidebarType) {
            document.body.setAttribute("data-sidebartype", sidebarType);
        }

        const fullSidebarElement = document.getElementById("full-sidebar");
        const miniSidebarElement = document.getElementById("mini-sidebar");

        if (fullSidebarElement) {
            fullSidebarElement.addEventListener("click", () =>
                setSidebarType("full")
            );
        }
        if (miniSidebarElement) {
            miniSidebarElement.addEventListener("click", () =>
                setSidebarType("mini-sidebar")
            );
        }
    }
    handleSidebarToggle();

// Toggle Sidebar
    function handleSidebar() {
        const mainWrapper = document.getElementById("main-wrapper");
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

                // Obtener estado actual
                const currentSidebarType = document.body.getAttribute("data-sidebartype");

                // Cambiar entre full y mini-sidebar
                if (currentSidebarType === "full") {
                    // Cambiar a mini-sidebar
                    document.body.setAttribute("data-sidebartype", "mini-sidebar");
                    mainWrapper.classList.add("show-sidebar");
                } else {
                    // Cambiar a full
                    document.body.setAttribute("data-sidebartype", "full");
                    mainWrapper.classList.remove("show-sidebar");
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
    handleSidebar();


});
