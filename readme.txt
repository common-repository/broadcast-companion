=== Broadcast Companion (Twitch) ===
Contributors: j.burleigh1, streamweasels
Requires at least: 5.0
Tested up to: 6.2
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
This plugin is for use with the Broadcast Lite theme and provides the Twitch, YouTube and Kick integration.

== Description ==

Broadcast Companion, used in combination with the theme [Broadcast Lite](https://wordpress.org/themes/broadcast-lite/) or [Broadcast PRO](https://www.streamweasels.com/twitch-wordpress-themes/broadcast-pro/?utm_source=wordpress&utm_medium=broadcast-companion-twiitch&utm_campaign=readme) will integrate Twitch, YouTube and Kick data into your theme. Every time your website is loaded, this plugin makes a request to check if you are online on these services. If you are online, data from Twitch is presented in the theme. 

The following data is displayed from Twitch:

* Online / Offline status from Twitch
* Active game / category
* Viewer count
* Videos (clips, highlights and past broadcasts)

=== Setup ===

This plugin keeps an active connection to the Twitch API, which requires you to add a Client ID and Client secret from Twitch. Instructions on where to find this data are included in the plugin, but you can also find those instructions [here](https://support.streamweasels.com/article/12-how-to-setup-a-client-id-and-client-secret).

== Screenshots ==

1. Broadcast Lite Demo (Twitch)
2. Broadcast Lite Demo (YouTube)

== Changelog ==

= 3.0.6 =
* adding support for YouTube and Kick

= 3.0.5 =
* removed unused assets
* updated readme
* added screenshot

= 3.0.4 =
* bumping version

= 3.0.3 =
* Updated API to use new Helix endpoints
* Fixed an issue with error_log

= 2.0.7 =
* Cleaned up some errors encountered with WP_DEBUG enabled

= 2.0.6 =
* Added the ability to pull Clips, Highlights, or Past Broadcasts

= 2.0.4 =
* Fixed a bug where ps4 streams were not showing as live.

= 2.0.3 =
* Removed the second sponsor link.
* Fixed a bug with embedded streams.

= 2.0.2 =
* REQUIRED UPDATE: Anyone not running this version after April 30th 2020 - Broadcast will cease to integrate with Twitch API.
* Reverted twitch API usage back to kraken.
* Broadcast now pulls your top clips instead of most recent broadcasts, this is due to a limitation in kraken API.

= 2.0 =
* Fixed a bug causing issues on Broadcast Lite.
* Fixed a bug with stream in embedded mode.

= 1.9 =
* Upgraded Twitch API to helix.
* Fixed a bug with the Watch Now button.

= 1.8 =
* Added an auto-embed option.
* Added notice for YouTube support.

= 1.7 =
* Fix for the missing fist VOD.

= 1.6 =
* Fix for broken VOD tile (first tile had broken thumbnail).
* Name change - Broadcast Companion (Twitch).
* Added notice for Mixer support.

= 1.5 =
* Upgraded API call for vods to helix.
* Vod thumbnails are now a better size.

= 1.4 =
* Tiny fix for sites running Broadcast on https. 

= 1.3 =
* Fixed an issue where the StreamWeasels logo was being inserted into Broadcast PRO theme.

= 1.2 =
* Fixed an issue with the game playing text not displaying in the nav.

= 1.1 =
* Added a default StreamWeasels affiliate link to theme.
* Added code to handle stream modal.

= 1.0 =
* First release.