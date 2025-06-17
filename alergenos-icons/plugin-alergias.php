<?php
/*
Plugin Name: Alergenos Icons
Description: Permite seleccionar alérgenos en los productos de WooCommerce y mostrarlos en el front-end mediante un shortcode. Los iconos se gestionan internamente y se cargan desde la carpeta de imágenes del plugin.
Version: 1.3
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
            'allergen_meta_box_ingredients',
            'Seleccionar Alérgenos (Ingredientes)',
            array( $this, 'render_allergen_meta_box_ingredients' ),
            'product',
            'side',
            'default'
        );
        add_meta_box(
            'allergen_meta_box_traces',
            'Seleccionar Alérgenos (Trazas)',
            array( $this, 'render_allergen_meta_box_traces' ),
            'product',
            'side',
            'default'
        );
    }

    /**
     * Renderiza la interfaz del metabox para la selección de alérgenos (Ingredientes).
     */
    public function render_allergen_meta_box_ingredients( $post ) {
        wp_nonce_field( 'save_allergens', 'allergen_nonce' );
        $selected_allergens_ingredients = get_post_meta( $post->ID, '_selected_allergens_ingredients', true );
        if ( ! is_array( $selected_allergens_ingredients ) ) {
            $selected_allergens_ingredients = array();
        }

        echo '<div class="allergen-selector">';
        echo '<p><strong>Ingredientes:</strong></p>';
        foreach ( $this->allergens as $key => $data ) {
            $checked = in_array( $key, $selected_allergens_ingredients ) ? 'checked' : '';
            echo '<label class="allergen-label">';
                echo '<input type="checkbox" name="selected_allergens_ingredients[]" value="' . esc_attr( $key ) . '" ' . $checked . ' />';
                echo '<img src="' . esc_url( plugin_dir_url( __FILE__ ) . 'img/' . $data['img'] ) . '" alt="' . esc_attr( $data['label'] ) . '" width="24" height="24" />';
                echo '<span>' . esc_html( $data['label'] ) . '</span>';
            echo '</label>';
        }
        echo '</div>';
    }

    /**
     * Renderiza la interfaz del metabox para la selección de alérgenos (Trazas).
     */
    public function render_allergen_meta_box_traces( $post ) {
        wp_nonce_field( 'save_allergens', 'allergen_nonce' );
        $selected_allergens_traces = get_post_meta( $post->ID, '_selected_allergens_traces', true );
        if ( ! is_array( $selected_allergens_traces ) ) {
            $selected_allergens_traces = array();
        }

        echo '<div class="allergen-selector">';
        echo '<p><strong>Trazas:</strong></p>';
        foreach ( $this->allergens as $key => $data ) {
            $checked = in_array( $key, $selected_allergens_traces ) ? 'checked' : '';
            echo '<label class="allergen-label">';
                echo '<input type="checkbox" name="selected_allergens_traces[]" value="' . esc_attr( $key ) . '" ' . $checked . ' />';
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
        update_post_meta( $post_id, '_selected_allergens_ingredients', isset( $_POST['selected_allergens_ingredients'] ) ? array_map( 'sanitize_text_field', $_POST['selected_allergens_ingredients'] ) : array() );
        update_post_meta( $post_id, '_selected_allergens_traces', isset( $_POST['selected_allergens_traces'] ) ? array_map( 'sanitize_text_field', $_POST['selected_allergens_traces'] ) : array() );
    }

    /**
     * Shortcode para mostrar en el frontend los alérgenos seleccionados.
     * Uso: [mostrar_alergenos]
     */
    public function display_allergens_shortcode( $atts ) {
        global $post;

		// Check if $post is already populated and is a product
		if ( ! $post || 'product' !== get_post_type( $post ) ) {
			// If not, try to get the current queried object
			$post = get_queried_object();
		}

		// Double check if we now have a product
		if ( ! $post || 'product' !== get_post_type( $post ) ) {
			return '<p>No hay alérgenos seleccionados para este producto.</p>';
		}
        
        $selected_allergens_ingredients = get_post_meta( $post->ID, '_selected_allergens_ingredients', true );
        $selected_allergens_traces = get_post_meta( $post->ID, '_selected_allergens_traces', true );

        $output = '<div class="allergen-display">';

        // Mostrar ingredientes
        if ( ! empty( $selected_allergens_ingredients ) && is_array( $selected_allergens_ingredients ) ) {
            $output .= '<h4>Ingredientes:</h4>';
            $output .= '<div class="allergen-ingredients">';
            foreach ( $selected_allergens_ingredients as $allergen_key ) {
                if ( isset( $this->allergens[ $allergen_key ] ) ) {
                    $data    = $this->allergens[ $allergen_key ];
                    $img_src = plugin_dir_url( __FILE__ ) . 'img/' . $data['img'];
                    $output .= '<div class="allergen-item">';
                        $output .= '<img src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $data['label'] ) . '"  />';
                        $output .= '<span class="allergen-tooltip">' . esc_html( $data['label'] ) . '</span>';
                    $output .= '</div>';
                }
            }
            $output .= '</div>';
        }

        // Mostrar trazas
        if ( ! empty( $selected_allergens_traces ) && is_array( $selected_allergens_traces ) ) {
            $output .= '<h4>Trazas:</h4>';
            $output .= '<div class="allergen-traces">';
            foreach ( $selected_allergens_traces as $allergen_key ) {
                if ( isset( $this->allergens[ $allergen_key ] ) ) {
                    $data    = $this->allergens[ $allergen_key ];
                    $img_src = plugin_dir_url( __FILE__ ) . 'img/' . $data['img'];
                    $output .= '<div class="allergen-item">';
                        $output .= '<img class="allergen-trace-icon" src="' . esc_url( $img_src ) . '" alt="' . esc_attr( $data['label'] ) . '"  />';
                        $output .= '<span class="allergen-tooltip">' . esc_html( $data['label'] ) . '</span>';
                    $output .= '</div>';
                }
            }
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }
}

// Inicializamos el plugin.
new Woo_Allergen_Selector();
