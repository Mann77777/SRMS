const body = document.querySelector("body");
const darkLight = document.querySelector("#darkLight");
const sidebar = document.querySelector(".sidebar");
const submenuItems = document.querySelectorAll(".submenu_item");
const sidebarOpen = document.querySelector("#sidebarOpen");
const sidebarClose = document.querySelector(".collapse_sidebar");
const sidebarExpand = document.querySelector(".expand_sidebar");
sidebarOpen.addEventListener("click", () => sidebar.classList.toggle("close"));

sidebarClose.addEventListener("click", () => {
  sidebar.classList.add("close", "hoverable");
});
sidebarExpand.addEventListener("click", () => {
  sidebar.classList.remove("close", "hoverable");
});

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

if (window.innerWidth < 768) {
  sidebar.classList.add("close");
} else {
  sidebar.classList.remove("close");
}

// Submenu Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const submenus = document.querySelectorAll('.has-submenu > .nav_link');
    
    submenus.forEach(submenu => {
        submenu.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            
            // Toggle active class on parent li
            const parentLi = this.closest('.has-submenu');
            parentLi.classList.toggle('active');
            
            // Close other open submenus
            const otherSubmenus = document.querySelectorAll('.has-submenu.active');
            otherSubmenus.forEach(other => {
                if (other !== parentLi) {
                    other.classList.remove('active');
                }
            });
        });
    });
});


document.addEventListener('DOMContentLoaded', () => {
  const mobileMenuToggle = document.getElementById('mobileSidebarToggle');
  const sidebar = document.querySelector('.sidebar');

  mobileMenuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('active');
  });
});