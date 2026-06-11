/**
 * NeoNews Security Center — admin dashboard JS.
 */
(function() {
    'use strict';

    if (typeof neonewsSecurity === 'undefined') {
        return;
    }

    var cfg = neonewsSecurity;

    document.addEventListener('DOMContentLoaded', function() {
        var scanBtn = document.getElementById('nn-sec-scan');
        var fixAllBtn = document.getElementById('nn-sec-fix-all');
        var wrap = document.querySelector('.nn-sec-wrap');

        if (scanBtn) {
            scanBtn.addEventListener('click', runScan);
        }

        if (fixAllBtn) {
            fixAllBtn.addEventListener('click', function() {
                var ids = [];
                try {
                    ids = JSON.parse(fixAllBtn.getAttribute('data-ids') || '[]');
                } catch (e) {}
                runFixAll(ids);
            });
        }

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.nn-sec-fix-btn');
            if (btn) {
                e.preventDefault();
                runFix(btn.getAttribute('data-fix-id'), btn);
            }
        });

        updateScoreRing(cfg.lastScan ? cfg.lastScan.score : 0);
    });

    function updateScoreRing(score) {
        var ring = document.querySelector('.nn-sec-score-ring');
        var val = document.querySelector('.nn-sec-score-value');
        if (ring) {
            ring.style.setProperty('--score', score);
            ring.setAttribute('data-score', score);
        }
        if (val) {
            val.textContent = score;
        }
    }

    function toast(msg) {
        var el = document.createElement('div');
        el.className = 'nn-sec-toast';
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(function() {
            el.remove();
        }, 4000);
    }

    function setLoading(loading) {
        var wrap = document.querySelector('.nn-sec-wrap');
        if (wrap) {
            wrap.classList.toggle('nn-sec-loading', loading);
        }
    }

    function runScan() {
        setLoading(true);
        var formData = new FormData();
        formData.append('action', 'neonews_security_scan');
        formData.append('nonce', cfg.nonce);

        fetch(cfg.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                setLoading(false);
                if (!data.success) {
                    toast(cfg.i18n.fixError);
                    return;
                }
                updateScoreRing(data.data.score);
                updateAiPanel(data.data.analysis);
                var findings = document.getElementById('nn-sec-findings');
                if (findings && data.data.findings_html) {
                    findings.innerHTML = data.data.findings_html;
                }
                updateFixAllButton(data.data.fix_all_ids);
                toast('Scan complete — score ' + data.data.score + '/100');
            })
            .catch(function() {
                setLoading(false);
                toast(cfg.i18n.fixError);
            });
    }

    function runFix(fixId, btn) {
        if (!fixId) {
            return;
        }
        if (btn) {
            btn.disabled = true;
            btn.textContent = cfg.i18n.fixing;
        }

        var formData = new FormData();
        formData.append('action', 'neonews_security_fix');
        formData.append('nonce', cfg.nonce);
        formData.append('fix_id', fixId);

        fetch(cfg.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = 'Fix automatically';
                }
                if (!data.success) {
                    toast(data.data && data.data.message ? data.data.message : cfg.i18n.fixError);
                    return;
                }
                applyFixResponse(data.data);
                toast(data.data.message || cfg.i18n.fixSuccess);
            })
            .catch(function() {
                if (btn) {
                    btn.disabled = false;
                }
                toast(cfg.i18n.fixError);
            });
    }

    function runFixAll(ids) {
        setLoading(true);
        var formData = new FormData();
        formData.append('action', 'neonews_security_fix_all');
        formData.append('nonce', cfg.nonce);
        ids.forEach(function(id) {
            formData.append('fix_ids[]', id);
        });

        fetch(cfg.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                setLoading(false);
                if (!data.success) {
                    toast(cfg.i18n.fixError);
                    return;
                }
                applyFixResponse(data.data);
                toast(data.data.message || cfg.i18n.fixSuccess);
            })
            .catch(function() {
                setLoading(false);
                toast(cfg.i18n.fixError);
            });
    }

    function applyFixResponse(data) {
        updateScoreRing(data.score);
        updateAiPanel(data.analysis);
        var findings = document.getElementById('nn-sec-findings');
        if (findings && data.findings_html) {
            findings.innerHTML = data.findings_html;
        }
        var log = document.getElementById('nn-sec-log');
        if (log && data.log_html) {
            log.innerHTML = data.log_html;
        }
        updateFixAllButton(data.fix_all_ids);
    }

    function updateAiPanel(analysis) {
        var panel = document.getElementById('nn-sec-ai-content');
        if (!panel || !analysis) {
            return;
        }
        panel.innerHTML =
            '<p class="nn-sec-risk nn-sec-risk-' + escapeAttr(analysis.risk_level) + '">' +
            escapeHtml(analysis.summary) + '</p>' +
            '<div class="nn-sec-narrative">' + escapeHtml(analysis.narrative).replace(/\n\n/g, '<br><br>') + '</div>';
    }

    function updateFixAllButton(ids) {
        var btn = document.getElementById('nn-sec-fix-all');
        if (!btn) {
            if (ids && ids.length) {
                var header = document.querySelector('.nn-sec-header-actions');
                if (header) {
                    btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'button button-secondary';
                    btn.id = 'nn-sec-fix-all';
                    btn.textContent = cfg.i18n.fixAll;
                    header.appendChild(btn);
                    btn.addEventListener('click', function() {
                        runFixAll(ids);
                    });
                }
            }
            return;
        }
        if (!ids || !ids.length) {
            btn.remove();
            return;
        }
        btn.setAttribute('data-ids', JSON.stringify(ids));
    }

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function escapeAttr(str) {
        return String(str).replace(/"/g, '&quot;');
    }
})();
