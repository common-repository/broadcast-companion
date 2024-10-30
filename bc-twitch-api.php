<?php
/**
 * Twitch API Class
 *
 * @since       2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BC_Twitch_API' ) ) {

	class BC_Twitch_API {

		private $api_url = 'https://api.twitch.tv/helix/';
		private $token_url = 'https://id.twitch.tv/oauth2/token';
		private $game_url = 'https://api.twitch.tv/helix/games?name=';
		private $team_url = 'https://api.twitch.tv/helix/teams?name=';
		private $game;
		private $team;
		private $client_id;
		private $client_secret;
		private $token;
		private $debug = false;

		public function __construct() {
		}

		public function refresh_token() {
            delete_transient( 'bc_twitch_auth_token' );
            delete_transient( 'bc_twitch_auth_token_expires' );
		}

		public function get_token($clientId="",$clientSecret="") {
			$token = get_transient( 'bc_twitch_auth_token' );
            $expires = get_transient( 'bc_twitch_auth_token_expires' );
			$clientIdVar = ($clientId !== '' ? $clientId : $this->client_id);
			$clientSecretVar = ($clientSecret !== '' ? $clientSecret : $this->client_secret);

			if ( $expires !== false ) {
				return array($token, $expires);
			}

			$args = [
				'client_id' => $clientIdVar,
				'client_secret' => $clientSecretVar,
				'grant_type' => 'client_credentials'
			];

			$headers = [
				'Content-Type' => 'application/json'
			];

			$response = wp_remote_post( $this->token_url, [
				'headers' => $headers,
				'body'    => wp_json_encode( $args ),
				'timeout' => 15
			]);

			if ( is_wp_error( $response ) ) {
				if ( is_array( $response ) || is_object( $response ) ) {
					error_log( print_r( $response, true ) );
				} else {
					error_log( $response );
				}
				return false;
			}

			$result = wp_remote_retrieve_body( $response );
			$result = json_decode( $result, true );

			if ( $result === false || !isset( $result['access_token'] ) ) {
				delete_transient( 'bc_twitch_auth_token' );
				delete_transient( 'bc_twitch_auth_token_expires' );
				return array($result['message'], '');
			}
			
			$token = $result['access_token'];
            $expires = $result['expires_in'];
			$today = time();
			$todayPlusExpires = $today + $expires;
			$expiresDate = date('F j, Y', $todayPlusExpires);

			set_transient( 'bc_twitch_auth_token', $token, $result['expires_in'] - 30 );
            set_transient( 'bc_twitch_auth_token_expires', $expiresDate, $result['expires_in'] - 30 );

			return array($token, $expiresDate);
		}
	}
}


function bc_deactivate() {
    wp_clear_scheduled_hook( 'bc_cron' );
}
 
add_action('init', function() {
    add_action( 'bc_cron', 'bc_run_cron' );
    register_deactivation_hook( __FILE__, 'bc_deactivate' );
 
    if (! wp_next_scheduled ( 'bc_cron' )) {
        wp_schedule_event( time(), 'daily', 'bc_cron' );
    }
});
 
function bc_run_cron() {
	$expires = get_transient( 'bc_twitch_auth_token_expires' );
	$settings = (array) get_option( 'bc_options' );
	$clientId = $settings['bc_twitch_client_id'];
	$clientSecret = $settings['bc_twitch_client_secret'];
	
	if ( empty($expires) ) {
		error_log( 'twitch auth token not found' );
		if ( !empty($clientId) && !empty($clientSecret) ) {
			error_log( 'twitch auth token expired, running cron' );
			$BC_Cron_Twitch_API = new BC_Twitch_API();
			$BC_Cron_Twitch_API-> refresh_token();
			$result = $BC_Cron_Twitch_API->get_token( $clientId, $clientSecret );
			$BC_options = get_option( 'bc_options' );
			$BC_options['bc_twitch_auth_token'] = $result[0];
			$BC_options['bc_twitch_auth_token_expires'] = $result[1];
			update_option( 'bc_options', $BC_options );
		} else {
			error_log( 'client id and secret not found, skipping cron' );
		}
	
	} else {
		error_log( 'twitch auth token valid, skipping cron' );
	}
}