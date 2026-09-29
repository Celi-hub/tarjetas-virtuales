/**
 * Confeti al desbloquear la tarjeta (temas Divertido e Infantil).
 * Momentia · Tarjetas Virtuales
 *
 * - Sin dependencias ni canvas: piezas <i> animadas con la Web Animations API.
 * - Los colores salen del tema: --confeti-colores: #f00, #0f0, ...
 * - Se dispara con el evento 'momentia:musica-decision' que emite base_portada.php
 *   al terminar la pantalla de misterio. Respeta prefers-reduced-motion.
 */
(function () {
    'use strict';

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var PIEZAS = 46;

    function colores() {
        var css = getComputedStyle(document.body).getPropertyValue('--confeti-colores');
        var lista = css.split(',').map(function (c) { return c.trim(); }).filter(Boolean);
        return lista.length ? lista : ['#ff5d8f', '#ffc53d', '#3ec5ff', '#7c5cff', '#2ecc8f'];
    }

    function lanzar() {
        var paleta = colores();
        var capa = document.createElement('div');
        capa.setAttribute('aria-hidden', 'true');
        capa.style.cssText = 'position:fixed;inset:0;overflow:hidden;pointer-events:none;z-index:9998';
        document.body.appendChild(capa);

        var ancho = window.innerWidth;
        var alto = window.innerHeight;
        var animaciones = [];

        for (var i = 0; i < PIEZAS; i++) {
            var pieza = document.createElement('i');
            var tam = 7 + Math.random() * 8;
            var redonda = Math.random() < 0.35;
            pieza.style.cssText =
                'position:absolute;top:0;left:0;display:block;will-change:transform,opacity;' +
                'width:' + tam + 'px;height:' + (redonda ? tam : tam * 0.55) + 'px;' +
                'background:' + paleta[i % paleta.length] + ';' +
                'border-radius:' + (redonda ? '50%' : '2px');
            capa.appendChild(pieza);

            var x0 = ancho * (0.15 + Math.random() * 0.7);
            var y0 = alto * 0.55;
            var dx = (Math.random() - 0.5) * ancho * 0.9;
            var subida = alto * (0.25 + Math.random() * 0.4);
            var caida = alto * (0.55 + Math.random() * 0.35);
            var giro = (Math.random() - 0.5) * 900;

            animaciones.push(pieza.animate([
                { transform: 'translate(' + x0 + 'px,' + y0 + 'px) rotate(0deg)', opacity: 1 },
                { transform: 'translate(' + (x0 + dx * 0.5) + 'px,' + (y0 - subida) + 'px) rotate(' + giro * 0.5 + 'deg)', opacity: 1, offset: 0.38 },
                { transform: 'translate(' + (x0 + dx) + 'px,' + (y0 + caida) + 'px) rotate(' + giro + 'deg)', opacity: 0 }
            ], {
                duration: 1800 + Math.random() * 1400,
                delay: Math.random() * 220,
                easing: 'cubic-bezier(.2,.7,.4,1)',
                fill: 'forwards'
            }));
        }

        Promise.all(animaciones.map(function (a) { return a.finished; }))
            .catch(function () {})
            .then(function () { capa.remove(); });
    }

    document.addEventListener('momentia:musica-decision', function () {
        // La pantalla de misterio se desvanece en ~1s: el confeti acompaña la aparición de la portada
        setTimeout(lanzar, 350);
    });
})();
