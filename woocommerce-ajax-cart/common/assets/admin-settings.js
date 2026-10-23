(function($){
    "use strict";

    // WordPress's admin pages navigate with view transitions; when one is skipped
    // (a form submit, a quick click away) the browser rejects its promise and nothing
    // catches it, so "Transition was skipped" lands in the console. It is harmless:
    // keep that one rejection, and only that one, out of the console.
    window.addEventListener('unhandledrejection', function (event) {
        var reason = event.reason;
        if ( reason && /Transition was skipped/.test(String(reason.message || reason)) ) {
            event.preventDefault();
        }
    });
    
    var listenSubConfChange = function() {
        $('.check-subconf').on('change', function(){
            var subConfDiv = $(this).parent().parent().find('.mh-subconf');

            // when subconf div is empty, hide them
            if ( subConfDiv.length && subConfDiv.html().trim().length === 0 ) {
                subConfDiv.detach();
            }

            if ( $(this).is(':checked') ) {
                $( subConfDiv ).slideDown();
            }
            else {
                $( subConfDiv ).slideUp();
            }
        }).trigger('change');
    };


    jQuery(document).ready(function($){
        
        listenSubConfChange();

        // display active tab
        $('#settings-' + wac_tab_current).show();
    });

})(jQuery);
