<?php

namespace Sukellos\WPFw\AdminBuilder\Fields;

use Sukellos\WPFw\AdminBuilder\Item_Type;

defined( 'ABSPATH' ) or exit;

/**
 * Upload field
 *
 * @since 1.0
 * @type upload
 */
class Upload_Field extends Field {

    /**
     * Default settings specific for this field
     * @var array
     */
    private $default_specific_field_settings = array(

        'type' => Item_Type::UPLOAD,

        /**
         * (Optional) CSS rules to be used with this option. Only used when the option is placed in an admin page / tab
         *
         * @since 1.0.0
         * @var string
         * @see http://www.titanframework.net/generate-css-automatically-for-your-options/
         */
        'css' => '',

        /**
         * (Optional) The placeholder label shown when the input field is blank
         *
         * @since 1.0.0
         * @var string
         */
        'placeholder' => '',

        /**
         * The size of the image to use in the generated CSS.
         *
         * @since 1.0.0
         */
        'size' => 'full',
    );

    /**
     * Constructor
     *
     * @param $settings
     */
    public function __construct( $settings, $handler ) {

        parent::__construct( $settings, $handler );

        add_filter( 'upload_mimes', array( $this, 'filter_upload_mimes' ), 10, 1 );
    }

    /**
     * Get the default field settings
     * @return array
     */
    protected function get_default_specific_field_settings() {

        return $this->default_specific_field_settings;
    }

	/**
	 * Display for options and meta
     * @param bool $echo Whether to display or return string, default true
     */
    public function render_specific_body( $echo=true ) {

        // Display the preview image.
        $value = $this->get_value();

        // Gives us an array with the first element as the src or false on fail.
        $image_infos = wp_get_attachment_image_src( $value, array( 150, 150 ) );
        $url = '';
        if ( $image_infos !== false ) {

            $url = $image_infos[0];
        }

        $preview_image = '';
        if ( ! empty( $url ) ) {
            $preview_image = "<i class='dashicons dashicons-no-alt remove'></i><img src='" . esc_url( $url ) . "' style='display: none'/>";
        }
        $html_content = "<div class='thumbnail sk-admin-builder-image-preview'>" . $preview_image . '</div>';

        $html_content .= sprintf('<input name="%s" placeholder="%s" id="%s" type="hidden" value="%s" />',
            $this->get_id(),
            $this->get_placeholder(),
            $this->get_id(),
            esc_attr( $this->get_value() )
        );

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
     * Allow SVG mime types to be uploaded to WP.
     *
     * @since 1.0.0
     *
     * @param array $mimes The allowed mime types.
     *
     * @return array $mimes The modified mime types.
     */
    public function filter_upload_mimes( $mimes ) {
        $mimes['svg'] = 'image/svg+xml';
        return apply_filters( 'pbs_allow_svg_uploads', $mimes );
    }

    /**
     * Filter : sukellos_fw/admin_builder/field/set_cleaned_value_{field_id}
     * Used to clean up a value before updating it in field
     *
     * @param $value the raw value
     */
    public function filter_field_set_cleaned_value( $value ) {
        return $value;
    }

    /**
     * Enqueue the scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {

        wp_enqueue_style( 'sk-admin-builder-upload-field-style', plugins_url( '../../css/sk-field-upload.css', __FILE__ ) );
    }

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
            jQuery(document).ready(function($){
                "use strict";
                $('.sk-admin-builder-upload .thumbnail').find('img').parent().addClass('has-value').find(':before').css({'opacity':'0'});
                function skUploadOptionCenterImage($this) {
                    var _preview = $this.parents('.sk-admin-builder-upload').find('.thumbnail');
                    $this.css({
                        'marginTop': ( _preview.height() - $this.height() ) / 2,
                        'marginLeft': ( _preview.width() - $this.width() ) / 2
                    }).show();
                }


                // Calculate display offset of preview image on load.
                $('.sk-admin-builder-upload .thumbnail img').load(function() {
                    skUploadOptionCenterImage($(this));
                }).each(function(){
                    // Sometimes the load event might not trigger due to cache.
                    if(this.complete) {
                        $(this).trigger('load');
                    };
                });


                // In the theme customizer, the load event above doesn't work because of the accordion,
                // the image's height & width are detected as 0. We bind to the opening of an accordion
                // and adjust the image placement from there.
                var skUploadAccordionSections = [];
                $('.sk-admin-builder-upload').each(function() {
                    var $accordion = $(this).parents('.control-section.accordion-section');
                    if ( $accordion.length > 0 ) {
                        if ( $.inArray( $accordion, skUploadAccordionSections ) == -1 ) {
                            skUploadAccordionSections.push($accordion);
                        }
                    }
                });
                $.each( skUploadAccordionSections, function() {
                    var $title = $(this).find('.accordion-section-title:eq(0)'); // Just opening the section.
                    $title.click(function() {
                        var $accordion = $(this).parents('.control-section.accordion-section');
                        if ( ! $accordion.is('.open') ) {
                            $accordion.find('.sk-admin-builder-upload .thumbnail img').each(function() {
                                var $this = $(this);
                                setTimeout(function() {
                                    skUploadOptionCenterImage($this);
                                }, 1);
                            });
                        }
                    });
                });


                // Remove the image when the remove link is clicked.
                $('body').on('click', '.sk-admin-builder-upload i.remove', function(event) {
                    event.preventDefault();
                    var _input = $(this).parents('.sk-admin-builder-upload').find('input');
                    var _preview = $(this).parents('.sk-admin-builder-upload').find('div.thumbnail');

                    _preview.removeClass('has-value').find('img').remove().end().find('i').remove();
                    _input.val('').trigger('change');

                    return false;
                });


                // Open the upload media lightbox when the upload button is clicked.
                $('body').on('click', '.sk-admin-builder-upload .thumbnail, .sk-admin-builder-upload img', function(event) {
                    event.preventDefault();
                    // If we have a smaller image, users can click on the thumbnail.
                    if ( $(this).is('.thumbnail') ) {
                        if ( $(this).parents('.sk-admin-builder-upload').find('img').length != 0 ) {
                            $(this).parents('.sk-admin-builder-upload').find('img').trigger('click');
                            return true;
                        }
                    }

                    var _input = $(this).parents('.sk-admin-builder-upload').find('input');
                    var _preview = $(this).parents('.sk-admin-builder-upload').find('div.thumbnail');
                    var _remove = $(this).siblings('.sk-admin-builder-upload-image-remove');

                    // Uploader frame properties.
                    var frame = wp.media({
                        title: 'Select Image',
                        multiple: false,
                        library: { type: 'image' },
                        button : { text : 'Use image' }
                    });

                    // Get the url when done.
                    frame.on('select', function() {
                        var selection = frame.state().get('selection');
                        selection.each(function(attachment) {

                            // if ( typeof attachment.attributes.sizes === 'undefined' ) {
                            // 	return;
                            // }

                            if ( _input.length > 0 ) {
                                _input.val(attachment.id);
                            }

                            if ( _preview.length > 0 ) {
                                // remove current preview
                                if ( _preview.find('img').length > 0 ) {
                                    _preview.find('img').remove();
                                }
                                if ( _preview.find('i.remove').length > 0 ) {
                                    _preview.find('i.remove').remove();
                                }

                                // Get the preview image.
                                if ( typeof attachment.attributes.sizes != 'undefined' ) {
                                    var image = attachment.attributes.sizes.full;
                                    if ( typeof attachment.attributes.sizes.thumbnail != 'undefined' ) {
                                        image = attachment.attributes.sizes.thumbnail;
                                    }
                                    var url = image.url;
                                    var marginTop = ( _preview.height() - image.height ) / 2;
                                    var marginLeft = ( _preview.width() - image.width ) / 2;
                                    var filename = '';
                                } else {
                                    var url = attachment.attributes.url;
                                    var marginTop = ( _preview.height() - 64 ) / 2;
                                    var marginLeft = ( _preview.width() - 48 ) / 2;
                                    var filename = attachment.attributes.filename;
                                }

                                $("<img src='" + url + "'/>").appendTo(_preview);
                                $("<i class='dashicons dashicons-no-alt remove'></i>").prependTo(_preview);
                            }
                            // We need to trigger a change so that WP would detect that we changed the value.
                            // Or else the save button won't be enabled.
                            _input.trigger('change');

                            _remove.show();
                            $('.sk-admin-builder-upload .thumbnail').find('img').parent().addClass('has-value').find(':before').css({'opacity':'0'});
                        });
                        frame.off('select');
                    });

                    // Open the uploader.
                    frame.open();

                    return false;
                });
            });
        </script>
        <?php
    }
}
