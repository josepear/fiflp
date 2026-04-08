/**
 * Admin ACF: agrupa subcampos del hito en "Texto" e "Imagen" (solo DOM; mismos names/inputs).
 * En colapsado, pinta el título via data attribute para que siempre se vea.
 */
(function ($) {
	var fieldKey = 'field_cronologia_editorial_hitos';

	function setCollapsedTitle($td) {
		var value = '';
		var $input = $td.find('.acf-field[data-name="fecha_titulo"] input[type="text"]').first();
		if ($input.length) {
			value = String($input.val() || '').trim();
		}
		$td.attr('data-collapsed-title', value || '(sin titulo)');
	}

	function wrapMetaRow($grupoImagen) {
		if (!$grupoImagen.length || $grupoImagen.find('> .fiflp-crono-hito-imagen-meta-row').length) {
			return;
		}
		var $pos = $grupoImagen.children('.acf-field[data-name="imagen_posicion"]');
		var $sang = $grupoImagen.children('.acf-field[data-name="imagen_sangre"]');
		if (!$pos.length || !$sang.length) {
			return;
		}
		var $row = $('<div class="fiflp-crono-hito-imagen-meta-row" />');
		$row.append($pos, $sang);
		var $cap = $grupoImagen.children('.acf-field[data-name="caption"]');
		var $img = $grupoImagen.children('.acf-field[data-name="imagen"]');
		if ($cap.length) {
			$cap.after($row);
		} else if ($img.length) {
			$img.after($row);
		} else {
			$grupoImagen.prepend($row);
		}
	}

	function setupRows() {
		var $repeater = $('.acf-field[data-key="' + fieldKey + '"]');
		if (!$repeater.length) {
			return;
		}
		$repeater.find('.acf-repeater .acf-row').each(function () {
			var $td = $(this).children('td.acf-fields');
			if (!$td.length) {
				return;
			}
			if (!$td.children('.fiflp-crono-hito-grupo-texto').length) {
				var namesText = ['fecha_titulo', 'texto', 'texto_posicion'];
				var namesImg = ['imagen', 'caption', 'imagen_posicion', 'imagen_sangre'];
				var $gText = $('<div class="fiflp-crono-hito-grupo-texto" />');
				var $gImg = $('<div class="fiflp-crono-hito-grupo-imagen" />');
				namesText.forEach(function (n) {
					$td.children('.acf-field[data-name="' + n + '"]').appendTo($gText);
				});
				namesImg.forEach(function (n) {
					$td.children('.acf-field[data-name="' + n + '"]').appendTo($gImg);
				});
				$td.append($gText, $gImg);
				wrapMetaRow($gImg);
			} else {
				wrapMetaRow($td.children('.fiflp-crono-hito-grupo-imagen').first());
			}
			setCollapsedTitle($td);
		});

		// Evita arrastre accidental al hacer click: para ordenar hay que mantener pulsado y mover.
		$repeater.find('.acf-repeater.-block > .acf-table > tbody').each(function () {
			var $tbody = $(this);
			if ($tbody.data('ui-sortable')) {
				$tbody.sortable('option', 'delay', 180);
				$tbody.sortable('option', 'distance', 8);
			}
		});
	}

	function bindSync() {
		$(document).on('input change', '.acf-field[data-key="' + fieldKey + '"] .acf-field[data-name="fecha_titulo"] input[type="text"]', function () {
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
			if (!$row.length) {
				return;
			}

			// Abrir usando el toggle nativo de ACF si existe.
			var $toggle = $row.find('.acf-row-handle [data-event="collapse-row"], .acf-row-handle .acf-icon.-plus').first();
			if ($toggle.length) {
				$toggle.trigger('click');
				return;
			}

			// Fallback: click en la celda del asa.
			$row.children('.acf-row-handle').trigger('click');
		});
	}

	function bindRowNumberToggle() {
		var startX = 0;
		var startY = 0;
		var moved = false;
		var isSorting = false;

		$(document).on('mousedown', '.acf-field[data-key="' + fieldKey + '"] .acf-repeater .acf-row-handle .acf-row-number', function (e) {
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
			// Dejar terminar el click sintético tras drag antes de reactivar toggle.
			setTimeout(function () {
				isSorting = false;
			}, 0);
		});

		$(document).on('click', '.acf-field[data-key="' + fieldKey + '"] .acf-repeater .acf-row-handle .acf-row-number', function (e) {
			if (isSorting || moved) {
				startX = 0;
				startY = 0;
				moved = false;
				return;
			}

			var $row = $(this).closest('tr.acf-row');
			if (!$row.length) {
				startX = 0;
				startY = 0;
				moved = false;
				return;
			}

			var $toggle = $row.find('.acf-row-handle [data-event="collapse-row"], .acf-row-handle .acf-icon.-plus, .acf-row-handle .acf-icon.-minus').first();
			if ($toggle.length) {
				$toggle.trigger('click');
			} else {
				$row.children('.acf-row-handle').trigger('click');
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
