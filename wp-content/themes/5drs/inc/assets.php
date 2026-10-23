<?php
/**
 * Asset loading.
 *
 * Styles and scripts are authored in src/ and built by Vite into dist/ with
 * hashed filenames and a manifest. Nothing in src/ is ever enqueued directly.
 *
 * While `npm run dev` is running it writes dist/.vite-dev-server; when that
 * file exists (and only in a local environment) assets are loaded from the Vite
 * dev server instead, giving hot reload.
 *
 * @package 5drs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Absolute path to the Vite build manifest.
 *
 * @return string
 */
function fivedrs_manifest_path() {
	return FIVEDRS_DIR . '/dist/.vite/manifest.json';
}

/**
 * Read and memoise the Vite manifest.
 *
 * @return array<string,array> Manifest data, empty when the theme is unbuilt.
 */
function fivedrs_manifest() {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$path = fivedrs_manifest_path();
	if ( ! is_readable( $path ) ) {
		$manifest = array();
		return $manifest;
	}

	// Local build artefact, not a remote URL - wp_remote_get() does not apply.
	$decoded  = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$manifest = is_array( $decoded ) ? $decoded : array();

	return $manifest;
}

/**
 * Dev-server origin, or empty string when not running `npm run dev`.
 *
 * @return string
 */
function fivedrs_dev_server() {
	if ( 'local' !== wp_get_environment_type() ) {
		return '';
	}

	$hot = FIVEDRS_DIR . '/dist/.vite-dev-server';
	if ( ! is_readable( $hot ) ) {
		return '';
	}

	// Local marker file written by Vite, not a remote URL.
	return untrailingslashit( trim( (string) file_get_contents( $hot ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * Resolve a src/ entry to its built URL.
 *
 * @param string $entry Entry path relative to the theme root, e.g. "src/js/main.js".
 * @return string Public URL, or empty string when the entry is not built.
 */
function fivedrs_asset_url( $entry ) {
	$manifest = fivedrs_manifest();

	if ( empty( $manifest[ $entry ]['file'] ) ) {
		return '';
	}

	return FIVEDRS_URI . '/dist/' . $manifest[ $entry ]['file'];
}

/**
 * Version string for a built asset - the file mtime, so cache busting still
 * works if two builds happen to produce the same hash.
 *
 * @param string $entry Entry path relative to the theme root.
 * @return string
 */
function fivedrs_asset_version( $entry ) {
	$manifest = fivedrs_manifest();

	if ( empty( $manifest[ $entry ]['file'] ) ) {
		return FIVEDRS_VERSION;
	}

	$file = FIVEDRS_DIR . '/dist/' . $manifest[ $entry ]['file'];

	return file_exists( $file ) ? (string) filemtime( $file ) : FIVEDRS_VERSION;
}

/**
 * Enqueue front-end assets.
 *
 * @return void
 */
function fivedrs_enqueue_assets() {
	$dev = fivedrs_dev_server();

	if ( $dev ) {
		// Vite dev server: the JS entry pulls in CSS via HMR. A null version is
		// deliberate - the dev server handles its own cache busting, and a query
		// string would break its module resolution.
		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script( 'fivedrs-vite-client', $dev . '/@vite/client', array(), null, false );
		wp_enqueue_script( 'fivedrs-main', $dev . '/src/js/main.js', array(), null, true );
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		return;
	}

	$css = fivedrs_asset_url( 'src/css/main.css' );
	if ( $css ) {
		wp_enqueue_style( 'fivedrs-main', $css, array(), fivedrs_asset_version( 'src/css/main.css' ) );
	}

	$js = fivedrs_asset_url( 'src/js/main.js' );
	if ( $js ) {
		wp_enqueue_script( 'fivedrs-main', $js, array(), fivedrs_asset_version( 'src/js/main.js' ), true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'fivedrs_enqueue_assets' );

/**
 * Load Vite dev-server scripts and the built JS bundle as ES modules.
 *
 * @param string $tag    Script tag HTML.
 * @param string $handle Script handle.
 * @return string
 */
function fivedrs_script_type_module( $tag, $handle ) {
	if ( in_array( $handle, array( 'fivedrs-main', 'fivedrs-vite-client' ), true ) ) {
		$tag = str_replace( '<script ', '<script type="module" ', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'fivedrs_script_type_module', 10, 2 );

/**
 * Warn in the admin when the theme has not been built yet.
 *
 * @return void
 */
function fivedrs_unbuilt_notice() {
	if ( ! current_user_can( 'manage_options' ) || fivedrs_manifest() || fivedrs_dev_server() ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( '5drs theme assets are not built yet. Run "make theme-build" (or "npm run build" in the theme folder).', '5drs' )
	);
}
add_action( 'admin_notices', 'fivedrs_unbuilt_notice' );
