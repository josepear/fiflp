<?php
/**
 * Plugin Name: FIFLP SVG Support
 * Description: Soporte SVG nativo para FIFLP sin dependencia del plugin svg-support.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'option_active_plugins',
	function( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			return $plugins;
		}

		return array_values(
			array_filter(
				$plugins,
				static function( $plugin ) {
					return 'svg-support/svg-support.php' !== $plugin;
				}
			)
		);
	},
	1
);

add_filter(
	'upload_mimes',
	function( $mimes ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';

		return $mimes;
	}
);

add_filter(
	'wp_check_filetype_and_ext',
	function( $data, $file, $filename, $mimes ) {
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		if ( 'svg' === $ext || 'svgz' === $ext ) {
			$data['ext']             = $ext;
			$data['type']            = 'image/svg+xml';
			$data['proper_filename'] = $filename;
		}

		return $data;
	},
	10,
	4
);

add_filter(
	'wp_prepare_attachment_for_js',
	function( $response, $attachment ) {
		if ( ! $response || ! $attachment instanceof WP_Post ) {
			return $response;
		}

		if ( 'image/svg+xml' !== get_post_mime_type( $attachment ) ) {
			return $response;
		}

		$url = wp_get_attachment_url( $attachment->ID );

		if ( ! $url ) {
			return $response;
		}

		$response['image'] = array(
			'src'    => $url,
			'width'  => 800,
			'height' => 800,
		);

		$response['icon'] = $url;

		if ( empty( $response['sizes'] ) || ! is_array( $response['sizes'] ) ) {
			$response['sizes'] = array();
		}

		$response['sizes']['full'] = array(
			'url'         => $url,
			'width'       => 800,
			'height'      => 800,
			'orientation' => 'portrait',
		);

		return $response;
	},
	10,
	2
);

add_action(
	'admin_head',
	function() {
		?>
		<style>
			.attachment .thumbnail img[src$=".svg"],
			.media-frame-content .attachment-preview img[src$=".svg"],
			img[src$=".svg"].attachment-post-thumbnail,
			.components-responsive-wrapper img[src$=".svg"] {
				width: 100% !important;
				height: auto !important;
				object-fit: contain;
				background: #fff;
			}
		</style>
		<?php
	}
);
