/**
 * Sistema de Navegación Sidebar - Detección de Rutas y Menú Activo
 * Mejorado para Laravel con soporte para NavService dinámico
 */

var at = document.documentElement.getAttribute("data-layout");

if ((at = "vertical")) {
  // ==============================================================
  // Auto select left navbar (Vertical Layout)
  // ==============================================================

  document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    var isSidebar = document.getElementsByClassName("side-mini-panel");
    if (isSidebar.length > 0) {
      var currentURL =
        window.location != window.parent.location
          ? document.referrer
          : document.location.href;

      //****************************
      // Función mejorada para encontrar el elemento activo
      // Busca en TODOS los sidebars (no solo #sidebarnav único)
      //****************************
      function findMatchingElement() {
        // Remover parámetros de query string para comparación
        let finalUrl = currentURL.split("?")[0];

        // Buscar en todos los sidebars (sidebar-nav-*)
        var anchors = document.querySelectorAll(".sidebar-nav .sidebar-link");

        for (var i = 0; i < anchors.length; i++) {
          let anchorUrl = anchors[i].href.split("?")[0];

          // Comparación exacta
          if (anchorUrl === finalUrl) {
            return anchors[i];
          }
        }

        // Si no hay match exacto, buscar partial match (para rutas anidadas)
        for (var i = 0; i < anchors.length; i++) {
          let anchorUrl = anchors[i].href.split("?")[0];

          if (finalUrl.startsWith(anchorUrl) && anchorUrl !== window.location.origin + '/') {
            return anchors[i];
          }
        }

        return null;
      }

      var activeElement = findMatchingElement();

      if (activeElement) {
        // Marcar el link como activo
        activeElement.classList.add("active");

        // Expandir submenús si está dentro de uno
        var parentSubmenu = activeElement.closest("ul.collapse");
        if (parentSubmenu) {
          parentSubmenu.classList.add("in", "show");
          var parentToggle = parentSubmenu.previousElementSibling;
          if (parentToggle && parentToggle.classList.contains("has-arrow")) {
            parentToggle.setAttribute("aria-expanded", "true");
            parentToggle.closest(".sidebar-item")?.classList.add("open");
          }
        }
      }

      //****************************
      // Manejo de menús multinivel (has-arrow)
      //****************************
      document.querySelectorAll(".sidebar-link").forEach(function (link) {
        link.addEventListener("click", function (e) {
          const isActive = this.classList.contains("active");
          const parentUl = this.closest("ul");

          // Si el link tiene submenú (has-arrow)
          if (this.classList.contains("has-arrow")) {
            e.preventDefault();

            const parentItem = this.closest(".sidebar-item");
            const submenu = this.nextElementSibling;

            if (parentItem && submenu) {
              parentItem.classList.toggle("open");

              if (submenu.classList.contains("show")) {
                submenu.classList.remove("show", "in");
                this.setAttribute("aria-expanded", "false");
              } else {
                // Cerrar otros submenús del mismo nivel
                const siblings = parentUl.querySelectorAll(":scope > .sidebar-item > ul.collapse");
                siblings.forEach(function (sibling) {
                  if (sibling !== submenu) {
                    sibling.classList.remove("show", "in");
                    const siblingToggle = sibling.previousElementSibling;
                    if (siblingToggle) {
                      siblingToggle.setAttribute("aria-expanded", "false");
                      siblingToggle.closest(".sidebar-item")?.classList.remove("open");
                    }
                  }
                });

                submenu.classList.add("show", "in");
                this.setAttribute("aria-expanded", "true");
              }
            }
          } else {
            // Link normal sin submenú - marcar como activo
            if (!isActive) {
              // Quitar active de otros links del mismo nivel
              parentUl?.querySelectorAll(":scope > .sidebar-item > .sidebar-link").forEach(function (navLink) {
                navLink.classList.remove("active");
              });
              this.classList.add("active");
            }
          }
        });
      });

      //****************************
      // Detectar y mostrar el sidebar correcto basándose en el link activo
      //****************************
      if (activeElement) {
        var closestNav = activeElement.closest("nav[class*=sidebar-nav]");

        if (closestNav && closestNav.id) {
          // Extraer el ID del sidebar (menu-right-mini-1 -> mini-1)
          var menuId = closestNav.id.replace("menu-right-", "");

          // Ocultar todos los sidebars
          document.querySelectorAll(".sidebarmenu nav").forEach(function (nav) {
            nav.classList.remove("d-block");
            nav.classList.add("d-none");
          });

          // Mostrar el sidebar activo
          closestNav.classList.remove("d-none");
          closestNav.classList.add("d-block");

          // Marcar el mini-nav item correspondiente
          document.querySelectorAll(".mini-nav-item").forEach(function (item) {
            item.classList.remove("selected");
          });

          var miniNavItem = document.getElementById("mini-" + menuId);
          if (miniNavItem) {
            miniNavItem.classList.add("selected");
          }
        }
      }

      //****************************
      // Expandir submenús activos en mini-sidebar
      //****************************
      document
        .querySelectorAll(".sidebar-nav .sidebar-link.active")
        .forEach(function (link) {
          var parentSubmenu = link.closest("ul.collapse");
          if (parentSubmenu) {
            parentSubmenu.classList.add("in", "show");
            parentSubmenu.parentElement.classList.add("selected");
          }
        });

      //****************************
      // Click en mini-nav items para cambiar de sidebar
      // NOTA: El onclick inline en nav.blade.php llama a toggleSidebar()
      // Esta es una alternativa si no se usa onclick inline
      //****************************
      document
        .querySelectorAll(".mini-nav .mini-nav-item")
        .forEach(function (item) {
          // Solo agregar listener si no tiene onclick inline
          if (!item.hasAttribute("onclick")) {
            item.addEventListener("click", function () {
              var id = this.id.replace("mini-", "");

              // Quitar selected de todos
              document
                .querySelectorAll(".mini-nav .mini-nav-item")
                .forEach(function (navItem) {
                  navItem.classList.remove("selected");
                });

              // Marcar este como selected
              this.classList.add("selected");

              // Ocultar todos los sidebars
              document
                .querySelectorAll(".sidebarmenu nav")
                .forEach(function (nav) {
                  nav.classList.remove("d-block");
                  nav.classList.add("d-none");
                });

              // Mostrar el sidebar correspondiente
              var targetSidebar = document.getElementById("menu-right-" + id);
              if (targetSidebar) {
                targetSidebar.classList.remove("d-none");
                targetSidebar.classList.add("d-block");
              }

              // Forzar modo full para ver el menú
              document.body.setAttribute("data-sidebartype", "full");

              // Guardar en localStorage
              try {
                localStorage.setItem('activeSidebar', id);
                localStorage.setItem('activeMiniNav', this.id);
              } catch (e) {
                console.warn('localStorage not available:', e);
              }
            });
          }
        });
    }
  });
}

if ((at = "horizontal")) {
  // ==============================================================
  // Horizontal Layout Support (si se necesita en el futuro)
  // ==============================================================
  function findMatchingElement() {
    var currentUrl = window.location.href.split("?")[0];
    var anchors = document.querySelectorAll("#sidebarnavh ul#sidebarnav a");

    for (var i = 0; i < anchors.length; i++) {
      let anchorUrl = anchors[i].href.split("?")[0];
      if (anchorUrl === currentUrl) {
        return anchors[i];
      }
    }

    return null;
  }

  var elements = findMatchingElement();

  if (elements) {
    elements.classList.add("active");
  }

  document
    .querySelectorAll("#sidebarnavh ul#sidebarnav a.active")
    .forEach(function (link) {
      link.closest("a").parentElement.classList.add("selected");
      link.closest("ul").parentElement.classList.add("selected");
    });
}