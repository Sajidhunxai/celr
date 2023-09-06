<?php

defined( 'ABSPATH' ) || exit;
//add_filter("wnbell_user_notifications_array", "wnb_bp_sorted");
add_filter(
    "wnbell_user_notifications_output",
    "wnbell_bp_output",
    10,
    4
);
//add_filter("wnbell_unseen_count", "wnb_bp_unseen_count");
//add_action("wnbell_notifications_processed", "wnb_bp_update_bp_last_seen");
function wnbell_bp_get_notifications( $user_id )
{
    global  $wpdb ;
    $bp = buddypress();
    $table = $bp->notifications->table_name;
    $components = bp_notifications_get_registered_components();
    $select_query = "SELECT *, date_notified as date FROM {$table} WHERE user_id = %d\r\n    AND component_name IN(" . wnbell_escape_array( $components ) . ") ORDER BY date desc LIMIT 5";
    $query = $wpdb->prepare( $select_query, $user_id );
    $results = $wpdb->get_results( $query );
    foreach ( $results as $key => $notification ) {
        $results[$key] = (array) $notification;
        $results[$key]['type'] = 'bp';
    }
    return $results;
}

function wnbell_bp_sorted( $current_user_id, $trigger_array )
{
    //$current_user_id = get_current_user_id();
    $trigger_array = wnbell_trigger_sort( $trigger_array, wnbell_bp_get_notifications( $current_user_id ) );
    return $trigger_array;
}

function wnbell_bp_output(
    $output,
    $trigger_notification,
    $options,
    $seen_notifications
)
{
    if ( !isset( $options['enable_bp'] ) || !$options['enable_bp'] ) {
        return $output;
    }
    $trigger_id = '';
    $trigger_output = $output;
    $trigger_type = '';
    $item_id = '';
    $trigger_text = '';
    $item_class = 'wnbell_notification_item';
    $post_link = '';
    if ( !array_key_exists( 'type', $trigger_notification ) || !($trigger_notification['type'] === 'bp') ) {
        return $output;
    }
    $trigger_type = $trigger_notification['type'];
    
    if ( $trigger_notification['type'] === 'bp' ) {
        $trigger_text = 'You have a new notification';
        $trigger_text = wnbell_bp_get_message( $trigger_notification );
    }
    
    
    if ( array_key_exists( 'is_new', $trigger_notification ) && $trigger_notification['is_new'] == 0 ) {
        if ( isset( $options['item_seen_class_attribute'] ) && $options['item_seen_class_attribute'] != '' ) {
            $item_class = $options['item_seen_class_attribute'];
        }
        if ( isset( $options['item_seen_id_attribute'] ) && $options['item_seen_id_attribute'] != '' ) {
            $item_id = $options['item_seen_id_attribute'];
        }
    } else {
        if ( isset( $options['item_unseen_class_attribute'] ) && $options['item_unseen_class_attribute'] != '' ) {
            $item_class = $options['item_unseen_class_attribute'];
        }
        if ( isset( $options['item_unseen_id_attribute'] ) && $options['item_unseen_id_attribute'] != '' ) {
            $item_id = $options['item_unseen_id_attribute'];
        }
    }
    
    $trigger_output = '<div class="' . $item_class . '" id="' . $item_id . '">';
    //     $trigger_output .= apply_filters('wnbell_user_item_append', '', $trigger_id, $trigger_type);
    $trigger_output .= $trigger_text;
    $trigger_output .= '</div>';
    return $trigger_output;
}

function wnbell_bp_unseen_count( $count )
{
    global  $wpdb ;
    $user_id = get_current_user_id();
    $bp = buddypress();
    $table = $bp->notifications->table_name;
    $components = bp_notifications_get_registered_components();
    
    if ( isset( $_POST['last_seen_bp'] ) && $_POST['last_seen_bp'] ) {
        $bp_last_seen = $_POST['last_seen_bp'];
        $select_query = "SELECT count(*) FROM {$table} WHERE user_id = %d AND AND id > %d AND is_new = 1\r\n        AND component_name IN(" . wnbell_escape_array( $components ) . ")\r\n    GROUP BY user_id  ORDER BY date_notified desc ";
        $query = $wpdb->prepare( $select_query, $user_id, $bp_last_seen );
    } else {
        $bp_last_seen = get_user_meta( $user_id, 'wnbell_bp_last_seen', true );
        $select_query = "SELECT count(*) FROM {$table} WHERE user_id = %d ";
        if ( $bp_last_seen ) {
            $select_query .= " AND id > %d ";
        }
        $select_query .= " AND is_new = 1\r\n        AND component_name IN(" . wnbell_escape_array( $components ) . ") GROUP BY user_id  ORDER BY date_notified desc ";
        // and id >%d
        
        if ( $bp_last_seen ) {
            $query = $wpdb->prepare( $select_query, $user_id, $bp_last_seen );
        } else {
            $query = $wpdb->prepare( $select_query, $user_id );
        }
    
    }
    
    
    if ( isset( $query ) ) {
        $results = $wpdb->get_var( $query );
    } else {
        $results = 0;
    }
    
    $count += $results;
    return $count;
}

function wnbell_bp_update_bp_last_seen()
{
    global  $wpdb ;
    $user_id = get_current_user_id();
    $bp = buddypress();
    $table = $bp->notifications->table_name;
    $components = bp_notifications_get_registered_components();
    $query = "SELECT MAX(id) FROM {$table} WHERE user_id = %d AND component_name IN(" . wnbell_escape_array( $components ) . ")";
    $query = $wpdb->prepare( $query, $user_id );
    $last_seen = $wpdb->get_var( $query );
    update_user_meta( $user_id, 'wnbell_bp_last_seen', $last_seen );
}

function wnbell_bp_get_message( $notification )
{
    $bp = buddypress();
    $component_name = $notification['component_name'];
    if ( 'xprofile' == $notification['component_name'] ) {
        $component_name = 'profile';
    }
    
    if ( isset( $bp->{$component_name}->notification_callback ) && is_callable( $bp->{$component_name}->notification_callback ) ) {
        $description = call_user_func(
            $bp->{$component_name}->notification_callback,
            $notification['component_action'],
            $notification['item_id'],
            $notification['secondary_item_id'],
            1
        );
    } elseif ( isset( $bp->{$component_name}->format_notification_function ) && function_exists( $bp->{$component_name}->format_notification_function ) ) {
        $description = call_user_func(
            $bp->{$component_name}->format_notification_function,
            $notification['component_action'],
            $notification['item_id'],
            $notification['secondary_item_id'],
            1
        );
        // Allow non BuddyPress components to hook in
    } else {
        $ref_array = array(
            $notification['component_action'],
            $notification['item_id'],
            $notification['secondary_item_id'],
            1,
            'string',
            $notification['component_action'],
            $component_name,
            $notification['id']
        );
        $description = apply_filters_ref_array( 'bp_notifications_get_notifications_for_user', $ref_array );
    }
    
    return apply_filters( 'bp_get_the_notification_description', $description );
}
