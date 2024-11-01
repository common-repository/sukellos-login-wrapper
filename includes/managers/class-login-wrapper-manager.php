<?php
namespace Sukellos;

use Sukellos\WP_Sukellos_Login_Wrapper_Loader;
use Sukellos\WPFw\Utils\WP_Helper;
use Sukellos\WPFw\WP_PLoad;
use Sukellos\WPFw\Utils\WP_Log;
use WP_Query;
use Walker_Nav_Menu_Checklist;
use Sukellos\WPFw\Singleton;

defined( 'ABSPATH' ) or exit;

/**
 * Login Wrapper management
 *
 * @since 1.0.0
 */
class Login_Wrapper_Manager {

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

        // Init hooks
        add_action('admin_head-nav-menus.php', array( $this, 'action_admin_head_nav_menus') );

        add_filter('wp_setup_nav_menu_item', array( $this, 'filter_wp_setup_nav_menu_type_label') );
        add_filter('wp_setup_nav_menu_item', array( $this, 'filter_wp_setup_nav_menu_item') );
        add_filter('login_redirect', array( $this, 'filter_login_redirect_override'), 11, 3);
    }


    /**
     * Used to return the correct title for the double login/logout menu item
     */
    public function get_loginout_title($title) {
        $titles = explode('|', $title);

        if(!is_user_logged_in()) {

            return esc_html(isset($titles[0])?$titles[0]:WP_Helper::sk__( 'Login' ) );
        } else {

            return esc_html(isset($titles[1]) ? $titles[1] : WP_Helper::sk__('Logout'));
        }
    }

    public function sukellos_nav_menu_metabox($object) {
        global $nav_menu_selected_id;

        $elems = array(
            //'#sukelloslogin#' => WP_Helper::sk__('Login'),
            //'#sukelloslogout#' => WP_Helper::sk__('Logout'),
            '#sukellosloginout#' => WP_Helper::sk__('Contextual Login or Logout')
        );


        $elems_obj = array();

        foreach($elems as $value => $title) {
            $elems_obj[$title] = new Login_Logout_Item();
            $elems_obj[$title]->object_id = esc_attr($value);
            $elems_obj[$title]->title = esc_attr($title);
            $elems_obj[$title]->url = esc_attr($value);
        }

        $walker = new Walker_Nav_Menu_Checklist(array());

        $html_content = '
            <div id="login-links" class="loginlinksdiv">
                <div id="tabs-panel-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
                    <ul id="login-linkschecklist" class="list:login-links categorychecklist form-no-clear">
        ';
        $html_content .= walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $elems_obj), 0, (object) array('walker' => $walker));
        $html_content .= '
                    </ul>
                </div>
                <p class="button-controls">
                      <span class="add-to-menu">
                        <input type="submit"'.disabled($nav_menu_selected_id, 0).' class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-login-links-menu-item" id="submit-login-links" />
                        <span class="spinner"></span>
                      </span>
                </p>
            </div>
        ';
        echo $html_content;
    }


    /**
     *          ===============
     *      =======================
     *  ============ HOOKS ===========
     *      =======================
     *          ===============
     */

    /**
     * Add a metabox in admin menu page
     */
    public function action_admin_head_nav_menus() {

        add_meta_box('sukellos', WP_Helper::sk__('Login Wrapper'), array( $this, 'sukellos_nav_menu_metabox' ), 'nav-menus', 'side', 'default');
    }

    /**
     * Modify the "type_label"
     */
    public function filter_wp_setup_nav_menu_type_label($menu_item) {

//        $elems = array('#sukelloslogin#', '#sukelloslogout#', '#sukellosloginout#');
        $elems = array('#sukellosloginout#');

        if(isset($menu_item->object, $menu_item->url) && 'custom' == $menu_item->object && in_array($menu_item->url, $elems)) {

            $menu_item->type_label = WP_Helper::sk__('Dynamic Link');
        }
        return $menu_item;
    }

    /**
     * The main code, this replace the #keyword# by the correct links with nonce ect 
     */
    public function filter_wp_setup_nav_menu_item($item) {
        global $pagenow;

        if ( ($pagenow != 'nav-menus.php') && !defined('DOING_AJAX') && isset($item->url) && (strstr($item->url, '#sukellos') != '')) {
            $login_page_url = get_option( WP_Sukellos_Login_Wrapper_Loader::instance()->get_options_suffix_param().'_login_page_url', wp_login_url() );
            if ( $login_page_url === '' ) {

                $login_page_url = wp_login_url();
                update_option( WP_Sukellos_Login_Wrapper_Loader::instance()->get_options_suffix_param().'_login_page_url', $login_page_url);
            }
            $logout_redirect_url = get_option(WP_Sukellos_Login_Wrapper_Loader::instance()->get_options_suffix_param().'_logout_redirect_url', home_url());
            if ( $logout_redirect_url === '' ) {

                $logout_redirect_url = home_url();
                update_option( WP_Sukellos_Login_Wrapper_Loader::instance()->get_options_suffix_param().'_logout_redirect_url', $logout_redirect_url);
            }

            switch($item->url) {

//                case '#sukelloslogin#':
//                    $item->url = $login_page_url;
//                    break;
//                case '#sukelloslogout#':
//
//                    $item->url = wp_logout_url( $logout_redirect_url );
//                    break;
                default: //Should be #sukellosloginout#

                    $item->url = (is_user_logged_in()) ? wp_logout_url( $logout_redirect_url ) : $login_page_url;
                    $item->title = $this->get_loginout_title($item->title);
            }
        }

        return $item;
    }

    /**
     * Redirect override
     * @param $redirect_to
     * @param $request
     * @param $user
     * @return mixed
     */
    public function filter_login_redirect_override($redirect_to, $request, $user) {

        WP_Log::debug( 'filter_login_redirect_override ', ['$redirect_to' => $redirect_to] );
        //If the login failed, or if the user is an Admin - let's not override the login redirect
        if ( !is_a( $user, 'WP_User' ) || user_can( $user, 'manage_options') )  {

            return $redirect_to;
        }

        $login_redirect_url = get_option( WP_Sukellos_Login_Wrapper_Loader::instance()->get_options_suffix_param().'_login_redirect_url', home_url() );
        if ( $login_redirect_url === '' ) {

            $login_redirect_url = home_url();
            update_option( WP_Sukellos_Login_Wrapper_Loader::instance()->get_options_suffix_param().'_login_redirect_url', $login_redirect_url );
        }
        return $login_redirect_url;
    }
}


/**
 * Class used in menu generation by Walker
 */
class Login_Logout_Item {
    public $db_id = 0;
    public $object = 'sukelloslog';
    public $object_id;
    public $menu_item_parent = 0;
    public $type = 'custom';
    public $title;
    public $url;
    public $target = '';
    public $attr_title = '';
    public $classes = array();
    public $xfn = '';
}