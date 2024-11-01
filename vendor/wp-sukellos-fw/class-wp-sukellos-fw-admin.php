<?php

namespace Sukellos\WPFw;

use Sukellos\WPFw\AdminBuilder\Admin_Builder;
use Sukellos\WPFw\Utils\WP_Sukellos_Plugins_Manager;

defined( 'ABSPATH' ) or exit;

/**
 * Sukellos super Admin class used as admin entry point for all Sukellos plugins
 *
 * @since 1.0.0
 */
class WP_Sukellos_Fw_Admin extends WP_Plugin_Admin {

    // Use Trait Singleton
    use Singleton;

    /**
     * Default init method called when instance created
     * This method can be overridden if needed.
     *
     * @since 1.0.0
     * @access protected
     */
    public function init() {

        parent::init();
    }

    /**
     * Get sub admin instances.
     * Must be used ONLY in main admin, to init sub admin instances correctly
     *
     * @return array of WP_Plugin_Admin instances
     */
    public function get_plugin_sub_admins() {

        return array(
            WP_Sukellos_Fw_Admin_Tools::instance(),
            WP_Sukellos_Fw_Admin_Licenses::instance(),
        );
    }


    /**
     * Gets the plugin configuration URL
     * This is used to build actions list in plugins page
     * Leave blank ('') to disable
     *
     * @since 1.0.0
     *
     * @return string plugin settings URL
     */
    public function get_settings_url() {

        return admin_url( 'admin.php?page='.WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_tools' );
    }

    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */


    /***
     * Adding CSS and JS into header
     * Default add assets/admin.css and assets/admin.js
     */
    public function admin_enqueue_scripts() {

        // CSS
        wp_enqueue_style( 'sk-fw-admin-style', plugins_url( 'admin-builder/css/sk-fw-admin.css', __FILE__ ) );
   }


    /***
     * Admin page
     */
    public function create_items() {

        // Admin page.
        $admin_page = Admin_Builder::instance()->create_admin_page(
            array(
                'name' => WP_PLoad::WP_SK_NAME,
                'title' => WP_PLoad::WP_SK_NAME,
                'id' => WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_dashboard',
                'capability' => 'manage_options',
                'parent_id' => null,
                'icon' => 'dashicons-smiley', // Menu icon for top level menus only https://developer.wordpress.org/resource/dashicons/#editor-paste-word
                'position' => 10,
                'desc' => 'Speedup the development of Wordpress plugins'
            )
        );
        if ( is_null( $admin_page ) ) return;

        // Build HTML content
        $html_content = '
        <div class="sk-content-panel">
            <div class="sk-content-panel-content">
                <div class="sk-content-panel-2columns-container intro">
                    <div class="sk-content-panel-column">
                        <div class="sk-content-panel-2columns-container header">
                            <div class="sk-content-panel-icon dashicons dashicons-controls-forward"></div>
                            <div class="sk-content-panel-column-content">
                                <h3>WP Plugin Development In A Minute</h3>
                            </div>
                        </div>
                        <div class="sk-content-panel-column-content body">
                            <p>The Sukellos framework was designed to <b>speed up the development of WordPress plugins</b>. </p>
                            <p>Once installed in WordPress, its use is immediate and intuitive thanks to its object-oriented structure, by using inclusion and inheritance.</p>
                            <p>It allows you to overcome all the constraints of integration with WordPress, and to focus on the essentials of your functional logic.</p>
                            <p><b>The Sukellos framework is included in all plugins provided by Sukellos.</b></p>
                        </div>
                    </div>
                    <div class="sk-content-panel-column">
                        <div class="sk-content-panel-2columns-container header">
                            <div class="sk-content-panel-icon dashicons dashicons-welcome-learn-more"></div>
                            <div class="sk-content-panel-column-content">
                                <h3>Documentation</h3>
                            </div>
                        </div>
                        <div class="sk-content-panel-column-content body">
                            <p><a href="https://wp-adminbuilder.com/tutorial/sukellos-fw-minimum-requirements/" target="_blank">Documentation is avalaible here</a>. If you encounter a few problems, please <a href="https://wp-adminbuilder.com/contact/" target="_blank">submit a ticket on our support</a></p>
                            <p>A <a href="https://wp-adminbuilder.com/tutorial/basic-plugin-installation/" target="_blank">Sukellos Basic plugin</a> implementing our Sukellos Framework is <b>offered</b> to make your job even easier.</p>
                            <p>If you installed the Admin Builder Pro, you can download the <a href="https://wp-adminbuilder.com/tutorial/admin-builder-examples-installation/" target="_blank">Sukellos - Admin Builder Examples</a> plugin to benefit from a large number of examples.</p>
                        </div>
                    </div>
                </div>
                <div class="sk-content-panel-1column-container">
                    <div class="sk-content-panel-column">
                        <div class="sk-content-panel-2columns-container header">
                            <div class="sk-content-panel-icon dashicons dashicons-hammer"></div>
                            <div class="sk-content-panel-column-content">
                                <h3>Add-ons</h3>
                            </div>
                        </div>
                        <div class="sk-content-panel-column-content body">
                            <p>This framework can also include a range of features that we found useful when designing for our own customers: <b>admin builder</b>, logger with configurable level, ability to disable Gutenberg, customization of the login page, choice of image formats used by WordPress, management of the login and logout pages and menu… The list of supported functions is constantly evolving, listening to your needs.</p>
                        </div>
                    </div>
                </div>
                <div class="sk-content-panel-3columns-container addons">
        ';

        // Get infos about all Sukellos plugins
        $plugins = WP_Sukellos_Plugins_Manager::instance()->get_plugin_infos();
        foreach ( $plugins as $plugin_name => $plugin ) {

            $html_content .= '
                    <div class="sk-content-panel-column addon">
                        <div class="sk-content-panel-column-content header">
            ';

            $html_content .= '<h3>'.$plugin['name'].'</h3>';

            $html_content .= '
                        </div>
                        <div class="sk-content-panel-column-content body">
            ';

            $html_content .= '<p>'.$plugin['description'].'</p>';

            $html_content .= '
                        </div>
                        <div class="sk-content-panel-column-content footer">
            ';

            if ( $plugin['is_active'] && $plugin['is_full_version'] ) {

                $html_content .= '
                            <div class="sk-content-panel-2columns-container plugin_active">
                                <div class="sk-content-panel-icon dashicons dashicons-saved"></div>
                                <div class="sk-content-panel-column-content">Version: '.$plugin['plugin_info']['Version'].'</div>
                            </div>
                ';
            } else {

                $href = '';
                if ( ( '#' !== $plugin['url'] ) && ( '' !== $plugin['url'] ) ) {

                    $href = ' href="'.$plugin['url'].'"';
                }
                $html_content .= '<a class="button" target="_blank"'.$href.'>'.$plugin['button_label'].'</a>';
            }

            $html_content .= '
                        </div>
                    </div>
            ';

        }

        $html_content .= '
                </div>
            </div>
        </div>
        ';

        // Create a content
        $admin_page->create_content(
            array(
                'id' => WP_PLoad::WP_SK_OPTIONS_SUFFIX_PARAM.'_content_dashboard',
                'name' => 'Dashboard',
                'desc' => '',
                'content' => $html_content,
            )
        );
    }

}