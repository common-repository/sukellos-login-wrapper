<?php

namespace Sukellos\WPFw\AdminBuilder;

use Sukellos\WPFw\Utils\WP_Admin_Notices_Manager;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * Admin_Page_Simple inherit from Admin_Page Class
 *
 * It is used to create a admin page directly containing Fields, but not tabs
 *
 * Only one Admin Page allowed for one classical Wordpress admin page
 *
 * @since 1.0.0
 */
class Admin_Page_Simple extends Admin_Page {

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_admin_page_settings() {

        return array();
    }

    /**
     * May be call to add params in query url before redirect, in case of saving
     */
    protected function add_query_args( &$url ) {

        // Nothing to add
    }

    /**
     * Wordpress admin page creation (registered in admin_menu)
     * @param bool $echo Whether to display or return string, default true
     */
    public function render( $echo=true ) {

        if ( empty( $echo ) ) {
            $echo = true;
        }

        WP_Log::debug('Admin_Page_Simple->render', ['echo'=> $echo]);

        $html_content = '
            <div class="sk-admin-builder-wrapper" id="'.$this->get_id().'">
            <div class="sk-admin-builder-header-wrapper">
                <div class="sk-admin-builder-header">
                    <h1>'.$this->get_title().'</h1>
        ';

		if ( ! empty( $this->get_desc() ) ) {

            $html_content .= '<p class="description">'.$this->get_desc().'</p>';
		}

        $html_content .= '
                </div>
            </div>
            <div class="sk-admin-builder-container-notabs">
                <div class="sk-admin-builder-items">
        ';

		// Display notification if we did something
		if ( ! empty( $_GET['message'] ) ) {

			if ( $_GET['message'] == 'saved' ) {

                $html_content .= WP_Admin_Notices_Manager::instance()->format_notice( WP_Helper::sk__( 'Settings saved.' ), esc_html( $_GET['message'] ) );

			} else if ( $_GET['message'] == 'reset' ) {

                $html_content .= WP_Admin_Notices_Manager::instance()->format_notice( WP_Helper::sk__('Settings reset to default.' ), esc_html( $_GET['message'] ) );
			} else {

                $message = $_GET['message'];
                $type = WP_Admin_Notices_Manager::TYPE_SAVED;
                if ( strpos( $message, '!!' ) !== false ) {

                    WP_Log::debug( __METHOD__.' - Separator !! found', ['$message'=>$message]);

                    $parts = explode( '!!', $message );
                    $message = $parts[0];
                    WP_Log::debug( __METHOD__, ['$parts'=>$parts]);

                    if ( ( $parts[1] == WP_Admin_Notices_Manager::TYPE_SAVED )
                        || ( $parts[1] == WP_Admin_Notices_Manager::TYPE_ERROR )
                        || ( $parts[1] == WP_Admin_Notices_Manager::TYPE_NOTICE )
                        || ( $parts[1] == WP_Admin_Notices_Manager::TYPE_RESET )
                        || ( $parts[1] == WP_Admin_Notices_Manager::TYPE_UPDATED )
                    ) {
                        $type = $parts[1];
                    }

                }
                $html_content .= WP_Admin_Notices_Manager::instance()->format_notice( $message, $type );
            }
		}

        $html_content .= $this->render_items( $this->get_id(), false );

        $html_content .= '    
                </div>
            </div>
            </div>
        ';

        if ( $echo ) {

            echo $html_content;
        } else {

            return $html_content;
        }
	}

    /**
     * Create an option
     *
     * @param array $settings depending on option
     */
	public function create_option( $settings ) {

	    $field = $this->create_field( Item_Factory::OPTION, $settings );

        // Hook saving
        add_action( 'sukellos_fw/admin_builder/admin_page/save_field', array( $field, 'save' ), 5 );
        add_action( 'sukellos_fw/admin_builder/admin_page/load_field', array( $field, 'load' ), 5 );
        add_action( 'sukellos_fw/admin_builder/admin_page/reset_field', array( $field, 'reset' ), 5 );

        return $field;
    }

    /**
     * Create a header
     *
     * @param array $settings depending on item
     */
    public function create_header( $settings ) {

        $settings['type'] = Item_Type::HEADER;

        $item = $this->create_item( $settings );
        return $item;
    }

    /**
     * Create a note
     *
     * @param array $settings depending on item
     */
    public function create_note( $settings ) {

        $settings['type'] = Item_Type::NOTE;

        $item = $this->create_item( $settings );
        return $item;
    }

    /**
     * Create a content
     *
     * @param array $settings depending on item
     */
    public function create_content( $settings ) {

        $settings['type'] = Item_Type::CONTENT;

        $item = $this->create_item( $settings );
        return $item;
    }

    /**
     * Create a crud
     *
     * @param array $settings depending on item
     */
    public function create_crud( $settings ) {

        // Not supported in Basic version
        return null;
    }

    /**
     * Create a group
     *
     * @param array $settings depending on option
     */
    public function create_group( $settings ) {

        // Not supported in Basic version
        return null;
    }

    /**
     * Create a simple form.
     * No otion, post_meta... submitted values are checked and forwarded to hook sukellos_fw/admin_builder/item_form/fields_submitted
     *
     * @param array $settings depending on option
     */
    public function create_form( $settings ) {

        WP_Log::debug( __METHOD__, [] );
        $settings['type'] = Item_Type::ADMIN_ITEM_FORM;
        $settings['parent_id'] = $this->get_id();

        $item = $this->create_item( $settings );
        return $item;
    }

    /**
     * Create an user meta
     * User id is fixed
     *
     * @param array $settings depending on user meta, must contain user_id fixed
     */
    public function create_user_meta( $settings ) {

        // Not supported in Basic version
        return null;
    }


    /**
     * Create an AJAX button
     * This button is NOT an option, than no load/save into database.
     * It is used to simplify the AJAX requesting in an admin page.
     * An AJAX button is always displayed outside of <form>, then a form may be closed before button insertion, and reopen after
     *
     * @param array $settings depending on option
     */
    public function create_ajax_button( $settings ) {

        $settings['type'] = Item_Type::AJAX_BUTTON;

        $field = $this->create_item( $settings );
        return $field;
    }

    /**
     * Create a Tab
     * Here in Basic version to ensure compatibility with pro version
     *
     * @param $settings
     */
    public function create_tab( $settings ) {

        // Not supported in Basic version
        return $this;
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
    public function enqueue_scripts() {}

    /**
     * Load the javascript
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_scripts() {}
}
