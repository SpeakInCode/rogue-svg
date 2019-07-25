<?php
/*
Plugin Name: Rogue SVG Support
Plugin URI:  https://github.com/SpeakInCode/rogue-svg
Description: Add SVG Support for WordPress.
Version:     1.2.0
Author:      Spencer Merritt
Author URI:  https://github.com/SpeakInCode
Text Domain: rogue-svg
License:     GPL2
*/

if (!defined('ABSPATH')) {
	exit;
}


/**
 * Upload SVG Support
 */

function rogue_add_support($svg_editing) {

	$svg_editing['svg'] = 'image/svg+xml';

	// Echo the svg file
	return $svg_editing;
}

add_filter('upload_mimes', 'rogue_add_support');


/**
 * Uploading SVG Files into the Media Libary
 */

function rogue_upload_check($checked, $file, $filename, $mimes) {

	if (!$checked['type']) {

		$rogue_upload_check = wp_check_filetype($filename, $mimes);
		$ext                = $rogue_upload_check['ext'];
		$type               = $rogue_upload_check['type'];
		$proper_filename    = $filename;

		if ($type && 0 === strpos($type, 'image/') && $ext !== 'svg') {
			$ext = $type = false;
		}

		// Check the filename
		$checked = compact('ext', 'type', 'proper_filename');

	}

	return $checked;

}

add_filter('wp_check_filetype_and_ext', 'rogue_upload_check', 10, 4);


/**
 * Display SVG Files in Backend
 */

function rogue_display_svg_files_backend() {

	$url          = '';
	$attachmentID = isset($_REQUEST['attachmentID']) ? $_REQUEST['attachmentID'] : '';

	if ($attachmentID) {
		$url = wp_get_attachment_url($attachmentID);
	}
	echo $url;

	die();
}

add_action('wp_AJAX_svg_get_attachment_url', 'rogue_display_svg_files_backend');


/**
 * Display SVGs in Media Library
 */

function rogue_display_svg_media($response, $attachment, $meta) {
	if ($response['type'] === 'image' && $response['subtype'] === 'svg+xml' && class_exists('SimpleXMLElement')) {
		try {

			$path = get_attached_file($attachment->ID);

			if (@file_exists($path)) {
				$svg               = new SimpleXMLElement(@file_get_contents($path));
				$src               = $response['url'];
				$width             = (int) $svg['width'];
				$height            = (int) $svg['height'];
				$response['image'] = compact('src', 'width', 'height');
				$response['thumb'] = compact('src', 'width', 'height');

				$response['sizes']['full'] = [
					'height'      => $height,
					'width'       => $width,
					'url'         => $src,
					'orientation' => $height > $width ? 'portrait' : 'landscape',
				];
			}
		} catch (Exception $e) {
		}
	}

	return $response;
}

add_filter('wp_prepare_attachment_for_js', 'rogue_display_svg_media', 10, 3);
