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


	$site_route = new WP_REST_Settings_Controller();
	$site_route->register_routes();

}
