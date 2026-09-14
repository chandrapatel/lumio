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
 * Check whether the Liquid Glass style variation is the active global style.
 *
 * WordPress records no name for the variation a site has selected: applying one in
 * the Site Editor merges its contents into the user's global styles and discards the
 * label. `styles/liquid.json` therefore carries a `styleSlug` setting whose presence
 * in the merged settings is the marker, which also survives any later customisation
 * the user makes on top of the variation.
 *
 * `wp_get_global_settings()` returns the whole settings array when the requested path
 * is absent, so the value has to be type-checked rather than merely compared.
 *
 * @since 1.1.0
 *
 * @return bool True when the Liquid Glass variation is active.
 */
function lumio_is_liquid_style(): bool {

	$slug = wp_get_global_settings( array( 'custom', 'styleSlug' ) );

	return is_string( $slug ) && 'liquid' === $slug;
}

/**
 * Build a cache-busting version string for a theme asset.
 *
 * The theme version alone does not change when a stylesheet is edited, so browsers
 * keep serving the copy they already have. Appending the file's modification time
 * means an edit always invalidates the cached copy, and the theme version still
 * identifies the release. Falls back to the theme version if the file is missing.
 *
 * @since 1.1.0
 *
 * @param string $relative_path Path to the asset, relative to the theme root.
 *
 * @return string Version string for wp_enqueue_style()/wp_enqueue_script().
 */
function lumio_asset_version( string $relative_path ): string {

	$version = (string) wp_get_theme()->get( 'Version' );
	$file    = get_theme_file_path( $relative_path );
	$mtime   = file_exists( $file ) ? filemtime( $file ) : false;

	return $mtime ? $version . '.' . $mtime : $version;
}

/**
 * Enqueue the Liquid Glass assets on the front end.
 *
 * The stylesheet declares no dependency on `lumio-style`, deliberately. A missing
 * dependency makes WP_Dependencies skip the item silently — nothing is printed and
 * no error is raised — so a plugin that dequeues or renames the theme's handle would
 * take this file down with it. Running at priority 20 while the theme's own styles
 * enqueue at the default 10 puts this later in the queue, and styles without
 * dependencies print in queue order, so the cascade still resolves correctly.
 *
 * The script only enhances what the stylesheet already renders — the sliding
 * navigation pill and the pointer-tracked highlight — so it is deferred and left
 * out of the editor, where neither behaviour applies.
 *
 * @since 1.1.0
 *
 * @return void
 */
function lumio_enqueue_liquid_assets(): void {

	if ( ! lumio_is_liquid_style() ) {
		return;
	}

	wp_enqueue_style(
		'lumio-liquid',
		get_theme_file_uri( 'assets/css/liquid.css' ),
		array(),
		lumio_asset_version( 'assets/css/liquid.css' )
	);

	wp_enqueue_script(
		'lumio-liquid',
		get_theme_file_uri( 'assets/js/liquid.js' ),
		array(),
		lumio_asset_version( 'assets/js/liquid.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'lumio_enqueue_liquid_assets', 20 );

/**
 * Enqueue the Liquid Glass stylesheet inside the block editor canvas.
 *
 * `enqueue_block_assets` reaches the editor's iframe, and unlike `add_editor_style()`
 * it hands the CSS over untouched — core rewrites `:root` and `body` selectors in
 * theme editor styles, which this file depends on for its token overrides.
 *
 * The front end is served by lumio_enqueue_liquid_assets() instead, so this run is
 * limited to block editor screens: the hook also fires on plain admin pages, where
 * the page background gradient would repaint wp-admin.
 *
 * @since 1.1.0
 *
 * @return void
 */
function lumio_enqueue_liquid_editor_style(): void {

	if ( ! is_admin() || ! lumio_is_liquid_style() ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen instanceof WP_Screen || ! $screen->is_block_editor() ) {
		return;
	}

	wp_enqueue_style(
		'lumio-liquid',
		get_theme_file_uri( 'assets/css/liquid.css' ),
		array(),
		lumio_asset_version( 'assets/css/liquid.css' )
	);
}
add_action( 'enqueue_block_assets', 'lumio_enqueue_liquid_editor_style' );

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
