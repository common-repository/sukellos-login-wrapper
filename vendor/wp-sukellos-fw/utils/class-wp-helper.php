<?php

namespace Sukellos\WPFw\Utils;

use Sukellos\WPFw\WP_PLoad;

defined( 'ABSPATH' ) or exit;


/**
 *  Helper Class
 *
 * The purpose of this class is to centralize common utility functions that
 * are commonly used in SkyVerge plugins
 *
 * @since 1.0.0
 */
class WP_Helper {


    /** encoding used for mb_*() string functions */
    const MB_ENCODING = 'UTF-8';

    /**
     * Get a french representation of the datetime
     *
     * @param $datetime
     * @param $format french format, default %A %d %B %Y / %Hh%M
     * @return string representation
     */
    public static function get_datetime_fr_format( $datetime, $format='%A %d %B %Y / %Hh%M' ) {

        return utf8_encode(strftime($format, $datetime->getTimestamp()));
    }


    /**
     * Function which can be used in array_walk_recursive as callback to reduced huge string values in an array
     *
     * @param $string
     * @param $index
     * @return void
     */
    public static function reduce_array_value ( &$string, $index ) {

        if ( is_string( $string ) && ( strlen( $string ) > 2048 ) ) {

            $string = substr( $string, 0, 4800 ).' [HUGE STRING REDUCED...]';
        }
    }

    /**
     * Determines whether a plugin is active, and keep active plugins as instance array
     *
     * @since 1.0.0
     *
     * @param string $plugin_name plugin name corresponding with <plugin_name>/<plugin_name>.php or something/<plugin_name>.php or <plugin_name>/something.php
     * @return boolean true if the named plugin is installed and active, otherwise false
     */
    public static function is_plugin_active( $plugin_name ) {

        $is_active = false;

        $active_plugins = apply_filters( 'active_plugins', get_option('active_plugins') );

        if (is_multisite()) {
            $active_plugins = array_merge( $active_plugins, array_keys(get_site_option('active_sitewide_plugins', array())) );
        }

        foreach ( $active_plugins as $active_plugin ) {

            if (WP_Helper::str_exists( $active_plugin, $plugin_name )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an array of all thumbnail sizes, including their label, size, and crop setting.
     *
     * @return array An array, with the thumbnail label as the key and an array of thumbnail properties (width, height, crop).
     */
    public static function get_image_sizes() {
        global $_wp_additional_image_sizes;

        $thumbnail_sizes = array();

        foreach ( get_intermediate_image_sizes() as $size ) {
            $thumbnail_sizes[ $size ]['label'] = $size;
            if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
                $thumbnail_sizes[ $size ]['width']  = (int) get_option( $size . '_size_w' );
                $thumbnail_sizes[ $size ]['height'] = (int) get_option( $size . '_size_h' );
                $thumbnail_sizes[ $size ]['crop']   = ( 'thumbnail' == $size ) ? (bool) get_option( 'thumbnail_crop' ) : false;
            } elseif ( ! empty( $_wp_additional_image_sizes ) && ! empty( $_wp_additional_image_sizes[ $size ] ) ) {
                $thumbnail_sizes[ $size ]['width']  = (int) $_wp_additional_image_sizes[ $size ]['width'];
                $thumbnail_sizes[ $size ]['height'] = (int) $_wp_additional_image_sizes[ $size ]['height'];
                $thumbnail_sizes[ $size ]['crop']   = (bool) $_wp_additional_image_sizes[ $size ]['crop'];
            }
        }

        return $thumbnail_sizes;
    }

    /** String manipulation functions (all multi-byte safe) ***************/

    /**
     * Returns true if the haystack string starts with needle
     *
     * Note: case-sensitive
     *
     * @since 1.0.0
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function str_starts_with( $haystack, $needle ) {

        if ( self::multibyte_loaded() ) {

            if ( '' === $needle ) {
                return true;
            }

            return 0 === mb_strpos( $haystack, $needle, 0, self::MB_ENCODING );

        } else {

            $needle = self::str_to_ascii( $needle );

            if ( '' === $needle ) {
                return true;
            }

            return 0 === strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
        }
    }

    /**
     * Get a media ID from its filename, including extensions or not
     * @param type $filename
     * @return type
     */
    public static function get_media_id_from_filename( $filename ) {
        
        $ext = array(".png", ".jpg", ".gif", ".jpeg");
        $filename = str_replace($ext, "", $filename);
        $clean_filename = trim(html_entity_decode(sanitize_title($filename)));
        $page = get_page_by_title($clean_filename, OBJECT, 'attachment');
        return $page->ID;
    }


    /**
     * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
     */
    public static function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
        global $wp_filter;

        // Take only filters on right hook name and priority
        if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
            return false;
        }

        // Loop on filters registered
        foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
            // Test if filter is an array ! (always for class/method)
            if ( isset( $filter_array['function'] ) ) {
                if (is_array($filter_array['function'])) {
                    // Test if object is a class and method is equal to param !
                    if (is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && $filter_array['function'][1] == $method_name) {
                        // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                        if (is_a($wp_filter[$hook_name], 'WP_Hook')) {
                            unset($wp_filter[$hook_name]->callbacks[$priority][$unique_id]);
                        } else {
                            unset($wp_filter[$hook_name][$priority][$unique_id]);
                        }
                    }
                }
                // Else this is a static call
                else {
                    if (is_a($wp_filter[$hook_name], 'WP_Hook')) {
                        unset($wp_filter[$hook_name]->callbacks[$priority][$unique_id]);
                    } else {
                        unset($wp_filter[$hook_name][$priority][$unique_id]);
                    }
                }
            }

        }

        return false;
    }

    /**
     * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
     */
    public static function remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
        global $wp_filter;

        // Take only filters on right hook name and priority
        if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
            return false;
        }

        // Loop on filters registered
        foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
            // Test if filter is an array ! (always for class/method)
            if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
                // Test if object is a class, class and method is equal to param !
                if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
                    // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                    if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                        unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                    } else {
                        unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                    }
                }
            }

        }

        return false;
    }


    /**
     * Check if a image already exist in media
     * @global type $wpdb
     * @param type $filename
     * @return type
     */
    public static function media_file_already_exists( $filename, $boolean_response=true ) {

        // Must consider a -1.jpeg or -2.jpeg in file name, for example
        $exts = array(".png", ".jpg", ".gif", ".jpeg");
        foreach ( $exts as $ext ) {

            if ( FALSE !== strpos( $filename, $ext ) ) {

                $filename = str_replace($ext, "", $filename);
            }
        }
        require_once( ABSPATH . 'wp-admin/includes/post.php' );
        $image_exists = post_exists( $filename, '', '', 'attachment' );
        if ( $boolean_response ) return ( $image_exists > 0 );
        else return $image_exists;
        /*
        global $wpdb;

        $select = '*';
        if ( $boolean_response ) {
            $select = 'COUNT(*)';
        }
        // Very slow query -> Better search in posts on KEY post_name
        //$query = "SELECT ".$select." FROM {$wpdb->postmeta} WHERE meta_value LIKE '%$filename' ";
        // Image is stored as post :
        // - post_name -> name without extension .png or .jpeg ...
        // - post_type -> attachment

        // Must consider a -1.jpeg or -2.jpeg in file name, for example
        $exts = array(".png", ".jpg", ".gif", ".jpeg");
        foreach ( $exts as $ext ) {

            if ( FALSE !== strpos( $filename, $ext ) ) {

                $filename = str_replace($ext, "", $filename);
                $query .= " OR meta_value LIKE '%/$filename-%".$ext."'";
            }
        }

        if ( $boolean_response ) {
            $result = $wpdb->get_var($query);
            return ( $result > 0 );
        } else {

            $result = $wpdb->get_results($query);
            return $result ;
        }
        */
    }


    /**
     * Replace accent characters with non accentued ones
     * Eg. é becomes e
     * @param $string
     * @return array|string|string[]
     */
    public static function replace_accents( $string ) {

        $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
        $string = str_replace($search, $replace, $string);
        return $string;
    }

    /**
     * Get the filename from the URL, without that after ?
     * 
     * @param type $url the given URL
     */
    public static function get_url_strict_basename( $url ) {
        
        $e = explode( "?",basename( $url ) );
        return $e[0];
    }

    /**
     * Return true if the haystack string ends with needle
     *
     * Note: case-sensitive
     *
     * @since 1.0.0
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function str_ends_with( $haystack, $needle ) {

        if ( '' === $needle ) {
            return true;
        }

        if ( self::multibyte_loaded() ) {

            return mb_substr( $haystack, -mb_strlen( $needle, self::MB_ENCODING ), null, self::MB_ENCODING ) === $needle;

        } else {

            $haystack = self::str_to_ascii( $haystack );
            $needle   = self::str_to_ascii( $needle );

            return substr( $haystack, -strlen( $needle ) ) === $needle;
        }
    }


    /**
     * Returns true if the needle exists in haystack
     *
     * Note: case-sensitive
     *
     * @since 1.0.0
     * @param string $haystack the string to search in
     * @param string $needle the string to search for
     * @return bool
     */
    public static function str_exists( $haystack, $needle ) {

        if ( self::multibyte_loaded() ) {

            if ( '' === $needle ) {
                return false;
            }

            return false !== mb_strpos( $haystack, $needle, 0, self::MB_ENCODING );

        } else {

            $needle = self::str_to_ascii( $needle );

            if ( '' === $needle ) {
                return false;
            }

            return false !== strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
        }
    }


    /**
     * Truncates a given $string after a given $length if string is longer than
     * $length. The last characters will be replaced with the $omission string
     * for a total length not exceeding $length
     *
     * @since 1.0.0
     * @param string $string text to truncate
     * @param int $length total desired length of string, including omission
     * @param string $omission omission text, defaults to '...'
     * @return string
     */
    public static function str_truncate( $string, $length, $omission = '...' ) {

        if ( self::multibyte_loaded() ) {

            // bail if string doesn't need to be truncated
            if ( mb_strlen( $string, self::MB_ENCODING ) <= $length ) {
                return $string;
            }

            $length -= mb_strlen( $omission, self::MB_ENCODING );

            return mb_substr( $string, 0, $length, self::MB_ENCODING ) . $omission;

        } else {

            $string = self::str_to_ascii( $string );

            // bail if string doesn't need to be truncated
            if ( strlen( $string ) <= $length ) {
                return $string;
            }

            $length -= strlen( $omission );

            return substr( $string, 0, $length ) . $omission;
        }
    }


    /**
     * Returns a string with all non-ASCII characters removed. This is useful
     * for any string functions that expect only ASCII chars and can't
     * safely handle UTF-8. Note this only allows ASCII chars in the range
     * 33-126 (newlines/carriage returns are stripped)
     *
     * @since 1.0.0
     * @param string $string string to make ASCII
     * @return string
     */
    public static function str_to_ascii( $string ) {

        // strip ASCII chars 32 and under
        $string = filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );

        // strip ASCII chars 127 and higher
        return filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH );
    }


    /**
     * Return a string with insane UTF-8 characters removed, like invisible
     * characters, unused code points, and other weirdness. It should
     * accept the common types of characters defined in Unicode.
     *
     * The following are allowed characters:
     *
     * p{L} - any kind of letter from any language
     * p{Mn} - a character intended to be combined with another character without taking up extra space (e.g. accents, umlauts, etc.)
     * p{Mc} - a character intended to be combined with another character that takes up extra space (vowel signs in many Eastern languages)
     * p{Nd} - a digit zero through nine in any script except ideographic scripts
     * p{Zs} - a whitespace character that is invisible, but does take up space
     * p{P} - any kind of punctuation character
     * p{Sm} - any mathematical symbol
     * p{Sc} - any currency sign
     *
     * pattern definitions from http://www.regular-expressions.info/unicode.html
     *
     * @since 4.0.0
     *
     * @param string $string
     * @return string
     */
    public static function str_to_sane_utf8( $string ) {

        $sane_string = preg_replace( '/[^\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Zs}\p{P}\p{Sm}\p{Sc}]/u', '', $string );

        // preg_replace with the /u modifier can return null or false on failure
        return ( is_null( $sane_string ) || false === $sane_string ) ? $string : $sane_string;
    }


    /**
     * Helper method to check if the multibyte extension is loaded, which
     * indicates it's safe to use the mb_*() string methods
     *
     * @since 1.0.0
     * @return bool
     */
    protected static function multibyte_loaded() {

        return extension_loaded( 'mbstring' );
    }


    /** Array functions ***************************************************/


    /**
     * Insert the given element after the given key in the array
     *
     * Sample usage:
     *
     * given
     *
     * array( 'item_1' => 'foo', 'item_2' => 'bar' )
     *
     * array_insert_after( $array, 'item_1', array( 'item_1.5' => 'w00t' ) )
     *
     * becomes
     *
     * array( 'item_1' => 'foo', 'item_1.5' => 'w00t', 'item_2' => 'bar' )
     *
     * @since 1.0.0
     * @param array $array array to insert the given element into
     * @param string $insert_key key to insert given element after
     * @param array $element element to insert into array
     * @return array
     */
    public static function array_insert_after( Array $array, $insert_key, Array $element ) {

        $new_array = array();

        foreach ( $array as $key => $value ) {

            $new_array[ $key ] = $value;

            if ( $insert_key == $key ) {

                foreach ( $element as $k => $v ) {
                    $new_array[ $k ] = $v;
                }
            }
        }

        return $new_array;
    }


    /**
     * Convert array into XML by recursively generating child elements
     *
     * First instantiate a new XML writer object:
     *
     * $xml = new XMLWriter();
     *
     * Open in memory (alternatively you can use a local URI for file output)
     *
     * $xml->openMemory();
     *
     * Then start the document
     *
     * $xml->startDocument( '1.0', 'UTF-8' );
     *
     * Don't forget to end the document and output the memory
     *
     * $xml->endDocument();
     *
     * $your_xml_string = $xml->outputMemory();
     *
     * @since 1.0.0
     *
     * @param \XMLWriter $xml_writer XML writer instance
     * @param string|array $element_key name for element, e.g. <per_page>
     * @param string|array $element_value value for element, e.g. 100
     */
    public static function array_to_xml( $xml_writer, $element_key, $element_value = array() ) {

        if ( is_array( $element_value ) ) {

                // handle attributes
                if ( '@attributes' === $element_key ) {

                    foreach ( $element_value as $attribute_key => $attribute_value ) {

                        $xml_writer->startAttribute( $attribute_key );
                        $xml_writer->text( $attribute_value );
                        $xml_writer->endAttribute();
                    }

                    return;
                }

                // handle multi-elements (e.g. multiple <Order> elements)
                if ( is_numeric( key( $element_value ) ) ) {

                    // recursively generate child elements
                    foreach ( $element_value as $child_element_key => $child_element_value ) {

                        $xml_writer->startElement( $element_key );

                        foreach ( $child_element_value as $sibling_element_key => $sibling_element_value ) {
                            self::array_to_xml( $xml_writer, $sibling_element_key, $sibling_element_value );
                        }

                        $xml_writer->endElement();
                    }

                } else {

                    // start root element
                    $xml_writer->startElement( $element_key );

                    // recursively generate child elements
                    foreach ( $element_value as $child_element_key => $child_element_value ) {
                        self::array_to_xml( $xml_writer, $child_element_key, $child_element_value );
                    }

                    // end root element
                    $xml_writer->endElement();
                }

        } else {

            // handle single elements
            if ( '@value' === $element_key ) {

                $xml_writer->text( $element_value );

            } else {

                // wrap element in CDATA tags if it contains illegal characters
                if ( false !== strpos( $element_value, '<' ) || false !== strpos( $element_value, '>' ) ) {

                    $xml_writer->startElement( $element_key );
                    $xml_writer->writeCdata( $element_value );
                    $xml_writer->endElement();

                } else {

                    $xml_writer->writeElement( $element_key, $element_value );
                }
            }
        }
    }


    /**
     * Lists an array as text.
     *
     * Takes an array and returns a list like "one, two, three, and four"
     * with a (mandatory) oxford comma.
     *
     * @since 1.0.0
     *
     * @param array $items items to list
     * @param string|null $conjunction coordinating conjunction, like "or" or "and"
     * @param string $separator list separator, like a comma
     * @return string
     */
    public static function list_array_items( array $items, $conjunction = null, $separator = '' ) {

        if ( ! is_string( $conjunction ) ) {
            $conjunction = _x( 'and', 'coordinating conjunction for a list of items: a, b, and c', WP_PLoad::WP_SK_TEXT_DOMAIN );
        }

        // append the conjunction to the last item
        if ( count( $items ) > 1 ) {

            $last_item = array_pop( $items );

            array_push( $items, trim( "{$conjunction} {$last_item}" ) );

            // only use a comma if needed and no separator was passed
            if ( count( $items ) < 3 ) {
                    $separator = ' ';
            } elseif ( ! is_string( $separator ) || '' === $separator ) {
                $separator = ', ';
            }
        }

        return implode( $separator, $items );
    }


    /** Number helper functions *******************************************/


    /**
     * Format a number with 2 decimal points, using a period for the decimal
     * separator and no thousands separator.
     *
     * Commonly used for payment gateways which require amounts in this format.
     *
     * @since 1.0.0
     * @param float $number
     * @return string
     */
    public static function number_format( $number ) {

        return number_format( (float) $number, 2, '.', '' );
    }


    /** WooCommerce helper functions **************************************/


    /**
     * Safely gets and trims data from $_POST.
     *
     * @since 1.0.0
     * @deprecated 5.5.0
     *
     * @param string $key array key to get from $_POST array
     * @return string value from $_POST or blank string if $_POST[ $key ] is not set
     */
    public static function get_post( $key ) {

        wc_deprecated_function( __METHOD__, '5.5.0', __CLASS__ . '::get_posted_value()' );

        return self::get_posted_value( $key );
    }


    /**
     * Safely gets a value from $_POST.
     *
     * If the expected data is a string also trims it.
     *
     * @since 5.5.0
     *
     * @param string $key posted data key
     * @param int|float|array|bool|null|string $default default data type to return (default empty string)
     * @return int|float|array|bool|null|string posted data value if key found, or default
     */
    public static function get_posted_value( $key, $default = '' ) {

        $value = $default;

        if ( isset( $_POST[ $key ] ) ) {
            $value = is_string( $_POST[ $key ] ) ? trim( $_POST[ $key ] ) : $_POST[ $key ];
        }

        return $value;
    }


    /**
     * Safely gets and trims data from $_REQUEST.
     *
     * @since 1.0.0
     * @deprecated 5.5.0
     *
     * @param string $key array key to get from $_REQUEST array
     * @return string value from $_REQUEST or blank string if $_REQUEST[ $key ] is not set
     */
    public static function get_request( $key ) {

        wc_deprecated_function( __METHOD__, '5.5.0', __CLASS__ . '::get_requested_value()' );

        return self::get_requested_value( $key );
    }


    /**
     * Safely gets a value from $_REQUEST.
     *
     * If the expected data is a string also trims it.
     *
     * @since 1.0.0
     *
     * @param string $key posted data key
     * @param int|float|array|bool|null|string $default default data type to return (default empty string)
     * @return int|float|array|bool|null|string posted data value if key found, or default
     */
    public static function get_requested_value( $key, $default = '' ) {

        $value = $default;

        if ( isset( $_REQUEST[ $key ] ) ) {
            $value = is_string( $_REQUEST[ $key ] ) ? trim( $_REQUEST[ $key ] ) : $_REQUEST[ $key ];
        }

        return $value;
    }


    /**
     * Get the count of notices added, either for all notices (default) or for one
     * particular notice type specified by $notice_type.
     *
     * WC notice functions are not available in the admin
     *
     * @since 1.0.0
     * @param string $notice_type The name of the notice type - either error, success or notice. [optional]
     * @return int
     */
    public static function wc_notice_count( $notice_type = '' ) {

        if ( function_exists( 'wc_notice_count' ) ) {
            return wc_notice_count( $notice_type );
        }

        return 0;
    }


    /**
     * Add and store a notice.
     *
     * WC notice functions are not available in the admin
     *
     * @since 1.0.0
     * @param string $message The text to display in the notice.
     * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
     */
    public static function wc_add_notice( $message, $notice_type = 'success' ) {

        if ( function_exists( 'wc_add_notice' ) ) {
            wc_add_notice( $message, $notice_type );
        }
    }


    /**
     * Print a single notice immediately
     *
     * WC notice functions are not available in the admin
     *
     * @since 1.0.0
     * @param string $message The text to display in the notice.
     * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
     */
    public static function wc_print_notice( $message, $notice_type = 'success' ) {

        if ( function_exists( 'wc_print_notice' ) ) {
            wc_print_notice( $message, $notice_type );
        }
    }


    /**
     * Gets the full URL to the log file for a given $handle
     *
     * @since 1.0.0
     * @param string $handle log handle
     * @return string URL to the WC log file identified by $handle
     */
    public static function get_WP_Log_file_url( $handle ) {
        return admin_url( sprintf( 'admin.php?page=wc-status&tab=logs&log_file=%s-%s-log', $handle, sanitize_file_name( wp_hash( $handle ) ) ) );
    }


    /**
     * Gets the current WordPress site name.
     *
     * This is helpful for retrieving the actual site name instead of the
     * network name on multisite installations.
     *
     * @since 1.0.0
     * @return string
     */
    public static function get_site_name() {

        return ( is_multisite() ) ? get_blog_details()->blogname : get_bloginfo( 'name' );
    }


    /** JavaScript helper functions ***************************************/


    /**
     * Enhanced search JavaScript (Select2)
     *
     * Enqueues JavaScript required for AJAX search with Select2.
     *
     * @codeCoverageIgnore no need to unit test this since it's mostly JavaScript
     *
     * @since 1.0.0
     */
    public static function render_select2_ajax() {

        if ( ! did_action( 'sv_wc_select2_ajax_rendered' ) ) {

            $javascript = "( function(){
                    if ( ! $().select2 ) return;
            ";

            // Ensure localized strings are used.
            $javascript .= "

                    function getEnhancedSelectFormatString() {

                            if ( 'undefined' !== typeof wc_select_params ) {
                                    wc_enhanced_select_params = wc_select_params;
                            }

                            if ( 'undefined' === typeof wc_enhanced_select_params ) {
                                    return {};
                            }

                            var formatString = {
                                    formatMatches: function( matches ) {
                                            if ( 1 === matches ) {
                                                    return wc_enhanced_select_params.i18n_matches_1;
                                            }

                                            return wc_enhanced_select_params.i18n_matches_n.replace( '%qty%', matches );
                                    },
                                    formatNoMatches: function() {
                                            return wc_enhanced_select_params.i18n_no_matches;
                                    },
                                    formatAjaxError: function( jqXHR, textStatus, errorThrown ) {
                                            return wc_enhanced_select_params.i18n_ajax_error;
                                    },
                                    formatInputTooShort: function( input, min ) {
                                            var number = min - input.length;

                                            if ( 1 === number ) {
                                                    return wc_enhanced_select_params.i18n_input_too_short_1
                                            }

                                            return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', number );
                                    },
                                    formatInputTooLong: function( input, max ) {
                                            var number = input.length - max;

                                            if ( 1 === number ) {
                                                    return wc_enhanced_select_params.i18n_input_too_long_1
                                            }

                                            return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', number );
                                    },
                                    formatSelectionTooBig: function( limit ) {
                                            if ( 1 === limit ) {
                                                    return wc_enhanced_select_params.i18n_selection_too_long_1;
                                            }

                                            return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', number );
                                    },
                                    formatLoadMore: function( pageNumber ) {
                                            return wc_enhanced_select_params.i18n_load_more;
                                    },
                                    formatSearching: function() {
                                            return wc_enhanced_select_params.i18n_searching;
                                    }
                            };

                            return formatString;
                    }
            ";

            $javascript .= "

                    $( 'select.sv-wc-enhanced-search' ).filter( ':not(.enhanced)' ).each( function() {

                            var select2_args = {
                                    allowClear:         $( this ).data( 'allow_clear' ) ? true : false,
                                    placeholder:        $( this ).data( 'placeholder' ),
                                    minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
                                    escapeMarkup:       function( m ) {
                                            return m;
                                    },
                                    ajax:               {
                                            url:            '" . esc_js( admin_url( 'admin-ajax.php' ) ) . "',
                                            dataType:       'json',
                                            cache:          true,
                                            delay:          250,
                                            data:           function( params ) {
                                                    return {
                                                            term:         params.term,
                                                            request_data: $( this ).data( 'request_data' ) ? $( this ).data( 'request_data' ) : {},
                                                            action:       $( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
                                                            security:     $( this ).data( 'nonce' )
                                                    };
                                            },
                                            processResults: function( data, params ) {
                                                    var terms = [];
                                                    if ( data ) {
                                                            $.each( data, function( id, text ) {
                                                                    terms.push( { id: id, text: text } );
                                                            });
                                                    }
                                                    return { results: terms };
                                            }
                                    }
                            };

                            select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

                            $( this ).select2( select2_args ).addClass( 'enhanced' );
                    } );
            ";

            $javascript .= '} )();';

            wc_enqueue_js( $javascript );

            /**
             * WC Select2 Ajax Rendered Action.
             *
             * Fired when an Ajax select2 is rendered.
             *
             * @since 3.1.0
             */
            do_action( 'wc_select2_ajax_rendered' );
        }
    }


    /** Framework translation functions ***********************************/


    /**
     * Gettext `__()` wrapper for framework-translated strings
     *
     * Warning! This function should only be used if an existing
     * translation from the framework is to be used. It should
     * never be called for plugin-specific or untranslated strings!
     * Untranslated = not registered via string literal.
     *
     * @since 4.1.0
     * @param string $text
     * @return string translated text
     */
    public static function sk__( $text ) {

        return __( $text, WP_PLoad::WP_SK_TEXT_DOMAIN );
    }


    /**
     * Gettext `_e()` wrapper for framework-translated strings
     *
     * Warning! This function should only be used if an existing
     * translation from the framework is to be used. It should
     * never be called for plugin-specific or untranslated strings!
     * Untranslated = not registered via string literal.
     *
     * @since 4.1.0
     * @param string $text
     */
    public static function sk_e( $text ) {

        _e( $text, WP_PLoad::WP_SK_TEXT_DOMAIN );
    }


    /**
     * Gettext `_x()` wrapper for framework-translated strings
     *
     * Warning! This function should only be used if an existing
     * translation from the framework is to be used. It should
     * never be called for plugin-specific or untranslated strings!
     * Untranslated = not registered via string literal.
     *
     * @since 4.1.0
     *
     * @param string $text
     * @param string $context
     * @return string translated text
     */
    public static function sk_x( $text, $context ) {

        return _x( $text, $context, WP_PLoad::WP_SK_TEXT_DOMAIN );
    }


    /** Misc functions ****************************************************/


    /**
     * Gets the WordPress current screen.
     *
     * @see get_current_screen() replacement which is always available, unlike the WordPress core function
     *
     * @since 5.4.2
     *
     * @return \WP_Screen|null
     */
    public static function get_current_screen() {
        global $current_screen;

        return $current_screen ?: null;
    }


    /**
     * Checks if the current screen matches a specified ID.
     *
     * This helps avoiding using the get_current_screen() function which is not always available,
     * or setting the substitute global $current_screen every time a check needs to be performed.
     *
     * @since 5.4.2
     *
     * @param string $id id (or property) to compare
     * @param string $prop optional property to compare, defaults to screen id
     * @return bool
     */
    public static function is_current_screen( $id, $prop = 'id' ) {
        global $current_screen;

        return isset( $current_screen->$prop ) && $id === $current_screen->$prop;
    }


    /**
     * Convert a 2-character country code into its 3-character equivalent, or
     * vice-versa, e.g.
     *
     * 1) given USA, returns US
     * 2) given US, returns USA
     *
     * @since 1.0.0
     * @deprecated 5.4.3
     *
     * @param string $code ISO-3166-alpha-2 or ISO-3166-alpha-3 country code
     * @return string country code
     */
    public static function convert_country_code( $code ) {

        wc_deprecated_function( __METHOD__, '5.4.3', Country_Helper::class . '::convert_alpha_country_code()' );

        return Country_Helper::convert_alpha_country_code( $code );
    }


    /**
     * Displays a notice if the provided hook has not yet run.
     *
     * @since 5.2.0
     *
     * @param string $hook action hook to check
     * @param string $method method/function name
     * @param string $version version the notice was added
     */
    public static function maybe_doing_it_early( $hook, $method, $version ) {

        if ( ! did_action( $hook ) ) {
            wc_doing_it_wrong( $method, "This should only be called after '{$hook}'", $version );
        }
    }


}
