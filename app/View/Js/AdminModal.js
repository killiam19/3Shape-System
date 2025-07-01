$(document).ready(function () {
  $("#adminButton").click(function () {
    $("#adminModal").modal("show");

    console.log("Iniciando solicitud a:", "./Admin/get_forms.php");
    $.ajax({
      url: "./Admin/get_forms.php",
      type: "GET",
      dataType: "html",
      success: function (response) {
        console.log("Respuesta procesada:", response);
        
        // Insertar el contenido HTML completo de la respuesta
        $('#dynamicForms').html(response);

        // Asegurar que los contenedores estén visibles
        $('.form-container').show();
        $('.form-container').addClass('active-form');

        // Ejecutar scripts del response
        const scripts = $(response).find('script');
        scripts.each(function() {
          try {
            $.globalEval(this.textContent || this.innerHTML);
          } catch (e) {
            console.error('Error al ejecutar script:', e);
          }
        });

        // Reiniciar handlers de formularios
        $("form")
          .off("submit")
          .on("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append("csrf_token", $('input[name="csrf_token"]').val());
            // Envío AJAX preservando action original
            $.ajax({
              url: $(this).attr("action"),
              type: "POST",
              data: formData,
              processData: false,
              contentType: false,
              success: function (res) {
                $("#dynamicForms").html(res);
                $('.form-container').show();
                $('.form-container').addClass('active-form');
              },
            });
          });
      },
      error: function (xhr) {
        console.error("Error en la solicitud:", xhr.status, xhr.responseText);
        let mensaje = 'Error en el servidor';
        
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          mensaje = errorResponse.error || 'Error desconocido';
        } catch (e) {
          if (xhr.responseText.includes('Acceso denegado')) {
            mensaje = 'Permisos insuficientes';
          } else if (xhr.status === 403) {
            mensaje = 'Sesión expirada o no válida';
          } else if (xhr.status === 500) {
            mensaje = 'Error interno del servidor';
          }
        }
        
        Swal.fire({
          title: 'Error ' + xhr.status,
          text: mensaje,
          icon: 'error',
          confirmButtonText: 'Reintentar',
          allowOutsideClick: false
        }).then(() => {
          $('#adminModal').modal('hide');
          location.reload();
        });
      },
    });
  });
});

// Mapeo de formularios en orden
(function() {
  // Declaración en scope de función
  // Mostrar ambos formularios simultáneamente
  document.querySelectorAll('.form-container').forEach(container => {
    container.style.display = 'block';
    container.classList.add('active-form');
  });
})();
$('.form-container').show();
$('.form-container').addClass('active-form');