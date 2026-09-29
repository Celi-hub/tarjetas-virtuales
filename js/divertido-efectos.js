/**
 * EFECTOS DINÁMICOS Y LÚDICOS PARA EL TEMA "DIVERTIDO" (FIESTA)
 * Momentia · Tarjetas Virtuales
 * v2 — Reemplazo de emojis por SVGs artísticos personalizados basados en el diseño del print
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Agregar clase identificadora al body para activar el fondo dinámico de lunares
    document.body.classList.add('tema-divertido');

    // 2. Crear contenedor de elementos flotantes
    const container = document.createElement('div');
    container.className = 'decoraciones-flotantes';
    document.body.appendChild(container);

    // Paleta de colores e iconografía basada en el print
    const coloresConfetti = ['#ffa200', '#f2780c', '#f87171', '#c084fc', '#22c55e', '#3b82f6', '#f472b6', '#facc15'];

    const svgTemplates = {
        envelope: `<svg viewBox="0 0 100 100" class="svg-decoracion"><path d="M10,45 L10,85 L90,85 L90,45 Z" fill="#f87171" /><rect x="25" y="15" width="50" height="45" rx="4" fill="#a4cebc" /><rect x="35" y="38" width="30" height="15" rx="2" fill="#ffffff" /><rect x="40" y="28" width="20" height="10" rx="1" fill="#ffffff" /><rect x="48" y="20" width="4" height="8" fill="#16a34a" /><circle cx="50" cy="18" r="3" fill="#fbbf24" /><polygon points="10,45 50,65 90,45" fill="none" stroke="#ef4444" stroke-width="3" /><polygon points="10,85 50,65 10,45" fill="#fca5a5" opacity="0.8" /><polygon points="90,85 50,65 90,45" fill="#fca5a5" opacity="0.8" /><polygon points="10,85 50,65 90,85" fill="#fca5a5" /></svg>`,
        
        popper: `<svg viewBox="0 0 100 100" class="svg-decoracion"><polygon points="20,80 50,50 80,80" fill="#ffa200" /><path d="M20,80 Q50,90 80,80" fill="#ffa200" /><path d="M50,50 Q45,30 35,25 Q25,20 30,10" fill="none" stroke="#22c55e" stroke-width="4" stroke-linecap="round" /><path d="M50,50 Q55,30 65,25 Q75,20 70,10" fill="none" stroke="#c084fc" stroke-width="4" stroke-linecap="round" /><path d="M50,50 Q50,25 50,5" fill="none" stroke="#f472b6" stroke-width="3" stroke-dasharray="2,2" /><circle cx="25" cy="35" r="4" fill="#3b82f6" /><circle cx="75" cy="35" r="4" fill="#facc15" /><circle cx="50" cy="20" r="3" fill="#f472b6" /></svg>`,
        
        star: `<svg viewBox="0 0 24 24" class="svg-decoracion"><path d="M12,2 Q12,12 2,12 Q12,12 12,22 Q12,12 22,12 Q12,12 12,2 Z" fill="#f59e0b" /></svg>`,
        
        crescent: `<svg viewBox="0 0 30 20" class="svg-decoracion"><path d="M5,5 Q15,-2 25,5 Q30,12 25,15 Q15,8 5,15 Q0,12 5,5 Z" fill="#ffa200" /></svg>`,
        
        curly_green: `<svg viewBox="0 0 35 25" class="svg-decoracion"><path d="M5,15 C5,5 15,5 15,15 C15,25 25,25 25,15 C25,5 35,5 35,15" fill="none" stroke="#22c55e" stroke-width="4" stroke-linecap="round" /></svg>`,
        
        curly_orange: `<svg viewBox="0 0 35 25" class="svg-decoracion"><path d="M5,15 C5,5 15,5 15,15 C15,25 25,25 25,15 C25,5 35,5 35,15" fill="none" stroke="#f2780c" stroke-width="4" stroke-linecap="round" /></svg>`,
        
        purple_ball: `<svg viewBox="0 0 30 30" class="svg-decoracion"><circle cx="15" cy="15" r="12" fill="#a855f7" /><circle cx="10" cy="10" r="2" fill="#f472b6" /><circle cx="18" cy="11" r="1.5" fill="#facc15" /><circle cx="12" cy="18" r="1.5" fill="#3b82f6" /><circle cx="19" cy="19" r="2" fill="#f472b6" /></svg>`,
        
        leaf: `<svg viewBox="0 0 25 30" class="svg-decoracion"><path d="M12.5,2 C12.5,2 22,12 22,20 C22,25 18,28 12.5,28 C7,28 3,25 3,20 C3,12 12.5,2 12.5,2 Z" fill="#22c55e" /></svg>`,
        
        triangle_red: `<svg viewBox="0 0 24 24" class="svg-decoracion"><polygon points="12,3 22,21 2,21" fill="#f87171" /></svg>`,
        
        ribbon_purple: `<svg viewBox="0 0 25 25" class="svg-decoracion"><path d="M5,5 L20,5 L15,12 L20,19 L5,19 L10,12 Z" fill="#c084fc" /></svg>`
    };

    const keys = Object.keys(svgTemplates);

    // 3. Crear guirnaldas o banderines superiores en portada/secciones
    const sections = document.querySelectorAll('.modulo-fullscreen');
    sections.forEach((sec, idx) => {
        // Añadir una guirnalda festiva sutil arriba
        const guirnalda = document.createElement('div');
        guirnalda.className = 'guirnalda-festiva';
        sec.appendChild(guirnalda);

        // Añadir arreglos decorativos dinámicos (stickers SVGs festivos) en las esquinas superiores
        if (idx % 2 === 0) {
            const stickerIzq = document.createElement('div');
            stickerIzq.className = 'decoracion-esquina-izq';
            stickerIzq.innerHTML = idx === 0 ? svgTemplates.popper : svgTemplates.envelope;
            sec.appendChild(stickerIzq);

            const stickerDer = document.createElement('div');
            stickerDer.className = 'decoracion-esquina-der';
            stickerDer.innerHTML = idx === 0 ? svgTemplates.star : svgTemplates.purple_ball;
            sec.appendChild(stickerDer);
        } else {
            const stickerIzq = document.createElement('div');
            stickerIzq.className = 'decoracion-esquina-izq';
            stickerIzq.innerHTML = svgTemplates.curly_green;
            sec.appendChild(stickerIzq);

            const stickerDer = document.createElement('div');
            stickerDer.className = 'decoracion-esquina-der';
            stickerDer.innerHTML = svgTemplates.crescent;
            sec.appendChild(stickerDer);
        }
    });

    // 4. Configuración de generador de elementos flotantes
    function crearElementoFlotante() {
        // Limitar número de elementos flotantes simultáneos para optimizar rendimiento
        if (container.children.length > 20) return;

        const el = document.createElement('div');
        el.className = 'elemento-flotante';

        // Decidir tipo de decoración aleatoriamente: forma SVG o confetti circular
        const tipoNum = Math.random();
        if (tipoNum < 0.7) {
            // Un SVG aleatorio de nuestra lista de arte divertido
            el.classList.add('decoracion-globo');
            const randomKey = keys[Math.floor(Math.random() * keys.length)];
            el.innerHTML = svgTemplates[randomKey];
        } else {
            // Confetti circular o redondeado
            el.classList.add('decoracion-confetti');
            const size = Math.floor(Math.random() * 20) + 12;
            el.style.setProperty('--size', `${size}px`);
            el.style.setProperty('--color', coloresConfetti[Math.floor(Math.random() * coloresConfetti.length)]);
            el.style.setProperty('--radius', Math.random() > 0.5 ? '50%' : '20%'); // Circulos o cuadrados redondeados
        }

        // Posición horizontal aleatoria (0% a 95%)
        el.style.left = `${Math.random() * 95}%`;

        // Duración aleatoria del ascenso (8s a 18s)
        const duracion = Math.random() * 10 + 8;
        el.style.setProperty('--duracion', `${duracion}s`);

        // Balanceo horizontal aleatorio y rotación al final del ascenso
        const balanceo = Math.floor(Math.random() * 100) - 50; // -50px a 50px
        const rotacion = Math.floor(Math.random() * 720) - 360; // -360deg a 360deg
        el.style.setProperty('--balanceo', `${balanceo}px`);
        el.style.setProperty('--rotacion', `${rotacion}deg`);

        // Retraso de inicio aleatorio
        el.style.animationDelay = `${Math.random() * 2}s`;

        // COMPORTAMIENTO LÚDICO: Hacer clic o tap para explotar con sonido visual (confetti)
        el.addEventListener('click', (e) => {
            explotarElemento(el, e.clientX, e.clientY);
        });

        // Autodestrucción al finalizar animación
        el.addEventListener('animationend', () => {
            el.remove();
        });

        container.appendChild(el);
    }

    // Generar un elemento flotante inicial y luego periódicamente
    for (let i = 0; i < 6; i++) {
        setTimeout(crearElementoFlotante, i * 1500);
    }
    setInterval(crearElementoFlotante, 3000);

    // 5. Explosión lúdica de confeti
    function explotarElemento(elemento, x, y) {
        // Detener la animación de subida y reproducir la de pop
        elemento.classList.add('efecto-pop');
        
        // Si la coordenadas x o y no son válidas, estimar el centro del elemento
        if (!x || !y) {
            const rect = elemento.getBoundingClientRect();
            x = rect.left + rect.width / 2;
            y = rect.top + rect.height / 2;
        }

        // Crear una lluvia de confeti local (12 a 18 partículas)
        const cantParticulas = Math.floor(Math.random() * 7) + 12;
        for (let i = 0; i < cantParticulas; i++) {
            crearParticulaConfetti(x, y);
        }

        // Eliminar el globo del DOM después del pop
        setTimeout(() => {
            elemento.remove();
        }, 250);
    }

    function crearParticulaConfetti(origX, origY) {
        const p = document.createElement('div');
        p.className = 'particula-confetti';

        // Estilos aleatorios de tamaño, color y forma
        const size = Math.floor(Math.random() * 10) + 6;
        p.style.setProperty('--size', `${size}px`);
        p.style.setProperty('--color', coloresConfetti[Math.floor(Math.random() * coloresConfetti.length)]);
        p.style.setProperty('--radius', Math.random() > 0.4 ? '50%' : '0%'); // Circulares o cuadrados

        // Ubicar la partícula en el origen del click/pop
        p.style.left = `${origX}px`;
        p.style.top = `${origY}px`;

        // Trayectoria de explosión (dirección y distancia aleatoria en X e Y)
        const angulo = Math.random() * Math.PI * 2;
        const distancia = Math.random() * 120 + 40; // 40px a 160px
        const tx = Math.cos(angulo) * distancia;
        const ty = Math.sin(angulo) * distancia + 40; // Añadir un efecto leve de gravedad cayendo

        p.style.setProperty('--tx', `${tx}px`);
        p.style.setProperty('--ty', `${ty}px`);
        p.style.setProperty('--rot', `${Math.floor(Math.random() * 360)}deg`);
        p.style.setProperty('--duracion', `${Math.random() * 0.4 + 0.5}s`);

        p.addEventListener('animationend', () => {
            p.remove();
        });

        document.body.appendChild(p);
    }

    // COMPORTAMIENTO LÚDICO EXTRA: Clic en el fondo de cualquier pantalla también genera una pequeña explosión de confeti festivo
    document.addEventListener('mousedown', (e) => {
        // Solo explotar si hacen clic en un elemento contenedor neutral sin triggers directos
        const tag = e.target.tagName.toLowerCase();
        const classes = e.target.className || '';
        
        if (
            tag === 'section' || 
            classes.includes('modulo-fullscreen') || 
            classes.includes('decoraciones-flotantes') ||
            tag === 'body'
        ) {
            // Lluvia pequeña de confeti (5 a 8 partículas)
            const cantParticulas = Math.floor(Math.random() * 4) + 5;
            for (let i = 0; i < cantParticulas; i++) {
                crearParticulaConfetti(e.clientX, e.clientY);
            }
        }
    });
});