<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WPFw\AdminBuilder\Items\Item;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Container Class - Abstract class whic can contain both Fields
 *
 * Must be ovveride to create specific containers
 *
 * @since 1.0.0
 */
abstract class Container extends Item {

    const NONCE_PREFIX = 'sukellos';

    /**
     * Default settings
     * @var array
     */
    private $default_container_settings = array(

        'position' => null, // Menu position. Can be used for both top and sub level menus
    );

    /**
     * All item instances
     *
     * Format for items: array of : <item_id> => Item
     */
    private $item_instances = array();

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_item_settings() {

        // Merge all settings
        $settings = array_merge(
            $this->default_container_settings,
            $this->get_default_specific_container_settings()
        );
        return $settings;
    }

    /**
     * Get the default field settings
     * @return array
     */
    abstract protected function get_default_specific_container_settings();

    public function get_items() {

        return $this->item_instances;
    }

    /**
     * Verify a few things before saving fields
     * @return mixed
     */
    abstract public function verify_security();

    /**
     * Render items, one by one
     *
     * @param bool $echo Whether to display or return string, default true
     */
    public function render_items( $container_id, $echo=true ) {

        $html_content = '';
        foreach ( $this->item_instances as $item ) {

            $html_content .= $item->render( false );
        }

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
    }

    /**
     * Field creation, delegate to Field Factory
     *
     * @param array $settings The settings used to create options.
     */
    protected function create_item( $settings ) {

        WP_Log::debug( __METHOD__, ['$settings'=>$settings] );
        $item = Item_Factory::instance()->create_item( $settings );
        $this->item_instances[] = $item;

        return $item;
    }

    /**
     * Field creation, delegate to Field Factory
     *
     * @param string $handler_type must be an of POST_META, OPTION or USER_META
     * @param array $settings The settings used to create options.
     */
	protected function create_field( $handler_type, $settings ) {

        // Add handler type in settings
        $settings['handler_type'] = $handler_type;

        $field = $this->create_item( $settings );

        return $field;
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

}
