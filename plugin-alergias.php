<?php
/*
Plugin Name: Alergenos Icons
Description: Permite seleccionar alérgenos en productos WooCommerce y posts normales. Muestra los alérgenos mediante el shortcode [mostrar_alergenos]. Compatible con Elementor, Divi y otros editores. Los iconos se gestionan internamente y se cargan desde la carpeta de imágenes del plugin.
Version: 1.3
Author: Konstantin WDK -
Author URI: https://webdesignerk.com
Text Domain: alergenos-icons
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita accesos directos.
}

class Woo_Allergen_Selector {

    private $allergens;
    private $post_types;
    private $options;

    public function __construct() {
        $this->allergens = array(
            'gluten'          => array( 'label' => 'Cereales con gluten',          'img' => 'alg-gluten.png' ),
            'huevos'          => array( 'label' => 'Huevos',                        'img' => 'alg-huevos.png' ),
            'pescado'         => array( 'label' => 'Pescado',                       'img' => 'alg-pescado.png' ),
            'soja'            => array( 'label' => 'Soja',                          'img' => 'alg-soja.png' ),
            'lacteos'         => array( 'label' => 'Lácteos',                       'img' => 'alg-lacteos.png' ),
            'cacahuetes'      => array( 'label' => 'Cacahuetes',                    'img' => 'alg-cacahuetes.png' ),
            'frutos_cascara'  => array( 'label' => 'Frutos de cáscara',             'img' => 'alg-frutos-cascara.png' ),
            'apio'            => array( 'label' => 'Apio',                          'img' => 'alg-apio.png' ),
            'mostaza'         => array( 'label' => 'Mostaza',                       'img' => 'alg-mostaza.png' ),
            'sesamo'          => array( 'label' => 'Granos de sésamo',              'img' => 'alg-sesamo.png' ),
            'crustaceos'      => array( 'label' => 'Crustáceos',                    'img' => 'alg-crustaceos.png' ),
            'azufre_sulfitos' => array( 'label' => 'Dióxido de azufre y sulfitos',  'img' => 'alg-azufre-sulfitos.png' ),
            'altramuces'      => array( 'label' => 'Altramuces',                    'img' => 'alg-altramuces.png' ),
            'moluscos'        => array( 'label' => 'Moluscos',                      'img' => 'alg-moluscos.png' ),
        );

        // Tipos de post compatibles
        $this->post_types = array('post', 'page');
        if (class_exists('WooCommerce')) {
            $this->post_types[] = 'product';
        }

        // Cargar opciones
        $this->options = get_option('alergenos_icons_options', array(
            'hide_ingredients_title' => false,
            'hide_traces_title' => false,
            'custom_icons' => array()
        ));

        add_action( 'add_meta_boxes', array( $this, 'add_allergen_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_allergen_meta_box' ) );

        add_shortcode( 'mostrar_alergenos', array( $this, 'display_allergens_shortcode' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );

        // Enlace de ajustes en el listado de plugins
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );

        // Registrar página de ajustes
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function enqueue_admin_styles( $hook ) {
        global $post;
        
        // Cargar estilos para páginas de posts
        if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && $post && in_array( $post->post_type, $this->post_types ) ) {
            wp_enqueue_style(
                'allergen-admin-style',
                plugin_dir_url( __FILE__ ) . 'css/allergen-admin.css',
                array(),
                '1.0'
            );
        }
        
        // Cargar estilos y scripts para la página de ajustes
        if ( 'settings_page_alergenos-icons-settings' === $hook ) {
            wp_enqueue_style(
                'allergen-admin-style',
                plugin_dir_url( __FILE__ ) . 'css/allergen-admin.css',
                array(),
                '1.0'
            );
            
            // Enqueue WordPress media uploader scripts
            wp_enqueue_media();
            wp_enqueue_script(
                'alergenos-icons-admin',
                plugin_dir_url( __FILE__ ) . 'js/admin.js',
                array('jquery'),
                '1.0',
                true
            );
        }
    }

    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'allergen-frontend-style',
            plugin_dir_url( __FILE__ ) . 'css/allergen-frontend.css',
            array(),
            '1.0'
        );
    }

    public function add_allergen_meta_box() {
        foreach ( $this->post_types as $post_type ) {
            add_meta_box(
                'allergen_meta_box_ingredients',
                'Seleccionar Alérgenos (Ingredientes)',
                array( $this, 'render_allergen_meta_box_ingredients' ),
                $post_type,
                'side',
                'default'
            );
            add_meta_box(
                'allergen_meta_box_traces',
                'Seleccionar Alérgenos (Trazas)',
                array( $this, 'render_allergen_meta_box_traces' ),
                $post_type,
                'side',
                'default'
            );
        }
    }

    public function render_allergen_meta_box_ingredients( $post ) {
        wp_nonce_field( 'save_allergens', 'allergen_nonce' );

        $selected_allergens_ingredients = get_post_meta( $post->ID, '_selected_allergens_ingredients', true );
        if ( ! is_array( $selected_allergens_ingredients ) ) {
            $selected_allergens_ingredients = array();
        }

        // NUEVO: Campo texto ingredientes
        $text_ingredients = get_post_meta( $post->ID, '_allergens_text_ingredients', true );

        echo '<div class="allergen-selector">';
        echo '<p><strong>Ingredientes:</strong></p>';
        foreach ( $this->allergens as $key => $data ) {
            $checked = in_array( $key, $selected_allergens_ingredients ) ? 'checked' : '';
            echo '<label class="allergen-label">';
                echo '<input type="checkbox" name="selected_allergens_ingredients[]" value="' . esc_attr( $key ) . '" ' . $checked . ' />';
                echo '<img src="' . esc_url( $this->get_allergen_icon_url( $key ) ) . '" alt="' . esc_attr( $data['label'] ) . '" width="24" height="24" />';
                echo '<span>' . esc_html( $data['label'] ) . '</span>';
            echo '</label>';
        }
        echo '</div>';

        echo '<p><label for="allergens_text_ingredients"><strong>Texto adicional (Ingredientes):</strong></label></p>';
        echo '<textarea style="width:100%;" rows="3" id="allergens_text_ingredients" name="allergens_text_ingredients">' . esc_textarea( $text_ingredients ) . '</textarea>';
    }

    public function render_allergen_meta_box_traces( $post ) {
        wp_nonce_field( 'save_allergens', 'allergen_nonce' );

        $selected_allergens_traces = get_post_meta( $post->ID, '_selected_allergens_traces', true );
        if ( ! is_array( $selected_allergens_traces ) ) {
            $selected_allergens_traces = array();
        }

        // NUEVO: Campo texto trazas
        $text_traces = get_post_meta( $post->ID, '_allergens_text_traces', true );

        echo '<div class="allergen-selector">';
        echo '<p><strong>Trazas:</strong></p>';
        foreach ( $this->allergens as $key => $data ) {
            $checked = in_array( $key, $selected_allergens_traces ) ? 'checked' : '';
            echo '<label class="allergen-label">';
                echo '<input type="checkbox" name="selected_allergens_traces[]" value="' . esc_attr( $key ) . '" ' . $checked . ' />';
                echo '<img src="' . esc_url( $this->get_allergen_icon_url( $key ) ) . '" alt="' . esc_attr( $data['label'] ) . '" width="24" height="24" />';
                echo '<span>' . esc_html( $data['label'] ) . '</span>';
            echo '</label>';
        }
        echo '</div>';

        echo '<p><label for="allergens_text_traces"><strong>Texto adicional (Trazas):</strong></label></p>';
        echo '<textarea style="width:100%;" rows="3" id="allergens_text_traces" name="allergens_text_traces">' . esc_textarea( $text_traces ) . '</textarea>';
    }

    public function save_allergen_meta_box( $post_id ) {
        if ( ! isset( $_POST['allergen_nonce'] ) || ! wp_verify_nonce( $_POST['allergen_nonce'], 'save_allergens' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $this->post_types ) ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        update_post_meta( $post_id, '_selected_allergens_ingredients', isset( $_POST['selected_allergens_ingredients'] ) ? array_map( 'sanitize_text_field', $_POST['selected_allergens_ingredients'] ) : array() );
        update_post_meta( $post_id, '_selected_allergens_traces', isset( $_POST['selected_allergens_traces'] ) ? array_map( 'sanitize_text_field', $_POST['selected_allergens_traces'] ) : array() );

        // Guardar los textos adicionales
        if ( isset( $_POST['allergens_text_ingredients'] ) ) {
            update_post_meta( $post_id, '_allergens_text_ingredients', sanitize_textarea_field( $_POST['allergens_text_ingredients'] ) );
        }
        if ( isset( $_POST['allergens_text_traces'] ) ) {
            update_post_meta( $post_id, '_allergens_text_traces', sanitize_textarea_field( $_POST['allergens_text_traces'] ) );
        }
    }

    public function display_allergens_shortcode( $atts ) {
        global $post;

        if ( ! $post || ! in_array( get_post_type( $post ), $this->post_types ) ) {
            $post = get_queried_object();
        }

        if ( ! $post || ! in_array( get_post_type( $post ), $this->post_types ) ) {
            return '<p>No hay alérgenos seleccionados para este contenido.</p>';
        }

        $selected_allergens_ingredients = get_post_meta( $post->ID, '_selected_allergens_ingredients', true );
        $selected_allergens_traces = get_post_meta( $post->ID, '_selected_allergens_traces', true );

        // NUEVO: Obtener textos adicionales
        $text_ingredients = get_post_meta( $post->ID, '_allergens_text_ingredients', true );
        $text_traces = get_post_meta( $post->ID, '_allergens_text_traces', true );

        // Verificar si hay contenido para mostrar
        $has_ingredients = ! empty( $selected_allergens_ingredients ) && is_array( $selected_allergens_ingredients );
        $has_traces = ! empty( $selected_allergens_traces ) && is_array( $selected_allergens_traces );
        $has_text_ingredients = ! empty( $text_ingredients );
        $has_text_traces = ! empty( $text_traces );

        // Si no hay nada que mostrar, retornar mensaje
        if ( ! $has_ingredients && ! $has_traces && ! $has_text_ingredients && ! $has_text_traces ) {
            return '<p>No hay alérgenos seleccionados para este contenido.</p>';
        }

        $output = '<div class="allergen-container allergen-display">';

        // Ingredientes - Solo mostrar si hay contenido
        if ( $has_ingredients || $has_text_ingredients ) {
            // Solo mostrar título si hay ingredientes y no está oculto
            if ( $has_ingredients && empty( $this->options['hide_ingredients_title'] ) ) {
                $output .= '<h4 style="background-color:#f0f0f0;border-radius:5px;padding:5px 10px;font-size:0.6em;font-weight:bold;margin: 0px 0px;">Ingredientes:</h4>';
            }
            
            if ( $has_ingredients ) {
                $output .= '<div class="allergen-section allergen-ingredients">';
                foreach ( $selected_allergens_ingredients as $allergen_key ) {
                    if ( isset( $this->allergens[ $allergen_key ] ) ) {
                        $data    = $this->allergens[ $allergen_key ];
                        $img_src = $this->get_allergen_icon_url( $allergen_key );
                        $output .= '<div class="allergen-item">';
                            $output .= '<img class="allergen-image" src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $data['label'] ) . '"  />';
                            $output .= '<span class="allergen-text allergen-tooltip">' . esc_html( $data['label'] ) . '</span>';
                        $output .= '</div>';
                    }
                }
                $output .= '</div>';
            }
            
            // Mostrar texto adicional debajo de ingredientes si existe
            if ( $has_text_ingredients ) {
                $output .= '<div class="allergen-texto-adicional allergen-text-ingredients" style=" ">' . wpautop( esc_html( $text_ingredients ) ) . '</div>';
            }
        }

        // Trazas - Solo mostrar si hay contenido
        if ( $has_traces || $has_text_traces ) {
            // Solo mostrar título si hay trazas y no está oculto
            if ( $has_traces && empty( $this->options['hide_traces_title'] ) ) {
                $output .= '<h4 style="background-color:#f0f0f0;border-radius:5px;padding:5px 10px; font-size:0.6em;font-weight:bold;margin: 0px 0px;">Trazas:</h4>';
            }
            
            if ( $has_traces ) {
                $output .= '<div class="allergen-section allergen-traces">';
                foreach ( $selected_allergens_traces as $allergen_key ) {
                    if ( isset( $this->allergens[ $allergen_key ] ) ) {
                        $data    = $this->allergens[ $allergen_key ];
                        $img_src = $this->get_allergen_icon_url( $allergen_key );
                        $output .= '<div class="allergen-item">';
                            $output .= '<img class="allergen-image allergen-trace-icon" src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $data['label'] ) . '"  />';
                            $output .= '<span class="allergen-text allergen-tooltip">' . esc_html( $data['label'] ) . '</span>';
                        $output .= '</div>';
                    }
                }
                $output .= '</div>';
            }
            
            // Mostrar texto adicional debajo de trazas si existe
            if ( $has_text_traces ) {
                $output .= '<div class="allergen-texto-adicional allergen-text-traces" style=" ">' . wpautop( esc_html( $text_traces ) ) . '</div>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    // Función para obtener la URL del icono (por defecto o personalizado)
    private function get_allergen_icon_url( $allergen_key ) {
        // Verificar si hay un icono personalizado
        if ( isset( $this->options['custom_icons'][ $allergen_key ] ) && ! empty( $this->options['custom_icons'][ $allergen_key ] ) ) {
            return $this->options['custom_icons'][ $allergen_key ];
        }
        
        // Usar icono por defecto
        return plugin_dir_url( __FILE__ ) . 'img/' . $this->allergens[ $allergen_key ]['img'];
    }

    // Función para agregar enlace de ajustes en el listado de plugins
    public function add_settings_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=alergenos-icons-settings' ) . '">Ajustes</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    // Función para agregar menú de administración
    public function add_admin_menu() {
        add_options_page(
            'Configuración de Alérgenos Icons',
            'Alérgenos Icons',
            'manage_options',
            'alergenos-icons-settings',
            array( $this, 'display_settings_page' )
        );
    }

    // Registrar configuraciones
    public function register_settings() {
        register_setting( 'alergenos_icons_options', 'alergenos_icons_options', array( $this, 'sanitize_options' ) );

        add_settings_section(
            'alergenos_icons_display_section',
            'Configuración de Visualización',
            array( $this, 'display_section_callback' ),
            'alergenos-icons-settings'
        );

        add_settings_field(
            'hide_ingredients_title',
            'Ocultar título "Ingredientes"',
            array( $this, 'hide_ingredients_title_callback' ),
            'alergenos-icons-settings',
            'alergenos_icons_display_section'
        );

        add_settings_field(
            'hide_traces_title',
            'Ocultar título "Trazas"',
            array( $this, 'hide_traces_title_callback' ),
            'alergenos-icons-settings',
            'alergenos_icons_display_section'
        );

        add_settings_section(
            'alergenos_icons_custom_section',
            'Iconos Personalizados',
            array( $this, 'custom_icons_section_callback' ),
            'alergenos-icons-settings'
        );

        foreach ( $this->allergens as $key => $data ) {
            add_settings_field(
                'custom_icon_' . $key,
                $data['label'],
                array( $this, 'custom_icon_callback' ),
                'alergenos-icons-settings',
                'alergenos_icons_custom_section',
                array( 'allergen_key' => $key, 'label' => $data['label'] )
            );
        }
    }

    // Callback para la sección de visualización
    public function display_section_callback() {
        echo '<p>Configura cómo se muestran los alérgenos en el frontend.</p>';
    }

    // Callback para ocultar título de ingredientes
    public function hide_ingredients_title_callback() {
        $value = isset( $this->options['hide_ingredients_title'] ) ? $this->options['hide_ingredients_title'] : false;
        echo '<input type="checkbox" name="alergenos_icons_options[hide_ingredients_title]" value="1" ' . checked( 1, $value, false ) . ' />';
        echo '<p class="description">Si se marca, no se mostrará el título "Ingredientes:" en el frontend.</p>';
    }

    // Callback para ocultar título de trazas
    public function hide_traces_title_callback() {
        $value = isset( $this->options['hide_traces_title'] ) ? $this->options['hide_traces_title'] : false;
        echo '<input type="checkbox" name="alergenos_icons_options[hide_traces_title]" value="1" ' . checked( 1, $value, false ) . ' />';
        echo '<p class="description">Si se marca, no se mostrará el título "Trazas:" en el frontend.</p>';
    }

    // Callback para la sección de iconos personalizados
    public function custom_icons_section_callback() {
        echo '<p>Reemplaza los iconos por defecto con tus propias imágenes. Soporta PNG, SVG y WebP.</p>';
        echo '<p><strong>Nota:</strong> Los iconos por defecto siempre estarán disponibles como respaldo.</p>';
    }

    // Callback para cada icono personalizado
    public function custom_icon_callback( $args ) {
        $allergen_key = $args['allergen_key'];
        $current_value = isset( $this->options['custom_icons'][ $allergen_key ] ) ? $this->options['custom_icons'][ $allergen_key ] : '';
        $default_icon = plugin_dir_url( __FILE__ ) . 'img/' . $this->allergens[ $allergen_key ]['img'];
        
        echo '<div style="margin-bottom: 15px;">';
        
        // Campo de URL oculto
        echo '<input type="hidden" name="alergenos_icons_options[custom_icons][' . esc_attr( $allergen_key ) . ']" value="' . esc_url( $current_value ) . '" class="alergenos-icon-url regular-text" />';
        
        // Botones de subida y eliminación
        echo '<div style="margin-bottom: 5px;">';
        echo '<button type="button" class="button alergenos-upload-button">Seleccionar imagen</button>';
        if ( ! empty( $current_value ) ) {
            echo ' <button type="button" class="button alergenos-remove-button">Eliminar imagen</button>';
        }
        echo '</div>';
        
        // Mostrar vista previa
        echo '<div class="alergenos-icon-preview" style="margin-top: 5px;">';
        echo '<strong>Vista previa:</strong><br>';
        $preview_url = $current_value ? $current_value : $default_icon;
        echo '<img src="' . esc_url( $preview_url ) . '" alt="' . esc_attr( $args['label'] ) . '" data-default="' . esc_url( $default_icon ) . '" style="max-width: 32px; max-height: 32px; margin: 5px 0; border: 1px solid #ddd; padding: 2px;" />';
        echo '</div>';
        
        echo '<p class="description">Haz clic en "Seleccionar imagen" para subir o elegir una imagen desde la biblioteca de medios.</p>';
        
        echo '</div>';
    }

    // Sanitizar opciones
    public function sanitize_options( $input ) {
        $sanitized = array();
        
        // Sanitizar opciones booleanas
        $sanitized['hide_ingredients_title'] = isset( $input['hide_ingredients_title'] ) ? (bool) $input['hide_ingredients_title'] : false;
        $sanitized['hide_traces_title'] = isset( $input['hide_traces_title'] ) ? (bool) $input['hide_traces_title'] : false;
        
        // Sanitizar iconos personalizados
        $sanitized['custom_icons'] = array();
        if ( isset( $input['custom_icons'] ) && is_array( $input['custom_icons'] ) ) {
            foreach ( $input['custom_icons'] as $key => $url ) {
                $sanitized['custom_icons'][ $key ] = esc_url_raw( $url );
            }
        }
        
        return $sanitized;
    }

    // Función para mostrar la página de ajustes
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1>Configuración de Alérgenos Icons</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'alergenos_icons_options' );
                do_settings_sections( 'alergenos-icons-settings' );
                submit_button();
                ?>
            </form>

            <div style="background: #fff; padding: 20px; border-radius: 5px; margin-top: 20px;">
                <h2>Información del Plugin</h2>
                <p><strong>Versión:</strong> 1.3</p>
                <p><strong>Shortcode:</strong> <code>[mostrar_alergenos]</code></p>
                <p><strong>Tipos de contenido compatibles:</strong> Posts, Páginas<?php echo class_exists('WooCommerce') ? ', Productos WooCommerce' : ''; ?></p>
                
                <h3>Uso del Plugin</h3>
                <p>Para usar el plugin:</p>
                <ol>
                    <li>Edita cualquier post, página o producto</li>
                    <li>En la columna lateral encontrarás los metaboxes "Seleccionar Alérgenos"</li>
                    <li>Selecciona los alérgenos que correspondan (ingredientes y/o trazas)</li>
                    <li>Guarda los cambios</li>
                    <li>Inserta el shortcode <code>[mostrar_alergenos]</code> donde quieras mostrar los alérgenos</li>
                </ol>

                <h3>Compatibilidad</h3>
                <p>El plugin es compatible con:</p>
                <ul>
                    <li>WordPress Editor (Gutenberg)</li>
                    <li>Elementor</li>
                    <li>Divi Builder</li>
                    <li>WPBakery Page Builder</li>
                    <li>Beaver Builder</li>
                    <li>Y otros editores visuales</li>
                </ul>

                <h3>Personalización</h3>
                <p>Puedes personalizar la apariencia de los alérgenos modificando los archivos CSS en la carpeta <code>css/</code> del plugin.</p>
            </div>
        </div>
        <?php
    }
}

new Woo_Allergen_Selector();
