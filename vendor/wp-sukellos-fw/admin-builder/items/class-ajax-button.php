<?php

namespace Sukellos\WPFw\AdminBuilder\Items;

use Sukellos\WPFw\AdminBuilder\Item_Type;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\Utils\WP_Log;

defined( 'ABSPATH' ) or exit;

/**
 * AJAX Button item
 *
 * @since 1.0
 * @type ajax-button
 */
class Ajax_Button extends Item {

    /**
     * Default settings
     * @var array
     */
    private $default_settings = array(

        'type' => Item_Type::AJAX_BUTTON,

        /**
         * The action parameter for the AJAX request, that will trigger wp_ajax_<action> hook
         * Can be a string, or an array of actions.
         * In seond case, all other parameters must have the same number of entries as actions
         *
         * @since 1.0
         * @var string
         */
        'action' => 'custom_action',

        /**
         * Label of the button
         *
         * @since 1.0
         * @var string
         */
        'label' => 'Click me',

        /**
         * CSS class applied to the button, eg. may be button-primary or button-secondary
         *
         * @since 1.0
         * @var string
         */
        'button_class' => 'button-primary',

        /**
         * Label of the button while waiting for response
         *
         * @since 1.0
         * @var string
         */
        'wait_label' => 'Please wait...',

        /**
         * Label of the button when the response is success
         *
         * @since 1.0
         * @var string
         */
        'success_label' => 'Success',

        /**
         * Label of the button when the response is an error
         *
         * @since 1.0
         * @var string
         */
        'error_label' => 'Error',

        /**
         * JS callback when response is success
         * Must be inserted in admin.js asset
         * If empty, but ajax_result_container specified, the success result will be displayed in ajax_result_container div
         *
         * @since 1.0
         * @var string
         */
        'success_callback' => '',

        /**
         * JS callback when response is an error
         * Must be inserted in admin.js asset
         * If empty, but ajax_result_container specified, the error result will be displayed in ajax_result_container div
         *
         * @since 1.0
         * @var string
         */
        'error_callback' => '',

        /**
         * JS callback used to filter data
         * Must be inserted in admin.js asset
         *
         * @since 1.0
         * @var string
         */
        'data_filter_callback' => '',

        /**
         * CSS of a HTML tab where to display AJAX result.
         * This id can be used to customize it using CSS
         *
         * If specified, but no callbacks, AJAX result will be automatically displayed in this div
         *
         * @since 1.0
         * @var string
         */
        'ajax_result_container' => 'ajax_result_container',


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
        'global_form_independent' => true,
    );


    // Used for rendering
    private static $row_index = 0;

    /**
     * Constructor
     *
     * @param $settings
     */
    public function __construct( $settings ) {

        parent::__construct( $settings );

        // Adjust settings
        $action = $this->get_action();
        WP_Log::debug( 'Ajax_Button->__construct', ['$action'=>$action] );

        /**
         * Create ajax handlers for security and last resort success returns
         */
        if ( ! empty( $action ) ) {

            add_action( 'wp_ajax_' . $action, array( $this, 'ajax_security_checker' ), 1 );
            add_action( 'wp_ajax_' . $action, array( $this, 'ajax_last_success' ), 99999 );
        }
    }

    /**
     * Get the default item settings
     * @return array
     */
    protected function get_default_specific_item_settings() {

        return $this->default_settings;
    }


    /**
     * This is first called when an ajax button is clicked. This checks whether the nonce
     * is valid and if we should continue;
     *
     * @return	void
     */
    public function ajax_security_checker() {

        WP_Log::debug( 'Ajax_Button->ajax_security_checker', ['_POST'=>$_POST] );
        if ( empty( $_POST['nonce'] ) ) {

            wp_send_json_error( WP_Helper::sk__( 'Security check failed, please refresh the page and try again.' ) );
        }
        if ( ! wp_verify_nonce( $_POST['nonce'], 'sk-admin-builder-ajax-button' ) ) {

            wp_send_json_error( WP_Helper::sk__( 'Security check failed, please refresh the page and try again.' ) );
        }
    }

    /**
     * This is last called when an ajax button is clicked. This just exist with a successful state,
     * since doing nothing reads as an error with wp.ajax
     *
     * @return	void
     */
    public function ajax_last_success() {

        wp_send_json_success();
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
     * Render the header part
     */
    private function render_header( $show_desc = false, $echo = true ) {

        $id = $this->get_id();
        $name = $this->get_name();
        $desc = $this->get_desc();
        $class = $this->get_class();

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
                    <div class="sk-admin-builder-item-content sk-admin-builder-'.$this->get_type().'">
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
	 * Display body
     * @param bool $echo Whether to display or return string, default true
     */
    public function render_specific_body( $echo=true ) {

	    $action = $this->get_action();
	    $class = $this->get_button_class();
        $label = $this->get_label();
        $wait_label = $this->get_wait_label();
        $error_label = $this->get_error_label();
        $success_label = $this->get_success_label();
        $success_callback = $this->get_success_callback();
        $error_callback = $this->get_error_callback();
        $data_filter_callback = $this->get_data_filter_callback();
        $ajax_result_container = $this->get_ajax_result_container();

        $html_content = sprintf( '<button class="button %s" data-action="%s" data-label="%s" data-wait-label="%s" data-error-label="%s" data-success-label="%s" data-nonce="%s" data-success-callback="%s" data-error-callback="%s" data-data-filter-callback="%s" data-ajax-result-container="%s">%s</button>',
            $class,
            esc_attr( $action ),
            esc_attr( $label ),
            esc_attr( $wait_label ),
            esc_attr( $error_label ),
            esc_attr( $success_label ),
            esc_attr( wp_create_nonce( 'sk-admin-builder-ajax-button' ) ),
            esc_attr( $success_callback ),
            esc_attr( $error_callback ),
            esc_attr( $data_filter_callback ),
            esc_attr( $ajax_result_container ),
            esc_attr( $label )
        );

        // If no callbacks specified, but a CSS id for AJAX result, then create a div
        if ( !empty( $ajax_result_container ) && ( $ajax_result_container !== '' ) ) {

            $html_content .= '<div id="'.$ajax_result_container.'" class="content-container" style="margin-top: 10px;"></div>';
        }

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
    public function init_scripts() {

        ?>
        <script>
            jQuery(document).ready(function($) {
                "use strict";

                $('.sk-admin-builder-wrapper').on( 'click', '.sk-admin-builder-ajax-button .button', function( e ) {

                    // Only perform one ajax at a time
                    if ( typeof this.doingAjax == 'undefined' ) {
                        this.doingAjax = false;
                    }
                    e.preventDefault();
                    if ( this.doingAjax ) {
                        return false;
                    }
                    this.doingAjax = true;

                    // Form the data to send, we send the nonce and the post ID if possible
                    var data = { nonce: $(this).attr('data-nonce') };
                    <?php
                        global $post;
                        if ( ! empty( $post ) ) {
                        ?>data['id'] = <?php echo esc_attr( $post->ID ) ?>;<?php
                    }
                    ?>

                    if ( $(this).attr('data-data-filter-callback') !== '' && typeof window[ $(this).attr('data-data-filter-callback') ] !== 'undefined' ) {
                        data = window[ $(this).attr('data-data-filter-callback') ]( data );
                    }
                    // Perform the ajax call
                    wp.ajax.send( $(this).attr('data-action'), {

                        // Success callback
                        success: function( data ) {

                            this.labelTimer = setTimeout(function() {
                                $(this).text( $(this).attr('data-label') );
                                this.labelTimer = undefined;
                            }.bind(this), 3000 );

                            var successMessage = $(this).attr('data-success-label');
                            if (typeof data === 'string' || data instanceof String) {
                                successMessage = data;
                            } else if (typeof data.message !== 'undefined') {
                                successMessage = data.message;
                            }
                            $(this).text( successMessage );

                            // Call the success callback
                            var ajaxResultContainerId = $(this).attr('data-ajax-result-container');
                            if ( $(this).attr('data-success-callback') != '' ) {
                                if ( typeof window[ $(this).attr('data-success-callback') ] != 'undefined' ) {
                                    window[ $(this).attr('data-success-callback') ]( data );
                                }
                            } else if ( ajaxResultContainerId != '' ) {

                                $('#'+ajaxResultContainerId).html("<p>"+data+"</p>");
                            }
                            this.doingAjax = false;

                        }.bind(this),

                        // Error callback
                        error: function( data ) {
                            this.labelTimer = setTimeout(function() {
                                $(this).text( $(this).attr('data-label') );
                                this.labelTimer = undefined;
                            }.bind(this), 3000 );

                            var errorMessage = $(this).attr('data-error-label');
                            if (typeof data === 'string' || data instanceof String) {
                                errorMessage = data;
                            } else if (typeof data.message !== 'undefined') {
                                errorMessage = data.message;
                            }
                            $(this).text( errorMessage );

                            // Call the error callback
                            var ajaxResultContainerId = $(this).attr('data-ajax-result-container');
                            if ( $(this).attr('data-error-callback') != '' ) {
                                if ( typeof window[ $(this).attr('data-error-callback') ] != 'undefined' ) {
                                    window[ $(this).attr('data-error-callback') ]( data );
                                }
                            } else if ( ajaxResultContainerId != '' ) {

                                $('#'+ajaxResultContainerId).html("<p>"+data+"</p>");
                            }
                            this.doingAjax = false;

                        }.bind(this),

                        // Pass the data
                        data: data

                    });

                    // Clear the label timer
                    if ( typeof this.labelTimer != 'undefined' ) {
                        clearTimeout( this.labelTimer );
                        this.labelTimer = undefined;
                    }
                    $(this).text( $(this).attr('data-wait-label') );

                    return false;
                } );
            });
        </script>
        <?php
    }
}
