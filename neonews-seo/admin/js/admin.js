(function($) {
    'use strict';

    $(document).on('click', '.nn-seo-media-select', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        var field = $('#' + target);
        var frame = wp.media({
            title: 'Select image',
            button: { text: 'Use image' },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            field.val(attachment.id);
            field.closest('.nn-seo-media-field').find('.nn-seo-media-preview').html(
                '<img src="' + attachment.url + '" alt="" />'
            );
        });

        frame.open();
    });

    $(document).on('click', '.nn-seo-media-clear', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        $('#' + target).val('');
        $(this).closest('.nn-seo-media-field').find('.nn-seo-media-preview').empty();
    });
})(jQuery);
