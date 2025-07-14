window.onload = function () {
  // Variable global para almacenar los parámetros seleccionados
  let parametros = "";

  // Manejador de clic del botón
  document
    .getElementById("procesarSeleccionados")
    .addEventListener("click", function () {
      // Añadir efecto de spinning al icono
      let icon = this.querySelector("i");
      icon.classList.add("spin");

      // Detener la animación después de 2 segundos
      setTimeout(() => {
        icon.classList.remove("spin");
      }, 2000);

      const checkboxesSeleccionados = document.querySelectorAll(
        'input[name="selected_assets[]"]:checked'
      );
      // Validar que haya al menos un checkbox seleccionado
      if (checkboxesSeleccionados.length === 0) {
        alert("Please, select at least one item to process.");
        return;
      }

      // Construir lista de parámetros seleccionados
      parametros = Array.from(checkboxesSeleccionados)
        .map((checkbox) => "ids[]=" + encodeURIComponent(checkbox.value))
        .join("&");

      // Mostrar el modal
      const modal = document.getElementById("modalConfirmacion");
      const modalMensaje = document.getElementById("modalMensaje");
      modalMensaje.innerHTML =
        "Do you want to process:<br>" +
        "Option 1 - Generate an output record.<br>" +
        "Option 2 - Create an input record.<br>" +
        "Option 3 - Asset Information.<br>" +
        "Option 4 - Generate QR Codes.";
      modal.style.display = "block";

      // Depuración en consola
      console.log("show modal");
      console.log("Parameters Select:", parametros);
    });

  // Manejadores de botones del modal
  document.getElementById("btnAceptar").onclick = function (event) {
    event.preventDefault(); // Prevenir comportamiento por defecto
    if (!parametros) {
      console.error("Error: Parameters not defined.");
      return;
    }
    // Redirigir a la URL de la opción 1, Firma de acta de salida
    const urlFirmaSalida = `${window.location.origin}/3Shape_project/app/Model/prcsr_indv_sal.php?${parametros}`;
    console.log(`Redirigiendo a: ${urlFirmaSalida}`);
    window.location.href = urlFirmaSalida;
  };

  document.getElementById("btnCancelar").onclick = function (event) {
    event.preventDefault(); // Prevenir comportamiento por defecto
    if (!parametros) {
      console.error("Error: Parameters not defined.");
      return;
    }
    // Redirigir a la sección de firma de acta de entrada
    const urlFirma = `${window.location.origin}/3Shape_project/app/Model/prcsr_indv_ent.php?${parametros}`;
    console.log(`Redirigiendo a: ${urlFirma}`);
    window.location.href = urlFirma;

    // After signature is completed, redirect to the PDF document
    // This part will need to be implemented in the firma_act_indv_ent.php file
  };

  document.getElementById("btnOpcion3").onclick = function (event) {
    event.preventDefault(); // Prevenir comportamiento por defecto
    if (!parametros) {
      console.error("Error: Parameters not defined.");
      return;
    }
    // Redirigir a la URL de la opción 3 que es un Reporte con la información de los activos seleccionador
    const urlOpcion3 = `${window.location.origin}/3Shape_project/app/Model/prcr_nuevo_info.php?${parametros}`;
    console.log(`Redirigiendo a: ${urlOpcion3}`);
    window.location.href = urlOpcion3;
  };

  // Manejador para generar y descargar códigos QR
  document.getElementById("btnQR").onclick = function (event) {
    event.preventDefault();
    if (!parametros) {
      console.error("Error: Parameters not defined.");
      return;
    }

    const checkboxes = document.querySelectorAll(
      'input[name="selected_assets[]"]:checked'
    );
    checkboxes.forEach((checkbox) => {
      const row = checkbox.closest("tr");
      console.log("Procesando fila:", row.innerHTML);

      // Obtener celdas con validación
      const serialCell = row.querySelector("td:nth-child(3)");
      const userCell = row.querySelector("td:nth-child(5)");
      const statusCell = row.querySelector("td:nth-child(7)");

      if (!serialCell || !userCell || !statusCell) {
        console.error("Estructura de tabla inválida", {
          serialCell,
          userCell,
          statusCell,
        });
        return;
      }

      try {
        // Get all required cells first
        const jobTitleCell = row.querySelector("td:nth-child(6)");
        const statusCell = row.querySelector("td:nth-child(7)");
        const purchaseCountryCell = row.querySelector("td:nth-child(8)");
        const warrantyCell = row.querySelector("td:nth-child(9)");

        // Validate all required cells exist
        if (!serialCell || !userCell || !statusCell || !jobTitleCell) {
          console.error("Required cells not found", {
            serialCell: !!serialCell,
            userCell: !!userCell,
            statusCell: !!statusCell,
            jobTitleCell: !!jobTitleCell
          });
          return;
        }

        const assetData = {
          asset: {
            id: checkbox.value,
            serial_number: serialCell.textContent.trim().replace(/<[^>]*>/g, ''),
            purchase_country: purchaseCountryCell?.textContent?.trim().replace(/<[^>]*>/g, '') || 'N/A',
            warranty_enddate: warrantyCell?.textContent?.trim().replace(/<[^>]*>/g, '') || 'N/A',
          },
          user: {
            name: userCell.textContent.trim().replace(/<[^>]*>/g, ''),
            location: row.querySelector("td:nth-child(4)")?.textContent?.trim().replace(/<[^>]*>/g, '') || 'N/A',
            job_title: jobTitleCell.textContent.trim().replace(/<[^>]*>/g, ''),
            status: statusCell.textContent.trim().replace(/<[^>]*>/g, ''),
          },
        };

        console.log("QR Data:", assetData);
        console.log("Data being sent to server:", JSON.stringify(assetData, null, 2));

        // Validate essential data
        if (!assetData.asset.serial_number || !assetData.user.name) {
          throw new Error("Missing essential asset data");
        }
        // Validar que los datos esenciales estén presentes
        if (!assetData.asset.serial_number || !assetData.user.name) {
          console.error("Datos esenciales incompletos en la fila");
          return;
        }

        // Generar PDF y código QR
        fetch("/3Shape_project/app/Model/generar_qr_pdf.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(assetData),
        })
          .then(async (response) => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
              // If not JSON, read as text and throw an error
              const errorText = await response.text();
              console.error('Server response was not JSON:', errorText);
              throw new Error(`Server returned non-JSON response: ${response.status} ${response.statusText}`);
            }
            // If JSON, attempt to parse
            try {
              return await response.json();
            } catch (parseError) {
              console.error('Failed to parse JSON response:', parseError);
              // Attempt to get text for logging if JSON parsing fails
              try {
                  const errorText = await response.text(); // Re-read as text if possible
                  console.error('Raw response text:', errorText);
              } catch (textError) {
                  console.error('Could not read response text after JSON parse failure:', textError);
              }
              throw new Error('Failed to parse JSON response from server.');
            }
          })
          .then((data) => {
            if (!data.qr_data || typeof data.qr_data !== "string") {
              throw new Error("Datos QR inválidos recibidos del servidor");
            }
            // Crear un elemento para el código QR
            const qrContainer = document.createElement("div");
            qrContainer.id = "qr-container-" + Date.now();
            document.body.appendChild(qrContainer);

            try {
              const qr = new QRCode(qrContainer, {
                text: data.qr_data,
                width: 256,
                height: 256,
              });
            } catch (error) {
              console.error("Error en inicialización QR:", error);
              throw new Error(
                "Falló la creación del código QR: " + error.message
              );
            }

            if (!window.QRCode) {
              console.error("QRCode no está disponible en el ámbito global");
              throw new Error("La librería QRCode no se cargó correctamente");
            }
            // Crear un enlace para escanear
            const scanLink = document.createElement("a");
            scanLink.href = "#";
            scanLink.textContent = "Escanear QR para ver PDF";
            scanLink.style.display = "block";
            scanLink.style.marginTop = "10px";

            // Simular escaneo al hacer clic (para pruebas)
            scanLink.onclick = function (e) {
              e.preventDefault();
              fetch("/3Shape_project/app/Model/decode_qr_pdf.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({ qr_content: data.qr_data }),
              })
                .then((response) => response.json())
                .then((result) => {
                  if (result.success) {
                    window.open(result.pdf_url, "_blank");
                  } else {
                    throw new Error(result.error || "Error al procesar el PDF");
                  }
                })
                .catch((error) => {
                  console.error("Error al procesar el QR:", error);
                  alert(
                    "Error al procesar el código QR. Por favor, intente nuevamente."
                  );
                });
            };

            qrContainer.appendChild(scanLink);
            document.body.appendChild(qrContainer);
          })
          .catch((error) => {
            console.error("Error al generar el QR:", error);
            alert(
              "Error al generar el código QR: " + error.message + ". Por favor, revise la consola para más detalles e intente nuevamente."
            );
          });
      } catch (error) {
        console.error("Error processing row:", error);
        alert("Error processing the selected row. Please try again.");
      }
    });
  };

  document.getElementById("btnCerrarModal").onclick = function () {
    const modal = document.getElementById("modalConfirmacion");
    modal.style.display = "none";
  };
};
