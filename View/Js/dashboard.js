document.addEventListener("DOMContentLoaded", () => {
  // Sidebar functionality
  const sidebar = document.getElementById("sidebar")
  const sidebarToggle = document.getElementById("sidebarToggle")
  const mobileSidebarToggle = document.getElementById("mobileSidebarToggle")
  const mainContent = document.querySelector(".main-content")

  // Toggle sidebar
  function toggleSidebar() {
    sidebar.classList.toggle("collapsed")
  }

  // Mobile sidebar toggle
  function toggleMobileSidebar() {
    sidebar.classList.toggle("show")
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", toggleSidebar)
  }

  if (mobileSidebarToggle) {
    mobileSidebarToggle.addEventListener("click", toggleMobileSidebar)
  }

  // Close mobile sidebar when clicking outside
  document.addEventListener("click", (e) => {
    if (window.innerWidth <= 991.98) {
      if (!sidebar.contains(e.target) && !mobileSidebarToggle.contains(e.target)) {
        sidebar.classList.remove("show")
      }
    }
  })

  // Navigation functionality
  const navLinks = document.querySelectorAll(".nav-link[data-section]")
  const contentSections = document.querySelectorAll(".content-section")
  const pageTitle = document.getElementById("pageTitle")

  // Section titles mapping
  const sectionTitles = {
    dashboard: "Dashboard",
    assets: "Asset Management",
    import: "Import Data",
    filters: "Filter Assets",
    reports: "Reports",
    admin: "Admin Panel",
    users: "User Management",
    documentation: "Documentation",
  }

  function showSection(sectionId) {
    // Hide all sections
    contentSections.forEach((section) => {
      section.classList.remove("active")
    })

    // Show target section
    const targetSection = document.getElementById(sectionId + "-section")
    if (targetSection) {
      targetSection.classList.add("active")
    }

    // Update page title
    if (pageTitle && sectionTitles[sectionId]) {
      pageTitle.textContent = sectionTitles[sectionId]
    }

    // Update active nav link
    navLinks.forEach((link) => {
      link.classList.remove("active")
    })

    const activeLink = document.querySelector(`[data-section="${sectionId}"]`)
    if (activeLink) {
      activeLink.classList.add("active")
    }

    // Close mobile sidebar
    if (window.innerWidth <= 991.98) {
      sidebar.classList.remove("show")
    }

    // Update URL hash
    window.location.hash = sectionId
  }

  // Handle navigation clicks
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()
      const sectionId = this.getAttribute("data-section")
      showSection(sectionId)
    })
  })

  // Handle hash changes
  function handleHashChange() {
    const hash = window.location.hash.substring(1)
    if (hash && sectionTitles[hash]) {
      showSection(hash)
    } else {
      showSection("dashboard")
    }
  }

  window.addEventListener("hashchange", handleHashChange)

  // Initialize on page load
  handleHashChange()

  // File upload functionality
  const dropZone = document.getElementById("dropZone")
  const fileInput = document.getElementById("file-input")
  const fileName = document.getElementById("file-name")

  if (dropZone && fileInput && fileName) {
    dropZone.addEventListener("click", () => fileInput.click())

    dropZone.addEventListener("dragover", (e) => {
      e.preventDefault()
      dropZone.classList.add("dragover")
    })

    dropZone.addEventListener("dragleave", () => {
      dropZone.classList.remove("dragover")
    })

    dropZone.addEventListener("drop", (e) => {
      e.preventDefault()
      dropZone.classList.remove("dragover")

      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files
        updateFileName(e.dataTransfer.files[0].name)
      }
    })

    fileInput.addEventListener("change", () => {
      if (fileInput.files.length) {
        updateFileName(fileInput.files[0].name)
      }
    })

    function updateFileName(name) {
      fileName.textContent = name
      fileName.classList.add("text-primary")
    }
  }

  // Delete all confirmation
  const deleteAllButton = document.getElementById("deleteAllButton")
  if (deleteAllButton) {
    deleteAllButton.addEventListener("click", () => {
      if (typeof Swal !== "undefined") {
        Swal.fire({
          title: "Are you sure?",
          text: "This action will delete all logs permanently!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#dc3545",
          cancelButtonColor: "#6c757d",
          confirmButtonText: '<i class="fas fa-trash-alt me-2"></i>Yes, delete all!',
          cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
          customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-secondary",
          },
          buttonsStyling: false,
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "./Controller/delete_regist.php"
          }
        })
      } else {
        if (confirm("Are you sure you want to delete all logs? This action cannot be undone.")) {
          window.location.href = "./Controller/delete_regist.php"
        }
      }
    })
  }

  // Language selector functionality
  document.querySelectorAll(".language-selector").forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault()
      const lang = this.getAttribute("data-lang")

      const url = new URL(window.location.href)
      url.searchParams.set("lang", lang)
      window.location.href = url.toString()
    })
  })

  // Dark mode toggle
  const darkModeToggle = document.getElementById("darkModeToggle")

  if (
    localStorage.getItem("darkMode") === "enabled" ||
    (window.matchMedia("(prefers-color-scheme: dark)").matches && localStorage.getItem("darkMode") !== "disabled")
  ) {
    document.body.classList.add("dark-mode")
    if (darkModeToggle) darkModeToggle.checked = true
  }

  if (darkModeToggle) {
    darkModeToggle.addEventListener("change", function () {
      if (this.checked) {
        document.body.classList.add("dark-mode")
        localStorage.setItem("darkMode", "enabled")
      } else {
        document.body.classList.remove("dark-mode")
        localStorage.setItem("darkMode", "disabled")
      }
    })
  }

  // Notification functionality
  function updateNotificationCount() {
    fetch("./Controller/get_notification_count.php")
      .then((response) => response.json())
      .then((data) => {
        const badge = document.querySelector(".notification-badge")
        if (badge) {
          badge.textContent = data.count || 0
          badge.style.display = data.count > 0 ? "block" : "none"
        }
      })
      .catch((error) => console.error("Error updating notification count:", error))
  }

  // Update notification count on page load
  updateNotificationCount()

  // Update notification count every 30 seconds
  setInterval(updateNotificationCount, 30000)

  // Initialize DataTable for assets
  if (document.getElementById("mainTable")) {
    // DataTable initialization will be handled by existing scripts
    console.log("DataTable container found")
  }

  // Quick action buttons in dashboard
  document.querySelectorAll("[data-section]").forEach((button) => {
    if (button.tagName === "BUTTON" || button.tagName === "A") {
      button.addEventListener("click", function (e) {
        const section = this.getAttribute("data-section")
        if (section && this.tagName === "BUTTON") {
          e.preventDefault()
          showSection(section)
        }
      })
    }
  })

  // Responsive handling
  function handleResize() {
    if (window.innerWidth > 991.98) {
      sidebar.classList.remove("show")
    }
  }

  window.addEventListener("resize", handleResize)

  // Initialize tooltips if Bootstrap is available
  if (typeof bootstrap !== "undefined") {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
  }
})

// Global function for showing notification modal
function showModal() {
  const modal = new bootstrap.Modal(document.getElementById("notificationModal"))
  modal.show()
}

// Global function for clearing logs
function clearLogs() {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      title: "Are you sure?",
      text: "This will delete all notification logs. This action cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Yes, delete all!",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        fetch("./Model/clear_logs.php", {
          method: "POST",
        })
          .then((response) => response.text())
          .then((data) => {
            Swal.fire({
              title: "Success!",
              text: data,
              icon: "success",
              confirmButtonColor: "#0d6efd",
            }).then(() => {
              location.reload()
            })
          })
          .catch((error) => {
            console.error("Error:", error)
            Swal.fire({
              title: "Error!",
              text: "An error occurred while clearing logs.",
              icon: "error",
              confirmButtonColor: "#0d6efd",
            })
          })
      }
    })
  }
}
