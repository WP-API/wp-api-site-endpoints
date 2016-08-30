<?php

/**
 * Manage a WordPress site
 */

class WP_REST_Site_Controller extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'wp/v2';
		$this->rest_base = 'site';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_items' ),
				'args'     => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	public function get_items_permissions_check( $request ) {

	}

	/**
	 * Get a collection of site settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$options  = $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE );
		$response = array();

		foreach ( $options as $name => $args ) {
			if ( ! $this->get_item_mapping( $name ) ) {
				continue;
			}

			$response[ $name ] = $this->prepare_item_for_response( $name, $request );
		}

		return rest_ensure_response( $response );
	}

	public function delete_item_permission_check( $request ) {

	}

	public function delete_item( $request ) {

	}

	/**
	 * Get all the registered options for the Settings API
	 *
	 * @return array
	 */
	protected function get_registered_options() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		register_setting( 'general', 'test', array(
			'show_in_rest' => true,
			'type' => 'string',
			'description' => 'This is a test',
		) );

		return wp_list_filter( get_registered_settings(), array( 'show_in_rest' => true ) );
	}

	/**
	 * Prepare a site setting for response
	 *
	 * @param  string           $option_name The option name
	 * @param  WP_REST_Request  $request
	 * @return string           $value       The option value
	 */
	public function prepare_item_for_response( $option_name, $request ) {
		$schema = $this->get_item_schema();
		$value  = get_option( $this->get_item_mapping( $option_name ) );
		$value  = ( ! $value && isset( $schema['properties'][ $option_name ]['default'] ) ) ? $schema['properties'][ $option_name ]['default'] : $value;

		if ( isset( $schema['properties'][ $option_name ]['type'] ) ) {
			settype( $value, $schema['properties'][ $option_name ]['type'] );
		}

		return $value;
	}

	/**
	 * Get the site setting schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$options = $this->get_registered_options();

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'site',
			'type'       => 'object',
			'properties' => array(),
		);

		foreach ( $options as $option_name => $option ) {
			if ( $option['rest_base'] ) {
				$option_name = $option['rest_base'];
			}

			$schema['properties'][ $option_name ] = array(
				'title'      => $option_name,
				'type'       => $option['type'],
				'descrption' => $option['description'],
			);

			if ( isset( $option['rest_schema'] ) ) {
				$schema['properties'][ $option_name ] = array_merge( $schema['properties'][ $option_name ], $option['rest_schema'] );
			}
		}

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

	/**
	 * Return the mapped option name
	 *
	 * @param  string $option_name The API option name
	 * @return string|bool         The mapped option name, or false on failure
	 */
	protected function get_item_mapping( $option_name ) {
		$mappings = $this->get_item_mappings();

		return isset( $mappings[ $option_name ] ) ? $mappings[ $option_name ] : false;
	}

}
