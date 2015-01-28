jQuery(document).ready(function($) {
    $(".onetouch-request").onetouch({
        url: AuthyAjax.ajaxurl
    });
    $("#authy-onetouch-btn").on("click", function(){
        $("#authy-onetouch").css('display', 'none');
        $("#authy").css('display', 'inline-block');
    });
});

(function($) {
 
    $.fn.onetouch = function( options ) {
        var settings = $.extend( {}, $.fn.onetouch.defaults, options );

        return this.each(function() {
            var $this = $(this);
            form_data = $.extend( {}, $.fn.get_form_data(), { action: settings.action } );
            $.fn.onetouch.poll($this, settings, form_data);
        });
    };

    $.fn.onetouch.defaults = {
        action: "onetouch_status_ajax",
        url: "",
        poll_timeout: 2000
    };

    $.fn.onetouch.poll = function($this, settings, form_data){
        var redirect_status = ['approved', 'denied', 'expired'];
        $.ajax({
            url: settings.url,
            data: (form_data),
            method: 'get',
            success: function(response, http_status, jqXHR) {
                var data = $.parseJSON( response );
                var should_redirect = $.inArray(data.status, redirect_status) >= 0;
                if( should_redirect && ! $.fn.wp_auth_check() ) {
                    window.location.replace(data.redirect_to);                    
                }
            },
            error: function( jqXHR, status, error ) { 
                if ( typeof console != "undefined" ) {
                    console.log("Error !!!!", error)
                }
            },
            complete: function( jqXHR, msg ) {
                setTimeout( function(){ $.fn.onetouch.poll($this, settings, form_data); }, settings.poll_timeout );
            }
        });
    };

    $.fn.get_form_data = function() {
        var form_data = {};
        $.each( $("#authy").serializeArray(), function(){
            if ( this.name !== undefined ) {
              form_data[this.name] = this.value || '';
            }
        });
        return form_data;
    }

    $.fn.wp_auth_check = function() {
        $wp_auth_check_wrap = $( '#wp-auth-check-wrap', window.parent.document );
        if ( $wp_auth_check_wrap && $wp_auth_check_wrap.is( ':visible' ) ) {
            $wp_auth_check_wrap.addClass('hidden').css('display', '');
            $( '#wp-auth-check-frame', window.parent.document ).remove();
            $( 'body', window.parent.document ).removeClass( 'modal-open' );
            return true;
        }
        return false;
    }
 
}(jQuery));
