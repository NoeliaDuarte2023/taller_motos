// Muestra un cartel rojo debajo del campo cuando el teléfono o la dirección no son válidos.
(function () {
    var MSG_TEL = 'Número de teléfono no válido';
    var MSG_DIR = 'Dirección no válida';

    function telefonoValido(v) {
        v = v.trim();
        var limpio = v.replace(/[\s\-().]/g, '');
        if (!/^\+?[0-9]{8,15}$/.test(limpio)) return false;
        if (/^\+?(\d)\1+$/.test(limpio)) return false;
        return true;
    }
    function direccionValida(v) {
        v = v.trim();
        if (v === '') return true; // la dirección es opcional
        if (v.length < 5 || v.length > 200) return false;
        var letras = v.match(/[A-Za-zÀ-ÿ]/g);
        if (!letras || letras.length < 3) return false;
        return /^[A-Za-zÀ-ÿ0-9 .,°º#\/'\-]+$/.test(v);
    }

    function preparar(campo, esTelefono) {
        var cartel = document.createElement('div');
        cartel.className = 'text-danger fw-bold small mt-1';
        cartel.style.display = 'none';
        cartel.setAttribute('role', 'alert');
        campo.parentNode.appendChild(cartel);

        function revisar() {
            var valor = campo.value;
            var ok;
            if (esTelefono) {
                // si el teléfono no es obligatorio y está vacío, se acepta
                ok = (valor.trim() === '' && !campo.required) ? true : telefonoValido(valor);
            } else {
                ok = direccionValida(valor);
            }
            cartel.textContent = ok ? '' : (esTelefono ? MSG_TEL : MSG_DIR);
            cartel.style.display = ok ? 'none' : 'block';
            campo.classList.toggle('is-invalid', !ok);
            campo.setCustomValidity(ok ? '' : (esTelefono ? MSG_TEL : MSG_DIR));
            return ok;
        }
        campo.addEventListener('input', revisar);
        campo.addEventListener('blur', revisar);
        // evita el globito gris del navegador: el aviso es el cartel rojo
        campo.addEventListener('invalid', function (e) { e.preventDefault(); revisar(); });
        if (campo.form) {
            campo.form.addEventListener('submit', function (e) {
                if (!revisar()) { e.preventDefault(); campo.focus(); }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['telefono', 'nuevo_telefono'].forEach(function (n) {
            var c = document.querySelector('input[name="' + n + '"]');
            if (c) preparar(c, true);
        });
        ['direccion', 'nuevo_direccion'].forEach(function (n) {
            var c = document.querySelector('input[name="' + n + '"]');
            if (c) preparar(c, false);
        });
    });
})();
