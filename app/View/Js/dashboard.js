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
    adjustment: "Asset Adjustment",
    import: "Import Data",
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
        updateFileName()
      }
    })

    fileInput.addEventListener("change", updateFileName)

    function updateFileName() {
      if (fileInput.files.length > 0) {
        fileName.textContent = fileInput.files[0].name
      } else {
        fileName.textContent = "No file selected"
      }
    }
  }

  // DataTables initialization
  if (window.jQuery && window.jQuery.fn.DataTable && document.getElementById("mainTable")) {
    window.jQuery("#mainTable").DataTable({
      responsive: true,
      language: {
        search: "Search:",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        paginate: {
          first: "First",
          last: "Last",
          next: "Next",
          previous: "Previous",
        },
      },
      dom: '<"top"lf>rt<"bottom"ip><"clear">',
    })
  }

  // Admin panel access
  const showAdminModal = document.getElementById("showAdminModal")
  const adminLoginModal = document.getElementById("adminLoginModal")

  if (showAdminModal && adminLoginModal) {
    showAdminModal.addEventListener("click", () => {
      const adminModal = new bootstrap.Modal(adminLoginModal)
      adminModal.show()
    })
  }

  // Language selector
  const languageSelectors = document.querySelectorAll(".language-selector")
  languageSelectors.forEach((selector) => {
    selector.addEventListener("click", function (e) {
      e.preventDefault()
      const lang = this.getAttribute("data-lang")
      window.location.href = `?lang=${lang}`
    })
  })

  // Notification count
  function updateNotificationCount() {
    fetch("./app/Model/get_notification_count.php")
      .then((response) => response.json())
      .then((data) => {
        const badge = document.querySelector(".notification-badge")
        if (badge) {
          badge.textContent = data.count
          if (data.count > 0) {
            badge.style.display = "inline-block"

            // Check if we should play a sound
            if (data.new_notifications && data.sound_enabled) {
              playNotificationSound()
            }

            // Show notification alert if there are new notifications
            if (data.new_notifications && data.latest_message) {
              showNotificationAlert(data.latest_message)
            }
          } else {
            badge.style.display = "none"
          }
        }
      })
      .catch((error) => console.error("Error fetching notification count:", error))
  }

  // Show notification alert
  function showNotificationAlert(message) {
    const alert = document.getElementById("notificationAlert")
    const text = document.getElementById("notificationText")

    if (alert && text) {
      text.textContent = message
      alert.style.display = "block"

      // Hide after 5 seconds
      setTimeout(() => {
        alert.style.display = "none"
      }, 5000)
    }
  }

  // Check for notifications on page load and periodically
  updateNotificationCount()
  setInterval(updateNotificationCount, 60000) // Check every minute

  // Filter notifications
  const notificationFilter = document.getElementById("notificationFilter")
  if (notificationFilter) {
    notificationFilter.addEventListener("input", function () {
      const filterText = this.value.toLowerCase()
      const notifications = document.querySelectorAll(".notification-item")

      notifications.forEach((item) => {
        const text = item.textContent.toLowerCase()
        if (text.includes(filterText)) {
          item.style.display = "block"
        } else {
          item.style.display = "none"
        }
      })
    })
  }

  // Show notification modal
  window.showModal = () => {
    const notificationModal = new bootstrap.Modal(document.getElementById("notificationModal"))
    notificationModal.show()
  }

  // Clear notification logs
  window.clearLogs = () => {
    if (confirm("Are you sure you want to clear all notification history?")) {
      fetch("./app/Model/clear_logs.php")
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const container = document.getElementById("notificationHistoryContainer")
            container.innerHTML = `
              <div class='text-center p-4'>
                <i class='bi bi-inbox fs-1 text-muted'></i>
                <p class='mt-3 text-muted'>No notifications</p>
              </div>
            `
            updateNotificationCount()
          }
        })
        .catch((error) => console.error("Error clearing logs:", error))
    }
  }

  // Quick asset search functionality
  window.searchAsset = (event) => {
    event.preventDefault()
    const serial = document.getElementById("quickSearchSerial").value
    const name = document.getElementById("quickSearchName").value
    const resultsContainer = document.getElementById("quickSearchResults")

    if (!serial && !name) {
      resultsContainer.innerHTML = `<div class="alert alert-warning">Please enter a serial number or asset name to search</div>`
      return
    }

    resultsContainer.innerHTML = `<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>`

    // This would be replaced with an actual AJAX call to your backend
    setTimeout(() => {
      // Simulate search results
      if (serial || name) {
        resultsContainer.innerHTML = `
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Asset Name</th>
                  <th>Serial Number</th>
                  <th>Status</th>
                  <th>User</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>LAPTOP-${name || "XPS"}</td>
                  <td>${serial || "SN12345678"}</td>
                  <td><span class="badge bg-success">Active</span></td>
                  <td>John Doe</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="../app/View/Int_entrada.php?serial=${serial || "SN12345678"}" class="btn btn-primary">Change</a>
                      <a href="../app/View/Int_salida.php?serial=${serial || "SN12345678"}" class="btn btn-warning">Output</a>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        `
      } else {
        resultsContainer.innerHTML = `<div class="alert alert-info">No assets found matching your search criteria</div>`
      }
    }, 1000)
  }

  // Función para mostrar la sección activa
  function showActiveSection() {
    const hash = window.location.hash.substring(1) || 'dashboard';
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
      section.classList.remove('active');
      if (section.id === hash + '-section') {
        section.classList.add('active');
      }
    });

    // Actualizar el título de la página
    const pageTitle = document.getElementById('pageTitle');
    if (pageTitle) {
      pageTitle.textContent = document.querySelector(`[data-section="${hash}"] span`).textContent;
    }

    // Actualizar el ítem activo en el menú
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
      item.classList.remove('active');
      if (item.querySelector(`[data-section="${hash}"]`)) {
        item.classList.add('active');
      }
    });
  }

  // Mostrar la sección activa al cargar la página
  showActiveSection();

  // Mostrar la sección activa cuando cambie el hash
  window.addEventListener('hashchange', showActiveSection);

  // Manejar clics en los enlaces del menú
  document.querySelectorAll('.nav-link[data-section]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const section = this.getAttribute('data-section');
      window.location.hash = section;
    });
  });
})
