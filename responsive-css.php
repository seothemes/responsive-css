<?php
/**
 * Responsive CSS.
 *
 * Plugin Name: Responsive CSS
 * Plugin URI:  https://seothemes.com
 * Description: Adds a Responsive CSS section to the Customizer.
 * Version:     1.0.0
 * Author:      SEO Themes
 * Author URI:  https://seothemes.com
 * Text Domain: responsive-css
 * License:     GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   ResponsiveCSS
 * @author    SEO Themes <info@seothemes.com>
 * @license   GPL-2.0+
 * @link      https://seothemes.com
 * @copyright 2017 SEO Themes
 */

add_action( 'plugins_loaded', 'responsive_css_textdomain' );
/**
 * Load plugin text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function responsive_css_textdomain() {
	load_plugin_textdomain( 'responsive-css', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'customize_register', 'responsive_css_settings', 10, 1 );
/**
 * Adds an Additional JS setting to the Customizer.
 *
 * @since  1.0.0
 *
 * @param  $wp_customize
 *
 * @return void
 */
function responsive_css_settings( $wp_customize ) {
	$wp_customize->add_section( 'responsive-css', [
		'title'    => __( 'Responsive CSS', 'responsive-css' ),
		'priority' => 300,
	] );

	$wp_customize->add_setting( 'responsive-css-breakpoint', [
		'type' => 'option',
	] );

	$wp_customize->add_setting( 'responsive-css-all', [
		'type' => 'option',
	] );

	$wp_customize->add_setting( 'responsive-css-mobile', [
		'type' => 'option',
	] );

	$wp_customize->add_setting( 'responsive-css-desktop', [
		'type' => 'option',
	] );

	$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'responsive-css-breakpoint', [
		'label'    => __( 'Breakpoint', 'responsive-css' ),
		'type'     => 'number',
		'settings' => 'responsive-css-breakpoint',
		'section'  => 'responsive-css',
	] ) );

	$wp_customize->add_control( new \WP_Customize_Code_Editor_Control( $wp_customize, 'responsive-css-all', [
		'label'     => __( 'All Screen Sizes', 'responsive-css' ),
		'code_type' => 'text/css',
		'settings'  => 'responsive-css-all',
		'section'   => 'responsive-css',
	] ) );

	$wp_customize->add_control( new \WP_Customize_Code_Editor_Control( $wp_customize, 'responsive-css-mobile', [
		'label'     => __( 'Mobile Only', 'responsive-css' ),
		'code_type' => 'text/css',
		'settings'  => 'responsive-css-mobile',
		'section'   => 'responsive-css',
	] ) );

	$wp_customize->add_control( new \WP_Customize_Code_Editor_Control( $wp_customize, 'responsive-css-desktop', [
		'label'     => __( 'Desktop Only', 'responsive-css' ),
		'code_type' => 'text/css',
		'settings'  => 'responsive-css-desktop',
		'section'   => 'responsive-css',
	] ) );
}


add_action( 'wp_ajax_dynamic_css', 'responsive_css_dynamic_css' );
add_action( 'wp_ajax_nopriv_dynamic_css', 'responsive_css_dynamic_css' );
/**
 * Load the dynamic CSS with ajax.
 *
 * @since 1.0.0
 *
 * @return void
 */
function responsive_css_dynamic_css() {
	$nonce = $_REQUEST['wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'dynamic-css-nonce' ) ) {
		die( __( 'Invalid nonce.', 'responsive-css' ) );

	} else {
		require_once __DIR__ . '/dynamic-css.php';
	}

	exit;
}

add_action( 'wp_enqueue_scripts', 'responsive_css_output' );
/**
 * Outputs Additional JS to site footer.
 *
 * @since  1.0.0
 *
 * @return void
 */
function responsive_css_output() {
	$all     = get_option( 'responsive-css-all', '' );
	$mobile  = get_option( 'responsive-css-mobile', '' );
	$desktop = get_option( 'responsive-css-desktop', '' );

	if ( is_customize_preview() ) {
		$css = $all;
		$css .= sprintf(
			'@media %s{%s}',
			responsive_css_mq( 'mobile' ),
			$mobile
		);
		$css .= sprintf(
			'@media %s{%s}',
			responsive_css_mq( 'desktop' ),
			$desktop
		);

		wp_register_style( 'responsive-css', false );
		wp_enqueue_style( 'responsive-css' );
		wp_add_inline_style( 'responsive-css', $css );

	} else {
		wp_enqueue_style(
			'responsive-css-all',
			admin_url( 'admin-ajax.php' ) . '?action=dynamic_css&wpnonce=' . wp_create_nonce( 'dynamic-css-nonce' ) . '&size=all',
			[],
			'1.0.0',
			responsive_css_mq( 'all' )
		);
		wp_enqueue_style(
			'responsive-css-mobile',
			admin_url( 'admin-ajax.php' ) . '?action=dynamic_css&wpnonce=' . wp_create_nonce( 'dynamic-css-nonce' ) . '&size=mobile',
			[],
			'1.0.0',
			responsive_css_mq( 'mobile' )
		);
		wp_enqueue_style(
			'responsive-css-desktop',
			admin_url( 'admin-ajax.php' ) . '?action=dynamic_css&wpnonce=' . wp_create_nonce( 'dynamic-css-nonce' ) . '&size=desktop',
			[],
			'1.0.0',
			responsive_css_mq( 'desktop' )
		);
	}
}

/**
 * Returns a media query string.
 *
 * @since 1.0.0
 *
 * @param $size
 *
 * @return string
 */
function responsive_css_mq( $size = 'all' ) {
	$breakpoint = get_option( 'responsive-css-breakpoint', '' );

	if ( 'mobile' === $size ) {
		return '(max-width:' . $breakpoint . 'px)';

	} else if ( 'desktop' === $size ) {
		return '(min-width:' . $breakpoint . 'px)';

	} else {
		return 'all';
	}
}
