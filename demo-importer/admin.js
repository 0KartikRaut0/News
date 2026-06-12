/**
 * NeoNews Demo Importer - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        var $importBtn = $('#neonews-import-demo');
        var $removeBtn = $('#neonews-remove-demo');
        var $fixBtn = $('#neonews-fix-images');
        var $message = $('#neonews-demo-message');
        var $progress = $('#neonews-demo-progress');

        function showSuccess(response, reloadMs) {
            var html = response.data.message;
            if (response.data.homeUrl) {
                html += ' <a href="' + response.data.homeUrl + '" target="_blank" rel="noopener"><strong>View site →</strong></a>';
            }
            $message.removeClass('error').addClass('success').html(html).show();
            setTimeout(function() { window.location.reload(); }, reloadMs || 2000);
        }

        $importBtn.on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text(neonewsDemoData.i18n.importing);
            $message.hide();
            $progress.show();

            $.post(neonewsDemoData.ajaxUrl, {
                action: 'neonews_import_demo',
                nonce: neonewsDemoData.nonce
            }).done(function(response) {
                $progress.hide();
                if (response.success) {
                    showSuccess(response, 2500);
                } else {
                    $message.removeClass('success').addClass('error')
                        .html(response.data.message || neonewsDemoData.i18n.error).show();
                    $btn.prop('disabled', false).text('Import Full Demo Now');
                }
            }).fail(function() {
                $progress.hide();
                $message.removeClass('success').addClass('error').html(neonewsDemoData.i18n.error).show();
                $btn.prop('disabled', false).text('Import Full Demo Now');
            });
        });

        $fixBtn.on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text(neonewsDemoData.i18n.fixImages);
            $message.hide();
            $progress.show();

            $.post(neonewsDemoData.ajaxUrl, {
                action: 'neonews_fix_demo_images',
                nonce: neonewsDemoData.nonce
            }).done(function(response) {
                $progress.hide();
                if (response.success) {
                    showSuccess(response, 2000);
                } else {
                    $message.removeClass('success').addClass('error')
                        .html(response.data.message || neonewsDemoData.i18n.error).show();
                    $btn.prop('disabled', false).text('Fix Missing Images');
                }
            }).fail(function() {
                $progress.hide();
                $message.removeClass('success').addClass('error').html(neonewsDemoData.i18n.error).show();
                $btn.prop('disabled', false).text('Fix Missing Images');
            });
        });

        $removeBtn.on('click', function() {
            if (!confirm(neonewsDemoData.i18n.confirmRemove)) {
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text(neonewsDemoData.i18n.removing);
            $message.hide();
            $progress.show();

            $.post(neonewsDemoData.ajaxUrl, {
                action: 'neonews_remove_demo',
                nonce: neonewsDemoData.nonce
            }).done(function(response) {
                $progress.hide();
                if (response.success) {
                    $message.removeClass('error').addClass('success').html(response.data.message).show();
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    $message.removeClass('success').addClass('error')
                        .html(response.data.message || neonewsDemoData.i18n.error).show();
                    $btn.prop('disabled', false).text('Remove Demo');
                }
            }).fail(function() {
                $progress.hide();
                $message.removeClass('success').addClass('error').html(neonewsDemoData.i18n.error).show();
                $btn.prop('disabled', false).text('Remove Demo');
            });
        });
    });

})(jQuery);
