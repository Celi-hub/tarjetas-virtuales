<?php
/**
 * Renderiza un campo del formulario del paso 2 / edición.
 *
 * @param array       $campo Definición desde config/modulos.php o array dinámico
 * @param string|null $valor Valor precargado al editar
 */
function render_campo_formulario(array $campo, ?string $valor = null): void
{
    if (($campo['type'] ?? '') === 'info_aviso') {
        echo '<div class="aviso-info">';
        echo '<p>' . ($campo['texto'] ?? '') . '</p>';
        echo '</div>';
        return;
    }

    $name     = $campo['name'];
    $label    = $campo['label'] ?? $name;
    $type     = $campo['type'] ?? 'text';
    $required = !empty($campo['required']);
    $valor    = $valor ?? ($campo['default'] ?? '');

    $id_seguro = 'id_' . str_replace(['[', ']'], ['_', ''], $name);
    $id_seguro = rtrim($id_seguro, '_');

    if ($type === 'dress_code') {
        ?>
        <div class="form-group">
            <label for="selector_dress_code"><?php echo htmlspecialchars($label); ?></label>
            <select id="selector_dress_code">
                <option value="">Seleccioná una opción...</option>
                <option value="Formal - Traje, camisa, vestido largo o de cóctel.">Formal</option>
                <option value="Elegante Sport - Elegante pero relajado.">Elegante Sport</option>
                <option value="Cómodo y moderno, sin perder la presentación.">Casual Elegante</option>
                <option value="Deportivo/relajado - Look deportivo y cómodo.">Deportivo/Relajado</option>
                <option value="custom">Personalizado (Temática, color, otro...)</option>
            </select>
            <textarea name="dress_code_texto" id="dress_code_texto" rows="3"
                      placeholder="Detalles de vestimenta..."><?php echo htmlspecialchars($valor); ?></textarea>
        </div>
        <?php
        return;
    }

    if ($type === 'file_book') {
        ?>
        <div class="form-group">
            <label><?php echo htmlspecialchars($label); ?></label>
            <input type="file" name="fotos_book[]" multiple accept="image/*">
            <small class="form-help-text">Seleccioná hasta 5 fotos.</small>
        </div>
        <?php
        return;
    }

    echo '<div class="form-group" id="group_' . htmlspecialchars($id_seguro) . '">';
    echo '<label for="' . htmlspecialchars($id_seguro) . '">' . htmlspecialchars($label) . '</label>';

    $attrs = $required ? ' required' : '';
    $placeholder = isset($campo['placeholder']) ? ' placeholder="' . htmlspecialchars($campo['placeholder']) . '"' : '';

    switch ($type) {
        case 'textarea':
            $rows = (int)($campo['rows'] ?? 3);
            echo '<textarea name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id_seguro) . '" rows="' . $rows . '"' . $attrs . $placeholder . '>'
                . htmlspecialchars($valor) . '</textarea>';
            break;
        case 'select':
            echo '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id_seguro) . '"' . $attrs . '>';
            if (!empty($campo['placeholder'])) {
                echo '<option value="">' . htmlspecialchars($campo['placeholder']) . '</option>';
            }
            if (!empty($campo['options']) && is_array($campo['options'])) {
                foreach ($campo['options'] as $opt_val => $opt_label) {
                    $selected = ($valor == $opt_val) ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($opt_val) . '"' . $selected . '>' . htmlspecialchars($opt_label) . '</option>';
                }
            }
            echo '</select>';
            break;
        default:
            echo '<input type="' . htmlspecialchars($type) . '" name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id_seguro) . '"'
                . ' value="' . htmlspecialchars($valor) . '"' . $placeholder . $attrs . '>';
    }

    if (!empty($campo['help'])) {
        echo '<small class="form-help-text">' . $campo['help'] . '</small>';
    }

    echo '</div>';
}

/**
 * Trae hasta 3 frases sugeridas para un tipo de evento.
 */
function obtener_frases_sugeridas(PDO $pdo, int $tipo_evento_id): array
{
    try {
        $stmt = $pdo->prepare('
            SELECT id_frase, texto_frase
            FROM frases_sugeridas
            WHERE tipo_evento_id = ?
            ORDER BY id_frase
            LIMIT 3
        ');
        $stmt->execute([$tipo_evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error al obtener frases sugeridas: ' . $e->getMessage());
        return [];
    }
}

/**
 * Determina si la frase guardada es "personalizada" (no está entre las sugeridas).
 */
function frase_es_custom(string $frase_guardada, array $frases_sugeridas): bool
{
    if ($frase_guardada === '') {
        return false;
    }
    $textos = array_column($frases_sugeridas, 'texto_frase');
    return !in_array($frase_guardada, $textos, true);
}

/**
 * Arma la lista de módulos "a la carta" para la pantalla de edición:
 * cada uno con su estado actual (activo/inactivo) y sus campos de datos,
 * para renderizar checkbox + fieldset condicional.
 *
 * mod_rsvp_whatsapp queda afuera porque es incluido_base / no seleccionable.
 * mod_rsvp_interno SÍ se incluye: es elegible y reemplaza al de WhatsApp.
 *
 * @param array $modulos_permitidos Resultado de modulos_para_evento()
 * @param array $tarjeta            Fila de la tabla tarjetas
 */
function secciones_modulos_editables(array $modulos_permitidos, array $tarjeta): array
{
    $secciones = [];
    foreach ($modulos_permitidos as $columna => $modulo) {
        if ($columna === 'mod_rsvp_whatsapp') {
            continue;
        }
        $secciones[] = [
            'columna'     => $columna,
            'titulo'      => $modulo['titulo'],
            'descripcion' => $modulo['descripcion'],
            'campos'      => $modulo['campos'] ?? [],
            'activo'      => !empty($tarjeta[$columna]),
        ];
    }
    return $secciones;
}