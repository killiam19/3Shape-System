// Function to show the modal with animation
function showModal() {
  const modalElement = document.getElementById("notificationModal");
  const modalBackdrop = document.createElement('div');
  
  // Configure backdrop
  modalBackdrop.classList.add('modal-backdrop', 'fade');
  document.body.appendChild(modalBackdrop);
  
  // Add animation classes
  modalElement.classList.add('fade');
  modalElement.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
  modalElement.style.transform = 'scale(0.95) translateY(-30px)';
  modalElement.style.opacity = '0';
  
  const modal = new bootstrap.Modal(modalElement, {
    backdrop: false // We'll handle the backdrop manually
  });
  
  // Add event listener for when modal starts showing
  modalElement.addEventListener('show.bs.modal', function() {
    requestAnimationFrame(() => {
      modalBackdrop.style.opacity = '0.5';
      this.style.transform = 'scale(1) translateY(0)';
      this.style.opacity = '1';
    });
  });
  
  // Add event listener for when modal is closing
  modalElement.addEventListener('hide.bs.modal', function() {
    this.style.transform = 'scale(0.95) translateY(-30px)';
    this.style.opacity = '0';
    modalBackdrop.style.opacity = '0';
    
    // Remove backdrop after animation
    setTimeout(() => {
      modalBackdrop.remove();
    }, 400);
  });
  
  modal.show();
}

// Variables para control del contador de notificaciones
let notificationCount = 0;
let lastViewedTimestamp = 0;

// Function to update notification badge count
function updateNotificationBadge(count) {
  const badge = document.querySelector('.notification-badge');
  if (badge) {
    // Guardar el conteo actual
    notificationCount = count;
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
    
    // Add a pulse animation if there are new notifications
    if (count > 0) {
      const bellButton = document.querySelector('.bell-button');
      if (bellButton && !bellButton.classList.contains('bell-hover')) {
        bellButton.classList.add('bell-hover');
        setTimeout(() => {
          bellButton.classList.remove('bell-hover');
        }, 1000);
      }
    }
  }
}

// Initialize bell container functionality
document.addEventListener('DOMContentLoaded', function() {
  const bellButton = document.querySelector('.bell-button');
  const bellContainer = document.querySelector('.bell-container');
  const badge = document.querySelector('.notification-badge');
  let bodyOverflowBackup = '';

  if (bellButton) {
    bellButton.addEventListener('click', function(e) {
      e.preventDefault();
      showModal();
      // Al abrir el modal, guardamos el tiempo actual como último visto
      lastViewedTimestamp = Date.now();
      // Guardar en sessionStorage para persistir durante la sesión
      sessionStorage.setItem('lastNotificationCheck', lastViewedTimestamp);
      // Resetear contador visual
      if (badge) {
        badge.textContent = '0';
        badge.style.display = 'none';
      }
    });
  }

  // Restaurar el contador si hay valor en sessionStorage
  const lastCheckStored = sessionStorage.getItem('lastNotificationCheck');
  if (lastCheckStored) {
    lastViewedTimestamp = parseInt(lastCheckStored);
  }

  // Add hover effects to bell container
  if (bellContainer) {
    bellContainer.addEventListener('mouseenter', function() {
      this.querySelector('.bell-button').classList.add('bell-hover');
    });
    bellContainer.addEventListener('mouseleave', function() {
      this.querySelector('.bell-button').classList.remove('bell-hover');
    });
  }

  // Fix scroll bar issue when closing modal
  const modalElement = document.getElementById('notificationModal');
  if (modalElement) {
    modalElement.addEventListener('show.bs.modal', function() {
      // Guardar el overflow actual
      bodyOverflowBackup = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
    });
    modalElement.addEventListener('hidden.bs.modal', function() {
      // Restaurar el overflow
      document.body.style.overflow = bodyOverflowBackup || '';
    });
  }

  // Fetch initial notification count
  fetchNotificationCount(true); // true para inicialización

  // Set up periodic checking for new notifications (every 15 seconds)
  setInterval(() => fetchNotificationCount(false), 15000);
});

// Función para obtener la ruta correcta del endpoint de notificaciones
function getNotificationCountUrl() {
  if (window.location.pathname.includes('/app/Admin/')) {
    return '../Model/get_notification_count.php';
  }
  return './app/Model/get_notification_count.php';
}
function getClearLogsUrl() {
  if (window.location.pathname.includes('/app/Admin/')) {
    return '../Model/clear_logs.php';
  }
  return './app/Model/clear_logs.php';
}
function getDeleteRegistUrl() {
  if (window.location.pathname.includes('/app/Admin/')) {
    return '../Controller/delete_regist.php';
  }
  return './app/Controller/delete_regist.php';
}

// Function to fetch notification count from server
function fetchNotificationCount(isInitialFetch) {
  // Call the notification count endpoint
  fetch(getNotificationCountUrl(), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ 
      lastCheck: lastViewedTimestamp 
    })
  })
    .then(response => response.json())
    .then(data => {
      // Si hay nuevas notificaciones (más de las que ya teníamos)
      if (data.count > 0) {
        updateNotificationBadge(data.count);
        
        // Mostrar animación para nuevas notificaciones (pero no en la carga inicial)
        if (!isInitialFetch && data.count > notificationCount) {
          notifyUser();
        }
      }
    })
    .catch(error => {
      console.error('Error fetching notification count:', error);
    });
}

// Function to notify user of new notifications with animation
function notifyUser() {
  const bellButton = document.querySelector('.bell-button');
  if (bellButton) {
    // Agregar clase para animar
    bellButton.classList.add('bell-hover');
    
    // Repetir la animación 3 veces
    let animationCount = 0;
    const animateInterval = setInterval(() => {
      bellButton.classList.remove('bell-hover');
      setTimeout(() => {
        bellButton.classList.add('bell-hover');
      }, 300);
      
      animationCount++;
      if (animationCount >= 3) {
        clearInterval(animateInterval);
        // Al final dejamos de animar
        setTimeout(() => {
          bellButton.classList.remove('bell-hover');
        }, 1000);
      }
    }, 1000);
  }
}

function clearLogs() {
  if (confirm("Are you sure you want to delete all records?")) {
    fetch(getClearLogsUrl(), {
      method: "POST",
    })
      .then((response) => response.text())
      .then((data) => {
        alert(data); // Show server response
        
        // Reset notification counter after clearing logs
        updateNotificationBadge(0);
        lastViewedTimestamp = Date.now();
        
        location.reload(); // Reload the page to see changes
      })
      .catch((error) => console.error("Error:", error));
  }
}

// Function to delete all records
function deleteAllRecords() {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción eliminará todos los registros del sistema y no se puede deshacer.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar todo',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      // Mostrar estado de carga
      Swal.fire({
        title: 'Eliminando...',
        html: 'Por favor espere mientras se eliminan los registros.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      fetch(getDeleteRegistUrl(), {
        method: "POST",
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          Swal.fire(
            '¡Eliminado!',
            'Todos los registros han sido eliminados exitosamente.',
            'success'
          ).then(() => {
            location.reload();
          });
        } else {
          Swal.fire(
            'Error',
            data.message || 'Hubo un problema al eliminar los registros.',
            'error'
          );
        }
      })
      .catch(error => {
        console.error("Error:", error);
        Swal.fire(
          'Error',
          'Hubo un problema al eliminar los registros.',
          'error'
        );
      });
    }
  });
}