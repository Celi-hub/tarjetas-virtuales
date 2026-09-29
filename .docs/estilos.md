# Sistema de Estilos Visuales (Temas)

Cada tarjeta elige un estilo (`tarjetas.estilo_visual_id` -> `estilos_visuales.nombre`). El motor
(`visualizar_tarjeta.php`) carga **`css/base_tarjeta.css` + `css/{slug}.css`** y pone `class="tema-{slug}"` en `<body>`.

## Reparto de responsabilidades
- **`css/base_tarjeta.css`**: reset, estructura, librería de componentes (botones, campos, superficies, estados) y el
  estilo de **todos los módulos**. No tiene colores/fuentes fijos: todo sale de **tokens** (`:root`, sección 0).
- **`css/{estilo}.css`**: redefine los tokens y agrega decoración propia (portada, fondos, ornamentos). Un tema nuevo son ~150-250 líneas.
- **Los `mod_*.php` no llevan `<style>` ni `style=""`**: si un módulo necesita estilo, se agrega a la base con tokens.

## Estilos disponibles
| `estilos_visuales.nombre` | Archivo | Carácter |
|---|---|---|
| elegante | `elegante.css` | Crema y dorado, serif clásica |
| divertido | `divertido.css` | Pop "sticker": tinta índigo, sombras duras, banderines. Confeti (`js/confeti.js`) |
| infantil | `infantil.css` | Cielo, nubes, pastel, botones con volumen. Confeti |
| romántico | `romantico.css` | Rosa empolvado, caligrafía, portada en arco, pétalos |
| vintage | `vintage.css` | Papel con grano, sepia, dobles filetes, tipografía de máquina |
| minimalista | `minimalista.css` | Blanco/negro, tipografía grande, un acento |

**Slug**: `visualizar_tarjeta.php` normaliza el nombre de BD (sin tildes: `romántico` -> `romantico`) y solo acepta estilos con
hoja en `css/`; si no existe, cae a `elegante`. Los estilos con confeti se listan en `$estilos_con_confeti`.

## Tokens principales
Acento `--gold/--gold-light/--gold-dark/--gold-bg/--gold-border/--on-accent` · superficies `--bg-principal/--bg-secundario/--text-*` ·
tipografía `--fuente-titulos/--fuente-textos/--titulo-estilo/--titulo-peso` · forma `--radius/--radius-card/--borde-ancho` ·
tarjeta `--surface-bg/--surface-border/--surface-shadow/--surface-pad` · campos `--field-*` · botones `--btn-*` ·
portada `--marco-*` · luz de fondo `--glow-color/--glow-size` · confeti `--confeti-colores`.
Ver la sección 0 de `base_tarjeta.css` para el listado y valores por defecto.

## Cómo crear un estilo nuevo
1. `INSERT INTO estilos_visuales (nombre) VALUES ('nombre');`
2. Crear `css/nombre.css` (slug sin tildes): `@import` de fuentes, tokens en `:root`, decoración con prefijo `.tema-nombre` si necesita más especificidad que la base.
3. Probar en móvil (390px) y escritorio: portada, todos los módulos, formularios y estados de éxito.

## Convenciones
- Componentes con `<input type=radio>` (Asistiré/No asistiré) usan el radio **oculto visualmente**, no `display:none`, para conservar el foco de teclado.
- `prefers-reduced-motion` se respeta en la base; los temas que animan deben desactivarlo en su propio bloque.
- Nunca reasignar `$t` (diccionario de textos) dentro de un módulo: usar variables con otro nombre.
