(function ($) {
	// Display Style: Reveal
	$.fn.give_reveal_form = function () {
		return this.each(function () {
			var $form_wrapper = $(this),
				$button       = $('.give-btn-reveal', $form_wrapper);

			$button.on('click', function (e) {
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
	$.fn.give_modal_form = function () {
		return this.each(function () {
			var $form_wrapper = $(this),
				$button       = $('.give-btn-modal', $form_wrapper);

			$button.magnificPopup({
				items    : {
					src : $('form', $form_wrapper),
					type: 'inline'
				},
				callbacks: {
					beforeOpen: function () {
						// Trigger custom event.
						$button.trigger('give_modal_form_button_click');

						$button.hide();
					},

					open: function () {
						// Trigger custom event.
						$button.trigger('give_modal_form_popup_open');
					},

					close: function () {
						// Trigger custom event.
						$button.trigger('give_modal_form_popup_close');

						// Remove hide class from form.
						if ($('.give-show-without-modal', $form_wrapper).length) {
							$('form', $form_wrapper).removeClass('mfp-hide');
						}

						$button.show();
					}
				}
			});
		});
	};

	// Display Style: Button
	$.fn.give_button_form = function () {
		return this.each(function () {
			var $form_wrapper = $(this),
				$button       = $('.give-btn-button', $form_wrapper);

			$button.magnificPopup({
				items    : {
					src : $('form', $form_wrapper),
					type: 'inline'
				},
				callbacks: {
					beforeOpen: function () {
						// Trigger custom event.
						$button.trigger('give_button_form_button_click');

						$button.hide();
					},

					open: function () {
						// Trigger custom event.
						$button.trigger('give_button_form_popup_open');
					},

					close: function () {
						// Trigger custom event.
						$button.trigger('give_button_form_popup_close');

						$button.show();
					}
				}
			});
		});
	};

	// Display Style: Stepper
	$.fn.give_stepper_form = function () {
		return this.each(function () {
			var $form_wrapper   = $(this),
				$blocks         = $('.give-block-wrapper', $form_wrapper),
				$next_button    = $('input[name="next"]', $form_wrapper),
				$prev_button    = $('input[name="prev"]', $form_wrapper),
				step_width      = ( 100 / ( parseInt($blocks.length) - 1 ) ),
				animation_speed = 500;

			// Remove prev & next button when only one step exist.
			if (!$blocks.length) {
				$next_button.remove();
				$prev_button.remove();

				return false;
			}

			// Add step attributes.
			$blocks.each(function (index, item) {
				$(item).attr('data-step', index);
			});

			// Add active state to first block.
			$('.give-block-wrapper:first-child', $form_wrapper).addClass('give-active');

			// Animate container to height of form
			$form_wrapper.css({
				'paddingBottom': $('.give-block-wrapper.give-active', $form_wrapper).height() + 'px'
			});

			// Next/Prev button event.
			$form_wrapper.on('click', 'input[name="prev"], input[name="next"]', function () {
				var $active_block     = $('.give-block-wrapper.give-active', $form_wrapper),
					current_step_type = $(this).attr('name'),
					is_next           = ( 'next' === current_step_type ),
					$current_block    = is_next
						? $active_block.next('.give-block-wrapper')
						: $active_block.prev('.give-block-wrapper'),
					progressbar_width = parseInt(step_width) * parseInt($current_block.attr('data-step')),
					old_move          = is_next ? '-' : '',
					new_start         = is_next ? '' : '-';

				// Ensure top of form is in view
				$('html, body').animate({
					scrollTop : $form_wrapper.offset().top
				}, 'fast');

				// Animate container to height of form
				$form_wrapper.css({
					'paddingBottom': $current_block.height() + 'px'
				});

				$('.current_steps', $form_wrapper).animate({'width': progressbar_width + '%'}, animation_speed, function () {
					// $("#step"+(step+1)).removeClass('complete').addClass('current');
				});

				$active_block.animate({left: old_move + '100%'}, animation_speed).removeClass('give-active');
				$current_block.css({left: new_start + '100%'}).animate({left: '0%'}, animation_speed).addClass('give-active');
			});
		});
	};

	$(document).ready(function () {
		var $reveal_forms  = $('.give-display-style-reveal'),
			$modal_forms   = $('.give-display-style-modal'),
			$button_forms  = $('.give-display-style-button'),
			$stepper_forms = $('.give-display-style-stepper');

		if ($reveal_forms.length) {
			$reveal_forms.give_reveal_form();
		}

		if ($modal_forms.length) {
			$modal_forms.give_modal_form();
		}

		if ($button_forms.length) {
			$button_forms.give_modal_form();
		}

		if ($stepper_forms.length) {
			$stepper_forms.give_stepper_form();
		}
	})
}(jQuery));