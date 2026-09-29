// js/editar_tarjeta.js
// Lógica exclusiva de la pantalla de edición (no se comparte con completar_datos.php)

document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle visual de cada módulo al tildar/destildar ──
    document.querySelectorAll('.checkbox-modulo').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const columna  = this.dataset.modulo;
            const label    = document.getElementById('label_' + columna);
            const fieldset = document.getElementById('fieldset_' + columna);

            if (this.checked) {
                label?.classList.add('seleccionado');
                if (fieldset) {
                    fieldset.classList.remove('oculto');
                    setTimeout(() => fieldset.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 80);
                }
            } else {
                label?.classList.remove('seleccionado');
                fieldset?.classList.add('oculto');
            }
        });
    });

    // ── Toggle en vivo: RSVP interno vs WhatsApp ──
    const checkboxRsvpInterno = document.querySelector('.checkbox-modulo[data-modulo="mod_rsvp_interno"]');
    const bloqueInterno  = document.getElementById('rsvp-interno-bloque');
    const bloqueWhatsapp = document.getElementById('rsvp-whatsapp-bloque');

    if (checkboxRsvpInterno && bloqueInterno && bloqueWhatsapp) {
        checkboxRsvpInterno.addEventListener('change', function () {
            bloqueInterno.classList.toggle('oculto', !this.checked);
            bloqueWhatsapp.classList.toggle('oculto', this.checked);
        });
    }

});