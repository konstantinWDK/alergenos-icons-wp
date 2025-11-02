# Alergenos Icons

**Alergenos Icons** es un plugin versátil para WordPress que permite a los administradores seleccionar y mostrar alérgenos en productos WooCommerce, posts y páginas. La selección se realiza desde metaboxes intuitivos en la página de edición y se visualizan mediante un shortcode en el frontend, mostrando iconos representativos para cada alérgeno.

## Características

- **Compatibilidad extendida:** Funciona con productos WooCommerce, posts y páginas normales de WordPress
- **Doble categorización:** Diferencia entre alérgenos presentes como **ingredientes** y como **trazas**
- **Metaboxes separados:** Dos metaboxes en la edición (uno para ingredientes y otro para trazas)
- **Shortcode:** Usa `[mostrar_alergenos]` para mostrar los iconos y etiquetas de los alérgenos seleccionados
- **14 alérgenos incluidos:** Cereales con gluten, huevos, pescado, soja, lácteos, cacahuetes, frutos de cáscara, apio, mostaza, sésamo, crustáceos, dióxido de azufre y sulfitos, altramuces, moluscos
- **Iconos personalizables:** Posibilidad de reemplazar los iconos por defecto con imágenes propias (PNG, SVG, WebP)
- **Textos adicionales:** Campos de texto personalizables para añadir información extra sobre ingredientes y trazas
- **Página de configuración:** Panel de ajustes completo con opciones de visualización y personalización
- **Ocultar títulos:** Opción para ocultar los títulos "Ingredientes" y "Trazas" en el frontend
- **Compatible con editores visuales:** Funciona con Gutenberg, Elementor, Divi, WPBakery, Beaver Builder y más
- **Diseño responsivo:** Los iconos se adaptan a todos los dispositivos

## Instalación

1. Descarga o clona la carpeta **Alergenos Icons**
2. Sube la carpeta completa a la ruta `wp-content/plugins/` de tu instalación de WordPress
3. Activa el plugin desde el panel de administración de WordPress
4. Accede a **Ajustes > Alérgenos Icons** para configurar las opciones (opcional)

## Uso

### Selección de alérgenos

1. Edita cualquier producto de WooCommerce, post o página
2. En la columna lateral encontrarás dos metaboxes:
   - **Seleccionar Alérgenos (Ingredientes)**: Para alérgenos presentes como ingredientes
   - **Seleccionar Alérgenos (Trazas)**: Para posibles trazas de alérgenos
3. Marca las casillas de verificación de los alérgenos correspondientes
4. Opcionalmente, añade texto adicional en los campos de texto bajo cada selector
5. Guarda los cambios

### Mostrar alérgenos en el frontend

Inserta el shortcode `[mostrar_alergenos]` donde desees mostrar los alérgenos:
- En el editor de bloques (Gutenberg): Usa el bloque "Shortcode"
- En Elementor: Usa el widget "Shortcode"
- En Divi: Usa el módulo "Shortcode"
- En el código de tu tema: `<?php echo do_shortcode('[mostrar_alergenos]'); ?>`

## Configuración

Accede a **Ajustes > Alérgenos Icons** para:

### Configuración de visualización
- **Ocultar título "Ingredientes"**: Muestra solo los iconos sin el título
- **Ocultar título "Trazas"**: Muestra solo los iconos sin el título

### Iconos personalizados
- Reemplaza cualquiera de los 14 iconos por defecto con tus propias imágenes
- Soporta formatos PNG, SVG y WebP
- Los iconos por defecto siempre están disponibles como respaldo
- Usa el botón "Seleccionar imagen" para subir o elegir desde la biblioteca de medios

## Alérgenos incluidos

El plugin incluye los 14 alérgenos principales según la normativa:

1. Cereales con gluten
2. Huevos
3. Pescado
4. Soja
5. Lácteos
6. Cacahuetes
7. Frutos de cáscara
8. Apio
9. Mostaza
10. Granos de sésamo
11. Crustáceos
12. Dióxido de azufre y sulfitos
13. Altramuces
14. Moluscos

## Personalización

### CSS personalizado
Puedes personalizar la apariencia de los alérgenos añadiendo CSS personalizado a tu tema. Clases CSS disponibles:

- `.allergen-container` - Contenedor principal
- `.allergen-section` - Sección de ingredientes o trazas
- `.allergen-ingredients` - Específico para ingredientes
- `.allergen-traces` - Específico para trazas
- `.allergen-item` - Cada alérgeno individual
- `.allergen-image` - Imagen del icono
- `.allergen-text` - Texto del alérgeno
- `.allergen-tooltip` - Tooltip con el nombre
- `.allergen-texto-adicional` - Contenedor de texto adicional
- `.allergen-text-ingredients` - Texto adicional de ingredientes
- `.allergen-text-traces` - Texto adicional de trazas

## Requisitos

- WordPress 5.0 o superior
- PHP 7.0 o superior
- WooCommerce (opcional, solo si quieres usar el plugin con productos)

## Compatibilidad

El plugin es totalmente compatible con:

- WordPress Editor (Gutenberg)
- Elementor
- Divi Builder
- WPBakery Page Builder
- Beaver Builder
- Otros editores visuales populares

## Versión actual

**Versión:** 1.3

## Autor

**Konstantin WDK**
Web: [https://webdesignerk.com](https://webdesignerk.com)

---

Con **Alergenos Icons** tendrás una herramienta completa, flexible y profesional para gestionar los alérgenos en tu sitio WordPress.
