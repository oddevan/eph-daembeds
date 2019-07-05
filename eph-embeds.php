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
	//wp_oembed_add_provider( 'http://fav.me/*', 'https://backend.deviantart.com/oembed' );
	//wp_oembed_add_provider( 'http://fav.me/*', 'http://api.embed.ly/1/oembed' );
	$callback = __NAMESPACE__ . '\handle_deviantart';

	wp_embed_register_handler( 'deviantart-favme', '#^http://fav.me/*+#', $callback, 10 );
}

function handle_deviantart( $matches, $attr, $url, $rawattr ) {
	$http_options = [
		'headers' => [
			'User-Agent'      => 'WordPress OEmbed Consumer',
			'accept-encoding' => 'gzip, deflate',
		],
	];

	$da_response = \wp_remote_get( 'https://backend.deviantart.com/oembed?url=' . urlencode( $url ) );
	if ( empty( $da_response ) || 200 !== $da_response['response']['code'] ) {
		unset( $da_response['body'] );
		unset( $da_response['http_response'] );
		return "<p><!-- Could not embed: {$da_response['response']['code']} --><a href=\"{$url}\">View Deviation</a></p>";
	}

	$deviation = json_decode( $da_response['body'] );

	$html = <<<EOF
<div class="eph-daembed" style="border:1px solid rgb(99, 119, 104);margin:0;padding:10px;background:rgb(212, 223, 208);">
	<a href="$deviation->author_url" target="_blank" style="display:block;font-family:sans-serif;font-size:16px;line-height:25px;background:rgb(99, 119, 104);color:white">
		<img src="//st.deviantart.net/minish/main/logo/logo-mark.png"> / $deviation->author_name
	</a>
	<a href="$url" target="_blank"><img src="$deviation->url" style="height:auto;max-width:100%;margin:0;"></a>
	<a href="$deviation->author_url" target-"_blank" style="color:rgb(151, 162, 160);">&copy; $deviation->copyright->_attributes->year $deviation->copyright->_attributes->entity</a>
</div>
EOF;

	return $html;
}

add_action( 'init', __NAMESPACE__ . '\register_providers' );
