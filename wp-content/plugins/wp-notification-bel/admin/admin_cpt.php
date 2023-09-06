<?php

defined( 'ABSPATH' ) || exit;
add_action(
    'wnbell_custom_post_type_notification',
    'custom_post_type_notification_wnbell',
    10,
    2
);
function wnbell_set_object_terms(
    $object_id,
    $terms,
    $tt_ids,
    $taxonomy
)
{
}

function custom_post_type_notification_wnbell( $post_id, $post )
{
    // if (!current_user_can('edit_posts')) {
    //     wp_die('Not allowed');
    // }
    $options = get_option( 'wnbell_options' );
    $custom_post_type = ( !$options || $options && isset( $options['custom_post_type'] ) && $options['custom_post_type'] != '' ? $options['custom_post_type'] : false );
    
    if ( $custom_post_type !== $post->post_type ) {
        $cpt = true;
        if ( $cpt ) {
            return;
        }
    }
    
    if ( $post->post_type == "wnbell_notifications" ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check if our nonce is set.
    
    if ( !isset( $_POST['wnbell_post_custom_box_nonce'] ) ) {
        wnbell_add_post_default( $post_id, $post, true );
        return $post_id;
    }
    
    $nonce = $_POST['wnbell_post_custom_box_nonce'];
    // Verify that the nonce is valid.
    if ( !wp_verify_nonce( $nonce, 'wnbell_post_custom_box' ) ) {
        return $post_id;
    }
    // if (!current_user_can('edit_posts')) {
    //     wp_die('Not allowed');
    // }
    
    if ( $post->post_status == 'publish' || $post->post_status == 'draft' ) {
        $options = get_option( 'wnbell_options' );
        
        if ( !isset( $_POST['enable_new_custom_post_type'] ) ) {
            return;
        } else {
            if ( sanitize_text_field( $_POST['enable_new_custom_post_type'] ) == '1' ) {
                if ( isset( $options['enable_new_custom_post_type'] ) && $options['enable_new_custom_post_type'] == false ) {
                    return;
                }
            }
        }
        
        foreach ( array( 'enable_new_custom_post_type' ) as $option_name ) {
            
            if ( isset( $_POST[$option_name] ) && sanitize_text_field( $_POST[$option_name] ) === '0' ) {
                $options[$option_name] = true;
            } else {
                $options[$option_name] = false;
            }
        
        }
        update_option( 'wnbell_options', $options );
        if ( get_option( 'wnbell_options' )['enable_new_custom_post_type'] == false ) {
            return;
        }
        $length = sizeof( ( isset( $options['wnbell_name'] ) ? $options['wnbell_name'] : array( 1 ) ) );
        $args = array(
            'fields' => 'all',
        );
        $notification_exists = get_post_meta( $post_id, "wnbell_notification_id", true );
        
        if ( !$notification_exists ) {
            $args = array(
                'post_type' => 'wnbell_notifications',
            );
            $notification_id = wp_insert_post( $args );
        }
        
        for ( $i = 0 ;  $i < $length ;  $i++ ) {
            //if (isset($_POST['wnbell_item_name_' . $i]) && sanitize_text_field($_POST['wnbell_item_name_' . $i]) != '') {
            if ( isset( $_POST['wnbell_item_name_' . $i] ) ) {
                
                if ( !$notification_exists ) {
                    // $notification_id = wp_insert_post($args);
                    
                    if ( !is_wp_error( $notification_id ) ) {
                        //the post is valid
                        update_post_meta( $notification_id, 'wnbell_item_name_' . $i, sanitize_text_field( $_POST['wnbell_item_name_' . $i] ) );
                        update_post_meta( $notification_id, "post_id", $post_id );
                        update_post_meta( $post_id, "wnbell_notification_id", $notification_id );
                    } else {
                        //there was an error in the post insertion,
                        echo  $notification_id->get_error_message() ;
                    }
                
                } else {
                    update_post_meta( $notification_exists, 'wnbell_item_name_' . $i, sanitize_text_field( $_POST['wnbell_item_name_' . $i] ) );
                }
            
            }
        }
        
        if ( isset( $_POST['recipient_role'] ) && sanitize_text_field( $_POST['recipient_role'] ) != '' && sanitize_text_field( $_POST['recipient_role'] ) != 'all' ) {
            update_post_meta( $notification_id, 'wnbell_recipient_role', sanitize_text_field( $_POST['recipient_role'] ) );
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'wnbell_recipients_role';
            $recipient_role = sanitize_text_field( $_POST['recipient_role'] );
            $n_id = ( $notification_exists ? $notification_exists : $notification_id );
            if ( !$wpdb->update(
                $table_name,
                array(
                'user_role' => $recipient_role,
            ),
                array(
                'notification_id' => $n_id,
            ),
                array( '%s' )
            ) ) {
                $wpdb->insert( $table_name, array(
                    'notification_id' => $n_id,
                    'user_role'       => $recipient_role,
                ) );
            }
        } elseif ( isset( $_POST['recipient_role'] ) && sanitize_text_field( $_POST['recipient_role'] ) == 'all' ) {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'wnbell_recipients_role';
            $n_id = ( $notification_exists ? $notification_exists : $notification_id );
            $wpdb->delete( $table_name, array(
                'notification_id' => $n_id,
            ) );
        }
        
        $current_lg = false;
        $post_permalink = apply_filters( 'wnbell_cpt_link', get_post_permalink( $post_id ), $post_id );
        // $post_permalink = get_post_permalink($post_id);
        
        if ( !$notification_exists ) {
            update_post_meta( $notification_id, 'wnbell_link', $post_permalink );
        } else {
            update_post_meta( $notification_exists, 'wnbell_link', $post_permalink );
        }
        
        //featured image
        if ( $post->post_status == 'publish' ) {
            
            if ( !$notification_exists ) {
                wp_publish_post( $notification_id );
            } else {
                wp_publish_post( $notification_exists );
            }
        
        }
    }

}

add_action(
    'wnbell_adding_custom_meta_boxes',
    'wnbell_custom_post_type_meta_box',
    10,
    2
);
function wnbell_custom_post_type_meta_box( $post_type, $post )
{
    $options = get_option( 'wnbell_options' );
    if ( $options && isset( $options['enable_new_custom_post_type'] ) ) {
        
        if ( isset( $options['custom_post_type'] ) && $options['custom_post_type'] == $post_type ) {
            $context = ( isset( $options['meta_box_context'] ) ? $options['meta_box_context'] : 'normal' );
            add_meta_box(
                'wnbell_custom_post_notification_details_meta_box',
                __( 'Notification Details' ),
                'wnbell_render_post_meta_box',
                $post_type,
                $context,
                'low'
            );
        }
    
    }
}

function wnbell_add_post_default( $post_id, $post, $cpt = false )
{
    
    if ( $post->post_status == 'publish' || $post->post_status == 'draft' ) {
        $options = get_option( 'wnbell_options' );
        $enable_post = ( $cpt ? 'enable_new_custom_post_type' : 'enable_new_post' );
        if ( isset( $_POST[$enable_post] ) ) {
            return;
        }
        if ( !isset( $options[$enable_post] ) ) {
            return;
        }
        if ( isset( $options[$enable_post] ) && $options[$enable_post] == false ) {
            return;
        }
        if ( $post->post_type == "wnbell_notifications" ) {
            return;
        }
        if ( !apply_filters( 'wnbell_restrict_post_term', true, $post_id ) ) {
            return;
        }
        $length = sizeof( ( isset( $options['wnbell_name'] ) ? $options['wnbell_name'] : array( 1 ) ) );
        $args = array(
            'fields' => 'all',
        );
        $notification_exists = get_post_meta( $post_id, "wnbell_notification_id", true );
        
        if ( !$notification_exists ) {
            $args = array(
                'post_type' => 'wnbell_notifications',
            );
            $notification_id = wp_insert_post( $args );
        }
        
        for ( $i = 0 ;  $i < $length ;  $i++ ) {
            //if (isset($_POST['wnbell_item_name_' . $i]) && sanitize_text_field($_POST['wnbell_item_name_' . $i]) != '') {
            if ( !isset( $_POST['wnbell_item_name_' . $i] ) ) {
                
                if ( !$notification_exists ) {
                    // $notification_id = wp_insert_post($args);
                    
                    if ( !is_wp_error( $notification_id ) ) {
                        //the post is valid
                        update_post_meta( $notification_id, 'wnbell_item_name_' . $i, $options['wnbell_default_value'][$i] );
                        update_post_meta( $notification_id, "post_id", $post_id );
                        update_post_meta( $post_id, "wnbell_notification_id", $notification_id );
                    } else {
                        //there was an error in the post insertion,
                        echo  $notification_id->get_error_message() ;
                    }
                
                } else {
                    update_post_meta( $notification_exists, 'wnbell_item_name_' . $i, $options['wnbell_default_value'][$i] );
                }
            
            }
        }
        $current_lg = false;
        $post_permalink = apply_filters( 'wnbell_cpt_link', get_post_permalink( $post_id ), $post_id );
        // $post_permalink = get_post_permalink($post_id);
        
        if ( !$notification_exists ) {
            update_post_meta( $notification_id, 'wnbell_link', $post_permalink );
        } else {
            update_post_meta( $notification_exists, 'wnbell_link', $post_permalink );
        }
        
        //featured image
        if ( $post->post_status == 'publish' ) {
            
            if ( !$notification_exists ) {
                wp_publish_post( $notification_id );
            } else {
                wp_publish_post( $notification_exists );
            }
        
        }
    }

}
