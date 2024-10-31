jQuery(document).ready(function($) {

	var savingLabel = $('#position-save-cue');
	savingLabel.hide();

	$('div.push-monkey-us-notice .close-btn a').click(function() {

		$('div.push-monkey-us-notice').fadeOut();
		CookieManager.setCookie('push_monkey_us_notice', true, 8);
	});

	$('div.push-monkey-welcome-notice .close-btn a').click(function(){

		$('div.push-monkey-welcome-notice').fadeOut();
		CookieManager.setCookie('push_monkey_welcome_notice', true, 60);
	}); 

	var initialText = $('#push_monkey_preview_title').text();
	$('.push-monkey input#custom-text').on("change keyup paste", function(){

		var el = $(this);
		var value = el.val();
		if (!value.length) {

			value = initialText;
		}
		$('#push_monkey_preview_title').html(value);
	});
	// accordion icon change.
	$( '.accordion-toggle' ).on( 'click', function() {
		var span = $( this ).find( 'span' );
		if ( span.hasClass( 'glyphicon-plus' ) ) {
			span.removeClass( 'glyphicon-plus' ).addClass( 'glyphicon-minus' );
		} else if( span.hasClass( 'glyphicon-minus' ) ) {
			span.removeClass( 'glyphicon-minus' ).addClass( 'glyphicon-plus' );
		}
	} );
});

var CookieManager = {};
CookieManager.setCookie = function(name, value, days) {

    var expires;
    if (days) {

        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {

        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

CookieManager.getCookie = function(c_name) {

    if (document.cookie.length > 0) {

        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {

            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {

                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return false;
}