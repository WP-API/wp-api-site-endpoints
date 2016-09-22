<?php
/**
 * Plugin Name: WP REST API - Site Endpoint
 * Description: Site endpoint for the WP REST API
 * Author: WP REST API Team
 * Author URI: http://wp-api.org
 * Version: 0.1.0
 * Plugin URI: https://github.com/WP-API/wp-api-site-endpoints
 * License: GPL2+
 */

add_action( 'rest_api_init', 'rest_create_settings_routes', 0 );

function rest_create_settings_routes() {
	if ( class_exists( 'WP_REST_Controller' )
		&& ! class_exists( 'WP_REST_Settings_Controller' ) ) {
			require_once dirname( __FILE__ ) . '/lib/class-wp-rest-settings-controller.php';
	}

	rest_register_settings();

	$site_route = new WP_REST_Settings_Controller();
	$site_route->register_routes();
}

/**
 * Register the settings to be used in the REST API.
 *
 * This is required are WordPress Core does not internally register
 * it's settings via `register_rest_setting()`. This should be removed
 * once / if core starts to register settings internally.
 */
function rest_register_settings() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	register_setting( 'general', 'blogname', array(
		'show_in_rest'   => array(
			'name'            => 'title',
		),
		'type'           => 'string',
		'description'    => __( 'Site title.' ),
	) );

	register_setting( 'general', 'blogdescription', array(
		'show_in_rest'   => array(
			'name'            => 'description',
		),
		'type'           => 'string',
		'description'    => __( 'Site description.' ),
	) );

	register_setting( 'general', 'siteurl', array(
		'show_in_rest'   => array(
			'name'            => 'url',
			'schema'          => array(
				'format'      => 'uri',
			),
		),
		'type'           => 'string',
		'description'    => __( 'Site URL' ),
	) );

	register_setting( 'general', 'admin_email', array(
		'show_in_rest'   => array(
			'name'            => 'email',
			'schema'          => array(
				'format'      => 'email',
			),
		),
		'type'           => 'string',
		'description'    => __( 'This address is used for admin purposes. If you change this we will send you an email at your new address to confirm it. The new address will not become active until confirmed.' ),
	) );

	register_setting( 'general', 'timezone_string', array(
		'show_in_rest'   => array(
			'name'            => 'timezone',
		),
		'type'           => 'string',
		'description'    => __( 'A city in the same timezone as you.' ),
	) );

	register_setting( 'general', 'date_format', array(
		'show_in_rest'   => true,
		'type'           => 'string',
		'description'    => __( 'A date format for all date strings.' ),
	) );

	register_setting( 'general', 'time_format', array(
		'show_in_rest'   => true,
		'type'           => 'string',
		'description'    => __( 'A time format for all time strings.' ),
	) );

	register_setting( 'general', 'start_of_week', array(
		'show_in_rest'   => true,
		'type'           => 'number',
		'description'    => __( 'A day number of the week that the week should start on.' ),
	) );

	register_setting( 'general', 'WPLANG', array(
		'show_in_rest'   => array(
			'name'       => 'language',
		),
		'type'           => 'string',
		'description'    => __( 'IETF "like" WordPress locale code.' ),
		'default'        => 'en_US',
	) );

	register_setting( 'writing', 'use_smilies', array(
		'show_in_rest'   => true,
		'type'           => 'boolean',
		'description'    => __( 'Convert emoticons like :-) and :-P to graphics on display.' ),
		'default'        => true,
	) );

	register_setting( 'writing', 'default_category', array(
		'show_in_rest'   => true,
		'type'           => 'number',
		'description'    => __( 'Default category.' ),
	) );

	register_setting( 'writing', 'default_post_format', array(
		'show_in_rest'   => true,
		'type'           => 'string',
		'description'    => __( 'Default post format.' ),
	) );

	register_setting( 'reading', 'posts_per_page', array(
		'show_in_rest'   => true,
		'type'           => 'number',
		'description'    => __( 'Blog pages show at most.' ),
		'default'        => 10,
	) );
}
