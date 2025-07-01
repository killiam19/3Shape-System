  //Script para la busqueda automatica de la cedula
  function debounce(func, timeout = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
}

function createAutocomplete(config) {
    let lastRequest = null;
    const $input = $(config.inputSelector);
    const $results = $(config.resultsSelector);
    
    const handler = debounce(function(value) {
        if (lastRequest) lastRequest.abort();
        
        if (value.length > config.minChars) {
            $results.html('<div class="loading">Buscando...</div>').show();
            
            lastRequest = $.ajax({
                url: config.url,
                type: 'POST',
                data: { [config.paramName]: value },
                success: function(data) {
                    $results.html(data);
                    if (data.trim().length === 0) $results.hide();
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') {
                        console.error("Error en AJAX:", error);
                        $results.html('<div class="error">Error en la búsqueda</div>');
                    }
                }
            });
        } else {
            $results.hide();
        }
    }, 300);

    $input.on('input', function() {
        handler($(this).val().trim());
    });

    $results.on('click', '.result-item', function() {
        $input.val($(this).text());
        $results.hide();
    });
}
// Evitar números negativos
document.getElementById('search_cedula').addEventListener('input', function() {
    const value = this.value;
    if (value < 0) {
        this.value = value.replace('-', ''); // Elimina el signo negativo
    }
});
$(document).ready(function() {
// Configuración común
const commonConfig = {
    minChars: 3,
    timeout: 300
};

// Inicializar todos los buscadores
createAutocomplete({
    ...commonConfig,
    inputSelector: '#search_cedula',
    resultsSelector: '#resultados',
    url: './Controller/buscar_id.php',
    paramName: 'cedula'
});

createAutocomplete({
    ...commonConfig,
    inputSelector: '#search_serial',
    resultsSelector: '#resultados2',
    url: './Controller/buscar_sr.php',
    paramName: 'serial'
});

createAutocomplete({
    ...commonConfig,
    inputSelector: '#search_user',
    resultsSelector: '#resultados3',
    url: './Controller/buscar_user.php',
    paramName: 'user'
});

// Cierre de resultados mejorado
$(document).on('click', function(e) {
    const containers = ['#search_cedula', '#resultados', 
                     '#search_serial', '#resultados2',
                     '#search_user', '#resultados3'];
    
    if (!containers.some(selector => $(e.target).closest(selector).length)) {
        $('[id^="resultados"]').hide();
    }
});
});