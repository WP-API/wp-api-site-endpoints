<?php

/**
 * Manage a WordPress site's settings.
 */

class WP_REST_Settings_Controller extends WP_REST_Controller {

	protected $namespace = 'settings';
	protected $rest_base = 'wp/v2';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'          => WP_REST_Server::READABLE,
				'callback'         => array( $this, 'get_item' ),
				'args'             => array(),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods'          => WP_REST_Server::EDITABLE,
				'callback'         => array( $this, 'update_item' ),
				'args'             => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	public function get_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|array
	 */
	public function get_item( $request ) {
		$options  = $this->get_registered_options();
		$response = array();

		foreach ( $options as $name => $args ) {
			// default to a null value as "null" in the response means "not set"
			$response[ $name ] = get_option( $args['option_name'], $args['schema']['default'] );

			// because get_option() is lossy, we have to
			// cast values to the type they are registered with.
			switch ( $args['schema']['type'] ) {
				case 'string':
					$response[ $name ] = strval( $response[ $name ] );
					break;
				case 'number':
					$response[ $name ] = floatval( $response[ $name ] );
					break;
				case 'boolean':
					$response[ $name ] = (bool) $response[ $name ];
					break;
			}
		}

		return $response;
	}

	/**
	 * Update settings for the settings object.
	 *
	 * @param  WP_REST_Request $request Full detail about the request.
	 * @return WP_Error|array
	 */
	public function update_item( $request ) {
		$options  = $this->get_registered_options();
		$params = $request->get_params();

		foreach ( $options as $name => $args ) {
			if ( array_key_exists( $name, $params ) ) {
				// a null value means reset the option, which is essentially deleting it
				// from the database and then relying on the default value.
				if ( is_null( $request[ $name ] ) ) {
					delete_option( $args['option_name'] );
				} else {
					update_option( $args['option_name'], $request[ $name ] );
				}
			}
		}

		return $this->get_item( $request );
	}

	/**
	 * Get all the registered options for the Settings API
	 *
	 * @return array
	 */
	protected function get_registered_options() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$rest_options = array();

		foreach ( get_registered_settings() as $name => $args ) {
			if ( empty( $args['show_in_rest'] ) ) {
				continue;
			}

			$rest_args = array();
			if ( is_array( $args['show_in_rest'] ) ) {
				$rest_args = $args['show_in_rest'];
			}

			$rest_args = array_merge( $rest_args, array(
				'option_name' => $name,
				'name'        => ! empty( $rest_args['name'] ) ? $rest_args['name'] : $name,
				'schema'      => array(
					'type'    => empty( $args['type'] ) ? null : $args['type'],
					'description' => empty( $args['description'] ) ? '' : $args['description'],
					'default'     => isset( $args['default'] ) ? $args['default'] : null,
				),
			));

			// skip over settings that don't have a defined type in the schema
			if ( empty( $rest_args['schema']['type'] ) ) {
				continue;
			}

			$rest_options[ $rest_args['name'] ] = $rest_args;
		}

		return $rest_options;
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
			'title'      => 'settings',
			'type'       => 'object',
			'properties' => array(),
		);

		foreach ( $options as $option_name => $option ) {
			$schema['properties'][ $option_name ] = $option['schema'];
		}

		return $this->add_additional_fields_schema( $schema );
	}
}
