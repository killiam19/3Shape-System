// Animación del contador
document.addEventListener('DOMContentLoaded', function() {
    const counterElement = document.getElementById('total-client');
    if (counterElement) {
        const targetCount = parseInt(counterElement.textContent.replace(/\./g, ''));
        const duration = 1000; // Duración de la animación en ms
        const steps = 50; // Número de pasos
        const increment = targetCount / steps;
        let currentCount = 0;

        // Función para formatear números con separadores de miles
        function formatNumber(num) {
            return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Usar requestAnimationFrame para una animación más suave
        const animate = () => {
            currentCount += increment;

            if (currentCount <= targetCount) {
                counterElement.textContent = formatNumber(currentCount);
                requestAnimationFrame(animate);
            } else {
                counterElement.textContent = formatNumber(targetCount);
            }
        };

        requestAnimationFrame(animate);
    }
});

// Animación del ícono de carpeta
function openfolder() {
    const folderIcon = document.getElementById('div1');
    if (folderIcon) {
        folderIcon.innerHTML = '&#xf114;';
        setTimeout(function() {
            folderIcon.innerHTML = '&#xf115;';
        }, 1000);
    }
}

// Iniciar animación de carpeta
document.addEventListener('DOMContentLoaded', function() {
    openfolder();
    setInterval(openfolder, 2000);
});

// Función para mostrar advertencias
function showWarnings() {
    const hiddenWarnings = document.getElementById('hidden-warnings');
    if (hiddenWarnings) {
        hiddenWarnings.classList.add('show'); // Muestra las advertencias ocultas
        event.target.classList.add('hide'); // Oculta el botón
    }
}