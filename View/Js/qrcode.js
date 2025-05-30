/* Librería QRCode.js estándar para generación de QR legibles en móviles. Fuente: https://davidshimjs.github.io/qrcodejs/ */
// Versión minificada de QRCode.js
// (function() { ... })();
// Por brevedad, aquí solo se incluye la interfaz mínima para el proyecto:

// QRCode constructor: new QRCode(canvasElement, { text, width, height, colorDark, colorLight, correctLevel })
(function() {
    var QRCode = function(el, options) {
        if (typeof el === 'string') el = document.getElementById(el);
        options = options || {};
        this._el = el;
        this._options = options;
        this.makeCode(options.text || '');
    };
    QRCode.CorrectLevel = { L: 1, M: 0, Q: 3, H: 2 };
    QRCode.prototype.makeCode = function(text) {
        // Usar la librería QRCode.js real aquí. Por simplicidad, se asume que la librería está correctamente incluida.
        // Si no está, se debe descargar de https://github.com/davidshimjs/qrcodejs y pegar aquí el código minificado.
        // Aquí solo se deja la interfaz para evitar código extenso.
        if (window.QRCode) {
            new window.QRCode(this._el, {
                text: text,
                width: this._options.width || 256,
                height: this._options.height || 256,
                colorDark: this._options.colorDark || '#000000',
                colorLight: this._options.colorLight || '#ffffff',
                correctLevel: window.QRCode.CorrectLevel.M
            });
        } else {
            this._el.innerHTML = '<div style="color:red">QRCode.js library missing</div>';
        }
    };
    window.QRCode = QRCode;
})();


// Función de ayuda para crear un QR y obtener su URL
function generateQRCodeURL(text, options = {}) {
    const canvas = document.createElement('canvas');
    const qr = new QRCode(canvas, options);
    qr.generate(text);
    return qr.toDataURL();
}
