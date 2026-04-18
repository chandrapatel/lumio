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
 * Filter the output of the `core/query-title` block to filter the search results title.
 *
 * @since 1.0.0
 *
 * @param string $block_content The original block content.
 * @param array  $block         The block data.
 * @param array  $attributes    The block attributes.
 *
 * @return string The modified block content.
 */
function lumio_filter_query_title_block( $block_content, $block, $attributes ): string {

	if ( is_search() ) {
		$block_content = str_replace( 'Search results for:', 'Results for', $block_content );
		$block_content = str_replace( '"' . get_search_query() . '"', '<span class="lumio-search-query">"' . get_search_query() . '"</span>', $block_content );
	}

	return $block_content;
}
add_filter( 'render_block_core/query-title', 'lumio_filter_query_title_block', 10, 3 );

/**
 * Output sticky-header overflow fix after WordPress global styles (priority 999).
 *
 * @since 1.0.0
 *
 * @return void
 */
function lumio_sticky_header_styles(): void {
	echo '<style>html,body,.wp-site-blocks{overflow:visible!important}</style>' . PHP_EOL;
}
add_action( 'wp_head', 'lumio_sticky_header_styles', 999 );
