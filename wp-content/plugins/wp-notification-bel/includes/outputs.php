<?php

defined( 'ABSPATH' ) || exit;
add_action( 'wp_head', 'wnbell_declare_ajaxurl' );
function wnbell_declare_ajaxurl()
{
    ?> <script type="text/javascript">
     var ajax_url = '<?php 
    echo  admin_url( 'admin-ajax.php' ) ;
    ?>'; </script>
     <?php 
}

add_action( 'wp_enqueue_scripts', 'wnbell_load_jquery' );
function wnbell_load_jquery()
{
    wp_enqueue_script( 'jquery' );
}

function wnbell_output_menu_bell(
    $options,
    $data,
    $trigger_array,
    $current_user_id
)
{
    $limit = apply_filters( 'wnbell_notifications_display_count', 5 );
    $header = ( isset( $options['header'] ) ? $options['header'] : '' );
    $seen_comments = get_user_meta( $current_user_id, 'wnbell_seen_comments_ids', true );
    $seen_bbp = get_user_meta( $current_user_id, 'wnbell_seen_bbp_ids', true );
    $seen_woocommerce = get_user_meta( $current_user_id, 'wnbell_seen_woocommerce_ids', true );
    $seen_notifications = apply_filters( 'wnbell_seen_data', array(), $current_user_id );
    // if ($notification_query->have_posts()) {
    
    if ( count( $data ) > 0 ) {
        $output .= '<div class="wnbell_header" id="wnbell_header">';
        $output .= stripslashes( base64_decode( $header ) );
        // $output .= '<a href="javascript:void(0)" class="wnbell-closebtn-menu">&times;</a>';
        $output .= '<span class="wnbell-closebtn-menu">&times;</span>';
        $output .= '</div>';
        $width = ( isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : 50 );
        $height = ( isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : 50 );
        $img_position = ( isset( $options['img_position'] ) && $options['img_position'] == true ? 'alignleft' : 'alignright' );
        $seen_posts = get_user_meta( $current_user_id, 'wnbell_seen_notification_post', true );
        // Cycle through all items retrieved
        //while ($notification_query->have_posts()) {
        foreach ( $data as $single_notification_id ) {
            $single_notification_id = $single_notification_id['ID'];
            //$notification_query->the_post();
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            //$post_date = get_the_date() . ' ' . get_the_time();
            //$post_date = strtotime(get_the_date('Y-m-d', $single_notification_id) . ' ' . get_the_time('', $single_notification_id));
            $post_date = get_post_timestamp( $single_notification_id, 'date' );
            $post_link = '';
            $trigger_text = '';
            foreach ( $trigger_array as $trigger_notification ) {
                if ( !is_array( $trigger_notification ) ) {
                    break;
                }
                $trigger_date = ( array_key_exists( 'time', $trigger_notification ) ? $trigger_notification['time'] : strtotime( str_replace( "at", "", $trigger_notification['date'] ) ) );
                
                if ( $post_date < $trigger_date ) {
                    $trigger_output = wnbell_trigger_output_menu(
                        $trigger_notification,
                        $options,
                        $seen_comments,
                        $seen_bbp,
                        $seen_woocommerce,
                        $post_link
                    );
                    $output .= apply_filters(
                        'wnbell_user_notifications_output',
                        $trigger_output,
                        $trigger_notification,
                        $options,
                        $seen_notifications
                    );
                    if ( is_array( $trigger_array ) ) {
                        array_shift( $trigger_array );
                    }
                    $limit--;
                    if ( $limit == 0 ) {
                        break 2;
                    }
                }
            
            }
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            // if ($seen_posts && in_array(get_the_ID(), $seen_posts)) {
            
            if ( $seen_posts && in_array( $single_notification_id, $seen_posts ) ) {
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
            
            $output .= '<div class="' . $item_class . '" id="' . $item_id . '">';
            // $post_id = get_the_ID();
            $post_id = $single_notification_id;
            $thumbnail = get_the_post_thumbnail( $post_id, array( $width, $height ), array(
                'class' => $img_position,
            ) );
            
            if ( $thumbnail == "" ) {
                $thumbnail_id = get_post_meta( $post_id, "post_id", true );
                $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                    'class' => $img_position,
                ) );
            }
            
            $wnbell_link = esc_html( get_post_meta( $post_id, 'wnbell_link', true ) );
            $link = ( isset( $wnbell_link ) ? $wnbell_link : '' );
            $a_style = ( $thumbnail == "" ? "" : "min-height:" . $height . "px;" );
            $link_class = ( empty($link) ? 'wnbell_disabled_link' : '' );
            $output .= '<a href="' . $link;
            $output .= '" onclick="wnbell_ajax_seen(' . $post_id;
            $output .= ');"';
            $output .= ' style="' . $a_style . '" class="' . $link_class . '">';
            // $output .= '<div class="wnbell_image">' . get_the_post_thumbnail(get_the_ID(), array(50, 50), array('class' => 'alignleft'));
            $output .= $thumbnail;
            //$output .= '</div>';
            $i = 0;
            $output .= '<div>';
            foreach ( $options['wnbell_name'] as $name ) {
                $field_class = ( isset( $options['wnbell_class_attribute'][$i] ) && $options['wnbell_class_attribute'][$i] != '' ? esc_html( $options['wnbell_class_attribute'][$i] ) : '' );
                $field_id = ( isset( $options['wnbell_id_attribute'][$i] ) && $options['wnbell_id_attribute'][$i] != '' ? esc_html( $options['wnbell_id_attribute'][$i] ) : '' );
                // $field_class = $options['wnbell_class_attribute'][$i] ?? '';
                // $field_id = $options['wnbell_id_attribute'][$i] ?? '';
                $output .= '<div class="' . $field_class . '" id="' . $field_id . '">';
                $item_content = esc_html( get_post_meta( $post_id, 'wnbell_item_name_' . $i, true ) );
                $item_content_replaced = wnbell_replace_placeholders( $item_content, $post_id );
                $output .= $item_content_replaced;
                $output .= '</div>';
                $i++;
            }
            $output .= apply_filters( 'wnbell_inner_item_append', '', $post_id );
            $output .= '</div>';
            $output .= '</a>';
            $output .= apply_filters( 'wnbell_item_append', '', $post_id );
            $output .= '</div>';
            $limit--;
            if ( $limit == 0 ) {
                break;
            }
        }
        
        if ( count( $trigger_array ) > 0 ) {
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            $post_link = '';
            $trigger_text = '';
            foreach ( $trigger_array as $trigger_notification ) {
                if ( !is_array( $trigger_notification ) ) {
                    break;
                }
                $trigger_output = wnbell_trigger_output_menu(
                    $trigger_notification,
                    $options,
                    $seen_comments,
                    $seen_bbp,
                    $seen_woocommerce,
                    $post_link
                );
                $output .= apply_filters(
                    'wnbell_user_notifications_output',
                    $trigger_output,
                    $trigger_notification,
                    $options,
                    $seen_notifications
                );
                if ( is_array( $trigger_array ) ) {
                    array_shift( $trigger_array );
                }
                $limit--;
                if ( $limit == 0 ) {
                    break;
                }
            }
        }
        
        // $output .= '</div>';
        // Reset post data query
        // wp_reset_postdata();
    } else {
        $no_notifs = ( isset( $options['no_notifs'] ) && $options['no_notifs'] != '' ? stripslashes( base64_decode( $options['no_notifs'] ) ) : '<div class="wnbell_empty_box" id="wnbell_empty_box_id">No new notifications<div>' );
        
        if ( sizeof( $trigger_array ) === 0 ) {
            $output .= '<div class="wnbell_header" id="wnbell_header">';
            $output .= '<div class="wnbell_header_empty" id="wnbell_header_empty">';
            $output .= stripslashes( base64_decode( $header ) );
            $output .= '</div>';
            $output .= '<span class="wnbell-closebtn-menu">&times;</span>';
            $output .= '</div>';
            $output .= $no_notifs;
        } else {
            $output .= '<div class="wnbell_header" id="wnbell_header">';
            $output .= stripslashes( base64_decode( $header ) );
            $output .= '<span class="wnbell-closebtn-menu">&times;</span>';
            $output .= '</div>';
        }
        
        $post_link = '';
        $trigger_text = '';
        foreach ( $trigger_array as $trigger_notification ) {
            if ( !is_array( $trigger_notification ) ) {
                break;
            }
            $trigger_output = wnbell_trigger_output_menu(
                $trigger_notification,
                $options,
                $seen_comments,
                $seen_bbp,
                $seen_woocommerce,
                $post_link
            );
            $output .= apply_filters(
                'wnbell_user_notifications_output',
                $trigger_output,
                $trigger_notification,
                $options,
                $seen_notifications
            );
            if ( is_array( $trigger_array ) ) {
                array_shift( $trigger_array );
            }
            $limit--;
            if ( $limit == 0 ) {
                break;
            }
        }
    }
    
    return $output;
}

function wnbell_output_menu_bell_lo( $options, $data, $trigger_array = array() )
{
    $header = ( isset( $options['header'] ) ? $options['header'] : '' );
    
    if ( count( $data ) > 0 ) {
        $output .= '<div class="wnbell_header" id="wnbell_header">';
        $output .= stripslashes( base64_decode( $header ) );
        $output .= '<span class="wnbell-closebtn-menu">&times;</span>';
        $output .= '</div>';
        $width = ( isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : 50 );
        $height = ( isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : 50 );
        $img_position = ( isset( $options['img_position'] ) && $options['img_position'] == true ? 'alignleft' : 'alignright' );
        //$seen_posts = get_user_meta($current_user_id, 'wnbell_seen_notification_post', true);
        $seen_posts = false;
        // Cycle through all items retrieved
        foreach ( $data as $single_notification_id ) {
            $single_notification_id = $single_notification_id['ID'];
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            //$post_date = strtotime(get_the_date('Y-m-d', $single_notification_id) . ' ' . get_the_time('', $single_notification_id));
            $post_date = get_post_timestamp( $single_notification_id, 'date' );
            $post_link = '';
            $trigger_text = '';
            if ( isset( $options['item_lo_class_attribute'] ) && $options['item_lo_class_attribute'] != '' ) {
                $item_class = $options['item_lo_class_attribute'];
            }
            if ( isset( $options['item_lo_id_attribute'] ) && $options['item_lo_id_attribute'] != '' ) {
                $item_id = $options['item_lo_id_attribute'];
            }
            $output .= '<div class="' . $item_class . '" id="' . $item_id . '">';
            $post_id = $single_notification_id;
            $thumbnail = "";
            $post_thumbnail_id = get_post_thumbnail_id( $post_id );
            // to prevent default images using filters in get_the_post_thumbnail
            
            if ( $post_thumbnail_id ) {
                $thumbnail = get_the_post_thumbnail( $post_id, array( $width, $height ), array(
                    'class' => $img_position,
                ) );
            } else {
                $thumbnail_id = get_post_meta( $post_id, "post_id", true );
                
                if ( $thumbnail_id != "" ) {
                    $post_thumbnail_id = get_post_thumbnail_id( $thumbnail_id );
                    if ( $post_thumbnail_id ) {
                        $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                            'class' => $img_position,
                        ) );
                    }
                }
            
            }
            
            
            if ( $thumbnail == "" ) {
                $thumbnail_id = get_post_meta( $post_id, "post_id", true );
                $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                    'class' => $img_position,
                ) );
            }
            
            $wnbell_link = esc_html( get_post_meta( $post_id, 'wnbell_link', true ) );
            $link = ( isset( $wnbell_link ) ? $wnbell_link : '' );
            $link_class = ( empty($link) ? 'wnbell_disabled_link' : '' );
            $a_style = ( $thumbnail == "" ? "" : "min-height:" . $height . "px;" );
            $output .= '<a href="' . $link;
            // $output .= '" onclick="wnbell_ajax_seen(' . $post_id;
            // $output .= ');"';
            $output .= '" style="' . $a_style . '" class="' . $link_class . '">';
            $output .= $thumbnail;
            $i = 0;
            foreach ( $options['wnbell_name'] as $name ) {
                $field_class = ( isset( $options['wnbell_class_attribute'][$i] ) && $options['wnbell_class_attribute'][$i] != '' ? esc_html( $options['wnbell_class_attribute'][$i] ) : '' );
                $field_id = ( isset( $options['wnbell_id_attribute'][$i] ) && $options['wnbell_id_attribute'][$i] != '' ? esc_html( $options['wnbell_id_attribute'][$i] ) : '' );
                // $field_class = $options['wnbell_class_attribute'][$i] ?? '';
                // $field_id = $options['wnbell_id_attribute'][$i] ?? '';
                $output .= '<div class="' . $field_class . '" id="' . $field_id . '">';
                $item_content = esc_html( get_post_meta( $post_id, 'wnbell_item_name_' . $i, true ) );
                $item_content_replaced = wnbell_replace_placeholders( $item_content, $post_id );
                $output .= $item_content_replaced;
                $output .= '</div>';
                $i++;
            }
            $output .= '</a></div>';
        }
        // $output .= '</div>';
        // Reset post data query
    } else {
        
        if ( count( $trigger_array ) > 0 ) {
        } else {
            $output .= '<div class="wnbell_header" id="wnbell_header">';
            $output .= '<div class="wnbell_header_empty" id="wnbell_header_empty">';
            $output .= stripslashes( base64_decode( $header ) );
            $output .= '</div>';
            $output .= '<span class="wnbell-closebtn-menu">&times;</span>';
            $output .= '</div>';
            $no_notifs = ( isset( $options['no_notifs'] ) && $options['no_notifs'] != '' ? stripslashes( base64_decode( $options['no_notifs'] ) ) : '<div class="wnbell_empty_box" id="wnbell_empty_box_id">No new notifications<div>' );
            $output .= $no_notifs;
        }
    
    }
    
    return $output;
}

function wnbell_output_list_widget(
    $options,
    $data,
    $trigger_array,
    $current_user_id
)
{
    $seen_comments = get_user_meta( $current_user_id, 'wnbell_seen_comments_ids', true );
    $seen_bbp = get_user_meta( $current_user_id, 'wnbell_seen_bbp_ids', true );
    $seen_woocommerce = get_user_meta( $current_user_id, 'wnbell_seen_woocommerce_ids', true );
    $seen_notifications = apply_filters( 'wnbell_seen_data', array(), $current_user_id );
    // if ($notification_query->have_posts()) {
    
    if ( count( $data ) > 0 ) {
        $width = ( isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : 50 );
        $height = ( isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : 50 );
        $img_position = ( isset( $options['img_position'] ) && $options['img_position'] == true ? 'alignleft' : 'alignright' );
        $seen_posts = get_user_meta( $current_user_id, 'wnbell_seen_notification_post', true );
        // Cycle through all items retrieved
        //while ($notification_query->have_posts()) {
        foreach ( $data as $single_notification_id ) {
            $single_notification_id = $single_notification_id['ID'];
            //$notification_query->the_post();
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            //$post_date = get_the_date() . ' ' . get_the_time();
            //$post_date = strtotime(get_the_date('Y-m-d', $single_notification_id) . ' ' . get_the_time('', $single_notification_id));
            $post_date = get_post_timestamp( $single_notification_id, 'date' );
            $post_link = '';
            $trigger_text = '';
            foreach ( $trigger_array as $trigger_notification ) {
                if ( !is_array( $trigger_notification ) ) {
                    break;
                }
                $trigger_date = ( array_key_exists( 'time', $trigger_notification ) ? $trigger_notification['time'] : strtotime( str_replace( "at", "", $trigger_notification['date'] ) ) );
                
                if ( $post_date < $trigger_date ) {
                    $trigger_output = wnbell_trigger_output_menu(
                        $trigger_notification,
                        $options,
                        $seen_comments,
                        $seen_bbp,
                        $seen_woocommerce,
                        $post_link
                    );
                    $output .= apply_filters(
                        'wnbell_user_notifications_output',
                        $trigger_output,
                        $trigger_notification,
                        $options,
                        $seen_notifications
                    );
                    if ( is_array( $trigger_array ) ) {
                        array_shift( $trigger_array );
                    }
                }
            
            }
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            // if ($seen_posts && in_array(get_the_ID(), $seen_posts)) {
            
            if ( $seen_posts && in_array( $single_notification_id, $seen_posts ) ) {
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
            
            $output .= '<div class="' . $item_class . ' wnbell_item_widget" id="' . $item_id . '">';
            // $post_id = get_the_ID();
            $post_id = $single_notification_id;
            $thumbnail = get_the_post_thumbnail( $post_id, array( $width, $height ), array(
                'class' => $img_position,
            ) );
            
            if ( $thumbnail == "" ) {
                $thumbnail_id = get_post_meta( $post_id, "post_id", true );
                $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                    'class' => $img_position,
                ) );
            }
            
            $wnbell_link = esc_html( get_post_meta( $post_id, 'wnbell_link', true ) );
            $link = ( isset( $wnbell_link ) ? $wnbell_link : '' );
            $a_style = ( $thumbnail == "" ? "" : "min-height:" . $height . "px;" );
            $output .= '<a href="' . $link;
            $output .= '" onclick="wnbell_ajax_seen(' . $post_id;
            $output .= ');"';
            $output .= ' style="' . $a_style . '">';
            // $output .= '<div class="wnbell_image">' . get_the_post_thumbnail(get_the_ID(), array(50, 50), array('class' => 'alignleft'));
            $output .= $thumbnail;
            //$output .= '</div>';
            $i = 0;
            $output .= '<div>';
            foreach ( $options['wnbell_name'] as $name ) {
                $field_class = ( isset( $options['wnbell_class_attribute'][$i] ) && $options['wnbell_class_attribute'][$i] != '' ? esc_html( $options['wnbell_class_attribute'][$i] ) : '' );
                $field_id = ( isset( $options['wnbell_id_attribute'][$i] ) && $options['wnbell_id_attribute'][$i] != '' ? esc_html( $options['wnbell_id_attribute'][$i] ) : '' );
                // $field_class = $options['wnbell_class_attribute'][$i] ?? '';
                // $field_id = $options['wnbell_id_attribute'][$i] ?? '';
                $output .= '<div class="' . $field_class . '" id="' . $field_id . '">';
                $item_content = esc_html( get_post_meta( $post_id, 'wnbell_item_name_' . $i, true ) );
                $item_content_replaced = wnbell_replace_placeholders( $item_content, $post_id );
                $output .= $item_content_replaced;
                $output .= '</div>';
                $i++;
            }
            $output .= apply_filters( 'wnbell_inner_item_append', '', $post_id );
            $output .= '</div>';
            $output .= '</a>';
            $output .= apply_filters( 'wnbell_item_append', '', $post_id );
            $output .= '</div>';
        }
        
        if ( count( $trigger_array ) > 0 ) {
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            $post_link = '';
            $trigger_text = '';
            foreach ( $trigger_array as $trigger_notification ) {
                if ( !is_array( $trigger_notification ) ) {
                    break;
                }
                $trigger_output = wnbell_trigger_output_menu(
                    $trigger_notification,
                    $options,
                    $seen_comments,
                    $seen_bbp,
                    $seen_woocommerce,
                    $post_link
                );
                $output .= apply_filters(
                    'wnbell_user_notifications_output',
                    $trigger_output,
                    $trigger_notification,
                    $options,
                    $seen_notifications
                );
                if ( is_array( $trigger_array ) ) {
                    array_shift( $trigger_array );
                }
            }
        }
        
        // $output .= '</div>';
        // Reset post data query
        // wp_reset_postdata();
    } else {
        $post_link = '';
        $trigger_text = '';
        foreach ( $trigger_array as $trigger_notification ) {
            if ( !is_array( $trigger_notification ) ) {
                break;
            }
            $trigger_output = wnbell_trigger_output_menu(
                $trigger_notification,
                $options,
                $seen_comments,
                $seen_bbp,
                $seen_woocommerce,
                $post_link
            );
            $output .= apply_filters(
                'wnbell_user_notifications_output',
                $trigger_output,
                $trigger_notification,
                $options,
                $seen_notifications
            );
            if ( is_array( $trigger_array ) ) {
                array_shift( $trigger_array );
            }
        }
    }
    
    return $output;
}

function wnbell_output_recent_list_widget_lo( $options, $data )
{
    $width = ( isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : 50 );
    $height = ( isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : 50 );
    $img_position = ( isset( $options['img_position'] ) && $options['img_position'] == true ? 'alignleft' : 'alignright' );
    //$seen_posts = get_user_meta($current_user_id, 'wnbell_seen_notification_post', true);
    // Cycle through all items retrieved
    foreach ( $data as $single_notification_id ) {
        $single_notification_id = $single_notification_id['ID'];
        $item_id = '';
        $item_class = 'wnbell_notification_item_menu';
        //$post_date = get_the_date('', $single_notification_id) . ' ' . get_the_time('', $single_notification_id);
        $post_link = '';
        if ( isset( $options['item_lo_class_attribute'] ) && $options['item_lo_class_attribute'] != '' ) {
            $item_class = $options['item_lo_class_attribute'];
        }
        if ( isset( $options['item_lo_id_attribute'] ) && $options['item_lo_id_attribute'] != '' ) {
            $item_id = $options['item_lo_id_attribute'];
        }
        $output .= '<div class="' . $item_class . ' wnbell_item_widget" id="' . $item_id . '">';
        $post_id = $single_notification_id;
        $thumbnail = get_the_post_thumbnail( $post_id, array( $width, $height ), array(
            'class' => $img_position,
        ) );
        
        if ( $thumbnail == "" ) {
            $thumbnail_id = get_post_meta( $post_id, "post_id", true );
            $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                'class' => $img_position,
            ) );
        }
        
        $wnbell_link = esc_html( get_post_meta( $post_id, 'wnbell_link', true ) );
        $link = ( isset( $wnbell_link ) ? $wnbell_link : '' );
        $a_style = ( $thumbnail == "" ? "" : "min-height:" . $height . "px;" );
        $output .= '<a href="' . $link;
        // $output .= '" onclick="wnbell_ajax_seen(' . $post_id;
        // $output .= ');"';
        $output .= '" style="' . $a_style . '">';
        $output .= $thumbnail;
        $i = 0;
        foreach ( $options['wnbell_name'] as $name ) {
            $field_class = ( isset( $options['wnbell_class_attribute'][$i] ) && $options['wnbell_class_attribute'][$i] != '' ? esc_html( $options['wnbell_class_attribute'][$i] ) : '' );
            $field_id = ( isset( $options['wnbell_id_attribute'][$i] ) && $options['wnbell_id_attribute'][$i] != '' ? esc_html( $options['wnbell_id_attribute'][$i] ) : '' );
            // $field_class = $options['wnbell_class_attribute'][$i] ?? '';
            // $field_id = $options['wnbell_id_attribute'][$i] ?? '';
            $output .= '<div class="' . $field_class . '" id="' . $field_id . '">';
            $item_content = esc_html( get_post_meta( $post_id, 'wnbell_item_name_' . $i, true ) );
            $item_content_replaced = wnbell_replace_placeholders( $item_content, $post_id );
            $output .= $item_content_replaced;
            $output .= '</div>';
            $i++;
        }
        $output .= '</a></div>';
    }
    return $output;
}

function wnbell_output_list_widget_lo( $options, $data )
{
    
    if ( count( $data ) > 0 ) {
        $width = ( isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : 50 );
        $height = ( isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : 50 );
        $img_position = ( isset( $options['img_position'] ) && $options['img_position'] == true ? 'alignleft' : 'alignright' );
        //$seen_posts = get_user_meta($current_user_id, 'wnbell_seen_notification_post', true);
        // Cycle through all items retrieved
        foreach ( $data as $single_notification_id ) {
            $single_notification_id = $single_notification_id['ID'];
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            //$post_date = get_the_date('Y-m-d', $single_notification_id) . ' ' . get_the_time('', $single_notification_id);
            $post_link = '';
            if ( isset( $options['item_lo_class_attribute'] ) && $options['item_lo_class_attribute'] != '' ) {
                $item_class = $options['item_lo_class_attribute'];
            }
            if ( isset( $options['item_lo_id_attribute'] ) && $options['item_lo_id_attribute'] != '' ) {
                $item_id = $options['item_lo_id_attribute'];
            }
            $output .= '<div class="' . $item_class . ' wnbell_item_widget" id="' . $item_id . '">';
            $post_id = $single_notification_id;
            $thumbnail = get_the_post_thumbnail( $post_id, array( $width, $height ), array(
                'class' => $img_position,
            ) );
            
            if ( $thumbnail == "" ) {
                $thumbnail_id = get_post_meta( $post_id, "post_id", true );
                $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                    'class' => $img_position,
                ) );
            }
            
            $wnbell_link = esc_html( get_post_meta( $post_id, 'wnbell_link', true ) );
            $link = ( isset( $wnbell_link ) ? $wnbell_link : '' );
            $a_style = ( $thumbnail == "" ? "" : "min-height:" . $height . "px;" );
            $output .= '<a href="' . $link;
            // $output .= '" onclick="wnbell_ajax_seen(' . $post_id;
            // $output .= ');"';
            $output .= '" style="' . $a_style . '">';
            $output .= $thumbnail;
            $i = 0;
            foreach ( $options['wnbell_name'] as $name ) {
                $field_class = ( isset( $options['wnbell_class_attribute'][$i] ) && $options['wnbell_class_attribute'][$i] != '' ? esc_html( $options['wnbell_class_attribute'][$i] ) : '' );
                $field_id = ( isset( $options['wnbell_id_attribute'][$i] ) && $options['wnbell_id_attribute'][$i] != '' ? esc_html( $options['wnbell_id_attribute'][$i] ) : '' );
                // $field_class = $options['wnbell_class_attribute'][$i] ?? '';
                // $field_id = $options['wnbell_id_attribute'][$i] ?? '';
                $output .= '<div class="' . $field_class . '" id="' . $field_id . '">';
                $item_content = esc_html( get_post_meta( $post_id, 'wnbell_item_name_' . $i, true ) );
                $item_content_replaced = wnbell_replace_placeholders( $item_content, $post_id );
                $output .= $item_content_replaced;
                $output .= '</div>';
                $i++;
            }
            $output .= '</a></div>';
        }
        // $output .= '</div>';
        // Reset post data query
    } else {
        //}
        // $output .= '<div>No new notifications</div>';
        $post_link = '';
    }
    
    return $output;
}

function wnbell_output_recent_list_widget(
    $options,
    $data,
    $trigger_array,
    $current_user_id
)
{
    $seen_comments = get_user_meta( $current_user_id, 'wnbell_seen_comments_ids', true );
    $seen_bbp = get_user_meta( $current_user_id, 'wnbell_seen_bbp_ids', true );
    $seen_woocommerce = get_user_meta( $current_user_id, 'wnbell_seen_woocommerce_ids', true );
    $seen_notifications = apply_filters( 'wnbell_seen_data', array(), $current_user_id );
    // if ($notification_query->have_posts()) {
    
    if ( count( $data ) > 0 ) {
        $width = ( isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : 50 );
        $height = ( isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : 50 );
        $img_position = ( isset( $options['img_position'] ) && $options['img_position'] == true ? 'alignleft' : 'alignright' );
        $seen_posts = get_user_meta( $current_user_id, 'wnbell_seen_notification_post', true );
        // Cycle through all items retrieved
        //while ($notification_query->have_posts()) {
        foreach ( $data as $single_notification_id ) {
            $single_notification_id = $single_notification_id['ID'];
            //$notification_query->the_post();
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            //$post_date = get_the_date() . ' ' . get_the_time();
            //$post_date = strtotime(get_the_date('Y-m-d', $single_notification_id) . ' ' . get_the_time('', $single_notification_id));
            $post_date = get_post_timestamp( $single_notification_id, 'date' );
            $post_link = '';
            $trigger_text = '';
            foreach ( $trigger_array as $trigger_notification ) {
                if ( !is_array( $trigger_notification ) ) {
                    break;
                }
                $trigger_date = ( array_key_exists( 'time', $trigger_notification ) ? $trigger_notification['time'] : strtotime( str_replace( "at", "", $trigger_notification['date'] ) ) );
                
                if ( $post_date < $trigger_date ) {
                    $trigger_output = wnbell_trigger_output_menu(
                        $trigger_notification,
                        $options,
                        $seen_comments,
                        $seen_bbp,
                        $seen_woocommerce,
                        $post_link
                    );
                    $output .= apply_filters(
                        'wnbell_user_notifications_output',
                        $trigger_output,
                        $trigger_notification,
                        $options,
                        $seen_notifications
                    );
                    if ( is_array( $trigger_array ) ) {
                        array_shift( $trigger_array );
                    }
                }
            
            }
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            // if ($seen_posts && in_array(get_the_ID(), $seen_posts)) {
            
            if ( $seen_posts && in_array( $single_notification_id, $seen_posts ) ) {
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
            
            $output .= '<div class="' . $item_class . ' wnbell_item_widget" id="' . $item_id . '">';
            // $post_id = get_the_ID();
            $post_id = $single_notification_id;
            $thumbnail = get_the_post_thumbnail( $post_id, array( $width, $height ), array(
                'class' => $img_position,
            ) );
            
            if ( $thumbnail == "" ) {
                $thumbnail_id = get_post_meta( $post_id, "post_id", true );
                $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                    'class' => $img_position,
                ) );
            }
            
            $wnbell_link = esc_html( get_post_meta( $post_id, 'wnbell_link', true ) );
            $link = ( isset( $wnbell_link ) ? $wnbell_link : '' );
            $a_style = ( $thumbnail == "" ? "" : "min-height:" . $height . "px;" );
            $output .= '<a href="' . $link;
            $output .= '" onclick="wnbell_ajax_seen(' . $post_id;
            $output .= ');"';
            $output .= ' style="' . $a_style . '">';
            // $output .= '<div class="wnbell_image">' . get_the_post_thumbnail(get_the_ID(), array(50, 50), array('class' => 'alignleft'));
            $output .= $thumbnail;
            //$output .= '</div>';
            $i = 0;
            $output .= '<div>';
            foreach ( $options['wnbell_name'] as $name ) {
                $field_class = ( isset( $options['wnbell_class_attribute'][$i] ) && $options['wnbell_class_attribute'][$i] != '' ? esc_html( $options['wnbell_class_attribute'][$i] ) : '' );
                $field_id = ( isset( $options['wnbell_id_attribute'][$i] ) && $options['wnbell_id_attribute'][$i] != '' ? esc_html( $options['wnbell_id_attribute'][$i] ) : '' );
                // $field_class = $options['wnbell_class_attribute'][$i] ?? '';
                // $field_id = $options['wnbell_id_attribute'][$i] ?? '';
                $output .= '<div class="' . $field_class . '" id="' . $field_id . '">';
                $item_content = esc_html( get_post_meta( $post_id, 'wnbell_item_name_' . $i, true ) );
                $item_content_replaced = wnbell_replace_placeholders( $item_content, $post_id );
                $output .= $item_content_replaced;
                $output .= '</div>';
                $i++;
            }
            $output .= apply_filters( 'wnbell_inner_item_append', '', $post_id );
            $output .= '</div>';
            $output .= '</a>';
            $output .= apply_filters( 'wnbell_item_append', '', $post_id );
            $output .= '</div>';
        }
        
        if ( count( $trigger_array ) > 0 ) {
            $item_id = '';
            $item_class = 'wnbell_notification_item_menu';
            $post_link = '';
            $trigger_text = '';
            foreach ( $trigger_array as $trigger_notification ) {
                if ( !is_array( $trigger_notification ) ) {
                    break;
                }
                $trigger_output = wnbell_trigger_output_menu(
                    $trigger_notification,
                    $options,
                    $seen_comments,
                    $seen_bbp,
                    $seen_woocommerce,
                    $post_link
                );
                $output .= apply_filters(
                    'wnbell_user_notifications_output',
                    $trigger_output,
                    $trigger_notification,
                    $options,
                    $seen_notifications
                );
                if ( is_array( $trigger_array ) ) {
                    array_shift( $trigger_array );
                }
            }
        }
        
        // $output .= '</div>';
        // Reset post data query
        // wp_reset_postdata();
    } else {
        $post_link = '';
        $trigger_text = '';
        foreach ( $trigger_array as $trigger_notification ) {
            if ( !is_array( $trigger_notification ) ) {
                break;
            }
            $trigger_output = wnbell_trigger_output_menu(
                $trigger_notification,
                $options,
                $seen_comments,
                $seen_bbp,
                $seen_woocommerce,
                $post_link
            );
            $output .= apply_filters(
                'wnbell_user_notifications_output',
                $trigger_output,
                $trigger_notification,
                $options,
                $seen_notifications
            );
            if ( is_array( $trigger_array ) ) {
                array_shift( $trigger_array );
            }
        }
    }
    
    return $output;
}

function wnbell_trigger_output_menu(
    $trigger_notification,
    $options,
    $seen_comments,
    $seen_bbp,
    $seen_woocommerce,
    $post_link
)
{
    $trigger_id = '';
    $trigger_type = '';
    $item_id = '';
    $item_class = 'wnbell_notification_item_menu wnbell_user_item';
    $trigger_text = '';
    $trigger_output = '';
    
    if ( array_key_exists( 'comment_id', $trigger_notification ) || array_key_exists( 'count', $trigger_notification ) ) {
        
        if ( array_key_exists( 'comment_id', $trigger_notification ) ) {
            $commenter = ucfirst( $trigger_notification['commenter'] );
            
            if ( array_key_exists( 'type', $trigger_notification ) && $trigger_notification['type'] === 'cfa' ) {
                $trigger_text = sprintf( __( '%s commented on your post.', 'wp-notification-bell' ), $commenter );
            } else {
                $trigger_text = sprintf( __( '%s replied to your comment.', 'wp-notification-bell' ), $commenter );
            }
        
        } else {
            $trigger_text = sprintf( __( 'You have %s replies on your comment.', 'wp-notification-bell' ), $trigger_notification['count'] );
        }
        
        //                         //You have %s replies on this post:...
        // $post_link = get_permalink($trigger_notification['post']);
        $post_link = get_comment_link( $trigger_notification['comment_id'] );
        $trigger_id = $trigger_notification['comment_id'];
        $trigger_type = 'comment';
    } elseif ( array_key_exists( 'reply_id', $trigger_notification ) ) {
        $author = ucfirst( $trigger_notification['reply_author_name'] );
        $trigger_text = sprintf( _x( '%1$s wrote a new comment in "%2$s".', '%1$s = Name of user who create comment
    %2$s = Topic title in which user leave a comment', 'wp-notification-bell' ), $author, $trigger_notification['topic_title'] );
        $post_link = $trigger_notification['reply_url'];
        $trigger_id = $trigger_notification['reply_id'];
        $trigger_type = 'bbp';
    } elseif ( array_key_exists( 'order_id', $trigger_notification ) ) {
        $trigger_text = sprintf( _x( 'Your order is %1$s.', '%1$s = Order status', 'wp-notification-bell' ), strtolower( wc_get_order_status_name( $trigger_notification['status'] ) ) );
        $trigger_text = apply_filters( 'wnbell_woo_orders_text', $trigger_text, $trigger_notification );
        //$order = new WC_Order($trigger_notification['order_id']);
        $order = wc_get_order( $trigger_notification['order_id'] );
        if ( !$order ) {
            return '';
        }
        $post_link = esc_url( $order->get_view_order_url() );
        $trigger_datetime = ( array_key_exists( 'time', $trigger_notification ) ? $trigger_notification['time'] : $trigger_notification['date'] );
        $trigger_id = $trigger_notification['order_id'] . '_' . $trigger_datetime;
        $trigger_type = 'woocommerce';
        $i = 0;
        $trigger_default = '';
    }
    
    
    if ( $seen_comments && in_array( $trigger_id, $seen_comments ) || $seen_bbp && in_array( $trigger_id, $seen_bbp ) || $seen_woocommerce && in_array( $trigger_id, $seen_woocommerce ) ) {
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
    
    
    if ( strlen( $trigger_text ) > 0 ) {
        $trigger_output .= '<div class="' . $item_class . '" id="' . $item_id . '">';
        $trigger_id = ( $trigger_type == 'woocommerce' ? '\'' . $trigger_id . '\'' : $trigger_id );
        $trigger_output .= '<a href="' . $post_link;
        $trigger_output .= '" onclick="wnbell_ajax_seen(' . $trigger_id;
        $trigger_output .= ', \'' . $trigger_type;
        $trigger_output .= '\');"';
        $trigger_output .= ' class= "wnbell_user_item_link">';
        $trigger_output .= '<div>';
        $trigger_output .= ( strlen( $trigger_default ) > 0 ? $trigger_default : $trigger_text );
        $trigger_output .= '</div>';
        $trigger_output .= '</a>';
        $trigger_output .= apply_filters(
            'wnbell_user_item_append',
            '',
            $trigger_id,
            $trigger_type
        );
        $trigger_output .= '</div>';
    }
    
    return $trigger_output;
}

function wnbell_trigger_output(
    $trigger_notification,
    $options,
    $seen_comments,
    $seen_bbp,
    $seen_woocommerce,
    $post_link
)
{
    $trigger_id = '';
    $trigger_type = '';
    $item_id = '';
    $item_class = 'wnbell_notification_item wnbell_user_item';
    $trigger_text = '';
    $trigger_output = '';
    
    if ( array_key_exists( 'comment_id', $trigger_notification ) || array_key_exists( 'count', $trigger_notification ) ) {
        
        if ( array_key_exists( 'comment_id', $trigger_notification ) ) {
            $commenter = ucfirst( $trigger_notification['commenter'] );
            
            if ( array_key_exists( 'type', $trigger_notification ) && $trigger_notification['type'] === 'cfa' ) {
                $trigger_text = sprintf( __( '%s commented on your post.', 'wp-notification-bell' ), $commenter );
            } else {
                $trigger_text = sprintf( __( '%s replied to your comment.', 'wp-notification-bell' ), $commenter );
            }
        
        } else {
            $trigger_text = sprintf( __( 'You have %s replies on your comment.', 'wp-notification-bell' ), $trigger_notification['count'] );
        }
        
        //                         //You have %s replies on this post:...
        // $post_link = get_permalink($trigger_notification['post']);
        $post_link = get_comment_link( $trigger_notification['comment_id'] );
        $trigger_id = $trigger_notification['comment_id'];
        $trigger_type = 'comment';
    } elseif ( array_key_exists( 'reply_id', $trigger_notification ) ) {
        $author = ucfirst( $trigger_notification['reply_author_name'] );
        $trigger_text = sprintf( _x( '%1$s wrote a new comment in "%2$s".', '%1$s = Name of user who create comment
    %2$s = Topic title in which user leave a comment', 'wp-notification-bell' ), $author, $trigger_notification['topic_title'] );
        $post_link = $trigger_notification['reply_url'];
        $trigger_id = $trigger_notification['reply_id'];
        $trigger_type = 'bbp';
    } elseif ( array_key_exists( 'order_id', $trigger_notification ) && !array_key_exists( 'type', $trigger_notification ) ) {
        $trigger_text = sprintf( _x( 'Your order is %1$s.', '%1$s = Order status', 'wp-notification-bell' ), strtolower( wc_get_order_status_name( $trigger_notification['status'] ) ) );
        $trigger_text = apply_filters( 'wnbell_woo_orders_text', $trigger_text, $trigger_notification );
        //$order = new WC_Order($trigger_notification['order_id']);
        $order = wc_get_order( $trigger_notification['order_id'] );
        if ( !$order ) {
            return '';
        }
        $post_link = esc_url( $order->get_view_order_url() );
        $trigger_datetime = ( array_key_exists( 'time', $trigger_notification ) ? $trigger_notification['time'] : $trigger_notification['date'] );
        $trigger_id = $trigger_notification['order_id'] . '_' . $trigger_datetime;
        $trigger_type = 'woocommerce';
        $i = 0;
        $trigger_default = '';
    }
    
    
    if ( $seen_comments && in_array( $trigger_id, $seen_comments ) || $seen_bbp && in_array( $trigger_id, $seen_bbp ) || $seen_woocommerce && in_array( $trigger_id, $seen_woocommerce ) ) {
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
    
    
    if ( strlen( $trigger_text ) > 0 ) {
        $trigger_output .= '<div class="' . $item_class . '" id="' . $item_id . '">';
        $trigger_id = ( $trigger_type == 'woocommerce' ? '\'' . $trigger_id . '\'' : $trigger_id );
        $trigger_output .= '<a href="' . $post_link;
        $trigger_output .= '" onclick="wnbell_ajax_seen(' . $trigger_id;
        $trigger_output .= ', \'' . $trigger_type;
        $trigger_output .= '\');"';
        $trigger_output .= ' class= "wnbell_user_item_link">';
        $trigger_output .= '<div>';
        $trigger_output .= ( strlen( $trigger_default ) > 0 ? $trigger_default : $trigger_text );
        $trigger_output .= '</div>';
        $trigger_output .= '</a>';
        $trigger_output .= apply_filters(
            'wnbell_user_item_append',
            '',
            $trigger_id,
            $trigger_type
        );
        $trigger_output .= '</div>';
    }
    
    return $trigger_output;
}

function wnbell_output_comments_lo( $comment_id, $item_id = '', $item_class = 'wnbell_notification_item_menu' )
{
    $output = '';
    return $output;
}
