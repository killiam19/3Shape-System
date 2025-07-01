document.addEventListener('DOMContentLoaded', function() {
    // Obtener todos los elementos colapsables
    const collapseElements = document.querySelectorAll('.collapse');
    const collapseHeaders = document.querySelectorAll('[data-bs-toggle="collapse"]');

    // Crear una instancia de Bootstrap Collapse para cada elemento
    const collapseInstances = Array.from(collapseElements).map(element => {
        return new bootstrap.Collapse(element, {
            toggle: false
        });
    });

    // Agregar evento click a cada encabezado
    collapseHeaders.forEach((header, index) => {
        header.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Cerrar todos los demás elementos colapsables
            collapseElements.forEach((element, elementIndex) => {
                if (elementIndex !== index && element.classList.contains('show')) {
                    collapseInstances[elementIndex].hide();
                }
            });

            // Alternar el elemento actual
            collapseInstances[index].toggle();
        });
    });
});