<?php
// ========================================================
// ARCHIVO: validaciones.php
// OBJETIVO: Funciones de validación compartidas por todos los formularios.
// Se usa con:  require_once 'validaciones.php';
// Cada función recibe el valor, el nombre del campo y la lista $errores
// (por referencia). Si algo está mal, agrega un mensaje a $errores.
// Devuelve el valor limpio (o null si el campo opcional está vacío).
// ========================================================

// Fecha desde la que se empezó a usar el sistema (se aceptan fechas desde acá)
if (!defined('FECHA_INICIO_SISTEMA')) {
    define('FECHA_INICIO_SISTEMA', '2026-08-01');
}

if (!function_exists('v_mostrar_errores')) {
    // Muestra los errores con el mismo estilo del sistema y corta la ejecución
    function v_mostrar_errores(array $errores, string $titulo = 'No se pudo guardar') {
        echo "<div style='font-family: sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; background-color: #fff8f8;'>";
        echo "<h3 style='color: #dc3545; margin-top: 0;'>❌ " . htmlspecialchars($titulo) . "</h3>";
        echo "<p>Revisá los siguientes datos:</p><ul>";
        foreach ($errores as $e) {
            echo "<li>" . htmlspecialchars($e) . "</li>";
        }
        echo "</ul>";
        echo "<br><a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Volver y corregir</a>";
        echo "</div>";
        exit;
    }
}

if (!function_exists('v_limpiar')) {
    function v_limpiar($v) {
        return preg_replace('/\s+/u', ' ', trim((string)$v));
    }
}

// Nombre / apellido: solo letras, espacios, apóstrofe, punto y guion (sin números)
if (!function_exists('v_nombre')) {
    function v_nombre($v, $etq, array &$e, $obligatorio = true, $max = 100) {
        $v = v_limpiar($v);
        if ($v === '') {
            if ($obligatorio) $e[] = "$etq es obligatorio.";
            return null;
        }
        $len = mb_strlen($v);
        if ($len < 2 || $len > $max) {
            $e[] = "$etq debe tener entre 2 y $max caracteres.";
        } elseif (!preg_match("/^\p{L}[\p{L}' .\-]*$/u", $v)) {
            $e[] = "$etq solo puede tener letras, espacios, apóstrofes y guiones (no números ni símbolos).";
        }
        return $v;
    }
}

// Texto con letras y números (marca, modelo, color, especialidad)
if (!function_exists('v_alfanum')) {
    function v_alfanum($v, $etq, array &$e, $obligatorio = true, $max = 50) {
        $v = v_limpiar($v);
        if ($v === '') {
            if ($obligatorio) $e[] = "$etq es obligatorio.";
            return null;
        }
        if (mb_strlen($v) > $max) {
            $e[] = "$etq no puede superar los $max caracteres.";
        } elseif (!preg_match('/\p{L}/u', $v) || !preg_match("/^[\p{L}0-9 .,\/+'\-]+$/u", $v)) {
            $e[] = "$etq debe tener al menos una letra y solo letras, números, espacios o . , / + - (no símbolos raros).";
        }
        return $v;
    }
}

// Teléfono: 8 a 15 dígitos, se aceptan espacios, guiones, paréntesis y + inicial
if (!function_exists('v_telefono')) {
    function v_telefono($v, $etq, array &$e, $obligatorio = true) {
        $v = trim((string)$v);
        if ($v === '') {
            if ($obligatorio) $e[] = "$etq es obligatorio.";
            return null;
        }
        $limpio = preg_replace('/[\s\-().]/', '', $v);
        if (!preg_match('/^\+?[0-9]{8,15}$/', $limpio)) {
            $e[] = "$etq no es válido: debe tener entre 8 y 15 números (podés usar espacios, guiones, paréntesis y + al inicio). No se aceptan letras.";
        } elseif (preg_match('/^\+?(\d)\1+$/', $limpio)) {
            $e[] = "$etq no es válido: no puede ser un mismo número repetido.";
        }
        return $limpio;
    }
}

if (!function_exists('v_email')) {
    function v_email($v, array &$e) {
        $v = trim((string)$v);
        if ($v === '') return null;
        if (mb_strlen($v) > 100 || !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $e[] = "El email no tiene un formato válido (ejemplo: nombre@correo.com).";
        }
        return $v;
    }
}

// Dirección: opcional; debe parecer una dirección real
if (!function_exists('v_direccion')) {
    function v_direccion($v, array &$e, $obligatorio = false) {
        $v = v_limpiar($v);
        if ($v === '') {
            if ($obligatorio) $e[] = "La dirección es obligatoria.";
            return null;
        }
        $len = mb_strlen($v);
        $letras = preg_match_all('/\p{L}/u', $v);
        if ($len < 5 || $len > 200) {
            $e[] = "La dirección debe tener entre 5 y 200 caracteres.";
        } elseif ($letras < 3) {
            $e[] = "La dirección no es válida: debe incluir el nombre de la calle o barrio (con letras), por ejemplo: Av. San Martín 450.";
        } elseif (!preg_match("/^[\p{L}0-9 .,°º#\/'\-]+$/u", $v)) {
            $e[] = "La dirección tiene símbolos no permitidos. Usá letras, números y . , # / -";
        }
        return $v;
    }
}

// Año del vehículo: entero entre 1950 y el año próximo
if (!function_exists('v_anio')) {
    function v_anio($v, array &$e) {
        $v = trim((string)$v);
        if ($v === '') return null;
        $max = (int)date('Y') + 1;
        if (!ctype_digit($v) || (int)$v < 1950 || (int)$v > $max) {
            $e[] = "El año debe ser un número entre 1950 y $max.";
            return null;
        }
        return (int)$v;
    }
}

// Entero dentro de un rango
if (!function_exists('v_entero')) {
    function v_entero($v, $etq, array &$e, $min, $max, $defecto = 0) {
        $v = trim((string)$v);
        if ($v === '') return $defecto;
        if (!preg_match('/^[0-9]+$/', $v) || (int)$v < $min || (int)$v > $max) {
            $e[] = "$etq debe ser un número entero entre $min y $max.";
            return $defecto;
        }
        return (int)$v;
    }
}

// Importe con decimales dentro de un rango
if (!function_exists('v_decimal')) {
    function v_decimal($v, $etq, array &$e, $min = 0, $max = 99999999.99) {
        $v = str_replace(',', '.', trim((string)$v));
        if ($v === '') return 0.00;
        if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $v) || (float)$v < $min || (float)$v > $max) {
            $e[] = "$etq debe ser un importe válido (número positivo, hasta 2 decimales, máximo " . number_format($max, 2, ',', '.') . ").";
            return 0.00;
        }
        return round((float)$v, 2);
    }
}

// Fecha real (AAAA-MM-DD) dentro de un rango opcional
if (!function_exists('v_fecha')) {
    function v_fecha($v, $etq, array &$e, $desde = null, $hasta = null) {
        $v = trim((string)$v);
        if ($v === '') return null;
        $d = DateTime::createFromFormat('!Y-m-d', $v);
        $err = DateTime::getLastErrors();
        if (!$d || ($err && ($err['warning_count'] || $err['error_count'])) || $d->format('Y-m-d') !== $v) {
            $e[] = "$etq no es una fecha válida.";
            return null;
        }
        if ($desde !== null && $v < $desde) {
            $e[] = "$etq no puede ser anterior al " . date('d/m/Y', strtotime($desde)) . ".";
        }
        if ($hasta !== null && $v > $hasta) {
            $e[] = "$etq no puede ser posterior al " . date('d/m/Y', strtotime($hasta)) . ".";
        }
        return $v;
    }
}

// Fecha y hora real (acepta el formato del campo datetime-local)
if (!function_exists('v_fecha_hora')) {
    function v_fecha_hora($v, $etq, array &$e, $desde = null, $hasta = null) {
        $v = str_replace('T', ' ', trim((string)$v));
        if ($v === '') return null;
        $d = DateTime::createFromFormat('!Y-m-d H:i:s', $v) ?: DateTime::createFromFormat('!Y-m-d H:i', $v);
        $err = DateTime::getLastErrors();
        if (!$d || ($err && ($err['warning_count'] || $err['error_count']))) {
            $e[] = "$etq no es una fecha y hora válida.";
            return null;
        }
        $norm = $d->format('Y-m-d H:i:s');
        if ($desde !== null && $norm < $desde) {
            $e[] = "$etq no puede ser anterior al ingreso del trabajo (" . date('d/m/Y H:i', strtotime($desde)) . ").";
        }
        if ($hasta !== null && $norm > $hasta) {
            $e[] = "$etq no puede ser posterior al " . date('d/m/Y H:i', strtotime($hasta)) . ".";
        }
        return $norm;
    }
}

// Texto libre (falla, arreglo, descripción): debe tener letras, no solo números o símbolos
if (!function_exists('v_texto')) {
    function v_texto($v, $etq, array &$e, $obligatorio = true, $min = 3, $max = 1000) {
        $v = v_limpiar($v);
        if ($v === '') {
            if ($obligatorio) $e[] = "$etq es obligatorio.";
            return $obligatorio ? '' : null;
        }
        $len = mb_strlen($v);
        if ($len < $min || $len > $max) {
            $e[] = "$etq debe tener entre $min y $max caracteres.";
        } elseif (preg_match_all('/\p{L}/u', $v) < 2) {
            $e[] = "$etq: tiene que describirse con palabras, no solo con números o símbolos.";
        }
        return $v;
    }
}

// Código de repuesto: letras, números, guion, punto o barra
if (!function_exists('v_codigo')) {
    function v_codigo($v, $etq, array &$e) {
        $v = strtoupper(trim((string)$v));
        if ($v === '') { $e[] = "$etq es obligatorio."; return ''; }
        if (!preg_match('/^[A-Z0-9][A-Z0-9\-_.\/]{1,29}$/', $v)) {
            $e[] = "$etq debe tener entre 2 y 30 caracteres: letras, números y - _ . / (sin espacios).";
        }
        return $v;
    }
}

// Valor de una lista permitida (estados)
if (!function_exists('v_opcion')) {
    function v_opcion($v, array $permitidas, $etq, array &$e, $defecto) {
        $v = trim((string)$v);
        if ($v === '') return $defecto;
        if (!in_array($v, $permitidas, true)) {
            $e[] = "$etq no es una opción válida.";
            return $defecto;
        }
        return $v;
    }
}
