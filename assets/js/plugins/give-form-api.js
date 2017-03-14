(function($){
	// Display Style: Reveal
	$.fn.give_reveal_form = function(){
		return this.each(function(){
			var $form_wrapper = $(this),
				$button = $( '.give-btn-reveal', $form_wrapper );

			$button.on( 'click', function( e ) {
				e.preventDefault();
				// Trigger custom event.
				$(this).trigger('give_reveal_form_button_click');

				// Show form.
				$(this).prev('form').slideDown();

				$(this).show();
			});
		});
	};

	// Display Style: Modal
	$.fn.give_modal_form = function(){
		return this.each(function(){
			var $form_wrapper = $(this),
				$button = $( '.give-btn-modal', $form_wrapper );

			$button.magnificPopup({
				items: {
					src: $( 'form', $form_wrapper ),
					type: 'inline'
				},
				callbacks: {
					beforeOpen: function () {
						// Trigger custom event.
						$button.trigger('give_modal_form_button_click');

						$button.hide();
					},

					open: function(){
						// Trigger custom event.
						$button.trigger('give_modal_form_popup_open');
					},

					close: function(){
						// Trigger custom event.
						$button.trigger('give_modal_form_popup_close');

						// Remove hide class from form.
						if( $( '.give-show-without-modal', $form_wrapper ).length ) {
							$( 'form', $form_wrapper ).removeClass( 'mfp-hide' );
						}

						$button.show();
					}
				}
			});
		});
	};

	// Display Style: Button
	$.fn.give_button_form = function(){
		return this.each(function(){
			var $form_wrapper = $(this),
				$button = $( '.give-btn-button', $form_wrapper );

			$button.magnificPopup({
				items: {
					src: $( 'form', $form_wrapper ),
					type: 'inline'
				},
				callbacks: {
					beforeOpen: function () {
						// Trigger custom event.
						$button.trigger('give_button_form_button_click');

						$button.hide();
					},

					open: function(){
						// Trigger custom event.
						$button.trigger('give_button_form_popup_open');
					},

					close: function(){
						// Trigger custom event.
						$button.trigger('give_button_form_popup_close');

						$button.show();
					}
				}
			});
		});
	};

	$(document).ready(function(){
		$('.give-display-style-reveal').give_reveal_form();
		$('.give-display-style-modal').give_modal_form();
		$('.give-display-style-button').give_button_form();
	})
}(jQuery));