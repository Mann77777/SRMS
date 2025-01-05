document.addEventListener('DOMContentLoaded', function() {
  const body = document.querySelector("body");
  const darkLight = document.querySelector("#darkLight");
  const sidebar = document.querySelector(".sidebar");
  const submenuItems = document.querySelectorAll(".submenu_item");
  const sidebarOpen = document.querySelector("#sidebarOpen");
  const sidebarClose = document.querySelector(".collapse_sidebar");
  const sidebarExpand = document.querySelector(".expand_sidebar");

  // Safe event listener binding
  if (sidebarOpen) {
      sidebarOpen.addEventListener("click", () => {
          if (sidebar) sidebar.classList.toggle("close");
      });
  }

  if (sidebarClose) {
      sidebarClose.addEventListener("click", () => {
          if (sidebar) {
              sidebar.classList.add("close", "hoverable");
          }
      });
  }

  if (sidebarExpand) {
      sidebarExpand.addEventListener("click", () => {
          if (sidebar) {
              sidebar.classList.remove("close", "hoverable");
          }
      });
  }

  if (sidebar) {
      sidebar.addEventListener("mouseenter", () => {
          if (sidebar.classList.contains("hoverable")) {
              sidebar.classList.remove("close");
          }
      });

      sidebar.addEventListener("mouseleave", () => {
          if (sidebar.classList.contains("hoverable")) {
              sidebar.classList.add("close");
          }
      });
  }

  // Submenu toggle
  submenuItems.forEach((item, index) => {
      item.addEventListener("click", () => {
          item.classList.toggle("show_submenu");
          submenuItems.forEach((item2, index2) => {
              if (index !== index2) {
                  item2.classList.remove("show_submenu");
              }
          });
      });
  });

  // Responsive sidebar
  if (window.innerWidth < 768) {
      if (sidebar) sidebar.classList.add("close");
  } else {
      if (sidebar) sidebar.classList.remove("close");
  }

  // Additional submenu functionality
  const submenus = document.querySelectorAll('.has-submenu > .nav_link');
  submenus.forEach(submenu => {
      submenu.addEventListener('click', function() {
          const parentLi = this.closest('.has-submenu');
          if (parentLi) {
              parentLi.classList.toggle('active');
          }
      });
  });
});