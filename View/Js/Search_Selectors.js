// Optimización 1: Cachear elementos del DOM y configuraciones
const searchSelectors = {
    assetname: 'input[name="search_assetname"]',
    serial: 'input[name="search_serial"]',
    cedula: 'input[name="search_cedula"]',
    status_change: 'input[name="search_status_change"]',
    user: 'input[name="search_user"]',
    entry_date: 'input[name="search_entry_date"]',
    departure_date: 'input[name="search_departure_date"]',
    user_status: 'select[name="search_user_status[]"]'
};

// Cachear elementos una vez al cargar la página
const cachedElements = {};
Object.entries(searchSelectors).forEach(([key, selector]) => {
    cachedElements[key] = document.querySelector(selector);
});

// Optimización 2: Usar event delegation
document.body.addEventListener('click', function(e) {
    const button = e.target.closest('[id^="generate"]');
    if (!button) return;

    e.preventDefault();
    
    // Optimización 3: Usar objeto para parámetros
    const params = new URLSearchParams();
    
    // Optimización 4: Recorrido optimizado de elementos
    Object.entries(cachedElements).forEach(([key, element]) => {
        if (key === 'user_status') {
            const options = element.selectedOptions;
            for (let i = 0; i < options.length; i++) {
                params.append('search_user_status[]', options[i].value);
            }
        } else {
            const value = element.value.trim();
            if (value) params.append(`search_${key}`, value);
        }
    });

    // Optimización 5: Usar requestIdleCallback para apertura de ventana
    const url = button.getAttribute('data-url');
    window.requestIdleCallback(() => {
        window.open(`${url}?${params.toString()}`, '_blank');
    }, { timeout: 1000 });
});