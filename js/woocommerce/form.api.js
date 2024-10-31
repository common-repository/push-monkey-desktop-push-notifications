( function( $ ) {
	// Show ajax loader
	var showLoader = function( show = false ) {
		if ( show ) {
			$( '.pm-loader' ).show();
		} else {
			$( '.pm-loader' ).hide();
		} 
	};

	/**
	 * Init onload functions.
	 */
	 var initWooSettings = function() {
	 	AbandonedCart();
	 	WelComeDiscount();
	 	ReviewReminder();
	 	BackInStock();
	 	PriceDrop();
	 	UpdateWooSettings();
	 };

	/**
	 * Ajax fail message.
	 */
	 var ajaxFailMessage = function() {
	 	showLoader( false );
	 	$( 'form' ).find( 'input[type="submit"]' ).removeAttr( 'disabled' );
	 	alert( 'Something went. Please try again later.' );
	 }

	/**
	 * Get wooCommerce settings.
	 * @param { string } slug API Base slug
	 */
	 var getSettings = function( slug ) {
	 	showLoader( true );
	 	var getRequest = $.ajax( {
	 		dataType: "json",
	 		beforeSend: function ( jqXHR, settings ) {
				jqXHR.setRequestHeader( 'push-monkey-api-key', atob( PM_Woo.rk ) );
			},
	 		url: PM_Woo.api.replace( '%slug%', slug ),
	 		data: {
	 			account_key: atob( PM_Woo.account_key )
	 		},
	 	} );
	 	return getRequest;
	 };

	/**
	 * Update setting.
	 * @param { string } form_id Form element.
	 * @param { string } slug API Base slug.
	 */
	 var updateSettings = function( form_id = '', slug = '' ) {
		// Show ajax loader.
		showLoader( true );
		// Get form object.
		form_id = '#' + form_id;
		var formData = new FormData( $( form_id )[0] );
		var apiUrl = PM_Woo.api.replace( '%slug%', slug );
		apiUrl += '?account_key=' + atob( PM_Woo.account_key );
		var checkbox = $( form_id ).find( 'input[type=checkbox]' );
		$.each( checkbox, function( key, val ) {
			formData.delete( $( this ).attr( 'name' ) );
			formData.append( $( this ).attr( 'name' ), $( this ).is( ':checked' ) );
		} );
		// Send API request.
		return $.ajax( {
			url: apiUrl,
			type: 'post',
			beforeSend: function ( jqXHR, settings ) {
				jqXHR.setRequestHeader( 'push-monkey-api-key', atob( PM_Woo.rk ) );
			},
			data: formData,
			contentType: false,
			processData: false
		} );
		};


	/**
	 * Get Abandoned Cart data.
	 * @param {object} data Ajax response.
	 */
	 var AbandonedCart = function( data = null ) {
	 	if ( $( '#pm_abandoned_cart' ).length <= 0 ) {
	 		return;
	 	}
		var getAbandonedCart = data || getSettings( 'abandoned-cart' );
		getAbandonedCart.done( function( result ) {
			showLoader( false );
			// Checkbox
			$( 'input[name="active"]' ).attr( 'checked', result.active );
			$( 'input[name="second_notification_sent_after_status"]' ).attr( 'checked', result.second_notification_sent_after_status );
			$( 'input[name="third_notification_sent_after_status"]' ).attr( 'checked', result.third_notification_sent_after_status );
			// Inputs
			$( 'input[name="first_notification_sent_after"]' ).val( result.first_notification_sent_after );
			$( 'input[name="second_notification_sent_after"]' ).val( result.second_notification_sent_after );
			$( 'input[name="third_notification_sent_after"]' ).val( result.third_notification_sent_after );
			$( 'input[name="notification_title"]' ).val( result.notification_title );
			$( 'input[name="notification_message"]' ).val( result.notification_message );
			if ( result.notification_image != null ) {
				$( '#preview_image' ).show().find( 'img' ).attr( 'src', PM_Woo.image_url + result.notification_image );
			}
			$( 'form' ).find( 'input[type="submit"]' ).removeAttr( 'disabled' );
			showLoader( false );
		} ).fail( ajaxFailMessage );
		
	};

	/**
	 * Get welcome discount data.
	 * @param {object} data Ajax response.
	 */
	 var WelComeDiscount = function( data = null ) {
	 	if ( $( '#welcome_notification' ).length <= 0 ) {
	 		return;
	 	}
	 	var getWelComeDiscount = data || getSettings( 'welcome-discount' );
	 	getWelComeDiscount.done( function( result ) {
	 		showLoader( false );
			// Checkbox
			$( 'input[name="active"]' ).attr( 'checked', result.active );
			// Inputs
			$( 'input[name="custom_message"]' ).val( result.custom_message );
			$( 'input[name="welcome_link"]' ).val( result.welcome_link );
			if ( result.welcome_image != null ) {
				$( '#preview_image' ).show().find( 'img' ).attr( 'src', PM_Woo.image_url + result.welcome_image );
			}
			$( 'form' ).find( 'input[type="submit"]' ).removeAttr( 'disabled' );
			showLoader( false );
		} ).fail( ajaxFailMessage );

	 };

	/**
	 * Get welcome discount data.
	 * @param {object} data Ajax response.
	 */
	 var ReviewReminder = function( data = null ) {
	 	if ( $( '#review_reminder' ).length <= 0 ) {
	 		return;
	 	}
	 	var getReviewReminder = data || getSettings( 'review-reminder' );
	 	getReviewReminder.done( function( result ) {
	 		showLoader( false );
			// Checkbox
			$( 'input[name="active"]' ).attr( 'checked', result.active );
			// Inputs
			$( 'input[name="notification_title"]' ).val( result.notification_title );
			$( 'input[name="notification_delay"]' ).val( result.notification_delay );
			$( 'input[name="notification_message"]' ).val( result.notification_message );
			$( 'form' ).find( 'input[type="submit"]' ).removeAttr( 'disabled' );
			showLoader( false );
		} ).fail( ajaxFailMessage );

	 };

	/**
	 * Back In Stock Notification Settings.
	 * @param {object} data Ajax response.
	 */
	 var BackInStock = function( data = null ) {
	 	if ( $( '#back_in_stock' ).length <= 0 ) {
	 		return;
	 	}
	 	var getbackInStock = data || getSettings( 'back-in-stock' );
	 	getbackInStock.done( function( result ) {
	 		showLoader( false );
			// Checkbox
			$( 'input[name="active"]' ).attr( 'checked', result.active );
			// Inputs
			$( 'input[name="notification_title"]' ).val( result.notification_title );
			$( 'input[name="pop_up_message"]' ).val( result.pop_up_message );
			$( 'input[name="color"]' ).val( result.color );
			$( '#colorpicker' ).find( 'i' ).css( 'background-color', result.color );
			$( 'input[name="pop_up_title"]' ).val( result.pop_up_title );
			$( 'input[name="notification_message"]' ).val( result.notification_message );
			$( 'input[name="button_text"]' ).val( result.button_text );
			$( 'input[name="success_message"]' ).val( result.success_message );
			$( 'form' ).find( 'input[type="submit"]' ).removeAttr( 'disabled' );
			$( '#position' ).val( result.position );
			showLoader( false );
		} ).fail( ajaxFailMessage );

	};

	/**
	 * Price Drop Notification Settings.
	 * @param {object} data Ajax response.
	 */
	 var PriceDrop = function( data = null ) {
	 	if ( $( '#pm_woo_price_drop' ).length <= 0 ) {
	 		return;
	 	}
	 	var getPriceDrop = data || getSettings( 'price-drop' );
	 	getPriceDrop.done( function( result ) {
	 		showLoader( false );
			// Checkbox
			$( 'input[name="active"]' ).attr( 'checked', result.active );
			// Inputs
			$( 'input[name="notification_title"]' ).val( result.notification_title );
			$( 'input[name="pop_up_message"]' ).val( result.pop_up_message );
			$( 'input[name="color"]' ).val( result.color );
			$( '#colorpicker' ).find( 'i' ).css( 'background-color', result.color );
			$( 'input[name="pop_up_title"]' ).val( result.pop_up_title );
			$( 'input[name="notification_message"]' ).val( result.notification_message );
			$( 'input[name="button_text"]' ).val( result.button_text );
			$( 'input[name="success_message"]' ).val( result.success_message );
			$( 'form' ).find( 'input[type="submit"]' ).removeAttr( 'disabled' );
			$( '#position' ).val( result.position );
			showLoader( false );
		} ).fail( ajaxFailMessage );

	};

	/**
	 * Update settings.
	 */
	var UpdateWooSettings = function() {
		$( '#pm_abandoned_cart, #welcome_notification, #review_reminder, #back_in_stock, #pm_woo_price_drop' ).on( 'submit', function() {
			var formID = $( this ).attr( 'id' );
			$( this ).attr( 'disabled', 'disabled' );
			if ( 'pm_abandoned_cart' === formID ) {
				var updateResponse = updateSettings( formID, 'abandoned-cart' );
				AbandonedCart( updateResponse );
			} else if ( 'welcome_notification' === formID ) {
				var updateResponse = updateSettings( formID, 'welcome-discount' );
				WelComeDiscount( updateResponse );
			} else if ( 'review_reminder' === formID ) {
				var updateResponse = updateSettings( formID, 'review-reminder' );
				ReviewReminder( updateResponse );
			} else if ( 'back_in_stock' === formID ) {
				var updateResponse = updateSettings( formID, 'back-in-stock' );
				BackInStock( updateResponse );
			} else if ( 'pm_woo_price_drop' == formID ) {
				var updateResponse = updateSettings( formID, 'price-drop' );
				PriceDrop( updateResponse );
			}
			return false;
		} );
	};
	// Init functions.
	initWooSettings();
} )( jQuery );