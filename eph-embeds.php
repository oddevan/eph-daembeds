<?php
/**
 * Plugin Name: DeviantArt Embeds
 * Plugin URI: https://eph.me/eph-da-embed/
 * Description: Embed images from DeviantArt and related properties.
 * Version: 0.1
 * Author: Evan Hildreth
 * Author URI: https://eph.me/
 * License: GPL2
 * License URI: http://opensource.org/licenses/GPL2
 *
 * @package EPH\DAEmbed
 */

namespace EPH\DAEmbed;

function register_providers() {
	$callback = __NAMESPACE__ . '\handle_deviantart';

	wp_embed_register_handler( 'deviantart-favme', '#http://fav.me/*+#', $callback, 10 );
	wp_embed_register_handler( 'deviantart-stash', '#https://sta.sh/*+#', $callback, 10 );
	wp_embed_register_handler( 'deviantart-main', '#https://www.deviantart.com/*+#', $callback, 10 );
}

function handle_deviantart( $matches, $attr, $url, $rawattr ) {
	$http_options = [
		'headers' => [
			'User-Agent'      => 'WordPress OEmbed Consumer',
		],
	];

	$da_response = \wp_remote_get( 'https://backend.deviantart.com/oembed?url=' . rawurlencode( $url ), $http_options );
	if ( empty( $da_response ) || 200 !== $da_response['response']['code'] ) {
		return "<p><!-- Could not embed --><a href=\"{$url}\">View Deviation</a></p>";
	}

	$deviation = json_decode( $da_response['body'] );
	$copyright = $deviation->copyright->_attributes;

	$html = <<<EOF
<div class="eph-daembed" style="border:1px solid rgb(99, 119, 104);margin:0;padding:0 0 10px 0;background:rgb(212, 223, 208);">
	<a href="$deviation->author_url" target="_blank" style="text-decoration:none;margin:0 0 10px 0;padding:10px;display:block;font-family:sans-serif;font-size:16px;line-height:16px;background:rgb(99, 119, 104);color:white">
		<img src="//st.deviantart.net/minish/main/logo/logo-mark.png"> / $deviation->author_name
	</a>
	<a href="$url" target="_blank" style="display:block;margin:10px;padding:0;"><img src="$deviation->url" style="height:auto;max-width:100%;margin:0;"></a>
	<p style="margin:10px;display:block;font-size:16px;line-height:18px;font-family:Trebuchet MS, sans-serif;font-weight:bold;">
		<a href="$url" target="_blank" style="text-decoration:none;font-size:21px;line-height:25px;letter-spacing:-1px;color:black;">$deviation->title</a><br>
		by <a href="$deviation->author_url" style="text-decoration:none;color:rgb(51, 114, 135);" target="_blank">$deviation->author_name</a>
	</p>
	<a href="$deviation->author_url" target-"_blank" style="display:block;font-size:12px;line-height:12px;font-family:sans-serif;margin:10px;color:rgb(151, 162, 160);">&copy; $copyright->year $copyright->entity</a>
</div>
EOF;

	return $html;
}

add_action( 'init', __NAMESPACE__ . '\register_providers' );
