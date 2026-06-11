/**
 * NeoNews Core - Public JavaScript
 *
 * @package NeoNews_Core
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initSubmissionForm();
        initViewTracking();
    });

    /**
     * Initialize submission form
     */
    function initSubmissionForm() {
        var form = document.getElementById('nn-submission-form');
        
        if (!form) {
            return;
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var submitBtn = document.getElementById('nn-submit-article');
            var messageDiv = document.getElementById('nn-submission-message');
            
            if (!submitBtn || submitBtn.disabled) {
                return;
            }

            var title = form.querySelector('[name="article_title"]').value.trim();
            var content = '';
            
            if (typeof tinymce !== 'undefined' && tinymce.get('nn_article_content')) {
                content = tinymce.get('nn_article_content').getContent();
            } else {
                var textarea = form.querySelector('[name="article_content"]');
                if (textarea) {
                    content = textarea.value;
                }
            }
            
            var category = form.querySelector('[name="article_category"]').value;

            if (!title) {
                showMessage(messageDiv, 'error', neonewsCoreData.i18n.error || 'Please enter a title.');
                return;
            }

            if (!content || content.length < 100) {
                showMessage(messageDiv, 'error', 'Content must be at least 100 characters.');
                return;
            }

            if (!category) {
                showMessage(messageDiv, 'error', 'Please select a category.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.textContent = neonewsCoreData.i18n.submitting || 'Submitting...';

            var formData = new FormData(form);
            formData.append('action', 'neonews_submit_article');

            fetch(neonewsCoreData.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    showMessage(messageDiv, 'success', data.data.message || neonewsCoreData.i18n.success);
                    form.reset();
                    
                    if (typeof tinymce !== 'undefined' && tinymce.get('nn_article_content')) {
                        tinymce.get('nn_article_content').setContent('');
                    }
                } else {
                    showMessage(messageDiv, 'error', data.data.message || neonewsCoreData.i18n.error);
                }
            })
            .catch(function() {
                showMessage(messageDiv, 'error', neonewsCoreData.i18n.error || 'An error occurred.');
            })
            .finally(function() {
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
                submitBtn.textContent = 'Submit Article';
            });
        });
    }

    /**
     * Show message
     */
    function showMessage(element, type, message) {
        if (!element) {
            return;
        }

        element.className = 'nn-submission-message ' + type;
        element.textContent = message;
        element.style.display = 'block';

        element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Initialize view tracking
     */
    function initViewTracking() {
        var postId = document.body.getAttribute('data-post-id');
        
        if (!postId && document.body.classList.contains('single-post')) {
            var articleElement = document.querySelector('article[id^="post-"]');
            if (articleElement) {
                var match = articleElement.id.match(/post-(\d+)/);
                if (match) {
                    postId = match[1];
                }
            }
        }

        if (!postId || typeof neonewsCoreData === 'undefined') {
            return;
        }

        if (document.body.classList.contains('nn-consent-no-statistics') ||
            document.body.classList.contains('nn-consent-pending')) {
            return;
        }

        setTimeout(function() {
            var formData = new FormData();
            formData.append('action', 'neonews_track_view');
            formData.append('post_id', postId);

            fetch(neonewsCoreData.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            }).catch(function() {
            });
        }, 3000);
    }

})();
