document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap's JavaScript
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JavaScript is not loaded. Dropdown functionality may not work correctly.');
        return;
    }

    // Initialize all dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });

    // Add click event listeners to dropdown buttons to prevent default behavior
    document.querySelectorAll('.action-btn-view').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // Fix for dynamically added dropdown elements (like in DataTables)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const dropdowns = node.querySelectorAll('[data-bs-toggle="dropdown"]');
                        if (dropdowns.length) {
                            dropdowns.forEach(function(dropdown) {
                                new bootstrap.Dropdown(dropdown);
                            });
                        }
                    }
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    // Fix for mobile devices
    if ('ontouchstart' in document.documentElement) {
        document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
            element.setAttribute('data-bs-toggle', 'dropdown');
        });
    }
});
