(function($) {


Matrix.bind('text', 'display', function(cell){

	var settings = $.extend({ maxl: '', multiline: 'n', spaces: 'y' }, cell.settings),
		$textarea = $('> *[name]', cell.dom.$td),
		$charsLeft = $('> div > div', cell.dom.$td),
		clicked = false,
		clickedDirectly = false,
		focussed = false;

	// is this a textarea?
	if ($textarea.attr('nodeName') == 'TEXTAREA') {

		var updateHeight = true;

		var $stage = $('<stage />').appendTo(cell.dom.$td),
			val, textHeight;

		// replicate the textarea's text styles
		$stage.css({
			position: 'absolute',
			top: -9999,
			left: -9999,
			lineHeight: $textarea.css('lineHeight'),
			fontSize: $textarea.css('fontSize'),
			fontFamily: $textarea.css('fontFamily'),
			fontWeight: $textarea.css('fontWeight'),
			letterSpacing: $textarea.css('letterSpacing')
		});

		/**
		 * Update Stage Width
		 */
		var updateStageWidth = function(){
			$stage.width($textarea.width());
			updateTextHeight(true);
		}

		/**
		 * Update Text Height
		 */
		var updateTextHeight = function(force){
			// has the value changed?
			if (val === (val = $textarea.val()) && ! force) return;

			// update chars left notification
			if (settings.maxl) {
				var charsLeft = settings.maxl - val.length;
				$charsLeft.html(charsLeft);

				if (charsLeft < 0) {
					$charsLeft.addClass('negative');
				} else {
					$charsLeft.removeClass('negative');
				}
			}

			if (! val) {
				var html = '&nbsp;';
			} else {
				// html entities
				var html = val.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/[\n\r]$/g, '<br/>&nbsp;').replace(/[\n\r]/g, '<br/>');
			}

			if (settings.maxl) {
				html += charsLeft;
			}

			if (focussed) html += 'm';
			$stage.html(html);

			// has the height changed?
			if ((textHeight === (textHeight = $stage.height()) && ! force) || ! textHeight) return;

			// update the textarea height
			$textarea.height(textHeight);
		};

		$(window).resize(updateStageWidth);
		updateStageWidth();
	}
	else {
		updateHeight = false;
	}

	// -------------------------------------------
	//  Focus and Blur
	// -------------------------------------------

	cell.dom.$td.mousedown(function(){
		clicked = true;
	});

	$textarea.mousedown(function(){
		clickedDirectly = true;
	});

	/**
	 * Focus
	 */
	$textarea.focus(function(){
		focussed = true;

		if (updateHeight) {
			updateTextHeight(true);
			interval = setInterval(updateTextHeight, 1);
		}

		setTimeout(function(){
			if (! clickedDirectly) {
				// focus was *given* to the textarea, so we'll do our best
				// to make it seem like the entire $td is a normal text input

				var val = $textarea.val();

				if ($textarea[0].setSelectionRange) {
					var length = val.length * 2;

					if (! clicked) {
						// tabbed into, so select the entire value
						$textarea[0].setSelectionRange(0, length);
					} else {
						// just place the cursor at the end
						$textarea[0].setSelectionRange(length, length);
					}
				} else {
					// browser doesn't support setSelectionRange so try refreshing
					// the value as a way to place the cursor at the end
					$textarea.val(val);
				}
			}

			clicked = clickedDirectly = false;
		}, 0);
	});

	/**
	 * Blur
	 */
	$textarea.blur(function(){
		focussed = false;

		if (updateHeight) {
			clearInterval(interval);
			updateTextHeight(true);
		}
	});

	// -------------------------------------------
	//  Input validation
	// -------------------------------------------

	if (settings.multiline != 'y' || settings.spaces != 'y') {
		$textarea.keypress(function(event){
			if ((settings.multiline != 'y' && event.keyCode == 13)
				|| (settings.spaces != 'y' && event.keyCode == 32)) {
				event.preventDefault();
			}
		});
	}

	// -------------------------------------------
	//  Crop to max length
	// -------------------------------------------

	if (settings.maxl) {
		var $form = cell.dom.$td.closest('form');
		$form.submit(function(){
			var cropped = $textarea.val().substr(0, settings.maxl);
			$textarea.val(cropped);
		});
	}

});


})(jQuery);
