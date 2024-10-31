( function( $ ) {
    // Show ajax loader.
    var showAjaxLoader = function( show = false ) {
        if ( show ) {
            $( '.pm-loader' ).show();
        } else {
            $( '.pm-loader' ).hide();
        }
    };

    /**
     * Get woocommerce setting API.
     * @param  {Boolean} status  Status
     * @param  {String}  element HTML Element.
     */
    var refreshData = function( status = false, element = '' ) {
        if ( $( element ).length <= 0 ) {
            return;
        }
        var statusText = $( element ).find( '.pm-woo-status-text' );
        var pmWooStatus = $( element ).find( '.pm-woo-status' );
        if ( status ) {
            statusText.removeClass( 'bg-danger' ).addClass( 'bg-success' ).html( statusText.data( 'action-text' ) );
            pmWooStatus.removeClass( 'btn-success' ).addClass( 'btn-danger' ).html( '<i class="fa fa-remove"></i>' + pmWooStatus.data( 'disable' ) );
        } else {
            statusText.removeClass( 'bg-success' ).addClass( 'bg-danger' ).html( statusText.data( 'inaction-text' ) );
            pmWooStatus.removeClass( 'btn-danger' ).addClass( 'btn-success' ).html( '<i class="fa fa-mail-reply"></i>' + pmWooStatus.data( 'enable' ) );
        }
    };

    /**
     * Get woocommerce settings.
     * @param  {String} slug API Base Slug.
     */
    var getWooService = function( slug = '' ) {
        showAjaxLoader( true );
        // Send request.
        $.ajax( {
            url: PM_Woo.api.replace( '%slug%', slug ),
            dataType: 'json',
            beforeSend: function ( jqXHR, settings ) {
                jqXHR.setRequestHeader( 'push-monkey-api-key', atob( PM_Woo.rk ) );
            },
            data: {
                account_key: atob( PM_Woo.account_key )
            },
            success: function( data, textStatus, jqXHR ) {
                $.each( data, function( key, value ) {
                 refreshData( value, '#' + key );
             } );
                showAjaxLoader( false );
            },
        } ).fail (function() {
            showAjaxLoader( false );
            alert( 'Something went. Please try again later.' );
        } );
    };

    /**
     * Update Woo Service.
     * @param  {object} data API Data.
     * @param  {String} slug API Base Slug.
     */
    var updateWooService = function( data, slug = '' ) {
        $.post( PM_Woo.api.replace( '%slug%', slug ) + '?account_key=' + atob( PM_Woo.account_key ), data, function( data ) {
            $.each( data, function( key, value ) {
                refreshData( value, '#' + key );
            } );
        }, 'json' )
        .fail (function() {
            showAjaxLoader( false );
            alert( 'Something went. Please try again later.' );
        } );
    };

    // Click to update woo service.
    $( 'a[data-key]' ).on( 'click', function() {
        var wooSetting = [];
        var status = $( this ).find( 'i' ).hasClass( 'fa-remove' ) ? false : true;
        var objectKey = $( this ).data( 'key' );
        wooSetting[objectKey] = status;
        wooSetting = Object.assign( {}, wooSetting );
        // Enable ajax load.
        $( this ).find( 'i' ).removeClass( 'fa-mail-reply fa-remove' ).addClass( 'fa-refresh fa-spin' );
        // Send request.
        updateWooService( wooSetting, 'services' );
    } );
    // End woocommerce setting.
    // Init functions.
    getWooService( 'services' );
} )( jQuery );