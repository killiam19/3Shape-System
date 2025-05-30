// Script para manejar el cambio de modo oscuro y el logo
document.addEventListener("DOMContentLoaded", () => {
  const darkModeToggle = document.getElementById("darkModeToggle")
  const navbarLogo = document.querySelector(".navbar-brand img")

  // Rutas de los logos
  let lightModeLogo = "./Configuration/3shape-logo.png"
  let darkModeLogo = "./Configuration/3shape-logo2_dark_mode.png"

  // Función para cambiar el logo según el modo
  function updateLogo(isDarkMode) {
    if (navbarLogo) {
      navbarLogo.src = isDarkMode ? darkModeLogo : lightModeLogo
    }
  }

  // Verificar si estamos en la página de administración
  const isAdminPage = window.location.href.includes("/Admin/")


  // Comprobar preferencia guardada o preferencia del sistema
  const prefersDarkMode =
    localStorage.getItem("darkMode") === "enabled" ||
    (window.matchMedia("(prefers-color-scheme: dark)").matches && localStorage.getItem("darkMode") !== "disabled")

  // Aplicar modo oscuro si es necesario
  if (prefersDarkMode) {
    document.body.classList.add("dark-mode")
    if (darkModeToggle) {
      if (darkModeToggle.type === "checkbox") {
        darkModeToggle.checked = true
      } else {
        darkModeToggle.innerHTML = '<i class="fa-solid fa-sun"></i>'
      }
    }
    updateLogo(true)
  }

  // Función para alternar el modo oscuro
  function toggleDarkMode() {
    const isDarkMode = document.body.classList.toggle("dark-mode")

    // Guardar preferencia
    localStorage.setItem("darkMode", isDarkMode ? "enabled" : "disabled")

    // Actualizar el estado del toggle según su tipo
    if (darkModeToggle) {
      if (darkModeToggle.type === "checkbox") {
        darkModeToggle.checked = isDarkMode
      } else {
        darkModeToggle.innerHTML = isDarkMode ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>'
      }
    }

    // Actualizar el logo
    updateLogo(isDarkMode)
  }

  // Asignar el evento al botón según su tipo
  if (darkModeToggle) {
    if (darkModeToggle.type === "checkbox") {
      darkModeToggle.addEventListener("change", function () {
        const isDarkMode = this.checked
        document.body.classList.toggle("dark-mode", isDarkMode)
        localStorage.setItem("darkMode", isDarkMode ? "enabled" : "disabled")
        updateLogo(isDarkMode)
      })
    } else {
      darkModeToggle.addEventListener("click", toggleDarkMode)
    }
  }
})
