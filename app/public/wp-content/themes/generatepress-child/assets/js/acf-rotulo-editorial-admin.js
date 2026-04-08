/**
 * Admin ACF: comportamiento "zapato" para líneas del Rótulo editorial.
 */
(function ($) {
	var fieldKey = 'field_rotulo_editorial_titulo_lineas';
	var rootSelector = '.layout[data-layout="rotulo_editorial"] .acf-field[data-key="' + fieldKey + '"]';
	var configFieldNames = [
		'subtitulo',
		'ancho_subtitulo',
		'alineacion_subtitulo',
		'fuente_rotulo',
		'etiqueta_html',
		'tamano',
		'variante',
		'color_trazo',
		'color_fondo',
		'interlineado',
		'espaciado_letras'
	];

	function organizeLayoutFields() {
		$('.layout[data-layout="rotulo_editorial"]').each(function () {
			var $layout = $(this);
			var $layoutFields = $layout.children('.acf-fields');
			if (!$layoutFields.length) {
				return;
			}

			var $group = $layoutFields.children('.fiflp-rotulo-config-group');
			if (!$group.length) {
				$group = $('<div class="fiflp-rotulo-config-group" />');
				$layoutFields.append($group);
			}

			configFieldNames.forEach(function (name) {
				var $field = $layoutFields.children('.acf-field[data-name="' + name + '"]');
				if ($field.length) {
					$field.appendTo($group);
				}
			});
		});
	}

	function applyButtonTooltips() {
		var tooltipMap = {
			ancho_subtitulo: {
				igual_rotulo: 'Mismo ancho que el rotulo',
				estrecho: 'Subtitulo estrecho (72%)',
				ancho: 'Subtitulo a ancho completo'
			},
			alineacion_subtitulo: {
				left: 'Alinear a la izquierda',
				center: 'Alinear al centro',
				right: 'Alinear a la derecha',
				justify: 'Justificar el subtitulo'
			},
			variante: {
				linea: 'Variante en linea',
				linea_inversa: 'Linea inversa',
				relleno: 'Variante rellena',
				relleno_inverso: 'Relleno inverso'
			},
			tamano: {
				s: 'Tamano pequeno',
				m: 'Tamano medio',
				l: 'Tamano grande',
				xl: 'Tamano extra grande'
			}
		};

		$(rootSelector)
			.closest('.layout[data-layout="rotulo_editorial"]')
			.find('.acf-field[data-type="button_group"]')
			.each(function () {
				var $field = $(this);
				var fieldName = $field.data('name');
				var fieldMap = tooltipMap[fieldName] || {};

				$field.find('.acf-button-group input[type="radio"]').each(function () {
					var $input = $(this);
					var value = String($input.val() || '');
					var text = fieldMap[value] || String($input.closest('label').text() || '').trim();
					if (text) {
						$input.attr('title', text);
						$input.closest('label').attr('title', text);
					}
				});
			});

		// Tooltips base para botones de accion del modulo.
		$(rootSelector)
			.find('.acf-actions .acf-button, .acf-actions .button')
			.each(function () {
				var $btn = $(this);
				if (!$btn.attr('title')) {
					$btn.attr('title', String($btn.text() || '').trim());
				}
			});
	}

	function setCollapsedTitle($td) {
		var value = '';
		var $input = $td.find('.acf-field[data-name="texto"] input[type="text"]').first();
		if ($input.length) {
			value = String($input.val() || '').trim();
		}
		$td.attr('data-collapsed-title', value || '(sin texto)');
	}

	function setupRows() {
		organizeLayoutFields();
		applyButtonTooltips();

		var $repeater = $(rootSelector);
		if (!$repeater.length) {
			return;
		}

		$repeater.find('.acf-repeater .acf-row').not('.acf-clone').each(function () {
			var $td = $(this).children('td.acf-fields');
			if ($td.length) {
				setCollapsedTitle($td);
			}
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
		$(document).on('input change', rootSelector + ' .acf-field[data-name="texto"] input[type="text"]', function () {
			var $td = $(this).closest('td.acf-fields');
			if ($td.length) {
				setCollapsedTitle($td);
			}
		});
	}

	function bindRowToggleAndDrag() {
		var startX = 0;
		var startY = 0;
		var moved = false;
		var isSorting = false;

		$(document).on('mousedown', rootSelector + ' .acf-repeater.-block > .acf-table > tbody > tr.acf-row > .acf-row-handle .acf-row-number', function (e) {
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

		$(document).on('sortstart', rootSelector + ' .acf-repeater.-block > .acf-table > tbody', function () {
			isSorting = true;
		});

		$(document).on('sortstop', rootSelector + ' .acf-repeater.-block > .acf-table > tbody', function () {
			setTimeout(function () {
				isSorting = false;
			}, 0);
		});

		$(document).on('click', rootSelector + ' .acf-repeater.-block > .acf-table > tbody > tr.acf-row > .acf-row-handle .acf-row-number', function (e) {
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
	bindRowToggleAndDrag();
	$(setupRows);
})(jQuery);
