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
				var namesImg = [
					'imagen', 'caption', 'imagen_multiplicar_1',
					'ajuste_sombras_imagen_1', 'ajuste_medios_imagen_1', 'ajuste_luces_imagen_1',
					'imagen_2', 'caption_2', 'imagen_multiplicar_2',
					'ajuste_sombras_imagen_2', 'ajuste_medios_imagen_2', 'ajuste_luces_imagen_2',
					'imagen_posicion', 'imagen_sangre', 'escala_visual_imagen'
				];
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

	function readRangeValue($scope, name) {
		var $field = $scope.find('.acf-field[data-name="' + name + '"]').first();
		if (!$field.length) {
			return 0;
		}
		var $range = $field.find('input[type="range"]').first();
		var $number = $field.find('input[type="number"]').first();
		var raw = $range.length ? $range.val() : ($number.length ? $number.val() : 0);
		var value = Number(raw);
		if (!Number.isFinite(value)) {
			value = 0;
		}
		return Math.max(-100, Math.min(100, value));
	}

	function setRangeValue($scope, name, value) {
		var $field = $scope.find('.acf-field[data-name="' + name + '"]').first();
		if (!$field.length) {
			return;
		}
		var safeValue = Math.max(-100, Math.min(100, Number(value) || 0));
		$field.find('input[type="range"], input[type="number"]').each(function () {
			this.value = String(safeValue);
			$(this).trigger('input').trigger('change');
		});
	}

	function buildToneFilter(shadows, mids, highlights) {
		var brightness = 1 + ((mids + (shadows * 0.35) + (highlights * 0.25)) * 0.003);
		var contrast = 1 + ((highlights - shadows) * 0.002);
		return 'brightness(' + brightness.toFixed(4) + ') contrast(' + contrast.toFixed(4) + ')';
	}

	function ensureToneModal() {
		var $modal = $('.fiflp-imagen-tono-modal');
		if ($modal.length) {
			return $modal;
		}

		$modal = $(
			'<div class="fiflp-imagen-tono-modal">' +
				'<div class="fiflp-imagen-tono-modal__dialog" role="dialog" aria-modal="true" aria-label="Ajustar imagen">' +
					'<div class="fiflp-imagen-tono-modal__preview-wrap"><img class="fiflp-imagen-tono-modal__preview" src="" alt=""></div>' +
					'<div class="fiflp-imagen-tono-modal__controls">' +
						'<div class="fiflp-imagen-tono-modal__control"><label>Sombras: <strong data-tono-out="sombras">0</strong></label><input type="range" min="-100" max="100" step="1" value="0" data-tono-input="sombras"></div>' +
						'<div class="fiflp-imagen-tono-modal__control"><label>Medios tonos: <strong data-tono-out="medios">0</strong></label><input type="range" min="-100" max="100" step="1" value="0" data-tono-input="medios"></div>' +
						'<div class="fiflp-imagen-tono-modal__control"><label>Altas luces: <strong data-tono-out="luces">0</strong></label><input type="range" min="-100" max="100" step="1" value="0" data-tono-input="luces"></div>' +
						'<div class="fiflp-imagen-tono-modal__actions"><button type="button" class="button fiflp-btn-unificado" data-tono-action="cancelar">Cancelar</button><button type="button" class="button fiflp-btn-unificado" data-tono-action="guardar">Guardar</button></div>' +
					'</div>' +
				'</div>' +
			'</div>'
		);
		$('body').append($modal);
		return $modal;
	}

	function attachToneTools() {
		var activeState = null;

		$(document).off('click.fiflpToneLaunch').on('click.fiflpToneLaunch', '.fiflp-crono-tono-launch', function () {
			var $btn = $(this);
			var target = String($btn.data('img-target') || '1');
			var $scope = $btn.closest('.fiflp-crono-hito-grupo-imagen');
			if (!$scope.length) {
				return;
			}

			var $imgField = '2' === target
				? $scope.find('.acf-field[data-name="imagen_2"]').first()
				: $scope.find('.acf-field[data-name="imagen"]').first();
			var $img = $imgField.find('.acf-image-uploader .image-wrap img').first();
			var directUrl = '';
			var attachmentId = '';

			var $hidden = $imgField.find('input[type="hidden"]').first();
			if ($hidden.length) {
				var hiddenVal = String($hidden.val() || '').trim();
				if (/^\d+$/.test(hiddenVal)) {
					attachmentId = hiddenVal;
				} else if (/^(https?:)?\/\//.test(hiddenVal) || hiddenVal.indexOf('/') === 0) {
					directUrl = hiddenVal;
				}
			}

			var fallbackUrl = directUrl || String($img.attr('src') || '');
			var $modal = ensureToneModal();
			var $preview = $modal.find('.fiflp-imagen-tono-modal__preview');
			var $inS = $modal.find('[data-tono-input="sombras"]');
			var $inM = $modal.find('[data-tono-input="medios"]');
			var $inL = $modal.find('[data-tono-input="luces"]');
			var $outS = $modal.find('[data-tono-out="sombras"]');
			var $outM = $modal.find('[data-tono-out="medios"]');
			var $outL = $modal.find('[data-tono-out="luces"]');

			var names = '2' === target
				? { s: 'ajuste_sombras_imagen_2', m: 'ajuste_medios_imagen_2', l: 'ajuste_luces_imagen_2' }
				: { s: 'ajuste_sombras_imagen_1', m: 'ajuste_medios_imagen_1', l: 'ajuste_luces_imagen_1' };

			var sync = function () {
				var s = Number($inS.val() || 0);
				var m = Number($inM.val() || 0);
				var l = Number($inL.val() || 0);
				$outS.text(s);
				$outM.text(m);
				$outL.text(l);
				$preview.css('filter', buildToneFilter(s, m, l));
			};

			$inS.val(readRangeValue($scope, names.s));
			$inM.val(readRangeValue($scope, names.m));
			$inL.val(readRangeValue($scope, names.l));
			$preview.attr('src', fallbackUrl).attr('alt', String($img.attr('alt') || ''));
			sync();
			$modal.addClass('is-open');

			activeState = {
				$scope: $scope,
				$img: $img,
				names: names,
			};

			$modal.find('[data-tono-input]').off('input.fiflpTone change.fiflpTone').on('input.fiflpTone change.fiflpTone', sync);

			var params = { action: 'fiflp_get_original_image_url' };
			if (attachmentId) {
				params.id = attachmentId;
			}
			if (fallbackUrl) {
				params.url = fallbackUrl;
			}
			$.getJSON(ajaxurl, params).done(function (res) {
				if (res && res.success && res.data && res.data.url) {
					$preview.attr('src', String(res.data.url));
				}
			});
		});

		$(document).off('click.fiflpToneModal').on('click.fiflpToneModal', '.fiflp-imagen-tono-modal, .fiflp-imagen-tono-modal [data-tono-action]', function (e) {
			var $target = $(e.target);
			var $modal = $('.fiflp-imagen-tono-modal');
			if (!$modal.length) {
				return;
			}

			if ($target.closest('[data-tono-action="cancelar"]').length || $target.is('.fiflp-imagen-tono-modal')) {
				$modal.removeClass('is-open');
				activeState = null;
				return;
			}

			if ($target.closest('[data-tono-action="guardar"]').length && activeState) {
				var s = Number($modal.find('[data-tono-input="sombras"]').val() || 0);
				var m = Number($modal.find('[data-tono-input="medios"]').val() || 0);
				var l = Number($modal.find('[data-tono-input="luces"]').val() || 0);

				setRangeValue(activeState.$scope, activeState.names.s, s);
				setRangeValue(activeState.$scope, activeState.names.m, m);
				setRangeValue(activeState.$scope, activeState.names.l, l);
				activeState.$img.css('filter', (0 === s && 0 === m && 0 === l) ? '' : buildToneFilter(s, m, l));

				$modal.removeClass('is-open');
				activeState = null;
			}
		});
	}

	function injectToneButtons() {
		$('.acf-field[data-key="' + fieldKey + '"] .fiflp-crono-hito-grupo-imagen').each(function () {
			var $scope = $(this);
			var $img1 = $scope.find('.acf-field[data-name="imagen"]').first();
			var $img2 = $scope.find('.acf-field[data-name="imagen_2"]').first();

			if ($img1.length && !$img1.find('.fiflp-crono-tono-launch').length) {
				$img1.append('<button type="button" class="button fiflp-btn-unificado fiflp-crono-tono-launch" data-img-target="1">Ajustar imagen 1</button>');
			}

			if ($img2.length && !$img2.find('.fiflp-crono-tono-launch').length) {
				$img2.append('<button type="button" class="button fiflp-btn-unificado fiflp-crono-tono-launch" data-img-target="2">Ajustar imagen 2</button>');
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
		acf.addAction('ready', function () {
			setupRows();
			injectToneButtons();
		});
		acf.addAction('append', function () {
			setupRows();
			injectToneButtons();
		});
	}
	attachToneTools();
	bindSync();
	bindCollapsedClickOpen();
	bindRowNumberToggle();
	$(function () {
		setupRows();
		injectToneButtons();
	});
})(jQuery);
