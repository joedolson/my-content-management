var mediaPopup = '';
(function ($) {
	"use strict";
	$(function () {
		/**
		 * Clears any existing Media Manager instances
		 * 
		 * @author Gabe Shackle <gabe@hereswhatidid.com>
		 * @modified Joe Dolson <plugins@joedolson.com>
		 * @return void
		 */
		function clear_existing() {
			if ( typeof mediaPopup !== 'string' ) {
				mediaPopup.detach();
				mediaPopup = '';
			}
		}
		$('.mcm_post_fields')
				.on( 'click', '.textfield-field', function(e) {
					e.preventDefault();
					var $self = $(this),
							$inpField = $self.parent('.field-holder').find('input.textfield'),
							$displayField = $self.parent('.field-holder').find('.selected');
					clear_existing();
					mediaPopup = wp.media( {
						multiple: false, // add, reset, false
						title: 'Choose an Uploaded Document',
						button: {
							text: 'Select'
						}
					} );

					mediaPopup.on( 'select', function() {
						var selection = mediaPopup.state().get('selection'),
							id = '',
							img = '',
							height = '',
							width = '';
						if( selection ) {
							id = selection.first().attributes.id;
							height = mcm_images.thumbsize;
							width = ( ( selection.first().attributes.width )/( selection.first().attributes.height ) )*height;
							img = "<img src='"+selection.first().attributes.url+"' width='"+width+"' height='"+height+"' />";
							$inpField.val( id );
							$displayField.html( img );
						}
					});
					mediaPopup.open();
				})
		});
	})(jQuery);