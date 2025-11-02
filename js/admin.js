jQuery(document).ready(function($) {
    // Media uploader para iconos personalizados
    $('.alergenos-upload-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var inputField = button.siblings('.alergenos-icon-url');
        var previewImg = button.siblings('.alergenos-icon-preview').find('img');
        
        // Crear el frame de medios
        var frame = wp.media({
            title: 'Seleccionar o subir imagen',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false,
            library: {
                type: ['image'] // Solo imágenes
            }
        });
        
        // Cuando se selecciona una imagen
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            
            // Actualizar el campo de URL
            inputField.val(attachment.url);
            
            // Actualizar la vista previa
            previewImg.attr('src', attachment.url);
        });
        
        // Abrir el frame de medios
        frame.open();
    });
    
    // Botón para eliminar imagen
    $('.alergenos-remove-button').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var inputField = button.siblings('.alergenos-icon-url');
        var previewImg = button.siblings('.alergenos-icon-preview').find('img');
        var defaultIcon = previewImg.data('default');
        
        // Limpiar el campo
        inputField.val('');
        
        // Restaurar la vista previa al icono por defecto
        previewImg.attr('src', defaultIcon);
    });
});
