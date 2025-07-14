$(document).ready(function () {
  $("#mainTable").addClass("fade-in");

  // Configuración global de errores
  $.fn.dataTable.ext.errMode = "none";

  let table = $("#mainTable").DataTable({
    // Configuración básica
    searching: false,
    ordering: true,
    lengthMenu: [
      [10, 25, 50, 100, 200, -1],
      [10, 25, 50, 100, 200, "All"],
    ],
    pageLength: 25,

    // Mejoras de estilo
    dom: '<"container-fluid"<"row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-6"f>>>rt<"container-fluid"<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>>',
    language: {
      lengthMenu: "Show _MENU_ entries",
      zeroRecords: "No matching records found",
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: "Showing 0 to 0 of 0 entries",
      infoFiltered: "(filtered from _MAX_ total entries)",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous",
      },
    },
    autoWidth: true,
    responsive: true,

    columnDefs: [
      {
        orderable: false, // Deshabilita el ordenamiento
        targets: [0, -1, -2, -3], // Primera columna (0) y las tres últimas (-1, -2, -3)
      },
    ],
    keys: {
      focus: ":not(.non-focusable)",
    },
    select: {
      style: "os",
      items: "row",
    },

    // Scroll y dimensiones
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
    fixedColumns: {
      leftColumns: 2,
      rightColumns: 1,
    },
    fixedHeader: {
      header: true,
      footer: true,
      headerOffset: $("#Navitem").outerHeight(), // Ajusta según tu navbar
    },

    // Estado persistente
    stateSave: true,
    stateDuration: -1,
    stateSaveCallback: function (settings, data) {
      localStorage.setItem(
        "DataTables_" + settings.sInstance,
        JSON.stringify(data)
      );
    },

    stateLoadCallback: function (settings) {
      return JSON.parse(
        localStorage.getItem("DataTables_" + settings.sInstance)
      );
    },

    // Renderizado personalizado
    createdRow: function (row, data, dataIndex) {
      $(row).attr("data-index", dataIndex);
      $(row).addClass("hover-effect");
    },

    // Configuración avanzada
    initComplete: function (settings, json) {
      // Añade placeholder al buscador
      $("div.dataTables_filter input").attr("placeholder", "Search...");

      // Añade clases de Bootstrap a los elementos
      $(".dataTables_length select").addClass("form-control form-control-sm");
      $(".dataTables_filter input").addClass("form-control form-control-sm");
    },
  });

  // Añade transición suave al recargar
  table.on("draw", function () {
    $("#mainTable").addClass("fade-in");
  });

  // Manejo de errores
  table.on("error.dt", function (e, settings, techNote, message) {
    console.error("DataTables Error: ", message);
    // Aquí puedes mostrar un mensaje al usuario
  });
});
