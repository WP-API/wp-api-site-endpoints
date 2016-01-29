<?php
/**
 * Plugin Name: WP REST API - Site Endpoint
 * Description: Site endpoint for the WP REST API
 * Author: WP REST API Team
 * Author URI: http://wp-api.org
 * Version: 0.1.0
 * Plugin URI: https://github.com/WP-API/wp-api-site-endpoint
 * License: GPL2+
 */

add_action( 'rest_api_init', 'create_site_routes', 0 );

function create_site_routes() {

	if ( class_exists( 'WP_REST_Controller' )
		&& ! class_exists( 'WP_REST_Site_Controller' ) ) {
			require_once dirname( __FILE__ ) . '/lib/class-wp-rest-site-controller.php';
	}

	$site_route = new WP_REST_Site_Controller();
	$site_route->register_routes();

}
