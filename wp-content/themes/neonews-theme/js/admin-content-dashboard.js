/**
 * Posts/Pages list dashboard — panel wrap + active stat highlight.
 */
(function () {
	'use strict';

	var cfg = typeof nnContentDash === 'object' ? nnContentDash : {};
	var status = cfg.postStatus || 'all';
	var filter = cfg.filter || '';

	function wrapListPanel() {
		var wrap = document.querySelector('body.nn-content-dashboard .wrap');
		var bar = wrap && wrap.querySelector('.nn-content-dashboard-bar');
		if (!wrap || !bar || wrap.querySelector('.nn-content-list-panel')) {
			return;
		}

		var panel = document.createElement('div');
		panel.className = 'nn-admin-panel nn-content-list-panel';

		var move = false;
		var node = wrap.firstChild;
		while (node) {
			var next = node.nextSibling;
			if (node === bar) {
				move = true;
			} else if (move && node.nodeType === 1) {
				if (!node.classList.contains('nn-content-dashboard-bar')) {
					panel.appendChild(node);
				}
			}
			node = next;
		}

		if (panel.childNodes.length) {
			wrap.appendChild(panel);
		}
	}

	function markActiveStats() {
		var activeKey = filter === 'premium' ? 'premium' : status;
		if (activeKey === 'all' || activeKey === '') {
			activeKey = 'publish';
		}

		document.querySelectorAll('.nn-content-dashboard-bar .nn-admin-stat-card').forEach(function (card) {
			card.classList.remove('nn-stat-active');
			var state = card.getAttribute('data-nn-state') || '';
			if (state && state === activeKey) {
				card.classList.add('nn-stat-active');
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			wrapListPanel();
			markActiveStats();
		});
	} else {
		wrapListPanel();
		markActiveStats();
	}
})();
