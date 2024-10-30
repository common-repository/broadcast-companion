<?php
$settings = (array) get_option( 'bc_options' );
$field = "bc_twitch_auth_token_expires";

// activate plugin hook
function bc_twitch_create_custom_option() {
    update_option( 'bc_twitch-dismiss', true );
}
register_activation_hook( __FILE__, 'bc_twitch_create_custom_option' );

if (array_key_exists($field, $settings)) {
    $value = esc_attr( $settings[$field] );
} else {
    $value = '';
}

if( empty( $value ) ) {
    add_action( 'admin_notices', 'bc_twitch_error_notice' );
}

function bc_twitch_error_notice() {
    echo '<div class="notice error"><p>Twitch settings not configured! To hook your twitch up to the Broadcast Theme, please add your Twitch username, Client ID and Client Secret <a href="/wp-admin/admin.php?page=bc-companion">here.</a></div>';
}

// ajax stuff.
add_action( 'wp_ajax_bc_twitch_display_dismissible_admin_notice', 'bc_twitch_display_dismissible_admin_notice' );

function bc_twitch_display_dismissible_admin_notice() {
    echo "Processing Ajax request...";
    update_option( 'bc_twitch-dismiss', false );
    wp_die();
}