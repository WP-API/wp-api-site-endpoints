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
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'site',
			'type'       => 'object',
			'properties' => array(
				'title' => array(
					'description' => __( 'Site Title' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'tagline' => array(
					'description' => __( 'Tagline' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'wordpress_url' => array(
					'description' => __( 'WordPress Address (URL)' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
				),
				'url' => array(
					'description' => __( 'Site Address (URL)' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
				),
				'admin_email' => array(
					'description' => __( 'Email Address' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_email',
					),
				),
				'users_can_register' => array(
					'description' => __( 'Membership' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
				),
				'timezone_string' => array(
					'description' => __( 'Timezone' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'default'     => 'UTC',
				),
				'date_format' => array(
					'description' => __( 'Date Format' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'time_format' => array(
					'description' => __( 'Time Format' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'start_of_week' => array(
					'description' => __( 'Week Starts On' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'absint',
					),
				),
				'locale' => array(
					'description' => __( 'Site Language' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'default'     => 'en_US',
				),
				'permalink_structure' => array(
					'description' => __( 'Permalink Settings' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'permalink_category_base' => array(
					'description' => __( 'Category base' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'permalink_tag_base' => array(
					'description' => __( 'Tag base' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

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
	 * Return an array of option name mappings
	 *
	 * @return array
	 */
	public function get_item_mappings() {
		return array(
			'title'                   => 'blogname',
			'tagline'                 => 'blogdescription',
			'wordpress_url'           => 'siteurl',
			'url'                     => 'home',
			'admin_email'             => 'admin_email',
			'users_can_register'      => 'users_can_register',
			'timezone_string'         => 'timezone_string',
			'date_format'             => 'date_format',
			'time_format'             => 'time_format',
			'start_of_week'           => 'start_of_week',
			'locale'                  => 'WPLANG',
			'permalink_structure'     => 'permalink_structure',
			'permalink_category_base' => 'category_base',
			'permalink_tag_base'      => 'tag_base',
		);
	}

	/**
	 * Return the mapped option name
	 *
	 * @param  string $option_name The API option name
	 * @return string|bool         The mapped option name, or false on failure
	 */
	public function get_item_mapping( $option_name ) {
		$mappings = $this->get_item_mappings();

		return isset( $mappings[ $option_name ] ) ? $mappings[ $option_name ] : false;
	}

}
