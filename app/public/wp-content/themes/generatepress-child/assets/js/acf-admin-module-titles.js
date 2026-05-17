(function () {
	'use strict';

	function getLayoutTitle(layout) {
		var handle = layout.querySelector('.acf-fc-layout-handle');
		if (!handle) {
			return '';
		}

		var titleNode = handle.querySelector('.acf-fc-layout-title');
		if (titleNode && titleNode.textContent) {
			return titleNode.textContent.trim();
		}

		return handle.textContent ? handle.textContent.trim() : '';
	}

	function paintLayoutContext(layout) {
		if (!layout) {
			return;
		}

		var fields = layout.querySelector(':scope > .acf-fields');
		if (!fields) {
			return;
		}

		var title = getLayoutTitle(layout);
		if (!title) {
			return;
		}

		var badge = fields.querySelector(':scope > .fiflp-layout-context-title');
		if (!badge) {
			badge = document.createElement('div');
			badge.className = 'fiflp-layout-context-title';
			fields.insertBefore(badge, fields.firstChild);
		}

		badge.textContent = 'Módulo: ' + title;
	}

	function paintAll(root) {
		var scope = root || document;
		scope.querySelectorAll('.acf-flexible-content .layout').forEach(paintLayoutContext);
	}

	function init() {
		paintAll(document);

		if (window.acf && typeof window.acf.addAction === 'function') {
			window.acf.addAction('ready', function () {
				paintAll(document);
			});
			window.acf.addAction('append', function ($el) {
				if ($el && $el[0]) {
					paintAll($el[0]);
				} else {
					paintAll(document);
				}
			});
			window.acf.addAction('show', function ($el) {
				if ($el && $el[0]) {
					paintAll($el[0]);
				}
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
