<?php

namespace Sukellos\WPFw\Api;

defined( 'ABSPATH' ) or exit;

/**
 * Base JSON API response class.
 *
 * @since 1.0.0
 */
abstract class WP_API_JSON_Response implements WP_API_Response {


    /** @var string string representation of this response */
    protected $raw_response_json;

    /** @var mixed decoded response data */
    public $response_data;


    /**
     * Build the data object from the raw JSON.
     *
     * @since 1.0.0
     * @param string $raw_response_json The raw JSON
     */
    public function __construct( $raw_response_json ) {

        $this->raw_response_json = $raw_response_json;

        $this->response_data = json_decode( $raw_response_json );
    }


    /**
     * Magic accessor for response data attributes
     *
     * @since 1.0.0
     * @param string $name The attribute name to get.
     * @return mixed The attribute value
     */
    public function __get( $name ) {

        // accessing the response_data object indirectly via attribute (useful when it's a class)
        return isset( $this->response_data->$name ) ? $this->response_data->$name : null;
    }


    /**
     * Get the string representation of this response.
     *
     * @since 1.0.0
     * @see WP_API_Response::to_string()
     * @return string
     */
    public function to_string() {

        return $this->raw_response_json;
    }


    /**
     * Get the string representation of this response with any and all sensitive elements masked
     * or removed.
     *
     * @since 1.0.0
     * @see WP_API_Response::to_string_safe()
     * @return string
     */
    public function to_string_safe() {

        return $this->to_string();
    }
}
