<?php
/**
 * Lumio theme functions.
 *
 * @package Lumio
 */

/**
 * Set up theme supports.
 *
 * @since 1.0.0
 *
 * @return void
 */
function lumio_theme_setup(): void {

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
}
add_action( 'after_setup_theme', 'lumio_theme_setup' );

/**
 * Enqueue the main stylesheet.
 *
 * @since 1.0.0
 *
 * @return void
 */
function lumio_enqueue_styles(): void {
	wp_enqueue_style(
		'lumio-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'lumio_enqueue_styles' );

/**
 * Modify the tag archive title to include a hashtag.
 *
 * @since 1.0.0
 *
 * @param string $title The original archive title.
 *
 * @return string The modified archive title.
 */
function lumio_modify_tag_archive_title( string $title ) {

	if ( is_tag() ) {
		$title = '#' . $title;
	}

	return $title;
}
add_action( 'get_the_archive_title', 'lumio_modify_tag_archive_title' );

/**
 * Replace the search results title rendered by the `core/query-title` block.
 *
 * Rebuilds the heading's inner content rather than string-matching core's own copy.
 * Core renders the term as `Search results for: &#8220;%s&#8221;`, so matching that
 * text breaks whenever core adjusts its wording or punctuation, and never matches at
 * all on a translated site.
 *
 * @since 1.0.0
 *
 * @param string   $block_content The original block content.
 * @param array    $block         The parsed block.
 * @param WP_Block $instance      The block instance.
 *
 * @return string The modified block content.
 */
function lumio_filter_query_title_block( $block_content, $block, $instance ): string {

	$attributes = $instance->attributes ?? array();

	if ( ! is_search() || 'search' !== ( $attributes['type'] ?? '' ) ) {
		return $block_content;
	}

	if ( empty( $attributes['showSearchTerm'] ) ) {
		$title = esc_html__( 'Results', 'lumio' );
	} else {
		$title = sprintf(
			/* translators: %s: Search term, wrapped in a highlight element. */
			esc_html__( 'Results for %s', 'lumio' ),
			'<span class="lumio-search-query">&#8220;' . esc_html( get_search_query() ) . '&#8221;</span>'
		);
	}

	return (string) preg_replace_callback(
		'~(<(h[1-6]|p)\b[^>]*>).*(</\2>)~s',
		static function ( $matches ) use ( $title ) {
			return $matches[1] . $title . $matches[3];
		},
		$block_content,
		1
	);
}
add_filter( 'render_block_core/query-title', 'lumio_filter_query_title_block', 10, 3 );
