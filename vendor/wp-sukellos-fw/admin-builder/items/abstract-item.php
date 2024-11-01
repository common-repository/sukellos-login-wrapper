<?php

namespace Sukellos\WPFw\AdminBuilder\Items;

use Sukellos\WPFw\AdminBuilder\Settings_Bean;
use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Item Class - Abstract parent class for all fields
 *
 * Must be ovveride to create specific fields or specific items
 *
 * @since 1.0.0
 */
abstract class Item {

    use Settings_Bean;

    /**
     * Default settings
     * @var array
     */
    protected $default_item_settings = array(

        'type' => '',

        /**
         * A unique ID for this option. This ID will be used to get the value for this option.
         * It will be added as CSS ID in item, too
         *
         * @since 1.0.0
         * @var string
         */
        'id' => '',

        /**
         * The name of the option, for display purposes only.
         *
         * @since 1.0.0
         * @var string
         */
        'name' => '',

        /**
         * The description to display together with this item.
         *
         * @since 1.0.0
         * @var string
         */
        'desc' => '',

        /**
         * A CSS class for this option. It will be added as CSS class in item
         *
         * @since 1.0.0
         * @var string
         */
        'class' => '',

        /**
         * Used to know if the script of the Item can be enqueued once
         *
         * @since 1.3.0
         */
        'enqueue_once' => true,

        /**
         * Set to true is the item need a form to work, like a field
         * This option may be automatically set to true if the item is containing other items that have this option set to true
         *
         * @since 1.0.0
         */
        'need_form' => false,

        /**
         * Used to know if an item should be displayed independently of a global form.
         * This option is used to break a form rendering when the item is displayed.
         * It can be used in conjunction with need_form to force own form for a field
         * Or to exclude a content item from a global form
         *
         * @since 1.0.0
         */
        'global_form_independent' => false,

        /**
         * Used to display a reset button, to default values
         * Only used if 'need_form' set to true, and a Save button displayed
         *
         * @since 1.0.0
         */
        'use_reset' => true,

        /**
         * Text for save button
         * Only used if 'need_form' set to true, and a Save button displayed
         *
         * @since 1.0.0
         */
        'save' => 'Save',

        /**
         * Text for reset button
         * Only used if 'need_form' set to true, and a Reset button displayed
         *
         * @since 1.0.0
         */
        'reset' => 'Reset',

        /**
         * Question asked to confirm reset
         * Only used if 'need_form' set to true, and a Reset button displayed
         *
         * @since 1.0.0
         */
        'reset_question' => 'Do you really want to reset form?',
    );

    /**
     * Constructor
     *
     * @param $settings
     */
    public function __construct( $settings ) {

        // Merge all settings
        $default_specific_item_settings = $this->get_default_specific_item_settings();

        $default_settings = array_merge(
            $this->default_item_settings,
            $default_specific_item_settings
        );

        // Settings validation
        $new_settings = $this->validate_settings( $settings, $default_settings );

        $settings = array_merge(
            $default_settings,
            $new_settings
        );

        $this->update_settings( $settings );
    }

    /**
     * Get the default item settings
     * @return array
     */
    abstract protected function get_default_specific_item_settings();

    /**
     * Render field depending on its specific type
     *
     * @param bool $echo Whether to display or return string, default true
     */
    abstract public function render( $echo=true );

    /**
     * Validate settings
     * No settings keys allowed other than those defined in default settings
     *
     * @param $new_settings new settings
     * @param $ref_settings reference settings
     * @return the new settings without unsupported settings
     */
    private function validate_settings( $new_settings, $ref_settings ) {

        $cleaned_new_settings = array();
        foreach ( $new_settings as $setting_name => $value ) {

            if ( ( array_key_exists( $setting_name, $ref_settings ) ) || ( 'sukellos_rule_exception' === $setting_name ) || ( 'mandatory' === $setting_name ) ) {

                // Remove it from new settings...
                $cleaned_new_settings[ ''.$setting_name ] = $value;
            }
            else {

                // ... and generate a warning
                WP_Admin_Notices_Manager::instance()->add_admin_notice( 'validate_settings', WP_Admin_Notices_Manager::TYPE_NOTICE, sprintf(
                    WP_Helper::sk__( 'Setting %s not supported for field %s' ),
                    '<code>' . $setting_name . '</code>',
                    '<code>' . $ref_settings[ 'type' ] . '</code>'
                ) );
            }
        }
        return $cleaned_new_settings;
    }


    /**
     * Add save and reset buttons
     *
     * @param false $use_br
     * @return string
     */
    protected function render_buttons() {

        // Init a few vars
        $save_label = $this->get_save();
        $reset_label = $this->get_reset();
        $reset_question = $this->get_reset_question();
        $use_reset = $this->get_use_reset();

        // CLOSE FORM
        $html_content = '
                <div class="sk-admin-builder-submit-buttons">
                    <button name="action" value="save_fields" class="button button-primary">
                            '.$save_label.'
                    </button>
                ';

        if ( $use_reset ) {

            $html_content .= '
                    <button name="action" value="reset_fields" class="button button-secondary"
                            onclick="javascript: if ( confirm( \''.htmlentities( esc_attr( $reset_question ) ).'\' ) ) { return true; } jQuery(this).blur(); return false;">
                    '.$reset_label.'
                    </button>
                ';
        }

        $html_content .= '
                </div>
            ';

        return $html_content;
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /**
     * Enqueue the scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    abstract public function enqueue_scripts();

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    abstract public function init_scripts();
}
