// Buscador de clientes: un solo campo. Escribis y aparecen abajo los clientes que coinciden.
// Uso: agregar data-buscable="1" al <select> (el select queda oculto y es el que envia el dato).
(function () {
    function norm(t) {
        return (t || '').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }
    function coincide(it, q) {
        return q.split(/\s+/).every(function (t) {
            return it.palabras.some(function (w) { return w.indexOf(t) === 0; });
        });
    }
    function iniciar(sel) {
        var todas = Array.prototype.slice.call(sel.options).map(function (o) {
            return { o: o, texto: o.text.trim(), palabras: norm(o.text).replace(/\(tel:/, ' ').split(/[^a-z0-9]+/).filter(Boolean), vacia: o.value === '' };
        });
        var envoltura = document.createElement('div');
        envoltura.style.position = 'relative';
        sel.parentNode.insertBefore(envoltura, sel);

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-lg';
        input.placeholder = 'Escribí apellido, nombre o teléfono...';
        input.autocomplete = 'off';
        envoltura.appendChild(input);

        var lista = document.createElement('div');
        lista.className = 'list-group shadow';
        lista.style.cssText = 'position:absolute;left:0;right:0;z-index:1050;max-height:260px;overflow-y:auto;display:none;';
        envoltura.appendChild(lista);

        // El select queda oculto pero sigue enviando el cliente elegido
        sel.style.cssText = 'position:absolute;opacity:0;height:1px;width:1px;pointer-events:none;left:0;bottom:0;';
        sel.tabIndex = -1;
        envoltura.appendChild(sel);

        function textoElegido() {
            var o = sel.options[sel.selectedIndex];
            return (o && o.value !== '') ? o.text.trim() : '';
        }
        input.value = textoElegido();

        function elegir(valor) {
            sel.value = valor;
            input.value = textoElegido();
            lista.style.display = 'none';
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        }
        function mostrar() {
            var q = norm(input.value).trim();
            lista.innerHTML = '';
            if (!q) { lista.style.display = 'none'; return; }
            var n = 0;
            todas.forEach(function (it) {
                if (it.vacia || !coincide(it, q)) return;
                n++;
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'list-group-item list-group-item-action';
                b.textContent = it.texto;
                b.addEventListener('mousedown', function (e) { e.preventDefault(); elegir(it.o.value); });
                lista.appendChild(b);
            });
            if (!n) {
                var d = document.createElement('div');
                d.className = 'list-group-item text-danger fw-bold small';
                d.textContent = 'No se encontró ningún cliente con ese dato.';
                lista.appendChild(d);
            }
            lista.style.display = 'block';
        }
        input.addEventListener('input', function () {
            // si cambia el texto, se anula la eleccion anterior hasta elegir otro
            if (sel.value !== '' && input.value !== textoElegido()) {
                sel.value = '';
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
            mostrar();
        });
        input.addEventListener('focus', function () { input.select(); });
        input.addEventListener('blur', function () {
            lista.style.display = 'none';
            if (sel.value === '') input.value = input.value; // queda lo escrito, pero no hay cliente elegido
        });
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select[data-buscable]').forEach(iniciar);
    });
})();
