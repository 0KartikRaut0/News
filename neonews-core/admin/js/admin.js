/**
 * NeoNews Core - Admin JavaScript
 *
 * @package NeoNews_Core
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initMetaBoxes();
    });

    function initMetaBoxes() {
        var $breakingCheckbox = $('input[name="_neonews_breaking_news"]');
        var $pushCheckbox = $('input[name="_neonews_push_notification"]');

        if ($breakingCheckbox.length && $pushCheckbox.length) {
            $breakingCheckbox.on('change', function() {
                if ($(this).is(':checked')) {
                    $pushCheckbox.prop('checked', true);
                }
            });
        }
    }

})(jQuery);
