(function($) {
    'use strict';

    $(document).on('click', '.nn-membership-media-select', function(e) {
        e.preventDefault();

        var target = $(this).data('target');
        var field = $('#' + target);
        var frame = wp.media({
            title: 'Select badge image',
            button: { text: 'Use image' },
            library: { type: 'image' },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            field.val(attachment.id);
            field.closest('.nn-membership-media-field').find('.nn-membership-media-preview').html(
                '<img src="' + attachment.url + '" alt="" />'
            );
        });

        frame.open();
    });

    $(document).on('click', '.nn-membership-media-clear', function(e) {
        e.preventDefault();

        var target = $(this).data('target');
        $('#' + target).val('');
        $(this).closest('.nn-membership-media-field').find('.nn-membership-media-preview').empty();
    });
})(jQuery);
