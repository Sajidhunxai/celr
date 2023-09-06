<?php

defined( 'ABSPATH' ) || exit;
//add_filter('the_title', 'do_shortcode'); // for adding in page title
add_shortcode( 'wp-notification-bell', 'wnbell_notification_display' );
add_action( 'wp_enqueue_scripts', 'wnbell_adding_scripts_shortcode' );
add_action( 'wp_ajax_wnbell_list_ajax', 'wnbell_list_ajax' );
function wnbell_adding_scripts_shortcode()
{
    $custom_js_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'settings_script.js' ) );
    wp_enqueue_script(
        'wnb_shortcode_script',
        plugin_dir_url( __FILE__ ) . 'settings_script.js',
        array( 'jquery' ),
        $custom_js_ver
    );
    $custom_css_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'stylesheet.css' ) );
    wp_enqueue_style(
        'wnb_shortcode_style',
        plugin_dir_url( __FILE__ ) . 'stylesheet.css',
        array(),
        $custom_css_ver
    );
}

function wnbell_notification_list( $view = '' )
{
    $options = get_option( 'wnbell_options' );
    $output = '';
    
    if ( $view === 'yes' ) {
        $limit = filter_var( apply_filters( 'wnbell_notifications_display_count', 5 ), FILTER_SANITIZE_NUMBER_INT );
        $user_unseen = 0;
        $current_user = wp_get_current_user();
        $roles = (array) $current_user->roles;
        array_push( $roles, "all" );
        $username = $current_user->user_login;
        $username_value = $username . '";';
        $username_array = array( $username, "0" );
        global  $wpdb ;
        $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\n       LEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\n       LEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n            WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            AND (prole.user_role  IN(" . wnbell_escape_array( $roles ) . ") OR prole.user_role IS NULL)\r\n             AND (LOCATE('{$username_value}',pname.usernames) > 0 OR pname.usernames IS NULL)";
        $query .= apply_filters( "wnbell_notification_conditions", '' );
        $query .= " GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT {$limit};";
        $query = apply_filters(
            "wnbell_select_notifications",
            $query,
            $roles,
            $username_value,
            $options
        );
        //$sql = $wpdb->prepare($query, $username);
        $data = $wpdb->get_results( $query, ARRAY_A );
        $current_user_id = get_current_user_id();
        $trigger_array = array();
        
        if ( $options['enable_new_comment'] ) {
            $comments = get_user_meta( $current_user_id, 'wnbell_unseen_comments', true );
            
            if ( is_array( $comments ) ) {
                $comments = array_slice(
                    $comments,
                    0,
                    $limit,
                    true
                );
                $trigger_array = $comments;
            }
        
        }
        
        
        if ( $options['enable_new_bbpress_reply'] ) {
            $bbpress_replies = get_user_meta( $current_user_id, 'wnbell_unseen_bbpress_replies', true );
            
            if ( is_array( $bbpress_replies ) ) {
                $bbpress_replies = array_slice(
                    $bbpress_replies,
                    0,
                    $limit,
                    true
                );
                $trigger_array = wnbell_trigger_sort( $trigger_array, $bbpress_replies );
            }
        
        }
        
        
        if ( $options['enable_new_woocommerce'] ) {
            $woocommerce_updates = get_user_meta( $current_user_id, 'wnbell_woocommerce_updates', true );
            
            if ( is_array( $woocommerce_updates ) ) {
                $woocommerce_updates = array_slice(
                    $woocommerce_updates,
                    0,
                    $limit,
                    true
                );
                $trigger_array = wnbell_trigger_sort( $trigger_array, $woocommerce_updates );
            }
        
        }
        
        if ( isset( $options['enable_bp'] ) && $options['enable_bp'] ) {
            $trigger_array = wnbell_bp_sorted( $current_user_id, $trigger_array );
        }
        $trigger_array = apply_filters( 'wnbell_user_notifications_array', $trigger_array );
        $header = ( isset( $options['header'] ) ? $options['header'] : '' );
        $seen_comments = get_user_meta( $current_user_id, 'wnbell_seen_comments_ids', true );
        $seen_bbp = get_user_meta( $current_user_id, 'wnbell_seen_bbp_ids', true );
        $seen_woocommerce = get_user_meta( $current_user_id, 'wnbell_seen_woocommerce_ids', true );
        $seen_notifications = apply_filters( 'wnbell_seen_data', array(), $current_user_id );
        
        if ( count( $data ) > 0 ) {
            $output .= '<div class="wnbell_header" id="wnbell_header">';
            $output .= stripslashes( base64_decode( $header ) );
            //$output .= '<a href="javascript:void(0)" class="wnbell-closebtn">&times;</a>';
            $output .= '<span class="wnbell-closebtn">&times;</span>';
            $output .= '</div>';
            $width = ( isset( $options['image_width'] ) && $options['image_width'] != '' ? $options['image_width'] : 50 );
            $height = ( isset( $options['image_height'] ) && $options['image_height'] != '' ? $options['image_height'] : 50 );
            $img_position = ( isset( $options['img_position'] ) && $options['img_position'] == true ? 'alignleft' : 'alignright' );
            $seen_posts = get_user_meta( $current_user_id, 'wnbell_seen_notification_post', true );
            // Cycle through all items retrieved
            foreach ( $data as $single_notification_id ) {
                $single_notification_id = $single_notification_id['ID'];
                $item_id = '';
                $item_class = 'wnbell_notification_item';
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
                        $trigger_id = '';
                        $trigger_type = '';
                        $item_id = '';
                        $item_class = 'wnbell_notification_item';
                        $trigger_output = wnbell_trigger_output(
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
                $item_class = 'wnbell_notification_item';
                
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
                    if ( isset( $options['item_unseen_id_attribute'] ) && $options['item_unseen_class_attribute'] != '' ) {
                        $item_id = $options['item_unseen_class_attribute'];
                    }
                    //$item_class = "wngreen";
                }
                
                $output .= '<div class="' . $item_class . '" id="' . $item_id . '">';
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
                $output .= apply_filters( 'wnbell_item_prepend', '', $post_id );
                $output .= '<a href="' . $link;
                $output .= '" onclick="wnbell_ajax_seen(' . $post_id;
                $output .= ');"';
                $output .= ' style="' . $a_style . '" class="' . $link_class . '">';
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
                $output .= apply_filters( 'wnbell_inner_item_append', '', $post_id );
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
                $item_class = 'wnbell_notification_item';
                $post_link = '';
                $trigger_text = '';
                foreach ( $trigger_array as $trigger_notification ) {
                    if ( !is_array( $trigger_notification ) ) {
                        break;
                    }
                    $trigger_output = wnbell_trigger_output(
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
            
            $output .= apply_filters( 'wnbell_list_append', '', $data );
            // $output .= '</div>';
            // Reset post data query
        } else {
            $no_notifs = ( isset( $options['no_notifs'] ) && $options['no_notifs'] != '' ? stripslashes( base64_decode( $options['no_notifs'] ) ) : '<div class="wnbell_empty_box" id="wnbell_empty_box_id">No new notifications<div>' );
            
            if ( sizeof( $trigger_array ) === 0 ) {
                $output .= '<div class="wnbell_header" id="wnbell_header">';
                $output .= '<div class="wnbell_header_empty" id="wnbell_header_empty">';
                $output .= stripslashes( base64_decode( $header ) );
                $output .= '</div>';
                $output .= '<span class="wnbell-closebtn">&times;</span>';
                $output .= '</div>';
                $output .= $no_notifs;
                $output .= apply_filters( 'wnbell_empty_list_append', '' );
            } else {
                $output .= '<div class="wnbell_header" id="wnbell_header">';
                $output .= stripslashes( base64_decode( $header ) );
                $output .= '<span class="wnbell-closebtn">&times;</span>';
                $output .= '</div>';
            }
            
            // $output .= '<div>No new notifications</div>';
            $post_link = '';
            $trigger_text = '';
            foreach ( $trigger_array as $trigger_notification ) {
                if ( !is_array( $trigger_notification ) ) {
                    break;
                }
                $trigger_output = wnbell_trigger_output(
                    $trigger_notification,
                    $options,
                    $seen_comments,
                    $seen_bbp,
                    $seen_woocommerce,
                    $item_id,
                    $item_class,
                    $trigger_text,
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
        
        // $latest_cpt = get_posts("post_type=wnbell_notifications&numberposts=1&fields=ids");
        //try
        $latest_query = "select posts.ID from {$wpdb->prefix}posts as posts\r\n        WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            ORDER BY posts.post_date DESC LIMIT 1 ";
        $latest_cpt = $wpdb->get_var( $latest_query );
        update_user_meta( $current_user_id, 'wnbell_last_seen', $latest_cpt );
        if ( isset( $options['enable_bp'] ) && $options['enable_bp'] ) {
            wnbell_bp_update_bp_last_seen();
        }
        do_action( 'wnbell_notifications_processed' );
    } else {
        $user_id = get_current_user_id();
        $user_unseen = get_user_meta( $user_id, 'wnbell_unseen', true );
        $user_unseen = ( is_numeric( $user_unseen ) ? $user_unseen : 0 );
        //try
        //count notifications
        $current_user = wp_get_current_user();
        $roles = (array) $current_user->roles;
        $start_query = true;
        //$first_use_seen = false;
        
        if ( $_POST['last_seen'] ) {
            $user_last_seen = $_POST['last_seen'];
        } else {
            $user_last_seen = get_user_meta( $user_id, 'wnbell_last_seen', true );
            
            if ( !$user_last_seen ) {
                global  $wpdb ;
                //$latest_cpt = get_posts("post_type=wnbell_notifications&numberposts=1&fields=ids");
                $latest_query = "select posts.ID from {$wpdb->prefix}posts as posts\r\n        WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            ORDER BY posts.post_date DESC LIMIT 1 ";
                $latest_cpt = $wpdb->get_var( $latest_query );
                $latest_cpt = ( is_numeric( $latest_cpt ) ? $latest_cpt : 1 );
                update_user_meta( $user_id, 'wnbell_last_seen', $latest_cpt - 1 );
                $user_last_seen = $latest_cpt - 1;
                
                if ( !empty($latest_cpt) ) {
                    //$user_unseen = 1;
                } else {
                    $start_query = false;
                }
            
            }
        
        }
        
        
        if ( $start_query ) {
            global  $wpdb ;
            $count_query = "SELECT count(*) from (select posts.ID from {$wpdb->prefix}posts AS posts\r\nLEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\nLEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n            WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            AND posts.ID>%d\r\n            AND (prole.user_role  IN(" . wnbell_escape_array( $roles ) . ") OR prole.user_role IS NULL)\r\n             AND (pname.usernames IS NULL)\r\n            GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT 100) as subquery;";
            $count_query = apply_filters(
                "wnbell_count_notifications",
                $count_query,
                $roles,
                $options
            );
            $count = $wpdb->get_var( $wpdb->prepare( $count_query, $user_last_seen ) );
            $count = ( is_numeric( $count ) ? $count : 0 );
            //$count = $wpdb->get_results($count_query, ARRAY_A);
            $user_unseen = $user_unseen + $count;
        }
        
        if ( isset( $options['enable_bp'] ) && $options['enable_bp'] ) {
            $user_unseen = wnbell_bp_unseen_count( $user_unseen );
        }
        $count_limit = apply_filters( 'wnbell_notifications_badge_limit', 9 );
        if ( $user_unseen > $count_limit ) {
            $user_unseen = "+" . $count_limit;
        }
        $user_unseen = apply_filters( "wnbell_unseen_count", $user_unseen );
    }
    
    //$interval = (isset($options['server_call_interval']) && $options['server_call_interval'] != '') ? intval($options['server_call_interval']) * 1000 : 15000;
    $data = array(
        'notification'        => $output,
        'unseen_notification' => $user_unseen,
    );
    if ( isset( $latest_cpt ) && $latest_cpt ) {
        $data['last_seen'] = $latest_cpt;
    }
    if ( !$_POST['last_seen'] && $user_last_seen ) {
        $data['last_seen'] = $user_last_seen;
    }
    return json_encode( $data );
}

function wnbell_notification_display( $floating = false )
{
    if ( !is_user_logged_in() ) {
        return false;
    }
    if ( !$floating ) {
    }
    //$nonce = wp_create_nonce('wnbell_ajax');
    $options = get_option( 'wnbell_options' );
    $class = ( $floating ? 'wnbell-floating-toggle' : 'wnbell-dropdown-toggle' );
    $container_class = ( $floating ? ' wnbell-floating-container' : '' );
    //$list_link = '#';
    $toggle_class = $class;
    //$toggle = 'return false;';
    $toggle = '';
    $class = $toggle_class;
    ob_start();
    ?>
     <div class="wnbell-dropdown" id="wnbell-dropdown-id">
     <div class="wnbell-icon-badge-container<?php 
    echo  $container_class ;
    ?>" id="wnbell-icon-badge-container-id">
     <div class="<?php 
    echo  $class ;
    ?>" id="wnbell-dropdown-toggle-id wnbell-dropdownMenu1" onclick="<?php 
    echo  $toggle ;
    ?>" style="outline:none;cursor:pointer;">
    <?php 
    $interval = ( isset( $options['server_call_interval'] ) && $options['server_call_interval'] != '' ? intval( $options['server_call_interval'] ) * 1000 : WNBELL_INTERVAL );
    $bell_icon = $options['wnbell_bell_icon'];
    
    if ( isset( $bell_icon ) && $bell_icon != '' ) {
        echo  stripslashes( base64_decode( $bell_icon ) ) ;
    } else {
        $output = '<svg width="25" height="25" class="wnbell_icon" aria-hidden="true" focusable="false" data-prefix="far" data-icon="bell" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <path fill="black" d="M224 512c35.32 0 63.97-28.65 63.97-64H160.03c0 35.35 28.65 64 63.97 64zm215.39-149.71c-19.32-20.76-55.47-51.99-55.47-154.29 0-77.7-54.48-139.9-127.94-155.16V32c0-17.67-14.32-32-31.98-32s-31.98 14.33-31.98 32v20.84C118.56 68.1 64.08 130.3 64.08 208c0 102.3-36.15 133.53-55.47 154.29-6 6.45-8.66 14.16-8.61 21.71.11 16.4 12.98 32 32.1 32h383.8c19.12 0 32-15.6 32.1-32 .05-7.55-2.61-15.27-8.61-21.71z">
        </path></svg>';
        echo  apply_filters( "wnbell_bell_icon", $output ) ;
    }
    
    ?>
    <!-- </a> -->
    </div>
    <span class="wnbell-count" id="wnbell-count-id"></span>
    </div>
    <?php 
    $style = '';
    
    if ( isset( $options['wnbell_box_class_attribute'] ) && $options['wnbell_box_class_attribute'] != '' ) {
        $box_class = esc_html( $options['wnbell_box_class_attribute'] );
    } else {
        $box_class = 'wnbell_notifications_lists';
        $style = ( isset( $options['box_position'] ) && $options['box_position'] == true ? 'position: absolute; right:40px;' : '' );
    }
    
    
    if ( isset( $options['wnbell_box_id_attribute'] ) && $options['wnbell_box_id_attribute'] != '' ) {
        $box_id = esc_html( $options['wnbell_box_id_attribute'] );
    } else {
        $box_id = '';
        $style = ( isset( $options['box_position'] ) && $options['box_position'] == true ? 'position: absolute; right:40px;' : '' );
    }
    
    $style .= ( $floating ? 'position: absolute;bottom:10px;' : '' );
    $style_top = ( isset( $options['box_position_top'] ) && $options['box_position_top'] != '' ? 'position: absolute;top:' . $options['box_position_top'] . 'px;' : '' );
    $style_bottom = ( isset( $options['box_position_top'] ) && $options['box_position_bottom'] != '' ? 'position: absolute;bottom:' . $options['box_position_bottom'] . 'px;' : '' );
    $style_left = ( isset( $options['box_position_left'] ) && $options['box_position_left'] != '' ? 'position: absolute;left:' . $options['box_position_left'] . 'px;' : '' );
    $style_right = ( isset( $options['box_position_right'] ) && $options['box_position_right'] != '' ? 'position: absolute;right:' . $options['box_position_right'] . 'px;' : '' );
    $style .= $style_top . $style_bottom . $style_left . $style_right;
    ?>
        <div class="wnbell-dropdown-box-wrap" id="wnbell-dropdown-box-wrap-id">
        <div class="<?php 
    echo  $box_class ;
    ?>" id="<?php 
    echo  $box_id ;
    ?>" style="<?php 
    echo  $style ;
    ?>">
        <div class="wnbell-spinner-wrap">
<span class="wnbell-spinner"></span>
</div>
    <div class="wnbell-dropdown-box" id="wnbell-dropdown-box-id"></div>
    </div>
</div>
    </div>
    <?php 
    wnbell_shortcode_script( $interval );
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

//add_action('wp_ajax_nopriv_wnbell_list_ajax', 'wnbell_list_ajax');
function wnbell_list_ajax()
{
    check_ajax_referer( 'wnbell_ajax' );
    
    if ( isset( $_POST['view'] ) ) {
        
        if ( sanitize_text_field( $_POST["view"] ) != '' ) {
            $current_user_id = get_current_user_id();
            update_user_meta( $current_user_id, 'wnbell_unseen', 0 );
            $data = wnbell_notification_list( 'yes' );
        } else {
            $data = wnbell_notification_list();
        }
        
        echo  $data ;
    }
    
    die;
}

function wnbell_shortcode_script( $interval )
{
    //$nonce = wp_create_nonce('wnbell_ajax');
    ob_start();
    wnbell_script( $interval );
    $output = ob_get_contents();
    ob_end_clean();
    echo  $output ;
    return;
}

function wnbell_script( $interval )
{
    $nonce = wp_create_nonce( 'wnbell_ajax' );
    ob_start();
    ?>
    <script type='text/javascript'>
function wnbell_load_unseen_notification(view = '') {
        jQuery.ajax({
            type: "POST",
            url: ajax_url,
            data: {
                action: 'wnbell_list_ajax',
                _ajax_nonce:"<?php 
    echo  $nonce ;
    ?>",
                view: view,
                last_seen:last_seen_id
            },
            dataType: 'JSON',
            success: function (data) {
                if (typeof wnbell_interval == 'undefined' || !wnbell_interval) {
                    wnbell_interval = setInterval(function () {
                        wnbell_load_unseen_notification();
                    }, <?php 
    echo  $interval ;
    ?>);
                    //data.interval);
                }
                if(data.last_seen){
                    last_seen_id=data.last_seen;
                }
                //jQuery('.wnbell-dropdown-box').html(data.notification);
                jQuery('.wnbell-spinner').hide();
                jQuery('.wnbell-spinner').removeClass('wnbell-active-spinner');
                if (data.notification != '') {
                    if(wnb_mediaQuery.matches) {
       jQuery('.wnbell_dropdown_list_ss').html(data.notification);
       jQuery('.wnbell-spinner-wrap-ss').hide();
                jQuery('.wnbell-spinner-ss').removeClass('wnbell-active-spinner-ss');
    }else{
                jQuery('.wnbell-dropdown-box').html(data.notification);
    }
                    clearInterval(wnbell_interval);
                    wnbell_interval = null;
                }
                if (data.unseen_notification > 0 && data.notification == '') {
                   // if(!jQuery('.wnbell-count').hasClass('wnbell-badge')){
                    jQuery('.wnbell-count').addClass('wnbell-badge wnbell-badge-shortcode wnbell-badge-danger');
                    jQuery('.wnbell-count').html(data.unseen_notification);
                }
            }
        });
    };
    function wnbell_ajax_seen(notificationID = '', notification_type = '') {
        jQuery.ajax({
            type: "POST",
            url: ajax_url,
            data: {
                action: 'wnbell_seen_notification_ajax',
                _ajax_nonce:"<?php 
    echo  $nonce ;
    ?>",
                notificationID: notificationID,
                notification_type: notification_type
            },
            dataType: 'JSON',
            success: function (data) {
            }
        });
    };
    var wnb_mediaQuery = window.matchMedia('(max-width: 768px)');
    var last_seen_id=0;
    jQuery(document).ready(function () {
        // var last_seen_id=0;
        wnbell_load_unseen_notification();
        jQuery('.wnbell-dropdown-toggle').click(function () {

            if (jQuery('.wnbell-dropdown-box').html() == '' && jQuery('.wnbell_dropdown_list_ss').html() == '') {
                if(wnb_mediaQuery.matches) {
                    jQuery('.wnbell_dropdown_list_ss').css('display', 'inline-block');
                  //  jQuery('body').css("overflow","hidden");
                  jQuery('.wnbell-spinner-wrap-ss').css('display', 'inline-block');
             jQuery('.wnbell-spinner-ss').addClass('wnbell-active-spinner-ss');
                }else{
             jQuery('.wnbell-dropdown-box-wrap').css('display', 'inline-block');

                jQuery('.wnbell-spinner').css('display', 'inline-block');
                jQuery('.wnbell-spinner').addClass('wnbell-active-spinner');
                }
                //clearInterval(wnbell_interval);
                wnbell_load_unseen_notification('yes');
            }
            jQuery('.wnbell-count').html('');
            jQuery('.wnbell-count').removeClass('wnbell-badge wnbell-badge-shortcode wnbell-badge-danger');
        });
        jQuery('html').click(function (e) {
            if (!jQuery(e.target).parent().hasClass('wnbell-dropdown-box')) {
                if (jQuery('.wnbell-dropdown-box').html() != '') {
                    jQuery(".wnbell-spinner").removeClass("wnbell-active-spinner");
                    jQuery('.wnbell-spinner').hide();
                    jQuery('.wnbell-dropdown-box-wrap').hide();
                    jQuery('.wnbell-dropdown-box').html('');
                    wnbell_load_unseen_notification();
                }
            }

        });
        jQuery(document).on('click', '.wnbell-closebtn', function (e) {
            jQuery('.wnbell_dropdown_list_ss').html('');
            jQuery('.wnbell-dropdown-box').html('');
            jQuery('.wnbell_dropdown_list_ss').hide();
            jQuery('.wnbell-dropdown-box-wrap').hide();
            wnbell_load_unseen_notification();
            //jQuery('body').css("overflow", "auto");
        });
    });
</script>
<?php 
    $output = ob_get_contents();
    ob_end_clean();
    echo  $output ;
    return;
}
