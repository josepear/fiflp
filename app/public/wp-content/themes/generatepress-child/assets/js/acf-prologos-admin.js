/**
 * Admin ACF: replica comportamiento de Cronología para Prólogos.
 */
(function ($) {
	var fieldKey = 'field_prologos_prologos';
	var rootSelector = '.layout[data-layout="prologos"] .acf-field[data-key="' + fieldKey + '"]';

	function setCollapsedTitle($td) {
		var value = '';
		var $input = $td.find('.acf-field[data-name="nombre"] input[type="text"]').first();
		if ($input.length) {
			value = String($input.val() || '').trim();
		}
		$td.attr('data-collapsed-title', value || '(sin titulo)');
	}

	function setupRows() {
		var $repeater = $(rootSelector);
		if (!$repeater.length) {
			return;
		}

		$repeater.find('.acf-repeater .acf-row').not('.acf-clone').each(function () {
			var $td = $(this).children('td.acf-fields');
			if (!$td.length) {
				return;
			}

			if (!$td.children('.fiflp-prologo-item-grupo-texto').length) {
				var namesText = ['nombre', 'cargo'];
				var namesImg = ['foto'];
				var $gText = $('<div class="fiflp-prologo-item-grupo-texto" />');
				var $gImg = $('<div class="fiflp-prologo-item-grupo-imagen" />');
				var $contenido = $td.children('.acf-field[data-name="contenido"]');

				namesText.forEach(function (n) {
					$td.children('.acf-field[data-name="' + n + '"]').appendTo($gText);
				});
				namesImg.forEach(function (n) {
					$td.children('.acf-field[data-name="' + n + '"]').appendTo($gImg);
				});
				$td.append($gText, $gImg);
				if ($contenido.length) {
					$contenido.addClass('fiflp-prologo-item-contenido-full').appendTo($td);
				}
			}

			setCollapsedTitle($td);
		});

		$repeater.find('.acf-repeater.-block > .acf-table > tbody').each(function () {
			var $tbody = $(this);
			if ($tbody.data('ui-sortable')) {
				$tbody.sortable('option', 'delay', 180);
				$tbody.sortable('option', 'distance', 8);
			}
		});

		if (!$repeater.data('fiflp-init-collapsed')) {
			$repeater.find('.acf-repeater tr.acf-row').not('.acf-clone').not('.-collapsed').each(function () {
				var $toggle = $(this).find('.acf-row-handle [data-event="collapse-row"]').first();
				if ($toggle.length) {
					$toggle.trigger('click');
				}
			});
			$repeater.data('fiflp-init-collapsed', true);
		}
	}

	function bindSync() {
		$(document).on('input change', '.acf-field[data-key="' + fieldKey + '"] .acf-field[data-name="nombre"] input[type="text"]', function () {
			var $td = $(this).closest('td.acf-fields');
			if ($td.length) {
				setCollapsedTitle($td);
			}
		});
	}

	function bindCollapsedClickOpen() {
		$(document).on('click', '.acf-field[data-key="' + fieldKey + '"] .acf-repeater tr.acf-row.-collapsed > td.acf-fields', function (e) {
			var $target = $(e.target);
			if ($target.closest('a, button, input, textarea, select, label').length) {
				return;
			}

			var $row = $(this).closest('tr.acf-row');
			if (!$row.length || $row.hasClass('acf-clone')) {
				return;
			}

			var $toggle = $row.find('.acf-row-handle [data-event="collapse-row"]').first();
			if ($toggle.length) {
				$toggle.trigger('click');
			}
		});
	}

	function bindRowNumberToggle() {
		var startX = 0;
		var startY = 0;
		var moved = false;
		var isSorting = false;

		$(document).on('mousedown', '.acf-field[data-key="' + fieldKey + '"] .acf-repeater.-block > .acf-table > tbody > tr.acf-row > .acf-row-handle .acf-row-number', function (e) {
			startX = e.pageX;
			startY = e.pageY;
			moved = false;
		});

		$(document).on('mousemove', function (e) {
			if (0 === startX && 0 === startY) {
				return;
			}
			if (Math.abs(e.pageX - startX) > 4 || Math.abs(e.pageY - startY) > 4) {
				moved = true;
			}
		});

		$(document).on('sortstart', '.acf-field[data-key="' + fieldKey + '"] .acf-repeater.-block > .acf-table > tbody', function () {
			isSorting = true;
		});

		$(document).on('sortstop', '.acf-field[data-key="' + fieldKey + '"] .acf-repeater.-block > .acf-table > tbody', function () {
			setTimeout(function () {
				isSorting = false;
			}, 0);
		});

		$(document).on('click', '.acf-field[data-key="' + fieldKey + '"] .acf-repeater.-block > .acf-table > tbody > tr.acf-row > .acf-row-handle .acf-row-number', function (e) {
			if (isSorting || moved) {
				startX = 0;
				startY = 0;
				moved = false;
				return;
			}

			var $row = $(this).closest('tr.acf-row');
			if (!$row.length || $row.hasClass('acf-clone')) {
				startX = 0;
				startY = 0;
				moved = false;
				e.preventDefault();
				e.stopPropagation();
				return;
			}

			var $toggle = $row.find('.acf-row-handle [data-event="collapse-row"]').first();
			if ($toggle.length) {
				$toggle.trigger('click');
			}

			e.preventDefault();
			e.stopPropagation();
			startX = 0;
			startY = 0;
			moved = false;
		});

		$(document).on('mouseup', function () {
			startX = 0;
			startY = 0;
			moved = false;
		});
	}

	if (typeof acf !== 'undefined') {
		acf.addAction('ready', setupRows);
		acf.addAction('append', setupRows);
	}
	bindSync();
	bindCollapsedClickOpen();
	bindRowNumberToggle();
	$(setupRows);
})(jQuery);
