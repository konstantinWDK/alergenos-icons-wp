<?php
/*
Plugin Name: Alergenos Icons
Description: Permite seleccionar alérgenos en los productos de WooCommerce y mostrarlos en el front-end mediante un shortcode. Los iconos se gestionan internamente y se cargan desde la carpeta de imágenes del plugin.
Version: 1.0
Author: Konstantin WDK -
Author URI: https://webdesignerk.com
Text Domain: alergenos-icons
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita accesos directos.
}

class Woo_Allergen_Selector {

    /**
     * Array con la definición de alérgenos y sus imágenes.
     */
    private $allergens;

    public function __construct() {
        // Definir alérgenos: clave interna => label y nombre de la imagen.
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

        // Agrega el metabox al editar productos (WooCommerce).
        add_action( 'add_meta_boxes', array( $this, 'add_allergen_meta_box' ) );
        // Guarda la selección de alérgenos.
        add_action( 'save_post', array( $this, 'save_allergen_meta_box' ) );
        
        // Shortcode para mostrar alérgenos: [mostrar_alergenos]
        add_shortcode( 'mostrar_alergenos', array( $this, 'display_allergens_shortcode' ) );

        // Encolar estilos para el área de administración y frontend.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
    }

    /**
     * Encola los estilos para la administración (metabox de alérgenos).
     */
    public function enqueue_admin_styles( $hook ) {
        global $post;
        if ( ( 'post.php' !== $hook && 'post-new.php' !== $hook ) || ( isset( $post ) && 'product' !== $post->post_type ) ) {
            return;
        }
        wp_enqueue_style(
            'allergen-admin-style',
            plugin_dir_url( __FILE__ ) . 'css/allergen-admin.css',
            array(),
            '1.0'
        );
    }

    /**
     * Encola los estilos para el frontend (muestra los iconos de alérgenos).
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'allergen-frontend-style',
            plugin_dir_url( __FILE__ ) . 'css/allergen-frontend.css',
            array(),
            '1.0'
        );
    }

    /**
     * Agrega el metabox en la edición del producto.
     */
    public function add_allergen_meta_box() {
        add_meta_box(
            'allergen_meta_box',
            'Seleccionar Alérgenos',
            array( $this, 'render_allergen_meta_box' ),
            'product',
            'side',
            'default'
        );
    }

    /**
     * Renderiza la interfaz del metabox para la selección de alérgenos.
     */
    public function render_allergen_meta_box( $post ) {
        wp_nonce_field( 'save_allergens', 'allergen_nonce' );
        $selected_allergens = get_post_meta( $post->ID, '_selected_allergens', true );
        if ( ! is_array( $selected_allergens ) ) {
            $selected_allergens = array();
        }

        echo '<div class="allergen-selector">';
        foreach ( $this->allergens as $key => $data ) {
            $checked = in_array( $key, $selected_allergens ) ? 'checked' : '';
            echo '<label class="allergen-label">';
                echo '<input type="checkbox" name="selected_allergens[]" value="' . esc_attr( $key ) . '" ' . $checked . ' />';
                echo '<img src="' . esc_url( plugin_dir_url( __FILE__ ) . 'img/' . $data['img'] ) . '" alt="' . esc_attr( $data['label'] ) . '" width="24" height="24" />';
                echo '<span>' . esc_html( $data['label'] ) . '</span>';
            echo '</label>';
        }
        echo '</div>';
    }

    /**
     * Guarda la selección de alérgenos al guardar el producto.
     */
    public function save_allergen_meta_box( $post_id ) {
        if ( ! isset( $_POST['allergen_nonce'] ) || ! wp_verify_nonce( $_POST['allergen_nonce'], 'save_allergens' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'product' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_product', $post_id ) ) {
                return;
            }
        }
        $selected_allergens = isset( $_POST['selected_allergens'] ) ? array_map( 'sanitize_text_field', $_POST['selected_allergens'] ) : array();
        update_post_meta( $post_id, '_selected_allergens', $selected_allergens );
    }

    /**
     * Shortcode para mostrar en el frontend los alérgenos seleccionados.
     * Uso: [mostrar_alergenos]
     */
    public function display_allergens_shortcode( $atts ) {
        global $post;
        // Primero intentamos obtener el objeto global.
        if ( ! $post || 'product' !== get_post_type( $post ) ) {
            // Si no es un producto, usamos el objeto de la consulta actual.
            $post = get_queried_object();
        }
        // Verificamos nuevamente que el objeto sea un producto.
        if ( ! $post || 'product' !== get_post_type( $post ) ) {
            return '<p>No hay alérgenos seleccionados para este producto.</p>';
        }
        
        $selected_allergens = get_post_meta( $post->ID, '_selected_allergens', true );
        if ( empty( $selected_allergens ) || ! is_array( $selected_allergens ) ) {
            return '<p>No hay alérgenos seleccionados para este producto.</p>';
        }

        $output = '<div class="allergen-display">';
        foreach ( $selected_allergens as $allergen_key ) {
            if ( isset( $this->allergens[ $allergen_key ] ) ) {
                $data    = $this->allergens[ $allergen_key ];
                $img_src = plugin_dir_url( __FILE__ ) . 'img/' . $data['img'];
                // Sustitución de la etiqueta <p> por un tooltip en span
                $output .= '<div class="allergen-item">';
                    $output .= '<img src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $data['label'] ) . '" width="26" height="26" />';
                    $output .= '<span class="allergen-tooltip">' . esc_html( $data['label'] ) . '</span>';
                $output .= '</div>';
            }
        }
        $output .= '</div>';
        return $output;
    }
}

// Inicializamos el plugin.
new Woo_Allergen_Selector();