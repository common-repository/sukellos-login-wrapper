<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;
use Sukellos\WPFw\AdminBuilder\Items\Item;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Field Class - Abstract class for all fields
 *
 * Must be ovveride to create specific fields
 *
 * @since 1.0.0
 */
abstract class Field extends Item implements Handler {

    /**
     * Default settings
     * @var array
     */
    private $default_field_settings = array(

        'handler_type' => null,

        /**
         * (Optional) The default value for this option.
         *
         * @since 1.0.0
         * @var mixed
         */
        'default' => '',

        /**
         * (Optional) If true, the option will not be displayed, but will still be accessible using `getOption`. This is helpful for deprecating old settings, while still making your project backward compatible.
         *
         * @since 1.0.0
         * @var bool
         */
        'hidden' => false,

        /**
         * (Optional) An example value for this field, will be displayed in a <code>
         *
         * @since 1.0.0
         * @var string
         */
        'example' => '', // An example value for this field, will be displayed in a <code>

        /**
         * (Optional) User id must be included and fixed if user_meta is managed in an admin page ONLY, outside of user profile container
         * For admin page ONLY
         *
         * @since 1.0.0
         * @var string
         */
        'user_id' => '',

        /**
         * (Optional) This option can be used if user_meta is managed in an admin page ONLY, outside of user profile container
         * If true, it will display this field at the end of the users profiles, not editable
         * For admin page ONLY
         *
         * @since 1.0.0
         * @var string
         */
        'show_in_user_profile' => false,

        /**
         * Set to true is the item need a form to work, like a field
         * This option may be automatically set to true if the item is containing other items that have this option set to true
         * Default for field: true
         *
         * @since 1.0.0
         */
        'need_form' => true,
    );

    // Used for rendering
	private static $row_index = 0;

    // Handler used to manage field value, and load / save for specific fields
    protected $handler = '';

    /**
     * Constructor
     *
     * @param $settings
     */
    public function __construct( $settings, $handler ) {

        parent::__construct( $settings );

        // Set handler
        $this->handler = $handler;

        // Filters used to clean values
        add_filter( 'sukellos_fw/admin_builder/field/get_cleaned_value_'.$this->get_id() , array( $this, 'filter_field_get_cleaned_value' ), 10, 1 );
        add_filter( 'sukellos_fw/admin_builder/field/set_cleaned_value_'.$this->get_id() , array( $this, 'filter_field_set_cleaned_value' ), 10, 1 );
    }

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_item_settings() {

        // Merge all settings
        $settings = array_merge(
            $this->default_field_settings,
            $this->get_default_specific_field_settings()
        );
        return $settings;
    }

    /**
     * Get the default field settings
     * @return array
     */
    abstract protected function get_default_specific_field_settings();

    /**
     * Set field value depending on its specific type
     * Handler implementation
     */
    public function set_value( $value ) {

        if ( is_null( $this->handler ) ) {

            return;
        }
        $this->handler->set_value( $value );
    }

    /**
     * Get field value depending on its specific type
     * Handler implementation
     */
    public function get_value() {

        if ( is_null( $this->handler ) ) {

            return null;
        }
        return $this->handler->get_value();
    }

    /**
     * Save field value depending on its specific type
     * Handler implementation
     */
    public function save() {

        if ( is_null( $this->handler ) ) {

            return;
        }

        $saved = $this->handler->save();

        /**
         * Action: sukellos_fw/admin_builder/field/field_saved_{$field_id}
         * Called just after field has been saved
         *
         * @param $value the raw value
         */
        if ( $saved ) do_action( 'sukellos_fw/admin_builder/field/field_saved_'.$this->get_id(), $this->get_value() );
    }

    /**
     * Load field value depending on its specific type
     * Handler implementation
     */
    public function load() {

        if ( is_null( $this->handler ) ) {

            return;
        }
        $this->handler->load();

        /**
         * Action: sukellos_fw/admin_builder/field/field_loaded_{$field_id}
         * Called just after field has been loaded
         *
         * @param $value the raw value
         */
        do_action( 'sukellos_fw/admin_builder/field/field_loaded_'.$this->get_id(), $this->get_value() );
    }

    /**
     * Reset field to its default value
     */
    public function reset() {

        if ( is_null( $this->handler ) ) {

            return;
        }
        $reseted = $this->handler->reset();

        /**
         * Action: sukellos_fw/admin_builder/field/field_reseted_{$field_id}
         * Called just after field has been reseted
         *
         * @param $value the raw value
         */
        if ( $reseted ) do_action( 'sukellos_fw/admin_builder/field/field_reseted_'.$this->get_id(), $this->get_value() );
    }

    /**
     * Render field depending on its specific type
     *
     * @param bool $echo Whether to display or return string, default true
     */
    public function render( $echo=true ) {

        // Header
        $html_content = $this->render_header( true, false );

        // Specific
        $html_content .= $this->render_specific_body( false );

        // Footer
        $html_content .= $this->render_footer( true, false );

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
    }

    /**
     * Render field depending of its specific type
     * Template pattern
     *
     * @param bool $echo Whether to display or return string, default true
     */
    abstract public function render_specific_body( $echo=true );

    /**
     * Render the header part
     * @param bool $echo Whether to display or return string, default true
     */
    private function render_header( $show_desc = false, $echo = true ) {

        $id = $this->get_id();
        $name = $this->get_name();
        $desc = $this->get_desc();
        $class = $this->get_class();

        $generic_type_class = '';
        if ( ( $this->get_generic_type()!=='' ) && ( $this->get_generic_type() !== $this->get_type() ) ) {

            $generic_type_class = ' sk-admin-builder-'.$this->get_generic_type();
        }

        $html_content = '
                <div id="'.$id.'" class="sk-admin-builder-grid-item '.( ( $class !== '' )?$class:'' ).'" '.($this->get_hidden()?'style="display: none;"':'').'>
                    <div class="sk-admin-builder-item-name">
                        <label for="'.( !empty( $id ) ? $id : '').'">'.( !empty( $name ) ? $name : '' ).'</label>
        ';

        // Add description
        $desc = $this->get_desc();
        if ( !empty( $desc ) && $show_desc ) {

            $html_content .= '<p class="description">'.$desc.'</p>';
        }

        $html_content .= '
                    </div>
                    <div class="sk-admin-builder-item-content'.$generic_type_class.' sk-admin-builder-'.$this->get_type().'">
        ';

        /**
         * Filter : sukellos_fw/admin_builder/field/render_header
         * Used to modify rendering of field header part
         *
         * Default HTML format:
         * <div class="sk-admin-builder-grid-item"> -> where .sk-admin-builder-grid-item is defined as a CSS Grid with 2 columns (40% 60%)
         *      <div class="sk-admin-builder-item-name">
         *          <label for="{item_id}">{item_name}</label>
         *          <p class="description">{item_description}</p>
         *      </div>
         *      <div class="sk-admin-builder-item-content {item_type}">
         *
         * @param $html_content the HTML formatted header
         * @param field the field being displayed
         */
        $html_content = apply_filters( 'sukellos_fw/admin_builder/field/render_header', $html_content, $this );

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
    }

    /**
     * Render the footer part
     * @param bool $echo Whether to display or return string, default true
     */
    private function render_footer( $show_desc = true, $echo = true  ) {

        $html_content = '';

        // Add example
        $example = $this->get_example();
        if ( ! empty( $example ) ) {

            $html_content .= '<p class="description"><code>'.htmlentities( $example ).'</code></p>';
        }

        $html_content .= '
                    </div>
        ';

        // May add buttons if item is form independent
        if ( $this->get_global_form_independent() ) {

            $html_content .= $this->render_buttons( true );
        }

        $html_content .= '
                </div>
        ';


        /**
         * Filter : sukellos_fw/admin_builder/field/render_footer
         * Used to modify rendering of field footer part
         *
         * Default HTML format:
         *          <p class="description"><code>{example}</code></p>
         *      </div>
         *      <div class="sk-admin-builder-submit-buttons">...</div> -> Only if the field "global_form_independent" setting is true
         *  </div>
         *
         * @param $html_content the HTML formatted footer
         * @param field the field being displayed
         */
        $html_content = apply_filters( 'sukellos_fw/admin_builder/field/render_footer', $html_content, $this );

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /**
     * Filter : sukellos_fw/admin_builder/field/get_cleaned_value_{field_id}
     * Used to clean up a value before returning it to a caller
     *
     * @param $value the raw value
     */
    public function filter_field_get_cleaned_value( $value ) {

        return Sanitize_Manager::get_sanitized_value( $value, $this->get_type() );
    }

    /**
     * Filter : sukellos_fw/admin_builder/field/set_cleaned_value_{field_id}
     * Used to clean up a value before updating it in field
     *
     * @param $value the raw value
     */
    abstract public function filter_field_set_cleaned_value( $value );

}
