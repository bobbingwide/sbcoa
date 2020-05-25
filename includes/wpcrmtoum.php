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

function map_um_to_wpcrm( $user_id, $um_field ) {
    $um_field_values = get_um_field_values( $um_field );
    $field = UM()->builtin()->get_specific_field($um_field);
    bw_trace2( $field, 'field');
    if ( $field['type'] === 'radio' ) {
        $wpcrm_field = str_replace( '_um', '', $um_field);
    } else {
        $wpcrm_field = $um_field;
    }
    $wpcrm_field_definition = get_wpcrm_field_definition( $wpcrm_field );

    if ( $wpcrm_field_definition) {
        if ( $wpcrm_field_definition['input_type'] === 'checkbox' ) {
            map_wpcrm_options_from_serialised($user_id, $wpcrm_field_definition, $um_field_values);

        } elseif ( $wpcrm_field_definition['input_type'] === 'radio' ) {
            map_wpcrm_value_from_um( $user_id, $wpcrm_field, $wpcrm_field_definition, $field, $um_field_values );



        }
        update_user_meta( $user_id, $um_field, $um_field_values );
    } else {

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

    bw_trace2( $um_array, 'um_array', false );
    $result = update_user_meta( $id, $um_field['metakey'], $um_array );
    bw_trace2( $result, 'result', false );
    //gob();
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
    bw_trace2();
    $um_value = $um_field_values[0];
    $um_option = $field['options'][$um_value];
    bw_trace2( $um_option, 'um_option', false );
    $flipped = array_flip( $wpcrm_field_definition['option_labels'] );
    $wpcrm_value = $flipped[ $um_option ];
    bw_trace2( $wpcrm_value, 'wpcrm_value', false);
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

function showUMExtraFields( $args ) {
    $id = um_user('ID');
    UM()->user()->set( $id );  // We do this to clear the cache

    $wpcrm_user = wp_crm_get_user( $id );
    bw_trace2( $wpcrm_user, 'wpcrm user');

    $names = apply_filters( 'query_extra_account_fields', [] );
    $fields = array();
    foreach( $names as $key => $wpcrm_name ) {
        if ( is_numeric( $key ) ) {
            $name = $wpcrm_name;
        } else {
            $name = $key;
        }
        $fields[$name] = UM()->builtin()->get_specific_field($name);
        map_wpcrm_to_um( $id, $wpcrm_name, $fields[$name]);
    }
    bw_trace2( $fields, "fields-before" );
    //map_wpcrm_to_um( $id, 'multiple_chalets', $fields['multiple_chalets_um'] );

    //map_wpcrm_to_um( $id, 'user_category', $fields['user_category'] );

    //map_wpcrm_to_um( $id, 'lease_length', $fields['lease_length'] );
    //map_wpcrm_to_um( $id, 'multiple_chalets', $fields['multiple_chalets'] );

    $fields = apply_filters('um_account_secure_fields', $fields, $id);
    bw_trace2( $fields, "fields-after" );
    $output = '';
    foreach( $fields as $key => $data )
        $output .= UM()->fields()->edit_field( $key, $data );

    echo $output;
}



function getUMFormData( $changes, $user_id)
{
    //gob();
    bw_trace2();
    //return;
    $id = um_user('ID');
    $names = array('user_category',
        /*
        ['user_category_option_chalet-owner',
            'user_category_option_association-member',
            'user_category_option_committee-member',
            'user_category_option_task-group-team-lead',
            'user_category_option_task-group-member',
            'user_category_option_contractor'],
        */
        'chalet_no',
        'multiple_chalets_um',
        'lease_length',

        /*
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
        */
    );

    foreach ($names as $name) {
        map_um_to_wpcrm( $id, $name);
        //update_user_meta($id, $name, $_POST[$name]);
    }
};

