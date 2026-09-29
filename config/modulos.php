<?php
/**
 * config/modulos.php — Registro central de módulos
 * Slugs alineados con la tabla tipos_evento de la BD
 */

$config_modulos = [

    'campos_base' => [
        [
            'name'     => 'nombres_portada',
            'label'    => 'Nombres del/los Agasajados',
            'type'     => 'text',
            'required' => true,
        ],
        [
            'name'     => 'fecha_evento',
            'label'    => 'Fecha del Evento',
            'type'     => 'date',
            'required' => true,
        ],
        [
            'name'     => 'hora_evento',
            'label'    => 'Hora de Inicio',
            'type'     => 'time',
            'required' => true,
        ],
    ],

    'modulos' => [

        'mod_musica' => [
            'titulo'        => 'Música de Fondo',
            'descripcion'   => 'Ambientá la tarjeta con un link de YouTube.',
            'plantilla'     => 'mod_musica.php',
            'orden'         => 5,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'egreso', '15 años', 'baby shower',
                'revelación de género', 'comunión', 'confirmación', 'aniversario',
                'despedida', 'reunión familia', 'fiestas varias',
            ],
            'campos' => [
                [
                    'name'        => 'link_musica',
                    'label'       => 'Link de YouTube',
                    'type'        => 'url',
                    'placeholder' => 'Ej: https://youtu.be/...',
                    'required'    => false,
                ],
            ],
        ],

        'mod_linea_tiempo' => [
            'titulo'        => 'Línea de tiempo',
            'descripcion'   => 'Historia del evento con fotos e hitos.',
            'plantilla'     => 'mod_linea_tiempo.php',
            'orden'         => 55,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'egreso', 'corporativo',
                '15 años', 'aniversario', 'fiestas varias',
                ],
            'campos' => [
                [
                    'name'    => 'linea_tiempo_estilo',
                    'label'   => 'Disposición de la línea',
                    'type'    => 'select',
                    'options' => [
                        'vertical'   => 'Vertical (Hacia abajo)',
                        'horizontal' => 'Horizontal (Deslizable)',
                    ],
                    'default' => 'vertical',
                    'help'    => '⚠️ Las fotos y fechas se agregan desde el Panel de Control una vez que finalices este paso.',
                ],
            ],
        ],

        'mod_book_fotos' => [
            'titulo'        => 'Book de fotos',
            'descripcion'   => 'Mostrale a tus invitados tus mejores fotos (máximo 5).',
            'plantilla'     => 'mod_book_fotos.php',
            'orden'         => 60,
            'seleccionable' => true,
            'eventos'       => [
                'boda', '15 años', 'egreso', 'aniversario', 'fiestas varias', 'bautismo',
            ],
            'campos' => [
                [
                    'name'     => 'fotos_book',
                    'label'    => 'Subí tus fotos favoritas (máximo 5)',
                    'type'     => 'file_book',
                    'required' => false,
                ],
                [
                    'name'        => 'book_texto',
                    'label'       => 'Texto de portada (opcional)',
                    'type'        => 'text',
                    'placeholder' => 'Ej: Momentos inolvidables',
                    'required'    => false,
                ],
            ],
        ],

        'mod_muro_deseos' => [
            'titulo'        => 'Muro de deseos',
            'descripcion'   => 'Espacio para mensajes de invitados antes del evento.',
            'plantilla'     => 'mod_muro_deseos.php',
            'orden'         => 80,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', 'egreso', 'corporativo',
                '15 años', 'baby shower', 'revelación de género', 'comunión', 'confirmación',
                'aniversario', 'despedida', 'reunión familia', 'fiestas varias',
                'premiación / reconocimiento',
            ],
            'campos' => [
                [
                    'name'    => 'muro_moderacion',
                    'label'   => 'Aprobación de mensajes',
                    'type'    => 'select',
                    'options' => [
                        '0' => 'Automática (Se publican apenas los envían)',
                        '1' => 'Manual (Requiere tu aprobación desde el panel)',
                    ],
                    'default' => '0',
                    'help'    => 'Podrás moderar y descargar todos los mensajes desde tu Panel de Control.',
                ],
            ],
        ],

        'mod_ubi_calen' => [
            'titulo'        => 'Ubicación GPS y agenda',
            'descripcion'   => 'Botón interactivo con el link de Google Maps del lugar y para agendar el evento en Google Calendar/Apple.',            
            'plantilla'     => 'mod_ubi_calen.php',
            'orden'         => 20,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', 'egreso', 'deportivo', 'corporativo',
                '15 años', 'baby shower', 'revelación de género', 'comunión', 'confirmación',
                'aniversario', 'inauguración', 'despedida', 'reunión familia',
                'fiestas varias', 'premiación / reconocimiento',
            ],
            'campos' => [
                [
                    'name'        => 'direccion_maps',
                    'label'       => 'Link de Google Maps',
                    'type'        => 'url',
                    'placeholder' => 'https://maps.google.com/...',
                    'required'    => true,
                ],
            ],
        ],        

        'mod_cuenta_regresiva' => [
            'titulo'        => 'Cuenta Regresiva',
            'descripcion'   => 'Reloj en tiempo real indicando los días y horas restantes.',
            'plantilla'     => 'mod_cuenta_regresiva.php',
            'orden'         => 10,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', 'egreso', 'deportivo', 'corporativo',
                '15 años', 'baby shower', 'revelación de género', 'comunión', 'confirmación',
                'aniversario', 'inauguración', 'despedida', 'reunión familia',
                'fiestas varias', 'premiación / reconocimiento',
            ],
            'campos' => [],
        ],

        'mod_dress_code' => [
            'titulo'        => 'Dress Code',
            'descripcion'   => 'Indicá a tus invitados el tipo de vestimenta sugerida.',
            'plantilla'     => 'mod_dress_code.php',
            'orden'         => 50,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'egreso', 'corporativo', '15 años',
                'comunión', 'confirmación', 'aniversario', 'inauguración',
                'despedida', 'reunión familia', 'fiestas varias',
                'premiación / reconocimiento',
            ],
            'campos' => [
                [
                    'name'     => 'dress_code_texto',
                    'label'    => 'Código de vestimenta',
                    'type'     => 'dress_code',
                    'required' => false,
                ],
            ],
        ],

        'mod_compartir_fotos' => [
            'titulo'        => 'Galería Colaborativa',
            'descripcion'   => 'Botón hacia tu Drive o Google Photos para que los invitados compartan fotos.',
            'plantilla'     => 'mod_compartir_fotos.php',
            'orden'         => 70,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', 'egreso', 'deportivo',
                '15 años', 'baby shower', 'revelación de género', 'comunión', 'confirmación',
                'aniversario', 'inauguración', 'despedida', 'reunión familia',
                'fiestas varias', 'premiación / reconocimiento',
            ],
            'campos' => [
                [
                    'name'        => 'link_galeria_externa',
                    'label'       => 'Link de Carpeta (Google Drive / Google Photos)',
                    'type'        => 'url',
                    'placeholder' => 'Ej: https://photos.app.goo.gl/...',
                    'required'    => false,
                ],
            ],
        ],

        'mod_playlist' => [
            'titulo'        => 'Playlist colaborativa',
            'descripcion'   => 'Los invitados sugieren canciones para la fiesta.',
            'plantilla'     => 'mod_playlist.php',
            'orden'         => 85,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', 'egreso', 'corporativo',
                '15 años', 'baby shower', 'aniversario', 'despedida',
                'reunión familia', 'fiestas varias',
            ],
            'campos' => [
                [
                    'name'    => 'playlist_modo',
                    'label'   => 'Modo de la Playlist',
                    'type'    => 'select',
                    'options' => [
                        'formulario' => 'Formulario en la tarjeta (Sugerencias internas)',
                        'enlace'     => 'Botón a Playlist externa (Spotify / YouTube)',
                    ],
                    'default' => 'formulario',
                    'help'    => 'El formulario guarda las canciones en tu panel. El botón lleva al invitado a tu app de música.',
                ],
                [
                    'name'        => 'playlist_url',
                    'label'       => 'Link de Spotify / YouTube',
                    'type'        => 'url',
                    'placeholder' => 'Ej: https://open.spotify.com/playlist/...',
                    'required'    => false,
                    'help'        => 'Completá este campo solo si elegiste el modo "Botón a Playlist externa".',
                ],
            ],
        ],
        
        'mod_pagar_evento' => [
            'titulo'        => 'Pago de Entrada / Inscripción',
            'descripcion'   => 'Cobra entradas, colaboraciones o inscripciones. El invitado sube su comprobante y vos lo aprobás.',
            'plantilla'     => 'mod_pagar_evento.php',
            'orden'         => 90,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', 'egreso', 'deportivo', 'corporativo',
                '15 años', 'baby shower', 'revelación de género', 'comunión', 'confirmación',
                'aniversario', 'inauguración', 'despedida', 'reunión familia',
                'fiestas varias', 'premiación / reconocimiento',
            ],
            'campos' => [
                [
                    'name'  => 'aviso_pago',
                    'type'  => 'info_aviso',
                    'texto' => '⚠️ <strong>IMPORTANTE:</strong> La configuración de tus tarifas, Alias/CBU y la aprobación de comprobantes se gestiona desde tu <strong>Panel de Control</strong> una vez que finalices la tarjeta.',
                ],
            ],
        ],

        'mod_regalos' => [
            'titulo'        => 'Datos de Regalos / CBU',
            'descripcion'   => 'Espacio para tu Alias, cuenta bancaria o frases sugerentes.',
            'plantilla'     => 'mod_regalos.php',
            'orden'         => 40,
            'seleccionable' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', '15 años', 'baby shower',
                'revelación de género', 'comunión', 'confirmación', 'aniversario',
                'fiestas varias',
            ],
            'campos' => [
                [
                    'name'        => 'datos_bancarios',
                    'label'       => 'Alias / CBU Bancario',
                    'type'        => 'text',
                    'placeholder' => 'Ej: ALIAS.MI.BODA',
                    'required'    => false,
                ],
                [
                    'name'     => 'mensaje_regalo',
                    'label'    => 'Mensaje sugerido (Opcional)',
                    'type'     => 'textarea',
                    'rows'     => 2,
                    'default'  => 'El mejor regalo es tu presencia, pero si querés ayudarnos...',
                    'required' => false,
                ],
            ],
        ],

        'mod_rsvp_whatsapp' => [
            'titulo'        => 'Confirmación por WhatsApp',
            'descripcion'   => 'Incluido en todas las tarjetas. El invitado confirma asistencia por WhatsApp.',
            'plantilla'     => 'mod_rsvp_whatsapp.php',
            'orden'         => 95,
            'incluido_base' => true,
            'seleccionable' => false,
            'visible_paso1' => true,
            'eventos'       => [],
            'campos'        => [
                [
                    'name'     => 'telefono_whatsapp',
                    'label'    => 'Número de WhatsApp para Confirmar',
                    'type'     => 'tel',
                    'required' => true,
                ],
            ],
        ],

        'mod_rsvp_interno' => [
            'titulo'        => 'Formulario RSVP en la tarjeta',
            'descripcion'   => 'El invitado confirma dentro de la invitación. Vos recibís el listado de confirmados.',
            'plantilla'     => 'mod_rsvp_interno.php',
            'orden'         => 96,
            'seleccionable' => true,
            'visible_paso1' => true,
            'eventos'       => [
                'boda', 'cumpleaños', 'bautismo', 'egreso', 'deportivo', 'corporativo',
                '15 años', 'baby shower', 'revelación de género', 'comunión', 'confirmación',
                'aniversario', 'inauguración', 'despedida', 'reunión familia',
                'fiestas varias', 'premiación / reconocimiento',
            ],
            'campos' => [],
        ],

    ],
];

// ── Funciones ──────────────────────────────────────────────────────────────

function config_modulos(): array { global $config_modulos; return $config_modulos; }
function campos_base_config(): array { global $config_modulos; return $config_modulos['campos_base']; }
function modulos_config(): array { global $config_modulos; return $config_modulos['modulos']; }
function modulos_columnas_bd(): array { return array_keys(modulos_config()); }
function modulos_incluidos_base(): array { return array_filter(modulos_config(), fn($m) => !empty($m['incluido_base'])); }
function modulos_seleccionables(): array { return array_filter(modulos_config(), fn($m) => !empty($m['seleccionable'])); }

function modulos_para_evento(string $nombreEvento): array
{
       // ─────────────────────────────────────────────────────────────────
    // RESTRICCIÓN POR EVENTO DESACTIVADA (fase de reducción de alcance)
    // Se devuelven todos los módulos seleccionables sin filtrar por
    // evento. Los arrays 'eventos' de cada módulo se conservan
    // intactos para poder reactivar este filtro más adelante.
    //
    // Para reactivar: comentar el "return modulos_seleccionables();"
    // de abajo y descomentar el bloque de filtrado original.
    // ─────────────────────────────────────────────────────────────────
    return modulos_seleccionables();

    /*
    $slug = mb_strtolower(trim($nombreEvento));
    $resultado = [];
    foreach (modulos_seleccionables() as $columna => $modulo) {
        if (in_array($slug, $modulo['eventos'], true)) {
            $resultado[$columna] = $modulo;
        }
    }
    return $resultado;
    */
}

function tarjeta_tiene_rsvp_whatsapp(array $tarjeta): bool { return !empty($tarjeta['mod_rsvp_whatsapp'] ?? 1); }
function tarjeta_tiene_rsvp_interno(array $tarjeta): bool  { return !empty($tarjeta['mod_rsvp_interno']); }
function tarjeta_muestra_rsvp_whatsapp(array $tarjeta): bool
{
    return tarjeta_tiene_rsvp_whatsapp($tarjeta) && !tarjeta_tiene_rsvp_interno($tarjeta);
}

function campos_formulario_por_tarjeta(array $tarjeta): array
{
    $secciones = [];
    foreach (modulos_config() as $columna => $modulo) {
        if (in_array($columna, ['mod_rsvp_whatsapp', 'mod_rsvp_interno'], true)) continue;
        if (empty($tarjeta[$columna]) || empty($modulo['campos'])) continue;
        $secciones[] = ['modulo' => $columna, 'legend' => $modulo['titulo'], 'campos' => $modulo['campos']];
    }
    return $secciones;
}

function campos_datos_para_tarjeta(array $tarjeta): array
{
    $campos = ['nombres_portada', 'fecha_evento', 'hora_evento'];
    if (tarjeta_muestra_rsvp_whatsapp($tarjeta)) $campos[] = 'telefono_whatsapp';
    foreach (modulos_config() as $columna => $modulo) {
        if ($columna === 'mod_rsvp_whatsapp') continue;
        if (empty($tarjeta[$columna])) continue;
        foreach ($modulo['campos'] as $campo) {
            if (in_array(($campo['type'] ?? ''), ['file_book', 'info_aviso'], true)) continue;
            $campos[] = $campo['name'];
        }
    }
    return array_values(array_unique($campos));
}

function plantillas_activas(array $tarjeta): array
{
    $activas = [];
    foreach (modulos_config() as $columna => $modulo) {
        if (empty($modulo['plantilla'])) continue;
        if ($columna === 'mod_rsvp_whatsapp') {
            if (!tarjeta_muestra_rsvp_whatsapp($tarjeta)) continue;
        } elseif (empty($tarjeta[$columna])) {
            continue;
        }
        $activas[] = ['columna' => $columna, 'archivo' => $modulo['plantilla'], 'orden' => $modulo['orden'] ?? 99];
    }
    usort($activas, fn($a, $b) => $a['orden'] <=> $b['orden']);
    return $activas;
}

return $config_modulos;