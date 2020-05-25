<?php
/*
Plugin Name: sbcoa
Plugin URI: https://github.com/bobbingwide/sbcoa
Description: SBCOA thugin - - WordPress theme/plugin hybrid for Sandown Bay Chalet Owners Association
Version: 0.0.0
Author: bobbingwide
Author URI: https://www.bobbingwide.com/about-bobbing-wide
Text Domain: sbcoa
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2020 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/
namespace sbcoa;

sbcoa_loaded();

function sbcoa_loaded() {
    add_action( 'um_after_account_general', 'sbcoa\showUMExtraFields', 99);
    add_action( 'um_account_pre_update_profile', 'sbcoa\getUMFormData', 99, 2);
    add_filter( 'query_extra_account_fields', 'sbcoa\query_extra_account_fields', 0 );
}

function showUMExtraFields( $args ) {
    $sbcoa_dir = dirname( __FILE__ );
    require_once( "${sbcoa_dir}/includes/wpcrmtoum.php" );
    \sbcoa\wpcrmtoum\showUMExtraFields( $args );

}
function getUMFormData( $changes, $user_id ) {
    $sbcoa_dir = dirname( __FILE__ );
    require_once( "${sbcoa_dir}/includes/wpcrmtoum.php" );
    \sbcoa\wpcrmtoum\getUMFormData( $changes, $user_id );

}

/**
 * Default function to return the array of UM fields
 * In case there's nothing in the child theme
 *
 * If the key is not numeric it means there's a different name for the UM field and the WP-CRM field.
 *
 * @param $field_names
 * @return array
 */
function query_extra_account_fields( $field_names ) {

    if ( empty( $field_names )) {
        $field_names = ['user_category',
            'chalet_no',
            'multiple_chalets_um' => 'multiple_chalets',
            'lease_length',
            'other_chalet_numbers',
            'lease_start_date',
            'date_chalet_purchased',
            'address',
            'town',
            'city_or_county',
            'postcode',
            'phone_number',
            'mobile_number',
            'lease_start_date',
            'lease_length',
            'subscription_status',
            'communication_preference',
            'membership_state',

        ];
        \sbcoa\remove_themes_um_logic();
    }
    return $field_names;
}

/**
 * Should we disable the logic in functions.php if it implements the `query_extra_account_fields` filter?
 * OR should we allow the theme's functionality to exist so that it can add further fields to the account?
 * For the time being I want to be able to develop the new code in the plugin without having to mess with the
 * code I've already written. So the disabling logic seems to be the way to go.
 */
function remove_themes_um_logic() {
    remove_action('um_after_account_general', 'showUMExtraFields', 100);
    remove_action( 'um_account_pre_update_profile', 'getUMFormData', 100, 2);
}

if ( !function_exists( "bw_trace2" ) ) {
    function bw_trace2( $p=null ) { return $p; }
    function bw_backtrace() {}
}
