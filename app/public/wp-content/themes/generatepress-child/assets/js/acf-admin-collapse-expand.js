(function () {
	'use strict';

	function getFlexibleRoot(el) {
		return el.closest('.acf-field-flexible-content');
	}

	function setAllRowsState(fieldWrap, expand) {
		if (!fieldWrap) {
			return;
		}

		var rows = fieldWrap.querySelectorAll('> .acf-input > .acf-flexible-content > .values > .layout');
		rows.forEach(function (row) {
			var handle = row.querySelector(':scope > .acf-fc-layout-handle');
			if (!handle) {
				return;
			}
			var isCollapsed = row.classList.contains('-collapsed');
			if (expand && isCollapsed) {
				handle.click();
			}
			if (!expand && !isCollapsed) {
				handle.click();
			}
		});
	}

	function ensureToolbar(fieldWrap) {
		if (!fieldWrap || fieldWrap.dataset.fiflpCollapseReady === '1') {
			return;
		}

		var input = fieldWrap.querySelector(':scope > .acf-input');
		if (!input) {
			return;
		}

		var toolbar = document.createElement('div');
		toolbar.className = 'fiflp-acf-collapse-toolbar';

		var expandBtn = document.createElement('button');
		expandBtn.type = 'button';
		expandBtn.className = 'button button-secondary';
		expandBtn.textContent = 'Expandir todo';
		expandBtn.addEventListener('click', function () {
			setAllRowsState(fieldWrap, true);
		});

		var collapseBtn = document.createElement('button');
		collapseBtn.type = 'button';
		collapseBtn.className = 'button button-secondary';
		collapseBtn.textContent = 'Colapsar todo';
		collapseBtn.addEventListener('click', function () {
			setAllRowsState(fieldWrap, false);
		});

		toolbar.appendChild(expandBtn);
		toolbar.appendChild(collapseBtn);
		input.insertBefore(toolbar, input.firstChild);

		fieldWrap.dataset.fiflpCollapseReady = '1';
	}

	function boot(root) {
		var scope = root || document;
		var fields = scope.querySelectorAll('.acf-field-flexible-content');
		fields.forEach(ensureToolbar);
	}

	function init() {
		boot(document);
		if (window.acf && typeof window.acf.addAction === 'function') {
			window.acf.addAction('ready', function () {
				boot(document);
			});
			window.acf.addAction('append', function ($el) {
				boot($el && $el[0] ? $el[0] : document);
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
