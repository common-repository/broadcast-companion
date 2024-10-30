jQuery(document).ready(function($) {

    jQuery('#tabs').tabs();

    jQuery(document).on("click", "#bc-refresh-token-submit", function(a) {
        jQuery("#bc-refresh-token").val("1");
    });

    $(document).on('click', '.bc-upsell-notice button', function( event ) {
        data = {
            action : 'bc_twitch_display_dismissible_admin_notice',
        };
 
    $.post(ajaxurl, data, function (response) {
            console.log(response, 'DONE!');
        });
    });
});