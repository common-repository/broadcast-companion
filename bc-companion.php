<?php
/**
 * Plugin Name: Broadcast Companion (Twitch / YouTube / Kick)
 * Description: Unlock Twitch, YouTube and Kick functionality in Broadcast theme.
 * Version: 3.0.6
 * Author: StreamWeasels
 * Author URI: https://www.streamweasels.com
 */

 // includes
require_once(plugin_dir_path( __FILE__ ) . '/bc-twitch-api.php');

 // create the admin menu
add_action( 'admin_menu', 'bc_companion_menu' );
function bc_companion_menu() {
	
	add_menu_page(
		'Broadcast',
		'Broadcast Stream Integration',
		'manage_options',
		'bc-companion',
		'bc_companion_options',
        plugins_url( 'weasels-icon.png', __FILE__ )
	);

}

// enqueue admin scripts
add_action( 'admin_enqueue_scripts', 'bc_admin_js' );
function bc_admin_js() {
    // Enqueue scripts and styles for the tabs
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
	wp_enqueue_script( 'broadcast-companion-admin-js', plugins_url( 'bc-companion-admin.js', __FILE__ ), array('jquery'), '3.0.6', true );
	wp_enqueue_style( 'broadcast-companion-admin-css', plugins_url( 'bc-companion-admin.css', __FILE__ ), array(), '3.0.6', '' );	

}

// enqueue public scripts
add_action( 'wp_enqueue_scripts', 'bc_companion_js' );
function bc_companion_js() {
	$version = '3.0.5';
	$settings = (array) get_option( 'bc_options' );
	$settingsYouTube = (array) get_option( 'bcyt_companion_options' );
	$settingsKick = (array) get_option( 'bckt_companion_options' );
	$bcTwitchUsername = esc_attr( isset($settings["bc_twitch_username"]) ? $settings["bc_twitch_username"] : '' );
	$bcTwitchUserId = esc_attr( isset($settings["bc_twitch_id"]) ? $settings["bc_twitch_id"] : '' );
	$bcTwitchEmbed = esc_attr( isset($settings["bc_auto_embed"]) ? $settings["bc_auto_embed"] : '' );	
	$bcTwitchEmbedChat = esc_attr( isset($settings["bc_auto_embed_chat"]) ? $settings["bc_auto_embed_chat"] : '' );	
	$bcVideoSettings = esc_attr( isset($settings["bc_video_settings"]) ? $settings["bc_video_settings"] : '' );			
	$bcClipPeriod = esc_attr( isset($settings["bc_video_period"]) ? $settings["bc_video_period"] : '' );
	$clientIdVal = esc_attr( isset($settings["bc_twitch_client_id"]) ? $settings["bc_twitch_client_id"] : '' );
	$clientAuthTokenVal = esc_attr( isset($settings["bc_twitch_auth_token"]) ? $settings["bc_twitch_auth_token"] : '' );		
	$swPlaceholder = plugins_url( '300x169-placeholder.png', __FILE__);
	$bcytYouTubeID = esc_attr( isset($settingsYouTube["bcyt_channel_id"]) ? $settingsYouTube["bcyt_channel_id"] : '' );
	$bcytApiKey = esc_attr( isset($settingsYouTube["bcyt_api_key"]) ? $settingsYouTube["bcyt_api_key"] : '' );
	$bcktKickID = esc_attr( isset($settingsKick["bckt_channel_username"]) ? $settingsKick["bckt_channel_username"] : '' );
	if ($bcClipPeriod == 'month') {
		$periodDate = '&started_at='.date('Y-m-d', strtotime('-4 Week')).'T00:00:00Z&ended_at='.date('Y-m-d', strtotime('today')).'T00:00:00Z';
	} else if ($bcClipPeriod == 'week') {
		$periodDate = '&started_at='.date('Y-m-d', strtotime('-1 Week')).'T00:00:00Z';
	} else {
		$periodDate = '';
	}
	if ($bcTwitchUserId && $clientIdVal) {
		wp_enqueue_script('bc-companion-main', plugins_url( 'bc-companion-main.js', __FILE__ ), array('jquery'), '3.0.5', false );
		wp_enqueue_script( 'twitch-API', 'https://embed.twitch.tv/embed/v1.js', array( 'jquery' ), '', false );	
	} else if ($bcytYouTubeID && $bcytApiKey) {
		wp_enqueue_script('bc-companion-main', plugins_url( 'bc-companion-youtube.js', __FILE__ ), array('jquery'), '3.0.5', false );
	} else if ($bcktKickID) {
		wp_enqueue_script('bc-companion-main', plugins_url( 'bc-companion-kick.js', __FILE__ ), array('jquery'), '3.0.5', false );
	} else {
		$bcTwitchUserId = '0';
	}
	wp_add_inline_script( 'bc-companion-main', 'jQuery(document).ready(function(){bcTwitchUsername =  "'.$bcTwitchUsername.'";bcTwitchId =  "'.$bcTwitchUserId.'";bcTwitchEmbed = "'.$bcTwitchEmbed.'";bcTwitchEmbedChat = "'.$bcTwitchEmbedChat.'";bcVideoSettings = "'.$bcVideoSettings.'";bcClipPeriod = "'.$bcClipPeriod.'";bcClipPeriodDate = "'.$periodDate.'"; swPlaceholder = "'.$swPlaceholder.'"; bcTwitchClientId = "'.$clientIdVal.'"; bcTwitchClientAuthToken = "'.$clientAuthTokenVal.'"; bcytYouTubeID = "'. esc_attr($bcytYouTubeID) .'"; bcytApiKey = "'. esc_attr($bcytApiKey).'"; bcktKickID = "'.$bcktKickID.'"});', 'before');
}

// initialize plugin scripts
function bc_companion_admin_init() {
  
  /* 
	 * http://codex.wordpress.org/Function_Reference/register_setting
	 * register_setting( $option_group, $option_name, $sanitize_callback );
	 * The second argument ($option_name) is the option name. It’s the one we use with functions like get_option() and update_option()
	 * */
  	# With input validation:
  	# register_setting( 'my-settings-group', 'my-plugin-settings', 'my_settings_validate_and_sanitize' );    
  	register_setting( 'bc_options-group', 'bc_options', 'bc_companion_validate_and_sanitize' );
	
  	/* 
	 * http://codex.wordpress.org/Function_Reference/add_settings_section
	 * add_settings_section( $id, $title, $callback, $page ); 
	 * */	 
	add_settings_section( 'section-1', __( 'Twitch Settings', 'bc-companion' ), function() {echo 'This plugin requires an active Twitch Auth Token to work. <a href="https://support.streamweasels.com/article/12-how-to-setup-a-client-id-and-client-secret" target="_blank">Click here</a> to learn more about Twitch Auth Tokens.<br>Make sure both YouTube and Kick settings are blank if you intend to use Twitch Integration.';}, 'bc-companion' );
	  
	add_settings_section( 'section-2', __( 'Integration Settings', 'bc-companion' ), function() {echo 'Automatically embed your stream when youre online.';}, 'bc-companion' );	  
	  
	add_settings_section( 'section-3', __( 'Video Settings', 'bc-companion' ), function() {echo 'Choose which category of video to display from Twitch';}, 'bc-companion' );	  
	
	/* 
	 * http://codex.wordpress.org/Function_Reference/add_settings_field
	 * add_settings_field( $id, $title, $callback, $page, $section, $args );
	 * */
	add_settings_field( 'bc-companion-twitch-username', __( 'Twitch Username', 'bc-companion' ), 'bc_companion_bc_twitch_username_callback', 'bc-companion', 'section-1' );

	add_settings_field( 'bc-companion-twitch-id', __( 'Twitch User ID', 'bc-companion' ), 'bc_companion_bc_twitch_id_callback', 'bc-companion', 'section-1' );
	  
	add_settings_field( 'bc-companion-client-id', __( 'Twitch Client ID', 'bc-companion' ), 'bc_companion_client_id_callback', 'bc-companion', 'section-1' );

	add_settings_field( 'bc-companion-client-secret', __( 'Twitch Client Secret', 'bc-companion' ), 'bc_companion_client_secret_callback', 'bc-companion', 'section-1' );

	add_settings_field( 'bc-companion-auth-token', __( 'Twitch Auth Token', 'bc-companion' ), 'bc_companion_auth_token_callback', 'bc-companion', 'section-1' );

	add_settings_field( 'bc-companion-auth-token-expires', __( 'Twitch Auth Token Expires', 'bc-companion' ), 'bc_companion_auth_token_expires_callback', 'bc-companion', 'section-1' );

	// add_settings_field( 'field-1-1', __( 'Twitch Username', 'bc-companion' ), 'bc_companion_bc_twitch_username_callback', 'bc-companion', 'section-1' );

	add_settings_field( 'bc-companion-auto-embed', __( 'Auto Embed Stream', 'bc-companion' ), 'bc_companion_bc_auto_embed_callback', 'bc-companion', 'section-2' );

	add_settings_field( 'bc-companion-auto-embed-chat', __( 'Embed Twitch Chat', 'bc-companion' ), 'bc_companion_bc_auto_embed_chat_callback', 'bc-companion', 'section-2' );

	add_settings_field( 'bc-video-settings', __( 'Video Settings', 'bc-companion' ), 'bc_companion_bc_video_settings_callback', 'bc-companion', 'section-3' );

	add_settings_field( 'field-1-5', __( 'Clip Period', 'bc-companion' ), 'bc_companion_bc_video_period_callback', 'bc-companion', 'section-3' );

	register_setting( 'bcyt_companion_settings_fields', 'bcyt_companion_options', 'bc_companion_validate_and_sanitize' );
	
	add_settings_section( 'bcyt_youtube_fields', __( 'YouTube Settings', 'bcyt-companion' ), 'bcyt_companion_section_callback2', 'bcyt_companion_settings_sections' );  

	add_settings_field( 'bcyt_api_key', __( 'YouTube API Key', 'bcyt-companion' ), 'bcyt_companion_api_key_callback2', 'bcyt_companion_settings_sections', 'bcyt_youtube_fields' );
	
	add_settings_field( 'bcyt_channel_username', __( 'YouTube Channel Username', 'bcyt-companion' ), 'bcyt_companion_channel_username_callback2', 'bcyt_companion_settings_sections', 'bcyt_youtube_fields' );
	  
	add_settings_field( 'bcyt_channel_id', __( 'YouTube Channel ID', 'bcyt-companion' ), 'bcyt_companion_channel_id_callback2', 'bcyt_companion_settings_sections', 'bcyt_youtube_fields' );	
	
	register_setting( 'bckt_companion_settings_fields', 'bckt_companion_options', 'bc_companion_validate_and_sanitize' );
	
	add_settings_section( 'bckt_kick_fields', __( 'Kick Settings', 'bckt-companion' ), 'bckt_companion_section_callback', 'bckt_companion_settings_sections' );  

	add_settings_field( 'bckt_channel_username', __( 'Kick Channel Username', 'bckt-companion' ), 'bckt_companion_channel_username_callback', 'bckt_companion_settings_sections', 'bckt_kick_fields' );

}
add_action( 'admin_init', 'bc_companion_admin_init' );


function bc_companion_options() { ?>
    
	<div class="wrap">
	<h1>Broadcast Stream Integration</h1>
		<div id="tabs">
			<ul>
				<li><a href="#tab1">Twitch</a></li>
				<li><a href="#tab2">YouTube</a></li>
				<li><a href="#tab3">Kick</a></li>
				<!-- Add more tabs as needed -->
			</ul>
			<div id="tab1">
				<form action="options.php" method="POST">	
					<?php
						settings_fields('bc_options-group');
						do_settings_sections('bc-companion');
					?>
					<input name="Submit" type="submit" value="Save Changes" />
				</form>
			</div>
			<div id="tab2">
				<form action="options.php" method="POST">
					<?php  
						settings_fields('bcyt_companion_settings_fields');
						do_settings_sections('bcyt_companion_settings_sections');
					?>
					<input name="Submit" type="submit" value="Save Changes" />
				</form>
			</div>
			<div id="tab3">
				<form action="options.php" method="POST">
					<?php  
						settings_fields('bckt_companion_settings_fields');
						do_settings_sections('bckt_companion_settings_sections');
					?>
					<input name="Submit" type="submit" value="Save Changes" />
				</form>
			</div>				
			<!-- Add more tab content as needed -->
		</div>
	</div>
<?php }

// Fields
function bc_companion_bc_twitch_username_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_twitch_username";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input type='text' size='40' name='bc_options[$field]' value='$value' />";
	echo "<p>Your Twitch Username, this is used to check your online / offline status.</p>";
}

function bc_companion_bc_twitch_id_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_twitch_id";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input type='text' size='40' name='bc_options[$field]' value='$value' />";
	echo "<p>Easily convert your Twitch username to a Twitch ID <a target='_blank' href='https://www.streamweasels.com/tools/convert-twitch-username-to-user-id/'>here</a>.</p>";
}

function bc_companion_client_id_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_twitch_client_id";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input type='text' size='40' name='bc_options[$field]' value='$value' />";
	echo "<p>Enter your Twitch Client ID, follow along with our guide <a href='https://support.streamweasels.com/article/12-how-to-setup-a-client-id-and-client-secret' target='_blank'>here</a> if you're unsure where to get this.</p>";
}

function bc_companion_client_secret_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_twitch_client_secret";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input type='text' size='40' name='bc_options[$field]' value='$value' />";
	echo "<p>Enter your Twitch Client Secret, follow along with our guide <a href='https://support.streamweasels.com/article/12-how-to-setup-a-client-id-and-client-secret' target='_blank'>here</a> if you're unsure where to get this.</p>";
}

function bc_companion_auth_token_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_twitch_auth_token";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input disabled type='text' size='40' name='bc_options[$field]' value='$value' />";
	echo "<input type='hidden' id='bc-refresh-token' name='bc_options[bc_refresh_token]' value='0' />";
		submit_button(
			'Refresh Token',
			'delete button-secondary',
			'bc-refresh-token-submit',
			false,
			array(
			'style' => 'margin-left: 4px',
		)
	);
	echo "<p>This field will be automatically generated, based on your Client ID and Secret above.";
}

function bc_companion_auth_token_expires_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_twitch_auth_token_expires";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input disabled type='text' size='40' name='bc_options[$field]' value='$value' />";
	echo "<p>This is the expiration date of your Auth Token, which should be listed above.";
}

function bc_companion_bc_auto_embed_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_auto_embed";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input type='checkbox' value='1' ". checked( 1, $value, false ) ." name='bc_options[$field]' />";
	echo "<p>Enable this if you want your Twitch stream to automatically embed when you are live.</p>";
}

function bc_companion_bc_auto_embed_chat_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_auto_embed_chat";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	echo "<input type='checkbox' value='1' ". checked( 1, $value, false ) ." name='bc_options[$field]' />";
	echo "<p>Enable this if you want your Twitch chat to be embedded automatically alongside your stream.</p>";
}

function bc_companion_bc_video_settings_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_video_settings";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	$html = "<select id='' name='bc_options[$field]'/>
	<option value='clips' ".selected( $value, 'clips', false ).">Clips</option>
	<option value='highlights' ".selected( $value, 'highlights', false ).">Highlights</option>
	<option value='past-broadcasts' ".selected( $value, 'past-broadcasts', false ).">Past Broadcasts</option>
	</select>
	<p>Choose which videos from Twitch you would like to display on the page.</p>";
	echo $html;
}

function bc_companion_bc_video_period_callback() {
	
	$settings = (array) get_option( 'bc_options' );
	$field = "bc_video_period";
	if (array_key_exists($field, $settings)) {
		$value = esc_attr( $settings[$field] );
	} else {
		$value = '';
	}
	$html = "<select id='' name='bc_options[$field]'/>
	<option value='week' ".selected( $value, 'week', false ).">Week</option>
	<option value='month' ".selected( $value, 'month', false ).">Month</option>
	<option value='all' ".selected( $value, 'all', false ).">All</option>
	</select>
	<p>Choose from which period you want your clips to display. This only works when Clips is chosen above.</p>";
	echo $html;
}

// Section
function bcyt_companion_section_callback2() {
	_e( 'This plugin requires an active YouTube API key to work. <a href="https://support.streamweasels.com/article/26-how-to-setup-a-youtube-api-key" target="_blank">Click here</a> to learn more about YouTube API keys.<br>Make sure both Twitch and Kick settings are blank if you intend to use YouTube Integration.', 'bcyt-companion' );
}

// Fields
function bcyt_companion_api_key_callback2() {
	
	$settings = (array) get_option( 'bcyt_companion_options' );
	$field = "bcyt_api_key";
	$value = isset($settings[$field]) ? $settings[$field] : '';

	echo "<input type='text' name='bcyt_companion_options[$field]' value='".esc_attr($value)."' />";
	echo "<p>Your YouTube API key, follow along with our guide <a href='https://support.streamweasels.com/article/26-how-to-setup-a-youtube-api-key' target='_blank'>here</a> if you're unsure where to get this.</p>";
}

function bcyt_companion_channel_username_callback2() {
	
	$settings = (array) get_option( 'bcyt_companion_options' );
	$field = "bcyt_channel_username";
	$value = isset($settings[$field]) ? $settings[$field] : '';

	echo "<input type='text' name='bcyt_companion_options[$field]' value='".esc_attr($value)."' />";
	echo "<p>Your YouTube Username, this is displayed in the main header area of your Broadcast theme.</p>";

}

function bcyt_companion_channel_id_callback2() {
	
	$settings = (array) get_option( 'bcyt_companion_options' );
	$field = "bcyt_channel_id";
	$value = isset($settings[$field]) ? $settings[$field] : '';

	echo "<input type='text' name='bcyt_companion_options[$field]' value='".esc_attr($value)."' />";
	echo "<p>Easily convert your YouTube username to a Channel ID <a href='https://www.streamweasels.com/tools/youtube-channel-id-and-user-id-convertor/' target='_blank'>here</a>.</p>";
}

// Section
function bckt_companion_section_callback() {
	_e( 'Kick does not currently require an API connectio, simply add your Kick username below.<br>Make sure both Twitch and YouTube settings are blank if you intend to use Kick Integration.', 'bcyt-companion' );
}

function bckt_companion_channel_username_callback() {
	
	$settings = (array) get_option( 'bckt_companion_options' );
	$field = "bckt_channel_username";
	$value = isset($settings[$field]) ? $settings[$field] : '';

	echo "<input type='text' name='bckt_companion_options[$field]' value='".esc_attr($value)."' />";
	echo "<p>Your Kick Username, this is displayed in the main header area of your Broadcast theme.</p>";
}


// Validation
function bc_companion_validate_and_sanitize( $input ) {
	$settings = (array) get_option( 'bc_options' );
	
	if ( isset( $input['bc_twitch_username'] ) ) {
		$output['bc_twitch_username'] = sanitize_text_field( $input['bc_twitch_username'] );
	}

	if ( isset( $input['bc_twitch_id'] ) ) {
		$output['bc_twitch_id'] = sanitize_text_field( $input['bc_twitch_id'] );
	}	

	if ( isset( $input['bc_twitch_client_id'] ) ) {
		$output['bc_twitch_client_id'] = $input['bc_twitch_client_id'];
	} else {
		$output['bc_twitch_client_id'] = '';
	}

	if ( isset( $input['bc_twitch_client_secret'] ) ) {
		$output['bc_twitch_client_secret'] = $input['bc_twitch_client_secret'];
	} else {
		$output['bc_twitch_client_secret'] = '';
	}	

	if (isset( $input['bc_twitch_client_id'] ) && isset( $input['bc_twitch_client_secret'] )) {
		$BC_Twitch_API = new BC_Twitch_API();
		if ( isset( $input['bc_refresh_token'] )) {
			if ( $input['bc_refresh_token'] == 1 ) {
				$BC_Twitch_API->refresh_token();
			}		
		}

		$result = $BC_Twitch_API->get_token( $input['bc_twitch_client_id'], $input['bc_twitch_client_secret'] );

		if ( $result[0] !== 'error' ) {
			$output['bc_twitch_auth_token'] = $result[0];
			$output['bc_twitch_auth_token_expires'] = $result[1];
			// $output['swti_api_access_token_error_code'] = '';
			// $output['swti_api_access_token_error_message'] = '';
		} else {
			$output['bc_twitch_auth_token'] = 'x';
			$output['bc_twitch_auth_token_expires'] = 'x';
			// $output['swti_api_access_token_error_code'] = '403';
			// $output['swti_api_access_token_error_message'] = $result[1];
		}
	}

	if ( isset( $input['bc_auto_embed'] ) ) {
		$output['bc_auto_embed'] = (int) $input['bc_auto_embed'];
	}  else {
		$output['bc_auto_embed'] = 0;
	}

	if ( isset( $input['bc_auto_embed_chat'] ) ) {
		$output['bc_auto_embed_chat'] = (int) $input['bc_auto_embed_chat'];
	}  else {
		$output['bc_auto_embed_chat'] = 0;
	}	

	if ( isset( $input['bc_video_settings'] ) ) {
		$output['bc_video_settings'] = sanitize_text_field( $input['bc_video_settings'] );
	}  else {
		$output['bc_video_settings'] = 'clips';
	}	

	if ( isset( $input['bc_video_period'] ) ) {
		$output['bc_video_period'] = sanitize_text_field( $input['bc_video_period'] );
	}  else {
		$output['bc_video_period'] = 'all';
	}		

	// $settings2 = (array) get_option( 'bcyt_companion_options' );

	if ( isset( $input['bcyt_api_key'] ) ) {
		$output['bcyt_api_key'] = sanitize_text_field( $input['bcyt_api_key'] );
	}	
	
	if ( isset( $input['bcyt_channel_username'] ) ) {
		$output['bcyt_channel_username'] = sanitize_text_field( $input['bcyt_channel_username'] );
	}

	if ( isset( $input['bcyt_channel_id'] ) ) {
		$output['bcyt_channel_id'] = sanitize_text_field( $input['bcyt_channel_id'] );
	}

	if ( isset( $input['bckt_channel_username'] ) ) {
		$output['bckt_channel_username'] = sanitize_text_field( $input['bckt_channel_username'] );
	}		
	
	// and so on for each field
	
	return $output;
}
