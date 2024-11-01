<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WPFw\AdminBuilder\Fields\Checkbox_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Custom_Choices;
use Sukellos\WPFw\AdminBuilder\Fields\Date_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Edd_Licensing_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Multicheck_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Radio_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Select_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Text_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Textarea_Field;
use Sukellos\WPFw\AdminBuilder\Fields\Form_Field_Handler;
use Sukellos\WPFw\AdminBuilder\Items\Ajax_Button;
use Sukellos\WPFw\AdminBuilder\Items\Header;
use Sukellos\WPFw\AdminBuilder\Items\Content;
use Sukellos\WPFw\AdminBuilder\Items\Note;
use Sukellos\WPFw\Singleton;
use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\Utils\WP_Log;
use Sukellos\WPFw\AdminBuilder\Fields\Option_Handler;
use ReflectionClass;

defined( 'ABSPATH' ) or exit;

/**
 * Item Factory Class - Factory and Flyweight pattern of Fields
 *
 * Manages all Items and Fields (sub items) instances
 * A Field can be an option, an user_meta, a post_meta
 * An Item can be an AJAX button
 *
 * @since 1.0.0
 */
class Item_Factory {

    // Use Trait Singleton
    use Singleton;

    // JQuery pickers used ?
    private $enabled_once_scripts = array();

    // Use type of option, user_meta, post_meta
    const OPTION = 'option';
    const FORM_FIELD = 'form_field';


    /**
     * All item instances (non fields)
     * Format for items: array of : <item_id> => Item
     * Format for fields: in a sub array (option), and array of : <field_id> => Item
     *
     * @var array
     */
    private $item_instances = array(
        self::OPTION => array(),
        self::FORM_FIELD => array(),
    );

    /**
     * Default init method called when instance created
     * This method can be overridden if needed.
     *
     * @since 1.0.0
     * @access protected
     */
    public function init() {

        // Reset some vars

        WP_Log::debug( 'Item_Factory->init OK');
    }

    /**
     * Creates an instance of an Item
     *
     * @since 1.0.0
     *
     * @param array $settings The settings used to create options.
     *
     * @return Item
     */
    public function create_item( $settings ) {

        WP_Log::debug('Item_Factory->create_item', ['$settings' => $settings]);

        // Type must be specified
        if ( !array_key_exists( 'type', $settings )) {

            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, 'Item type must be specified.');
            return null;
        }
        $item_type = $settings[ 'type' ];

        // Try and get an id from settings
        if ( !array_key_exists( 'id', $settings )) {

            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                WP_Helper::sk__( '%s needs a %s parameter.' ),
                '<code>' . __FUNCTION__ . '</code>',
                '<code>id</code>'
            ) );
            return null;
        }
        $item_id = $settings[ 'id' ];

        // Item id must not already exist
        if ( array_key_exists( $item_id, $this->item_instances )
            || array_key_exists( $item_id, $this->item_instances[ self::OPTION ] )
            || array_key_exists( $item_id, $this->item_instances[ self::FORM_FIELD ] )
        ) {

            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                WP_Helper::sk__( 'All item IDs must be unique. The id %s has been used multiple times.' ),
                '<code>' . $item_id . '</code>'
            ) );
            return null;
        }

        // Get supported item and field types
        $supported_field_types = Item_Type::get_supported_field_types();
        $supported_item_types = Item_Type::get_supported_item_types();

        // Item
        $item = null;

        // If item is a field, get handler_type
        if ( in_array( $item_type, $supported_field_types ) ) {

            // Get handler type
            $handler_type = $settings[ 'handler_type' ];

            // Determine handler
            $handler = null;
            $default = ( array_key_exists( 'default', $settings ) ? $settings['default'] : '' );
            switch( $handler_type ) {
                case self::OPTION:
                    $handler = new Option_Handler( $item_id, $default );
                    break;
                case self::FORM_FIELD:
                    $handler = new Form_Field_Handler( $item_id, $default );
                    break;
                default:
                    WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                        WP_Helper::sk__( 'Field must be %s.' ),
                        '<code>' . self::OPTION . '</code>',
                        '<code>id</code>'
                    ) );
                    return null;
            }

            // Depends on field type
            switch( $item_type ) {
                // Not supported in Basic version
                case Item_Type::FILE:
                case Item_Type::GALLERY:
                case Item_Type::SORTABLE:
                case Item_Type::CODE:
                case Item_Type::SELECT_POSTS:
                case Item_Type::SELECT_TERMS:
                case Item_Type::SELECT_USERS:
                case Item_Type::SELECT_WEBSAFE_FONT_FAMILY:
                case Item_Type::SELECT_GOOGLE_FONT_FAMILY:
                case Item_Type::MULTICHECK_POSTS:
                case Item_Type::MULTICHECK_TERMS:
                case Item_Type::MULTICHECK_USERS:
                case Item_Type::RADIO_IMAGE:
                case Item_Type::RADIO_FONT_ICON:
                case Item_Type::RADIO_PALETTE:
                    WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                        WP_Helper::sk__( 'Item type or extended class %s not supported in basic version.' ),
                        '<code>' . $item_type . '</code>'
                    ) );
                    return null;

                // Specific case used to manage compatibility with Pro (if future activation)
                case Item_Type::EDD_LICENSING:

                    if ( !array_key_exists( 'sukellos_rule_exception', $settings ) || ( !$settings[ 'sukellos_rule_exception' ] ) ) {

                        WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                            WP_Helper::sk__( 'Item type or extended class %s not supported in basic version.' ),
                            '<code>' . $item_type . '</code>'
                        ) );
                        return null;
                    }
                        $item = new Edd_Licensing_Field( $settings, $handler );
                    break;
                // Replaced with Textarea
                case Item_Type::WYSIWYG_EDITOR:
                        $item = new Textarea_Field( $settings, $handler );
                    break;
                // Replaced with Text
                case Item_Type::DATE:
                        if ( !array_key_exists( 'sukellos_rule_exception', $settings ) || ( !$settings[ 'sukellos_rule_exception' ] ) ) {

                            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                                WP_Helper::sk__( 'Item type or extended class %s not supported in basic version.' ),
                                '<code>' . $item_type . '</code>'
                            ) );
                            $item = new Text_Field( $settings, $handler );
                        } else {

                            $item = new Date_Field( $settings, $handler );
                        }
                    break;
                case Item_Type::COLOR:
                case Item_Type::NUMBER:
                        $item = new Text_Field( $settings, $handler );
                    break;
                // Replaced with Checkbox
                case Item_Type::ENABLE:
                        $item = new Checkbox_Field( $settings, $handler );
                    break;

                case Item_Type::TEXTAREA:
                case Item_Type::TEXT:
                case Item_Type::CHECKBOX:
                case Item_Type::UPLOAD:

                        // Dynamically create field instance
                        $class_name = 'Sukellos\WPFw\AdminBuilder\Fields\\'.ucwords( str_replace( '-', '_', $item_type ), '_' ).'_Field';
                        WP_Log::debug('Item_Factory->create_item Field', ['$class_name'=>$class_name, '$settings'=>$settings, '$handler'=>$handler]);

                        // Use ReflectionClass to properly resolve namespace
                        $ref = new ReflectionClass( $class_name );
                        $item = $ref->newInstanceArgs( array( $settings, $handler ) );
                    break;
                case Item_Type::SELECT:

                        $data_strategy = new Custom_Choices();
                        $item = new Select_Field( $settings, $handler, $data_strategy );
                    break;
                case Item_Type::MULTICHECK:

                        $data_strategy = new Custom_Choices();
                        $item = new Multicheck_Field( $settings, $handler, $data_strategy );
                    break;
                case Item_Type::RADIO:

                        $data_strategy = new Custom_Choices();
                        $item = new Radio_Field( $settings, $handler, $data_strategy );
                    break;
            }

            // Add field
            $this->item_instances[ ''.$handler_type ][ ''.$item_id ] = $item;
        }
        else if ( in_array( $item_type, $supported_item_types ) ) {

            // Depends on field type
            switch( $item_type ) {
                case Item_Type::AJAX_BUTTON:

                        if ( !array_key_exists( 'sukellos_rule_exception', $settings ) || ( !$settings[ 'sukellos_rule_exception' ] ) ) {

                            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                                WP_Helper::sk__( 'Item type or extended class %s not supported in basic version.' ),
                                '<code>' . $item_type . '</code>'
                            ) );
                            return null;
                        }
                        $item = new Ajax_Button( $settings );
                    break;
                case Item_Type::ADMIN_ITEM_GROUP:
                    WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                        WP_Helper::sk__( 'Item type or extended class %s not supported in basic version.' ),
                        '<code>' . $item_type . '</code>'
                    ) );
                    return null;
                case Item_Type::ADMIN_ITEM_FORM:
                    $item = new Admin_Item_Form( $settings );
                    break;
                case Item_Type::NOTE:

                        if ( !array_key_exists( 'sukellos_rule_exception', $settings ) || ( !$settings[ 'sukellos_rule_exception' ] ) ) {

                            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                                WP_Helper::sk__( 'Item type or extended class %s not supported in basic version.' ),
                                '<code>' . $item_type . '</code>'
                            ) );
                            return null;
                        }
                        $item = new Note( $settings );
                    break;
                case Item_Type::CONTENT:
                        $item = new Content( $settings );
                    break;
                case Item_Type::HEADER:
                        $item = new Header( $settings );
                    break;
            }

            // Add field
            $this->item_instances[ ''.$item_id ] = $item;
        }
        else {

            WP_Admin_Notices_Manager::instance()->add_admin_notice( 'create_item', WP_Admin_Notices_Manager::TYPE_ERROR, sprintf(
                WP_Helper::sk__( 'Item type or extended class %s does not exist.' ),
                '<code>' . $item_type . '</code>'
            ) );
            return null;
        }

        // Enqueue / init once scripts
        $enqueue_once = $item->get_enqueue_once();
        WP_Log::debug(__METHOD__, ['$item_type'=>$item_type, '$item'=>$item, '$enqueue_once'=>$enqueue_once]);

        if ( $enqueue_once && !in_array( $item_type, $this->enabled_once_scripts ) ) {

            // Enqueue JS / CSS and init Javascript
            add_action( 'admin_enqueue_scripts', array( $item, 'enqueue_scripts' ), 100 );
            add_action( 'admin_footer', array( $item, 'init_scripts' ), 100 );

            // Fixed use of color picker
            $this->enabled_once_scripts[] = $item_type;
        } elseif ( !$enqueue_once ) {

            // Enqueue JS / CSS and init Javascript
            add_action( 'admin_enqueue_scripts', array( $item, 'enqueue_scripts' ), 100 );
            add_action( 'admin_footer', array( $item, 'init_scripts' ), 100 );
        }

        return $item;
    }

    /**
     * Gets an active instance of Item
     *
     * @since 1.0.0
     *
     * @param string $item_id item id
     *
     * @return Item An Item object
     */
    public function get_item( $item_id ) {

        // Search for item in all admin builder namespaces
        if (array_key_exists( $item_id, $this->item_instances )) {

            return $this->item_instances[ ''.$item_id ];
        }
        foreach ( $this->item_instances as $cfield_type => $citem_instances ) {

            if (array_key_exists( $item_id, $citem_instances )) {

                return $citem_instances[ ''.$item_id ];
            }
        }
        return null;
    }

    /**
     * Gets all active instances of Field, of one specific type
     *
     * @since 1.0.0
     *
     * @param string $handler_type must be an of POST_META, OPTION or USER_META
     * @param string $field_type type of the field (text, color...)
     * @param bool $only_css filter fields with a CSS setting
     *
     * @return array An array of Field objects
     */
    public function get_fields( $handler_type=null, $field_type=null, $only_css=false ) {

        $returned_fields = array();

        // Filter handler type
        if ( !is_null( $handler_type ) && array_key_exists( ''.$handler_type, $this->item_instances )) {

            $returned_fields = $this->item_instances[ ''.$handler_type ];
        } else {

            $all_fields = $this->item_instances[ self::OPTION ];
            $returned_fields = $all_fields;
        }

        // Filter type and css only (only one array parsing)
        if ( !is_null( $field_type ) || $only_css ) {

            foreach ( $returned_fields as $key => $field ) {

                if ( !is_null( $field_type ) && ( $field->get_type() !== $field_type ) ) {

                    unset( $returned_fields[ $key ] );
                }

                if ( $only_css && !$field->exists_setting( 'css' ) ) {

                    unset( $returned_fields[ $key ] );
                }
            }
        }

        return $returned_fields;
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */


}
