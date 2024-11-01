<?php

namespace Sukellos\WPFw;

defined( 'ABSPATH' ) or exit;

use Sukellos\WPFw\Utils\WP_Log;

/**
 * Wordpress Generic Shortcode 
 *
 *
 * @version 5.5.0
 */
abstract class WP_GridBuilder {

    /**
     * Get current term id
     * May retrieve it directly from the REQUEST ... because of an AJAX call (facet) breaking on the current page context (current taxonomy missing)
     *
     * @return bi-uple the term id, otherwise null if not found, and the queried taxonomy
     */
    protected function get_current_tax_infos() {

        // Get current term for taxonomy product_tag
        $query_var_tax = get_query_var( 'taxonomy' );
        $query_var_term = get_query_var( 'term' );
        WP_Log::debug('get_current_term_id ', ['$query_var_tax' => $query_var_tax, '$query_var_term' => $query_var_term]);

        if ($query_var_tax === '') {

            // Get directly the REQUEST ... because of an AJAX call (facet) breaking on the current page context (current taxonomy missing)
            if ( array_key_exists( 'wpgb', $_REQUEST ) ) {

                $wpgb_request = stripslashes($_REQUEST['wpgb']);
                $wpgb = (array) json_decode($wpgb_request, true);
                $main_query = $wpgb['main_query'];
                if ( array_key_exists( 'product_cat', $main_query ) ) {

                    $query_var_tax = 'product_cat';
                    $query_var_term = $main_query['product_cat'];
                    WP_Log::debug('get_current_term_id taxonomy were not found, then took directly from request', ['$query_var_tax' => $query_var_tax, '$query_var_term' => $query_var_term]);
                }
            }
            if ($query_var_tax === '') {

                WP_Log::debug('get_current_term_id taxonomy were not found, and not found directly from request :(', []);
                return array(null, null);
            }
        }

        // Get the current term id
        $cterm = get_term_by( 'slug', $query_var_term, $query_var_tax );
        $cterm_id = $cterm->term_id;
        WP_Log::debug('get_current_term_id ', ['$cterm_id' => $cterm_id]);
        return array($cterm_id, $query_var_tax);
    }

}
