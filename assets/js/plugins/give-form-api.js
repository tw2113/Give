(function ($) {
	// Global Params.
	var give_media_uploader,
		$active_upload_file_btn;

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
					scrollTop: $form_wrapper.offset().top
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


	// Colorpicker field.
	$.fn.give_colorpicker_fields = function () {
		return this.each(function(){
			var $item = $(this);

			// Bailout: if already colorpicker initialize or colorpicker for repeater field group template.
			if ( $item.parents('.wp-picker-container').length || $item.parents('.give-template').length ) {
				return;
			}

			$item.wpColorPicker();
		});
	};

	// Media upload field.
	$.fn.give_media_fields = function(){
		return this.each(function(){
			$(this).on( 'click', function(e){
				e.preventDefault();

				// Cache active upload button selector.
				$active_upload_file_btn = $(this);

				// If the uploader object has already been created, reopen the dialog
				if (give_media_uploader) {
					give_media_uploader.open();
					return;
				}
				// Extend the wp.media object
				give_media_uploader = wp.media.frames.file_frame = wp.media({
					title   : give_form_api_var.metabox_fields.media.button_title,
					frame   : 'post',
					button  : {
						text: give_form_api_var.metabox_fields.media.button_title
					},
					multiple: false
				});

				// When a file is selected, grab the URL and set it as the text field's value
				give_media_uploader.on('insert', function () {
					var attachment           = give_media_uploader.state().get('selection').first().toJSON(),
						$input_field         = $active_upload_file_btn.prev(),
						$selected_image_size = $('.attachment-display-settings .size').val(),
						fvalue               = ( 'id' === $input_field.data('fvalue') ? attachment.id : attachment.sizes[$selected_image_size].url );
					// $parent = $active_upload_file_btn.parents('.give-field-wrap'),
					// $image_container = $('.give-image-thumb', $parent );

					$input_field.val(fvalue);

					// Show image.
					// if ( $image_container.length ) {
					// 	$image_container.find('img').attr( 'src', attachment.sizes[ $selected_image_size ].url );
					// 	$image_container.removeClass( 'give-hidden' );
					// }
				});

				// When an image is selected in the media $upload_image_frame...
				give_media_uploader.on('open', function () {
					$('a.media-menu-item').each(function () {
						switch ($(this).text().trim()) {
							case 'Create Gallery':
							case 'Insert from URL':
								$(this).hide();
						}
					});
				});

				// Hide necessary settings.
				$('body').on('click', '.thumbnail', function (e) {
					var $attachment_display_setting = $('.attachment-display-settings');

					if ($attachment_display_setting.length) {
						$('.alignment', $attachment_display_setting).closest('label').hide();
						$('.link-to', $attachment_display_setting).closest('label').hide();
						$('.attachment-details label').hide();
					}

				});

				// Open the uploader dialog
				give_media_uploader.open();

				return false;
			});
		})
	};

	// Repeater fields.
	var give_repeater_fields = {
		init: function () {
			this.setup_repeatable_fields();
			this.handle_repeater_group_events();
		},

		/**
		 * Setup repeater field.
		 */
		setup_repeatable_fields: function () {
			jQuery(function () {
				jQuery('.give-repeatable-field-section').each(function () {
					var $this = $(this);

					// Note: Do not change option params, it can break repeatable fields functionality.
					var options = {
						wrapper                       : '.give-repeatable-fields-section-wrapper',
						container                     : '.container',
						row                           : '.give-row',
						add                           : '.give-add-repeater-field-section-row',
						remove                        : '.give-remove',
						move                          : '.give-move',
						template                      : '.give-template',
						confirm_before_remove_row     : true,
						confirm_before_remove_row_text: give_form_api_var.confirm_before_remove_row_text,
						is_sortable                   : true,
						before_add                    : null,
						after_add                     : handle_metabox_repeater_field_row_count,
						//after_add:  after_add, Note: after_add is internal function in repeatable-fields.js. Uncomment this can cause of js error.
						before_remove                 : null,
						after_remove                  : handle_metabox_repeater_field_row_remove,
						sortable_options              : {
							placeholder: "give-ui-placeholder-state-highlight",
							start      : function (event, ui) {
								$('body').trigger('repeater_field_sorting_start', [ui.item]);
							},
							stop       : function (event, ui) {
								$('body').trigger('repeater_field_sorting_stop', [ui.item]);
							},
							update     : function (event, ui) {
								// Do not allow any row at position 0.
								if (ui.item.next().hasClass('give-template')) {
									ui.item.next().after(ui.item);
								}

								var $rows = $('.give-row', $this).not('.give-template');

								if ($rows.length) {
									var row_count = 1;
									$rows.each(function (index, item) {
										// Set name for fields.
										var $fields = $('.give-field, label', $(item));

										if ($fields.length) {
											$fields.each(function () {
												var $parent         = $(this).parents('.give-field-wrap'),
													$currentElement = $(this);

												$.each(this.attributes, function (index, element) {
													var old_class_name_prefix = this.value.replace(/\[/g, '_').replace(/]/g, ''),
														old_class_name        = old_class_name_prefix + '_field',
														new_class_name        = '',
														new_class_name_prefix = '';

													// Bailout.
													if (!this.value) {
														return;
													}

													// Reorder index.
													this.value            = this.value.replace(/\[\d+\]/g, '[' + (row_count - 1) + ']');
													new_class_name_prefix = this.value.replace(/\[/g, '_').replace(/]/g, '');

													// Update class name.
													if ($parent.hasClass(old_class_name)) {
														new_class_name = new_class_name_prefix + '_field';
														$parent.removeClass(old_class_name).addClass(new_class_name);
													}

													// Update field id.
													if (old_class_name_prefix == $currentElement.attr('id')) {
														$currentElement.attr('id', new_class_name_prefix);
													}
												});
											});
										}

										row_count++;
									});

									// Fire event.
									$this.trigger('repeater_field_row_reordered', [ui.item]);
								}
							}
						}
						//row_count_placeholder: '{{row-count-placeholder}}' Note: do not modify this param otherwise it will break repeatable field functionality.
					};

					jQuery(this).repeatable_fields(options);
				});
			});
		},

		/**
		 * Handle repeater field events.
		 */
		handle_repeater_group_events: function () {
			var $repeater_fields = $('.give-repeatable-field-section'),
				$body            = $('body');

			// Auto toggle repeater group
			$body.on('click', '.give-row-head button', function () {
				var $parent = $(this).closest('tr');
				$parent.toggleClass('closed');
				$('.give-row-body', $parent).toggle();
			});

			// Reset header title when new row added.
			$repeater_fields.on('repeater_field_new_row_added repeater_field_row_deleted repeater_field_row_reordered', function () {
				handle_repeater_group_add_number_suffix($(this));
			});

			// Disable editor when sorting start.
			$body.on('repeater_field_sorting_start', function (e, row) {
				var $textarea = $('.wp-editor-area', row);

				if ($textarea.length) {
					$textarea.each(function (index, item) {
						window.setTimeout(
							function () {
								tinyMCE.execCommand('mceRemoveEditor', true, $(item).attr('id'));
							},
							300
						);
					});
				}
			});

			// Enable editor when sorting stop.
			$body.on('repeater_field_sorting_stop', function (e, row) {
				var $textarea = $('.wp-editor-area', row);

				if ($textarea.length) {
					$textarea.each(function (index, item) {
						window.setTimeout(
							function () {
								var textarea_id = $(item).attr('id');
								tinyMCE.execCommand('mceAddEditor', true, textarea_id);

								// Switch editor to tmce mode to fix some glitch which appear when you reorder rows.
								window.setTimeout(function () {
									// Hack to show tmce mode.
									switchEditors.go(textarea_id, 'html');
									$('#' + textarea_id + '-tmce').trigger('click');
								}, 100);
							},
							300
						);
					});
				}
			});

			// Process jobs on document load for repeater fields.
			$repeater_fields.each(function (index, item) {
				// Reset title on document load for already exist groups.
				var $item = $(item);
				handle_repeater_group_add_number_suffix($item);

				// Close all tabs when page load.
				if (parseInt($item.data('close-tabs'))) {
					$('.give-row-head button', $item).trigger('click');
					$('.give-template', $item).removeClass('closed');
					$('.give-template .give-row-body', $item).show();
				}
			});

			// Setup colorpicker field when row added.
			$repeater_fields.on('repeater_field_new_row_added', function (e, container, new_row) {
				$('.give-colorpicker', $(this) ).give_colorpicker_fields();
				$('.give-media-upload', $(this) ).give_media_fields();

				// Load WordPress editor by ajax..
				var wysiwyg_editor_container = $('div[data-wp-editor]', new_row);

				if (wysiwyg_editor_container.length) {
					wysiwyg_editor_container.each(function (index, item) {
						var $item                = $(item),
							wysiwyg_editor       = $('.wp-editor-wrap', $item),
							textarea             = $('textarea', $item),
							textarea_id          = 'give_wysiwyg_unique_' + Math.random().toString().replace('.', '_'),
							wysiwyg_editor_label = wysiwyg_editor.prev();

						textarea.attr('id', textarea_id);

						$.post(
							ajaxurl,
							{
								action       : 'give_load_wp_editor',
								wp_editor    : $item.data('wp-editor'),
								wp_editor_id : textarea_id,
								textarea_name: $('textarea', $item).attr('name')
							},
							function (res) {
								wysiwyg_editor.remove();
								wysiwyg_editor_label.after(res);

								// Get default setting from already initialize editor.
								var mceInit = tinyMCEPreInit.mceInit[Object.keys(tinyMCEPreInit.mceInit)[0]],
									qtInit = tinyMCEPreInit.qtInit[Object.keys(tinyMCEPreInit.qtInit)[0]];

								// Setup qt data for editor.
								tinyMCEPreInit.qtInit[textarea.attr('id')] = $.extend(
									true,
									qtInit,
									{id: textarea_id}
								);

								// Setup mce data for editor.
								tinyMCEPreInit.mceInit[textarea_id] = $.extend(
									true,
									mceInit,
									{
										body_class: textarea_id + ' locale-' + mceInit['wp_lang_attr'].toLowerCase(),
										selector  : '#' + textarea_id
									}
								);

								// Setup editor.
								tinymce.init(tinyMCEPreInit.mceInit[textarea_id]);
								quicktags(tinyMCEPreInit.qtInit[textarea_id]);
								QTags._buttonsInit();

								window.setTimeout(function () {
									// Hack to show tmce mode.
									switchEditors.go(textarea_id, 'html');
									$('#' + textarea_id + '-tmce').trigger('click');
								}, 100);

								if (!window.wpActiveEditor) {
									window.wpActiveEditor = textarea_id;
								}
							}
						);
					});
				}

			});

		}
	};

	/**
	 * Handle row count and field count for repeatable field.
	 */
	var handle_metabox_repeater_field_row_count = function (container, new_row) {
		var row_count  = $(container).attr('data-rf-row-count'),
			$container = $(container),
			$parent    = $container.parents('.give-repeatable-field-section');

		row_count++;

		// Set name for fields.
		$('*', new_row).each(function () {
			$.each(this.attributes, function (index, element) {
				this.value = this.value.replace('{{row-count-placeholder}}', row_count - 1);
			});
		});

		// Set row counter.
		$(container).attr('data-rf-row-count', row_count);

		// Fire event: Row added.
		$parent.trigger('repeater_field_new_row_added', [container, new_row]);
	};

	/**
	 * Handle row remove for repeatable field.
	 */
	var handle_metabox_repeater_field_row_remove = function (container) {
		var $container = $(container),
			$parent    = $container.parents('.give-repeatable-field-section'),
			row_count  = $(container).attr('data-rf-row-count');

		// Reduce row count.
		$container.attr('data-rf-row-count', --row_count);

		// Fire event: Row deleted.
		$parent.trigger('repeater_field_row_deleted');
	};

	/**
	 * Add number suffix to repeater group.
	 */
	var handle_repeater_group_add_number_suffix = function ($parent) {
		// Bailout: check if auto group numbering is on or not.
		if (!parseInt($parent.data('group-numbering'))) {
			return;
		}

		var $header_title_container = $('.give-row-head h2 span', $parent),
			header_text_prefix      = $header_title_container.data('header-title');

		$header_title_container.each(function (index, item) {
			var $item = $(item);

			// Bailout: do not rename header title in fields template.
			if ($item.parents('.give-template').length) {
				return;
			}

			$item.html(header_text_prefix + ': ' + index);
		});
	};



	$(document).ready(function () {
		var $reveal_forms  = $('.give-display-style-reveal'),
			$modal_forms   = $('.give-display-style-modal'),
			$button_forms  = $('.give-display-style-button'),
			$stepper_forms = $('.give-display-style-stepper'),
			$media_upload_btn = $('.give-media-upload'),
			$colorpicker_fields = $('.give-colorpicker');

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

		if( $colorpicker_fields.length ) {
			$colorpicker_fields.give_colorpicker_fields();
		}

		if( $media_upload_btn.length ) {
			$media_upload_btn.give_media_fields();
		}

		if( $('.give-repeater-field-wrap').length ){
			give_repeater_fields.init();
			$('button.give-add-repeater-field-section-row').on( 'click', function(e){ e.preventDefault(); });
		}
	})
}(jQuery));