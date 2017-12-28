<?php
/*
Plugin Name: Lightbox Module
Plugin URI: http://github.com/employee451/lightbox-module/
Description: This plugin adds support for jquery.poptrox lightboxes for Employee 451 Pixelarity themes.
Author: Employee 451
Author URI: http://employee451.com/
Version: 1.0
GitHub Plugin URI: employee451/lightbox-module
*/

$lightbox_module_enabled = true;

/**
 * Enqueue scripts and styles.
 */
function lightbox_module_scripts() {
  // Poptrox
	wp_enqueue_script( 'lightbox-module-poptrox', plugins_url( 'assets/js/jquery.poptrox.min.js', __FILE__ ), array( 'jquery' ), '2.5.2-dev', true );

	// Lightbox Style
	wp_enqueue_style( 'lightbox-module-style', plugins_url( 'assets/css/lightbox.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'lightbox_module_scripts' );

function generate_lightbox_shortcodes( $text_domain, $container, $container_class, $from_indicator, $small_screen_name, $item_container, $item_class, $link_class, $thumb, $title, $description ) {
	// Gallery Shortcode
	add_filter( 'post_gallery', function( $string, $attr ) use( $text_domain, $container, $container_class, $from_indicator, $small_screen_name, $item_container, $item_class, $link_class, $thumb, $title, $description ) {
		$output = '<' . $container . ( $container_class ? ' class="' . $container_class . '">' : '>' );
		$posts = get_posts( array( 'include' => $attr[ 'ids' ], 'post_type' => 'attachment' ) );
		$gallery_counter = 0;

		foreach($posts as $imagePost) {
			$gallery_counter++;
			$attachment = get_post( $imagePost->ID );

			if( $from_indicator ) {
				$output .= '<' . $item_container . ' class="from-' . ( $gallery_counter % 2 === 0 ? 'right' : 'left' ) . ( $item_class ? ' ' . $item_class : '' ) . '">';
			} else {
				$output .= '<' . $item_container . ' class="6u' . ( $gallery_counter % 2 === 0 ? '$' : '' ) . ' 12u$(' . $small_screen_name . ')' . ( $item_class ? ' ' . $item_class : '' ) . '">';
			}

			$output .= '<a href="' . $attachment->guid . '"' . ( $link_class ? ' class="' . $link_class . '">' : '>' );
			$output .= '<img src="' . ( $thumb ? wp_get_attachment_image_src( $imagePost->ID, $text_domain . '-thumb' )[0] : wp_get_attachment_image_src( $imagePost->ID )[0] ) . '" alt="' . $attachment->post_title . '" />';
			$output .= '</a>';

			if( $title ) {
				$output .= '<h3>' . $attachment->post_title . '</h3>';
			}

			if( $description ) {
				$output .= '<p>' . $attachment->post_content . '</p>';
			}

			$output .= '</' . $item_container . '>';
		}

		$output .= '</' . $container . '>';

		return $output;
	}, 10, 2 );

	/* Playlist shortcode */
	// if( strpos( $mime_type, 'video' ) !== false ) {
	// 	if( !has_post_thumbnail( $imagePost->ID ) )
	// 		continue;
  //
	// 	$video_metadata = wp_get_attachment_metadata( $imagePost->ID );
	// 	$video_height = $video_metadata['height'];
	// 	$video_width = $video_metadata['width'];
  //
	// 	$output .= '<a href="' . $attachment->guid . '" ' . ( $link_class ? 'class="' . $link_class . '" ' : '' ) . 'data-poptrox="iframe,' . $video_width . 'x' . $video_height . '">';
	// }

	// Vimeo Shortcode
	add_shortcode( 'vimeo_gallery', function( $atts ) use( $text_domain, $container, $container_class, $from_indicator, $small_screen_name, $item_container, $item_class, $link_class, $thumb, $title, $description ) {
		$a = shortcode_atts( array(
				'ids' => '',
		), $atts );

		$output = '<' . $container . ( $container_class ? ' class="' . $container_class . '">' : '>' );
		$gallery_counter = 0;
		$ids = explode( ',', $a['ids'] );

		foreach( $ids as $id ) {
			$gallery_counter++;
			$trimmed_id = trim( $id );
			$hash = json_decode(file_get_contents('http://vimeo.com/api/v2/video/' . $trimmed_id . '.json'));

			if( $from_indicator ) {
				$output .= '<' . $item_container . ' class="from-' . ( $gallery_counter % 2 === 0 ? 'right' : 'left' ) . ( $item_class ? ' ' . $item_class : '' ) . '">';
			} else {
				$output .= '<' . $item_container . ' class="6u' . ( $gallery_counter % 2 === 0 ? '$' : '' ) . ' 12u$(' .  $small_screen_name . ')' . ( $item_class ? ' ' . $item_class : '' ) . '">';
			}

			$output .= '<a href="' . $hash[0]->url . '" data-poptrox="vimeo,800x480"' . ( $link_class ? ' class="' . $link_class . '">' : '>' );
			$output .= '<img src="' . $hash[0]->thumbnail_large . '" alt="' . $hash[0]->title . '" />';
			$output .= '</a>';

			if( $title ) {
				$output .= '<h3>' . $hash[0]->title . '</h3>';
			}

			if( $description ) {
				$output .= '<p>' . str_replace(array("<br>", "<br/>", "<br />"), NULL, $hash[0]->description) . '</p>';
			}

			$output .= '</' . $item_container . '>';
		}

		$output .= '</' . $container . '>';

		return $output;
	} );
}

/** To-do:
 * Only giving user ID in Vimeo Shortcode, playlist?
 * More shortcodes! (Playlist, YouTube, Wistia, Brightcove, Soundcloud, IFRAME, (AJAX), )
 * Default design & functionality for themes that don't naturally have lightbox implementation (JS for activating poptrox, add class to pages that include shortcodes)
 */
