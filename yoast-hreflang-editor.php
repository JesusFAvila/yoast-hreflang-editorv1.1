<?php
/**
 * Plugin Name: Yoast Hreflang Editor
 * Description: Agrega una opción a Yoast SEO para editar manualmente los atributos hreflang. Compatible con otros plugins de SEO y traductores automáticos.
 * Version: 1.1
 * Author: SNS MARKETING SEO
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.2
 */

// Evitar acceso directo
if (!defined('ABSPATH')) exit;

// Verificar si Yoast SEO está activo para evitar conflictos
if (!defined('WPSEO_VERSION')) {
    return;
}

// Agregar un metabox en la parte superior derecha del editor de WordPress
function yoast_hreflang_add_metabox() {
    add_meta_box('yoast_hreflang_editor', 'Configuración Hreflang', 'yoast_hreflang_metabox_callback', null, 'side', 'high');
}
add_action('add_meta_boxes', 'yoast_hreflang_add_metabox');

// Mostrar el campo personalizado en el metabox
function yoast_hreflang_metabox_callback($post) {
    $hreflang_values = get_post_meta($post->ID, '_yoast_hreflang_values', true);
    ?>
    <div>
        <label for="yoast_hreflang"><strong>Hreflang personalizado</strong></label>
        <p>Introduce los valores de hreflang en formato JSON, por ejemplo:</p>
        <code>{"es":"https://ejemplo.com/es/", "en":"https://ejemplo.com/en/"}</code>
        <textarea name="yoast_hreflang" id="yoast_hreflang" class="large-text code" rows="5"><?php echo esc_textarea($hreflang_values); ?></textarea>
    </div>
    <?php
}

// Guardar los valores al actualizar el post
function yoast_hreflang_save_post($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (array_key_exists('yoast_hreflang', $_POST)) {
        update_post_meta($post_id, '_yoast_hreflang_values', sanitize_text_field($_POST['yoast_hreflang']));
    }
}
add_action('save_post', 'yoast_hreflang_save_post');

// Insertar los hreflang en la cabecera del sitio y evitar conflictos con otros plugins de SEO
function yoast_hreflang_insert_head() {
    if (is_singular()) {
        $hreflang_values = get_post_meta(get_the_ID(), '_yoast_hreflang_values', true);
        if (!empty($hreflang_values)) {
            $hreflang_data = json_decode($hreflang_values, true);
            if (is_array($hreflang_data)) {
                foreach ($hreflang_data as $lang => $url) {
                    echo '<link rel="alternate" hreflang="'.esc_attr($lang).'" href="'.esc_url($url).'" />' . "\n";
                }
            }
        }
    }
}
add_action('wp_head', 'yoast_hreflang_insert_head', 1);
