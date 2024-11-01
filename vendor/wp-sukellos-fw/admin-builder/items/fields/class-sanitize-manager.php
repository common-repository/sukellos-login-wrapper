<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Manager all sanitize strategies
 *
 * @since 1.0
 */
class Sanitize_Manager {

    /**
     * Get sanitized value, based on field type
     *
     * @param $value the raw value
     * @param $field_type the \Item_Type of the field value
     * @return mixed depending on the field type
     */
    public static function get_sanitized_value( $value, $field_type ) {

        switch ( $field_type ) {

            case Item_Type::TEXT:
            case Item_Type::TEXTAREA:
            case Item_Type::COLOR:
            case Item_Type::FILE:
            case Item_Type::RADIO:
            case Item_Type::RADIO_IMAGE:
            case Item_Type::RADIO_FONT_ICON:
            case Item_Type::RADIO_PALETTE:

                return $value;

            case Item_Type::ENABLE:
            case Item_Type::CHECKBOX:

                if ( is_bool( $value ) ) {
                    return $value;
                }
                return ($value === '1') ? true : false;

            case Item_Type::NUMBER:

                if ( $value == '' ) {
                    return 0;
                }
                return $value;

            case Item_Type::DATE:

                if ( $value == 0 ) {
                    return '';
                }
                return $value;

            case Item_Type::CODE:
            case Item_Type::WYSIWYG_EDITOR:

                return stripslashes( $value );

            case Item_Type::EDD_LICENSING:

                return sanitize_text_field( $value );

            case Item_Type::SELECT:
            case Item_Type::SELECT_POSTS:
            case Item_Type::SELECT_TERMS:
            case Item_Type::SELECT_USERS:
            case Item_Type::SELECT_WEBSAFE_FONT_FAMILY:
            case Item_Type::SELECT_GOOGLE_FONT_FAMILY:

                if ( ! is_array( $value ) ) {
                    $value = (array) $value;
                }
                return $value;

            case Item_Type::MULTICHECK:
            case Item_Type::MULTICHECK_POSTS:
            case Item_Type::MULTICHECK_TERMS:
            case Item_Type::MULTICHECK_USERS:

                // Must return an array of values
                if ( empty( $value ) ) {

                    return array();
                }
                if ( is_array( $value ) ) {

                    return $value;
                }
                if ( is_serialized( $value ) ) {

                    return unserialize( $value );
                }
                if ( is_string( $value ) ) {

                    return explode( ',', $value );
                }
                return $value;

            case Item_Type::GALLERY:

                if ( is_array( $value ) ) {

                    $value = $value[0];
                }
                return $value;

            case Item_Type::SORTABLE:

                if ( is_array( $value ) ) {

                    return $value;
                }
                if ( is_serialized( stripslashes( $value ) ) ) {

                    return unserialize( $value );
                }
                return $value;

            case Item_Type::UPLOAD:

                if ( is_array( $value ) ) {

                    $value = $value[0];
                }
                return $value;
        }
    }
    
}
