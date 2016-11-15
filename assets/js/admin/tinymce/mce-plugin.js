/**
 * Give TinyMCE plugin.
 */
(function (tinymce) {

	tinymce.PluginManager.add('give_shortcode', function (editor, url) {

		/**
		 * Add Command.
		 */
		editor.addCommand('Give_Shortcode', function () {
			if (window.scForm) {
				window.scForm.open(editor.id);
			}
		});

		/**
		 * Replace from shortcode to an image placeholder.
		 */
		editor.on('BeforeSetcontent', function (event) {
			event.content = give_replace_shortcode(event.content);
		});

		/**
		 * Replace from image placeholder to shortcode.
		 */
		editor.on('GetContent', function (event) {
			event.content = give_restore_shortcode(event.content);
		});

		/**
		 * Open popup on placeholder double click.
		 */
		editor.on('DblClick', function (e) {
			var cls = e.target.className.indexOf('wp-give-shortcode');
			if (e.target.nodeName == 'IMG' && e.target.className.indexOf('wp-give-shortcode') > -1) {

				editor.execCommand('Give_Shortcode', '', {
					shortcode: 'give_form',
				});

			}
		});


		/**
		 * Give replace shortcode with HTML placeholder image.
		 *
		 * @param content
		 * @returns {XML|*|string|void}
		 */
		function give_replace_shortcode(content) {
			return content.replace(/\[give_form([^\]]*)\]/g, function (all, attr, con) {
				return give_shortcode_html('wp-give-shortcode', attr, con);
			});
		}


		/**
		 * Restore Shortcodes
		 */
		function give_restore_shortcode(content) {
			return content.replace(/(?:<p(?: [^>]+)?>)*(<img [^>]+>)(<\/p>)*/g, function (match, image) {
				var data = give_get_attr(image, 'data-give-form-attr');

				if (data) {
					return '<p>[give_form' + data + ']</p>';
				}
				return match;
			});
		}

		/**
		 * HTML that Replaces Raw Shortcode.
		 *
		 * @param cls The class name.
		 * @param data
		 * @param con
		 * @returns {string}
		 */
		function give_shortcode_html(cls, data, con) {
			var placeholder = url + '/img/give-shortcode-placeholder-640x239.png';
			var placeholder_2x = url + '/img/give-shortcode-placeholder-1280x477.png';
			data = window.encodeURIComponent(data);

			return '<img srcset="' + placeholder + ', ' + placeholder_2x + ' 2x" src="' + placeholder + '" class="mceItem ' + cls + '" ' + 'data-give-form-attr="' + data + '" data-mce-resize="false" data-mce-placeholder="1" />';
		}

		/**
		 * Helper function.
		 *
		 * @param s
		 * @param n
		 * @returns {string}
		 */
		function give_get_attr(s, n) {
			n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
			return n ? window.decodeURIComponent(n[1]) : '';
		}


	});

})(window.tinymce);
