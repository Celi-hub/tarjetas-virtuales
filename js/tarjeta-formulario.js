// js/tarjeta-formulario.js
// Lógica compartida entre completar_datos.php y editar_tarjeta.php

document.addEventListener('DOMContentLoaded', function () {
    initSelectorFrase();
    initPlaylistToggle();
    initDressCode();
});

function initSelectorFrase() {
    const radios         = document.querySelectorAll('.radio-frase');
    const toggleCustom    = document.getElementById('toggle-custom');
    const inputCustom     = document.getElementById('frase-custom-input');
    const textareaCustom  = document.getElementById('frase_custom_texto');
    const hiddenFrase     = document.getElementById('frase_portada_hidden');
    const preview         = document.getElementById('frase-preview');
    const previewTexto    = document.getElementById('frase-preview-texto');

    if (!radios.length || !hiddenFrase) return;

    function mostrarPreview(texto) {
        if (!preview || !previewTexto) return;
        if (texto && texto.trim()) {
            previewTexto.textContent = texto.trim();
            preview.classList.add('visible');
        } else {
            preview.classList.remove('visible');
        }
    }

    function actualizarHidden() {
        const seleccionado = document.querySelector('.radio-frase:checked');
        if (!seleccionado) return;

        if (seleccionado.value === '__custom__') {
            hiddenFrase.value = textareaCustom ? textareaCustom.value.trim() : '';
            mostrarPreview(hiddenFrase.value);
        } else {
            hiddenFrase.value = seleccionado.value;
            mostrarPreview(seleccionado.value);
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            const esCustom = this.value === '__custom__';
            inputCustom?.classList.toggle('visible', esCustom);
            toggleCustom?.classList.toggle('activo', esCustom);
            actualizarHidden();
            if (esCustom && textareaCustom) setTimeout(() => textareaCustom.focus(), 100);
        });
    });

    if (textareaCustom) {
        textareaCustom.addEventListener('input', function () {
            hiddenFrase.value = this.value.trim();
            mostrarPreview(this.value.trim());
        });
    }

    actualizarHidden();
}

function initPlaylistToggle() {
    const selector = document.querySelector('select[name="playlist_modo"]');
    const campoUrl  = document.querySelector('[name="playlist_url"]');
    if (!selector || !campoUrl) return;

    const contenedor = campoUrl.closest('.form-group');
    if (!contenedor) return;

    function check() {
        contenedor.style.display = selector.value === 'enlace' ? 'block' : 'none';
    }
    selector.addEventListener('change', check);
    check();
}

function initDressCode() {
    const selector = document.getElementById('selector_dress_code');
    const textarea = document.getElementById('dress_code_texto');
    if (!selector || !textarea) return;

    selector.addEventListener('change', function () {
        textarea.value = this.value === 'custom' ? '' : this.value;
        if (this.value === 'custom') textarea.focus();
    });
}