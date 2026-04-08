/**
 * Admin ACF: agrupa subcampos del hito en "Texto" e "Imagen" (solo DOM; mismos names/inputs).
 */
(function ($) {
	var fieldKey = 'field_cronologia_editorial_hitos';

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

	function wrapHitoRow() {
		var $repeater = $('.acf-field[data-key="' + fieldKey + '"]');
		if (!$repeater.length) {
			return;
		}
		$repeater.find('.acf-repeater .acf-row').each(function () {
			var $td = $(this).children('td.acf-fields');
			if (!$td.length || $td.children('.fiflp-crono-hito-grupo-texto').length) {
				return;
			}
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
		});
	}

	if (typeof acf !== 'undefined') {
		acf.addAction('ready', wrapHitoRow);
		acf.addAction('append', wrapHitoRow);
	}
	$(wrapHitoRow);
})(jQuery);
