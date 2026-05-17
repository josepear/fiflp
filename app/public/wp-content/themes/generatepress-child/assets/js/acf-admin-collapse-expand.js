(function () {
	'use strict';

	function removeCustomToolbars(scope) {
		var root = scope || document;
		root.querySelectorAll('.fiflp-acf-collapse-toolbar').forEach(function (toolbar) {
			toolbar.remove();
		});
	}

	function enhanceNativeToolbar(scope) {
		var root = scope || document;
		root.querySelectorAll('.acf-flexible-content .acf-actions.-hover').forEach(function (actions) {
			actions.classList.add('fiflp-acf-native-toolbar');

			actions.querySelectorAll('a, button').forEach(function (control) {
				var label = String(control.textContent || '').trim().toLowerCase();

				if (label === 'expand all' || label === 'expandir todo') {
					control.textContent = 'Expandir todo';
					control.classList.add('fiflp-acf-native-collapse-btn');
					control.classList.add('is-expand');
				}

				if (label === 'collapse all' || label === 'colapsar todo') {
					control.textContent = 'Colapsar todo';
					control.classList.add('fiflp-acf-native-collapse-btn');
					control.classList.add('is-collapse');
				}
			});
		});
	}

	function boot(root) {
		var scope = root || document;
		removeCustomToolbars(scope);
		enhanceNativeToolbar(scope);
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
