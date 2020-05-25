<?php
namespace sbcoa\wpcrmtoum;
/**
 * Maps the values from WP-CRM to Ultimate-Member
 *
 * For checkboxes and radio buttons
 *
 * e.g.
 *
 * user_category options map from 5 individual fields to an array
 *
 * @param integer $user_id User ID of the current user
 * @param string $wpcrm_field slug of the WP-CRM field
 * @param array $um_field definition of the UM field
 * *
 */
function map_wpcrm_to_um( $user_id, $wpcrm_field, $um_field ) {
    $wpcrm_field_definition = get_wpcrm_field_definition( $wpcrm_field );
    if ( $wpcrm_field_definition) {

        if ( $wpcrm_field_definition['input_type'] === 'checkbox' ) {
            map_wpcrm_options_to_serialised($user_id, $wpcrm_field_definition, $um_field );
        } elseif ( $wpcrm_field_definition['input_type'] === 'radio' ) {
            map_wpcrm_value_to_serialised( $user_id, $wpcrm_field, $wpcrm_field_definition, $um_field );
        }

    }

}

function get_um_field_values( $um_field ) {
    if ( isset( $_POST[ $um_field ])) {
        $um_field_values = $_POST[$um_field];
    } else {
        $um_field_values = null;
    }
    return $um_field_values;
}

/**
 * Maps the UM values back to the WP-CRM values
 * The UM values are also stored.
 *
 * @param $user_id
 * @param $um_field
 * @param $wpcrm_field
 */

function map_um_to_wpcrm( $user_id, $um_field, $wpcrm_field ) {
    $um_field_values = get_um_field_values( $um_field );
    $field = UM()->builtin()->get_specific_field($um_field);
    bw_trace2( $field, 'field', true, BW_TRACE_DEBUG );
    $wpcrm_field_definition = get_wpcrm_field_definition( $wpcrm_field );

    if ( $wpcrm_field_definition) {
        if ( $wpcrm_field_definition['input_type'] === 'checkbox' ) {
            map_wpcrm_options_from_serialised($user_id, $wpcrm_field_definition, $um_field_values);
        } elseif ( $wpcrm_field_definition['input_type'] === 'radio' ) {
            map_wpcrm_value_from_um( $user_id, $wpcrm_field, $wpcrm_field_definition, $field, $um_field_values );
        }
        update_user_meta( $user_id, $um_field, $um_field_values );
    } else {
        // Either WP-CRM is not activated or the field is not defined. Should we report an error?
    }
}

function map_wpcrm_options_to_serialised( $id, $wpcrm_field_definition, $um_field ) {
    bw_trace2();
    $um_array = array();
    $index = 0;
    foreach ( $wpcrm_field_definition['option_keys'] as $key => $meta_key ) {
        $value = get_user_meta( $id, $meta_key, true );
        if ( $value === 'on') {
            $um_array[$index] = $um_field['options'][$index];
        }
        $index++;
    }
    bw_trace2( $um_array, 'um_array', false, BW_TRACE_VERBOSE );
    $result = update_user_meta( $id, $um_field['metakey'], $um_array );
    bw_trace2( $result, 'result', false, BW_TRACE_VERBOSE );
}

/**
 * Maps the checkbox values from UM to the WP-CRM equivalents
 *
 * Question: Is it OK to use 'off' or should we delete the post meta value?
 * @param $id
 * @param $wpcrm_field_definition
 * @param $um_field
 */

function map_wpcrm_options_from_serialised( $id, $wpcrm_field_definition, $um_field ) {
    $um_field_values = $um_field;
    $index = 0;
    foreach ( $wpcrm_field_definition['option_keys'] as $key => $meta_key ) {
        if ( in_array(  $wpcrm_field_definition['option_labels'][$key], $um_field_values ) ) {
            $value = 'on';
        } else {
            $value = 'off';
        }
        update_user_meta( $id, $meta_key, $value );
        $index++;

    }
}

function map_wpcrm_value_to_serialised( $id, $wpcrm_field, $wpcrm_field_definition, $um_field ) {
    $value = get_user_meta( $id, $wpcrm_field, true );
    bw_trace2( $value, "value" );
    //foreach ( $wpcrm_field_definition['option_labels'] )

    $label = $wpcrm_field_definition['option_labels'][$value];
    $flipped = array_flip( $um_field['options'] );
    $pos = $flipped[ $label ];
    $um_array = [ $pos ];
    update_user_meta( $id, $um_field['metakey'], $um_array );
}

/**
 * @param $user_id
 * @param $wpcrm_field
 * @param $wpcrm_field_definition
 * @param $field
 * @param $um_field_values
 */
function map_wpcrm_value_from_um( $user_id, $wpcrm_field, $wpcrm_field_definition, $field, $um_field_values ) {
    $um_value = $um_field_values[0];
    $um_option = $field['options'][$um_value];
    bw_trace2( $um_option, 'um_option', false, BW_TRACE_VERBOSE );
    $flipped = array_flip( $wpcrm_field_definition['option_labels'] );
    $wpcrm_value = $flipped[ $um_option ];
    bw_trace2( $wpcrm_value, 'wpcrm_value', true, BW_TRACE_DEBUG );
    update_user_meta( $user_id, $wpcrm_field, $wpcrm_value );
}


/**
 * Obtains the definition of the WP-CRM field.

 * If WP-CRM is not activated then the global $wp_crm won't be set
 * so we expect this to return null. No mapping will be performed.
`
[user_category] => Array

    [title] => (string) "User category"
    [group] => (string) "0"
    [description] => (string) ""
    [primary] => (string) "true"
    [input_type] => (string) "checkbox"
    [options] => (string) "Chalet Owner,Association Member,Committee Member,Task Group Team Lead,Task Group Member,Contractor"
    [option_keys] => Array

        [chalet-owner] => (string) "user_category_option_chalet-owner"
        [association-member] => (string) "user_category_option_association-member"
        [committee-member] => (string) "user_category_option_committee-member"
        [task-group-team-lead] => (string) "user_category_option_task-group-team-lead"
        [task-group-member] => (string) "user_category_option_task-group-member"
        [contractor] => (string) "user_category_option_contractor"

    [option_labels] => Array

        [chalet-owner] => (string) "Chalet Owner"
        [association-member] => (string) "Association Member"
        [committee-member] => (string) "Committee Member"
        [task-group-team-lead] => (string) "Task Group Team Lead"
        [task-group-member] => (string) "Task Group Member"
        [contractor] => (string) "Contractor"

    [has_options] => (boolean) 1
`

*/
function get_wpcrm_field_definition( $wpcrm_field ) {
    global $wp_crm;
    bw_trace2( $wp_crm, 'wp_crm', true, BW_TRACE_VERBOSE );
    $field_definition = null;
    if ( isset( $wp_crm['data_structure']['attributes'][ $wpcrm_field ])) {
        $field_definition = $wp_crm['data_structure']['attributes'][ $wpcrm_field ];
    }
    return $field_definition;
}

/**
 * Gets the UM field's metakey when it's different from the WP-CRM field
 * This is required for radio buttons.
 * The convention is that the UM field name is suffixed `_um`.
 *
 * @param integer|string $key when integer the key names are the same
 * @param string $wpcrm_name the name of the WP-CRM field
 * @return mixed
 */
function get_um_name( $key, $wpcrm_name ) {
    if (is_numeric($key)) {
        $name = $wpcrm_name;
    } else {
        $name = $key;
    }
    return $name;
}

/**
 * Displays the additional user fields on the Account page
 * Performs a mapping of field values from WP-CRM to UM
 *
 * @param $args
 * @throws \Exception
 */

function showUMExtraFields( $args )
{
    $id = um_user('ID');
    UM()->user()->set($id);  // We do this to clear the cache

    // @TODO Why do we need this?
    $wpcrm_user = wp_crm_get_user($id);
    bw_trace2($wpcrm_user, 'wpcrm user', true, BW_TRACE_VERBOSE);

    $names = apply_filters('query_extra_account_fields', []);
    $fields = array();
    foreach ($names as $key => $wpcrm_name) {
        $name = get_um_name($key, $wpcrm_name);
        $fields[$name] = UM()->builtin()->get_specific_field($name);
        map_wpcrm_to_um($id, $wpcrm_name, $fields[$name]);
    }

    // @TODO Determine if applying this filter is this really necessary?
    bw_trace2($fields, "fields-before", false, BW_TRACE_DEBUG);
    $fields = apply_filters('um_account_secure_fields', $fields, $id);
    bw_trace2($fields, "fields-after", false, BW_TRACE_DEBUG);

    $output = '';
    foreach ($fields as $key => $data) {
        $output .= UM()->fields()->edit_field($key, $data);
    }
    echo $output;
}

/**
 * Maps the updated fields back to WP-CRM
 *
 * It appears that the $changes array doesn't contain the fields we're expecting
 * so we basically ignore that and determine the names of the fields using a filter.
 *
 * @param $changes
 * @param $user_id
 */

function getUMFormData( $changes, $user_id) {
    bw_trace2();
    $id = um_user('ID');
    $names = apply_filters( 'query_extra_account_fields', [] );
    foreach( $names as $key => $wpcrm_name ) {
        $name = get_um_name( $key, $wpcrm_name );
        map_um_to_wpcrm($id, $name, $wpcrm_name);
    }
}

