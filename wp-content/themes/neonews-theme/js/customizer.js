/**
 * NeoNews Theme - Customizer Preview
 *
 * @package NeoNews
 */

(function($) {
    'use strict';

    wp.customize('blogname', function(value) {
        value.bind(function(to) {
            $('.nn-logo-text').text(to);
        });
    });

    wp.customize('blogdescription', function(value) {
        value.bind(function(to) {
            $('.site-description').text(to);
        });
    });

    wp.customize('header_textcolor', function(value) {
        value.bind(function(to) {
            if ('blank' === to) {
                $('.nn-logo-text, .site-description').css({
                    'clip': 'rect(1px, 1px, 1px, 1px)',
                    'position': 'absolute'
                });
            } else {
                $('.nn-logo-text, .site-description').css({
                    'clip': 'auto',
                    'position': 'relative'
                });
                $('.nn-logo-text').css('color', to);
            }
        });
    });

})(jQuery);
