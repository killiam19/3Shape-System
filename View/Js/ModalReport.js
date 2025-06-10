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
    notificationCount = count;
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
    
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
  
  if (bellButton) {
    bellButton.addEventListener('click', function(e) {
      e.preventDefault();
      showModal();
      lastViewedTimestamp = Date.now();
      document.querySelector('.notification-badge').style.display = 'none';
    });
  }
  
  if (bellContainer) {
    bellContainer.addEventListener('mouseenter', function() {
      this.querySelector('.bell-button').classList.add('bell-hover');
    });
    
    bellContainer.addEventListener('mouseleave', function() {
      this.querySelector('.bell-button').classList.remove('bell-hover');
    });
  }
  
  fetchNotificationCount(true);
  setInterval(() => fetchNotificationCount(false), 15000);
});

// Function to fetch notification count from server
function fetchNotificationCount(isInitialFetch) {
  fetch('../Model/get_notification_count.php', {
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
      if (data.count > 0) {
        updateNotificationBadge(data.count);
        
        if (!isInitialFetch && data.count > notificationCount) {
          const notificationSound = new Audio('../View/assets/audio/notification-sound.mp3');
          notificationSound.volume = 0.5;
          notificationSound.play().catch(e => {
            console.log("Error playing sound:", e);
          });
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
    bellButton.classList.add('bell-hover');
    
    let animationCount = 0;
    const animateInterval = setInterval(() => {
      bellButton.classList.remove('bell-hover');
      setTimeout(() => {
        bellButton.classList.add('bell-hover');
      }, 300);
      
      animationCount++;
      if (animationCount >= 3) {
        clearInterval(animateInterval);
        setTimeout(() => {
          bellButton.classList.remove('bell-hover');
        }, 1000);
      }
    }, 1000);
  }
}

// Function to clear all logs
function clearLogs() {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción eliminará todas las notificaciones y no se puede deshacer.",
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
        html: 'Por favor espere mientras se eliminan las notificaciones.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      fetch("../Model/clear_logs.php", {
        method: "POST",
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'Todas las notificaciones han sido eliminadas.',
            icon: 'success'
          });
          updateNotificationBadge(0);
          lastViewedTimestamp = Date.now();
          location.reload();
        } else {
          Swal.fire({
            title: 'Error',
            text: data.message || 'Hubo un problema al eliminar las notificaciones.',
            icon: 'error'
          });
        }
      })
      .catch(error => {
        console.error("Error:", error);
        Swal.fire({
          title: 'Error',
          text: 'Hubo un problema al eliminar las notificaciones.',
          icon: 'error'
        });
      });
    }
  });
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
      fetch("../Controller/delete_regist.php", {
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