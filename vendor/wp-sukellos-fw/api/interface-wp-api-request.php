<?php

namespace Sukellos\WPFw\Api;

defined( 'ABSPATH' ) or exit;

/**
 * API Request
 */
interface WP_API_Request {


    /**
     * Returns the method for this request: one of HEAD, GET, PUT, PATCH, POST, DELETE
     *
     * @since 1.0.0
     * @return string the request method, or null to use the API default
     */
    public function get_method();


    /**
     * Returns the request path
     *
     * @since 1.0.0
     * @return string the request path, or '' if none
     */
    public function get_path();


    /**
     * Gets the request query params.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_params();


    /**
     * Gets the request data.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_data();


    /**
     * Returns the string representation of this request
     *
     * @since 1.0.0
     * @return string the request
     */
    public function to_string();


    /**
     * Returns the string representation of this request with any and all
     * sensitive elements masked or removed
     *
     * @since 1.0.0
     * @return string the request, safe for logging/displaying
     */
    public function to_string_safe();

}