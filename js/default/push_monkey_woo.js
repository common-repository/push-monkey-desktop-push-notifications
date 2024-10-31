jQuery(document).ready(function($) {

    $('.push-monkey .push_monkey_woo_settings #push-monkey-abandoned-title, .push-monkey .push_monkey_woo_settings #push-monkey-abandoned-message')
    .unbind('keyup change input paste').bind('keyup change input paste',function(e){

      var $this = $(this);
      var val = $this.val();
      var valLength = val.length;
      var maxCount = $this.attr('maxlength');
      if(valLength > maxCount){

        $this.val($this.val().substring(0, maxCount));
      }
    });

    // Products sync.
	var nextPage = 1;
	var _pm_sync_products = function() {
		$( '#push-monkey-sync-products' ).removeAttr( 'disabled' );
		$( '.sync-product' ).removeAttr( 'style' );
		$( '.sync-product' ).css( { 'visibility': 'visible', 'opacity': 1, 'position' : 'absolute' } );
		var sendData = {
			action: 'push_monkey_products_sync_process',
			page: nextPage,
		};
		// Send sync request.
		var muprAjax = $.post( ajaxurl, sendData, function( response ) {
			if ( response.result == 1 && response.status == 'continue' ) {
				$( '.total-synced' ).html( response.total_synced + ' Product Synced' );
				nextPage = response.next_page;
				_pm_sync_products();
			} else if ( response.result == 1 && response.status == 'end' ) {
				nextPage = 1;
				$( '.sync-product' ).removeAttr( 'style' );
				$( '.sync-product' ).css( { 'visibility': 'none', 'opacity': 1, 'position' : 'absolute' } );
				$( '#push-monkey-sync-products' ).removeAttr( 'disabled' );
				return;
			} else {
				nextPage = 1;
				$( '.sync-product' ).removeAttr( 'style' );
				$( '.sync-product' ).css( { 'visibility': 'none', 'opacity': 1, 'position' : 'absolute' } );
				$( '#push-monkey-sync-products' ).removeAttr( 'disabled' );
			}
		}, 'json' ).fail( function() {
			nextPage = 1;
			$( '.sync-product' ).removeAttr( 'style' );
			$( '.sync-product' ).css( { 'visibility': 'none', 'opacity': 1, 'position' : 'absolute' } );
			$( '#push-monkey-sync-products' ).removeAttr( 'disabled' );
			alert( 'something went wrong please try again' );
		} );
	};
	// Click to send sync request.
	$( '#push-monkey-sync-products' ).on( 'click', function( event ) {
		_pm_sync_products();
		event.preventDefault();
	} );
})