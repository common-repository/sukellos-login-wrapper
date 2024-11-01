<?php

namespace Sukellos\WPFw\AdminBuilder\Items;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Note item
 *
 * @since 1.0
 * @type note
 */
class Note extends Item {

    /**
     * Default settings
     * @var array
     */
    private $default_settings = array(

        'type' => Item_Type::NOTE,

        'context_color' => "#198fd9",
    );

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_item_settings() {

        return $this->default_settings;
    }

    /**
     * Render field depending on its specific type
     *
     * @param bool $echo Whether to display or return string, default true
     */
    public function render( $echo=true ) {

        $id = $this->get_id();
        $class = $this->get_class();

        $context_color = $this->get_context_color();
        $background_color = $context_color.'33';

        $html_content = '
                <div id="'.$id.'" class="sk-admin-builder-item-note '.( ( $class !== '' )?$class:'' ).'" style="background-color: '.$background_color.'">
                    <strong style="color: '.$context_color.'">'.$this->get_name().'</strong> 
        ';

        if ( ! empty( $this->get_desc() ) ) {

            $html_content .= $this->get_desc();
        }

        $html_content .= '
            </div>
        ';

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
     * Enqueue the scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {

        wp_enqueue_style( 'sk-admin-builder-note-item-style', plugins_url( '../css/sk-item-note.css', __FILE__ ) );
    }

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_scripts() {}
    
}
