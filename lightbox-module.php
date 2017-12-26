<?php
/*
Plugin Name: Lightbox Module
Plugin URI: http://github.com/employee451/lightbox-module/
Description: This plugin adds support for jquery.poptrox lightboxes for Employee 451 Pixelarity themes.
Author: Employee 451
Author URI: http://employee451.com/
Version: 0.0.1
GitHub Plugin URI: employee451/lightbox-module
*/

$lightbox_module_enabled = true;

/**
 * Enqueue scripts and styles.
 */
function lightbox_module_scripts() {
  // Poptrox
	wp_enqueue_script( 'strata-poptrox', plugins_url( 'assets/js/jquery.poptrox.min.js' ), array( 'jquery' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'lightbox_module_scripts' );

/* Portfolio Markup */
function customFormatGallery( $string, $attr ) {
    $output = '<div class="row">';
    $posts = get_posts( array( 'include' => $attr[ 'ids' ], 'post_type' => 'attachment' ) );
		$gallery_counter = 0;

    foreach($posts as $imagePost) {
			$gallery_counter++;
			$attachment = get_post( $imagePost->ID );
			$mime_type = get_post_mime_type( $imagePost->ID );
			if( strpos( $mime_type, 'video' ) !== false ) {
				if( !has_post_thumbnail( $imagePost->ID ) )
					continue;

				$video_metadata = wp_get_attachment_metadata( $imagePost->ID );
				$video_height = $video_metadata['height'];
				$video_width = $video_metadata['width'];

				$output .= '<article class="6u' . ( $gallery_counter % 2 === 0 ? '$' : '' ) . ' 12u$(xsmall) work-item">';
				$output .= '<a href="' . $attachment->guid . '" class="image fit thumb" data-poptrox="iframe,' . $video_width . 'x' . $video_height . '">';
				$output .= '<img src="' . get_the_post_thumbnail_url( $imagePost->ID, 'strata-thumb' ) . '" alt="' . $attachment->post_title . '" />';
				$output .= '</a>';
				$output .= '<h3>' . $attachment->post_title . '</h3>';
				$output .= '<p>' . $attachment->post_content . '</p>';
				$output .= '</article>';
			} else {
				$output .= '<article class="6u' . ( $gallery_counter % 2 === 0 ? '$' : '' ) . ' 12u$(xsmall) work-item">';
				$output .= '<a href="' . $attachment->guid . '" class="image fit thumb">';
				$output .= '<img src="' . wp_get_attachment_image_src( $imagePost->ID, 'strata-thumb' )[0] . '" alt="' . $attachment->post_title . '" />';
				$output .= '</a>';
				$output .= '<h3>' . $attachment->post_title . '</h3>';
				$output .= '<p>' . $attachment->post_content . '</p>';
				$output .= '</article>';
			}
    }

    $output .= "</div>";

    return $output;
}
add_filter( 'post_gallery', 'customFormatGallery', 10, 2 );

/* Vimeo Shortcode */
function strata_vimeo_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'ids' => '',
    ), $atts );

		$output = '<div class="row">';
		$gallery_counter = 0;
		$ids = explode( ',', $a['ids'] );

		foreach( $ids as $id ) {
			$gallery_counter++;
			$trimmed_id = trim( $id );
			$hash = json_decode(file_get_contents('http://vimeo.com/api/v2/video/' . $trimmed_id . '.json'));

			$output .= '<article class="6u' . ( $gallery_counter % 2 === 0 ? '$' : '' ) . ' 12u$(xsmall) work-item">';
			$output .= '<a href="' . $hash[0]->url . '" data-poptrox="vimeo,800x480" class="image fit thumb">';
			$output .= '<img src="' . $hash[0]->thumbnail_large . '" alt="' . $hash[0]->title . '" />';
			$output .= '</a>';
			$output .= '<h3>' . $hash[0]->title . '</h3>';
			$output .= '<p>' . str_replace(array("<br>", "<br/>", "<br />"), NULL, $hash[0]->description) . '</p>';
			$output .= '</article>';
		}

		$output .= "</div>";

    return $output;
}
add_shortcode( 'vimeo_gallery', 'strata_vimeo_shortcode' );

/** To-do:
 * Add classes to the pages that include the shortcodes
 * Add JS for activating poptrox on said pages ^
 * Default design for themes that don't naturally have lightbox implementation
 * More shortcodes! (YouTube, Wistia, Brightcove, Soundcloud, IFRAME, (AJAX), )
 */
