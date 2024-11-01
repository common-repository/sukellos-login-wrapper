<?php

namespace Sukellos\WPFw\AdminBuilder;

defined( 'ABSPATH' ) or exit;

/**
 * Item type interface
 *
 * Used for item types constants
 *
 * @since 1.0.0
 */
class Item_Type {

    // Containers
    const ADMIN_PAGE_SIMPLE = 'admin-page-simple';
    const ADMIN_PAGE_TABS = 'admin-page-tabs';
    const ADMIN_TAB = 'admin-tab';
    const ADMIN_ITEM_GROUP = 'admin-item-group';
    const ADMIN_ITEM_FORM = 'admin-item-form';
    const META_BOX = 'meta-box';
    const USER_PROFILE = 'user-profile';

    // Fields
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const WYSIWYG_EDITOR = 'wysiwyg-editor';
    const CHECKBOX = 'checkbox';
    const COLOR = 'color';
    const DATE = 'date';
    const ENABLE = 'enable';
    const FILE = 'file';
    const UPLOAD = 'upload';
    const GALLERY = 'gallery';
    const MULTICHECK = 'multicheck';
    const MULTICHECK_POSTS = 'multicheck-posts';
    const MULTICHECK_TERMS = 'multicheck-terms';
    const MULTICHECK_USERS = 'multicheck-users';
    const NUMBER = 'number';
    const RADIO = 'radio';
    const RADIO_IMAGE = 'radio-image';
    const RADIO_FONT_ICON = 'radio-font-icon';
    const RADIO_PALETTE = 'radio-palette';
    const SELECT = 'select';
    const SELECT_POSTS = 'select-posts';
    const SELECT_TERMS = 'select-terms';
    const SELECT_USERS = 'select-users';
    const SELECT_WEBSAFE_FONT_FAMILY = 'select-websafe-font-family';
    const SELECT_GOOGLE_FONT_FAMILY = 'select-google-font-family';
    const SORTABLE = 'sortable';
    const CODE = 'code';
    const NOTE = 'note';
    const EDD_LICENSING = 'edd-licensing';

    // Others
    const AJAX_BUTTON = 'ajax-button';
    const CONTENT = 'content';
    const HEADER = 'header';

    static public function get_supported_field_types() {

        return array(
            self::TEXT,
            self::TEXTAREA,
            self::WYSIWYG_EDITOR,
            self::CHECKBOX,
            self::COLOR,
            self::DATE,
            self::ENABLE,
            self::FILE,
            self::UPLOAD,
            self::GALLERY,
            self::MULTICHECK,
            self::MULTICHECK_POSTS,
            self::MULTICHECK_TERMS,
            self::MULTICHECK_USERS,
            self::NUMBER,
            self::RADIO,
            self::RADIO_IMAGE,
            self::RADIO_PALETTE,
            self::RADIO_FONT_ICON,
            self::SELECT,
            self::SELECT_POSTS,
            self::SELECT_TERMS,
            self::SELECT_USERS,
            self::SORTABLE,
            self::CODE,
            self::SELECT_WEBSAFE_FONT_FAMILY,
            self::SELECT_GOOGLE_FONT_FAMILY,
            self::EDD_LICENSING,
        );
    }

    static public function get_supported_item_types() {

        return array(
            self::AJAX_BUTTON,
            self::ADMIN_ITEM_GROUP,
            self::ADMIN_ITEM_FORM,
            self::CONTENT,
            self::HEADER,
            self::NOTE,
        );
    }
}
