(function ($) {
    /**
	 * Order Notes Panel
	 */
	var wc_order_notes = {
		init: function() {
			$( 'section[data-order-notes]' )
				.on( 'click', function (event) {
					event.originalEvent.stopPropagation();
				} )
				.on( 'click', 'button.add_note', this.add_order_note )
				.on( 'click', 'a.delete_note', this.delete_order_note );
		},

		add_order_note: function(event) {
            event.originalEvent.stopPropagation();

            var $target = $( this );
            var $form = $target.closest( 'section' );
            var $textarea = $form.find( 'textarea#add_order_note' );

			if ( ! $textarea.val() ) {
				return;
			}

            var $select = $form.find( 'select#order_note_type' )
            var post_id = $target.closest( 'tr' ).attr( 'id' );

			post_id = post_id.replace( 'post-', '' );

			$form.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			var data = {
				action:    'woocommerce_add_order_note',
				post_id:   post_id,
				note:      $textarea.val(),
				note_type: $select.val(),
				security:  woocommerce_admin_meta_boxes.add_order_note_nonce
			};

			$.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {
				$form.find( 'ul.order_notes .no-items' ).remove();
				$form.find( 'ul.order_notes' ).prepend( response );
				$form.unblock();
				$form.find( '#add_order_note' ).val( '' );
				// window.wcTracks.recordEvent( 'order_edit_add_order_note', {
				// 	order_id: post_id,
				// 	note_type: data.note_type || 'private',
				// 	status: $( '#order_status' ).val()
				// } );
			});

			return false;
		},

		delete_order_note: function(event) {
            event.stopPropagation();
			if ( window.confirm( woocommerce_admin_meta_boxes.i18n_delete_note ) ) {
				var note = $( this ).closest( 'li.note' );

				$( note ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				var data = {
					action:   'woocommerce_delete_order_note',
					note_id:  $( note ).attr( 'rel' ),
					security: woocommerce_admin_meta_boxes.delete_order_note_nonce
				};

				$.post( woocommerce_admin_meta_boxes.ajax_url, data, function() {
					$( note ).remove();
				});
			}

			return false;
		}
	};

    wc_order_notes.init()
})(jQuery)
