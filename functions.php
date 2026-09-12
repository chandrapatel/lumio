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

/**
 * Order the terms rendered by the `core/post-terms` block parent-first.
 *
 * `get_the_terms()` returns terms ordered by name, so an assigned child term can be
 * printed before its assigned parent. Only terms explicitly assigned to the post are
 * considered: a child whose parent is not assigned stays at the top level rather than
 * pulling an unassigned ancestor into the list.
 *
 * Scoped to the block render: `lumio_sort_block_terms_hierarchically()` detaches itself
 * after the block's single `get_the_terms()` call so other callers keep core ordering
 * and the term it drops on category archives stays out of that one block only.
 *
 * @since 1.0.0
 *
 * @param string|null $pre_render   The pre-rendered block content, or null.
 * @param array       $parsed_block The parsed block.
 *
 * @return string|null The unchanged pre-rendered content.
 */
function lumio_order_block_terms_hierarchically( $pre_render, $parsed_block ) {

	if ( 'core/post-terms' === ( $parsed_block['blockName'] ?? '' ) ) {
		add_filter( 'get_the_terms', 'lumio_sort_block_terms_hierarchically', 10, 3 );
	}

	return $pre_render;
}
add_filter( 'pre_render_block', 'lumio_order_block_terms_hierarchically', 10, 2 );

/**
 * Detach the ordering filter once the `core/post-terms` block has rendered.
 *
 * Covers the case where another filter short-circuits the block before it reaches
 * `get_the_terms()`, which would otherwise leave the filter attached.
 *
 * @since 1.0.0
 *
 * @param string $block_content The rendered block content.
 *
 * @return string The unchanged block content.
 */
function lumio_restore_block_term_order( $block_content ): string {

	remove_filter( 'get_the_terms', 'lumio_sort_block_terms_hierarchically', 10 );

	return $block_content;
}
add_filter( 'render_block_core/post-terms', 'lumio_restore_block_term_order' );

/**
 * Sort a post's assigned terms so each parent precedes its own children.
 *
 * On a category archive the term being viewed is dropped first: it repeats the page
 * heading on every row, while the remaining terms do not. Category archives include
 * posts from child categories, so a parent archive keeps showing the child a post sits
 * in, and a post assigned only to the queried term renders no terms at all.
 *
 * @since 1.0.0
 *
 * @param WP_Term[]|int[]|WP_Error $terms    The post's terms.
 * @param int                      $post_id  The post ID.
 * @param string                   $taxonomy The taxonomy name.
 *
 * @return WP_Term[]|int[]|WP_Error The terms, ordered parent-first.
 */
function lumio_sort_block_terms_hierarchically( $terms, $post_id, $taxonomy ) {

	remove_filter( 'get_the_terms', __FUNCTION__, 10 );

	if ( is_wp_error( $terms ) || ! is_taxonomy_hierarchical( $taxonomy ) ) {
		return $terms;
	}

	if ( 'category' === $taxonomy && is_category() ) {
		$queried_id = get_queried_object_id();

		$terms = array_values(
			array_filter(
				(array) $terms,
				static function ( $term ) use ( $queried_id ) {
					return (int) $term->term_id !== $queried_id;
				}
			)
		);
	}

	if ( count( (array) $terms ) < 2 ) {
		return $terms;
	}

	$assigned = array();

	foreach ( $terms as $term ) {
		$assigned[ $term->term_id ] = true;
	}

	// Terms keyed by the closest ancestor that is also assigned to the post, 0 when there is none.
	$branches = array();

	foreach ( $terms as $term ) {
		$parent                = isset( $assigned[ $term->parent ] ) ? $term->parent : 0;
		$branches[ $parent ][] = $term;
	}

	return lumio_flatten_term_branches( $branches, 0, array() );
}

/**
 * Walk grouped terms depth-first into a flat, parent-first list.
 *
 * @since 1.0.0
 *
 * @param array $branches Terms grouped by assigned parent term ID.
 * @param int   $parent   The parent term ID to walk.
 * @param array $seen     Parent term IDs already walked on this path, keyed by ID.
 *
 * @return WP_Term[] The flattened terms.
 */
function lumio_flatten_term_branches( array $branches, int $parent, array $seen ): array {

	if ( isset( $seen[ $parent ] ) || ! isset( $branches[ $parent ] ) ) {
		return array();
	}

	$seen[ $parent ] = true;
	$ordered         = array();

	foreach ( $branches[ $parent ] as $term ) {
		$ordered[] = $term;
		$ordered   = array_merge( $ordered, lumio_flatten_term_branches( $branches, (int) $term->term_id, $seen ) );
	}

	return $ordered;
}
