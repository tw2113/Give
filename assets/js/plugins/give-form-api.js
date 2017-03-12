(function($){
	// Display Style: Reveal
	$.fn.give_reveal_form = function(){
		return this.each(function(){
			var $form_wrapper = $(this);

			$( $form_wrapper ).on( 'click', '.give-btn-reveal', function( e ) {
				e.preventDefault();
				// Trigger cusotm event.
				$(this).trigger('give_reveal_form_button_click');

				// Show form.
				$(this).prev('form').slideDown();
			});
		});
	};

	$(document).ready(function(){
		$('.give-display-style-reveal').give_reveal_form();
	})
}(jQuery));