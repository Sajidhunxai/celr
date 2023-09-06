<?php

defined( 'ABSPATH' ) || exit;
//add_filter('the_title', 'do_shortcode'); // for adding in page title
add_shortcode( 'wp-notification-bell-logged-out', 'wnbell_notification_display_logged_out' );
add_action( 'wp_ajax_nopriv_wnbell_list_ajax_visitor', 'wnbell_list_ajax_visitor' );
//add_action('init', 'wnbell_set_cookie');
function wnbell_notification_display_logged_out( $floating_lo = false )
{
    if ( is_user_logged_in() ) {
        return false;
    }
    if ( !$floating_lo ) {
    }
    $nonce = wp_create_nonce( 'wnbell_ajax' );
    $options = get_option( 'wnbell_options' );
    $class = ( $floating_lo ? 'wnbell-floating-toggle' : 'wnbell-dropdown-toggle' );
    $container_class = ( $floating_lo ? ' wnbell-floating-container' : '' );
    // $list_link = '#';
    $toggle_class = $class;
    // $toggle = 'return false;';
    $toggle = '';
    $class = $toggle_class;
    ob_start();
    ?>
     <div class="wnbell-dropdown" id="wnbell-dropdown-id" style="padding:0px;">
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
        $output = '<svg width="20" height="20" class="wnbell_icon" aria-hidden="true" focusable="false" data-prefix="far" data-icon="bell" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
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
    
    $style .= ( $floating_lo ? 'position: absolute;bottom:10px;' : '' );
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
    wnbell_script_lo( $interval );
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function wnbell_notification_list_visitor( $view = '' )
{
    $options = get_option( 'wnbell_options' );
    $output = '';
    $unseen_notification = 0;
    
    if ( $view === 'yes' ) {
        $limit = filter_var( apply_filters( 'wnbell_notifications_display_count', 5 ), FILTER_SANITIZE_NUMBER_INT );
        //$user_unseen = 0;
        //$current_user = wp_get_current_user();
        //$roles = array("all");
        // $username = $current_user->user_login;
        // $username_value = $username . '";';
        // $username_array = array($username, "0");
        global  $wpdb ;
        $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\n         LEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\n         LEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n            WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            AND (prole.user_role IS NULL OR prole.user_role LIKE 'all' OR prole.user_role LIKE 'wnbell_guest')\r\n            AND (pname.usernames IS NULL)\r\n            GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT {$limit};";
        //$sql = $wpdb->prepare($query, $username);
        $query = apply_filters( "wnbell_select_notifications_visitor", $query, $options );
        $data = $wpdb->get_results( $query, ARRAY_A );
        $trigger_array = array();
        // $current_user_id = get_current_user_id();
        $header = ( isset( $options['header'] ) ? $options['header'] : '' );
        
        if ( count( $data ) > 0 ) {
            $output .= '<div class="wnbell_header" id="wnbell_header">';
            $output .= stripslashes( base64_decode( $header ) );
            $output .= '<span class="wnbell-closebtn">&times;</span>';
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
                $item_class = 'wnbell_notification_item';
                $post_date = get_the_date( '', $single_notification_id ) . ' ' . get_the_time( '', $single_notification_id );
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
                    //  if ($thumbnail == "") {
                    $thumbnail_id = get_post_meta( $post_id, "post_id", true );
                    
                    if ( $thumbnail_id != "" ) {
                        $post_thumbnail_id = get_post_thumbnail_id( $thumbnail_id );
                        if ( $post_thumbnail_id ) {
                            $thumbnail = get_the_post_thumbnail( $thumbnail_id, array( $width, $height ), array(
                                'class' => $img_position,
                            ) );
                        }
                        //   }
                    }
                
                }
                
                $wnbell_link = esc_html( get_post_meta( $post_id, 'wnbell_link', true ) );
                $link = ( isset( $wnbell_link ) ? $wnbell_link : '' );
                $a_style = ( $thumbnail == "" ? "" : "min-height:" . $height . "px;" );
                $link_class = ( empty($link) ? 'wnbell_disabled_link' : '' );
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
            // if (sizeof($trigger_array) === 0) {
            
            if ( count( $trigger_array ) > 0 ) {
            } else {
                $output .= '<div class="wnbell_header" id="wnbell_header">';
                $output .= '<div class="wnbell_header_empty" id="wnbell_header_empty">';
                $output .= stripslashes( base64_decode( $header ) );
                $output .= '</div>';
                $output .= '<span class="wnbell-closebtn">&times;</span>';
                $output .= '</div>';
                $no_notifs = ( isset( $options['no_notifs'] ) && $options['no_notifs'] != '' ? stripslashes( base64_decode( $options['no_notifs'] ) ) : '<div class="wnbell_empty_box" id="wnbell_empty_box_id">No new notifications<div>' );
                $output .= $no_notifs;
            }
            
            //}
            // $output .= '<div>No new notifications</div>';
            $post_link = '';
            $trigger_text = '';
        }
        
        
        if ( count( $data ) > 0 ) {
            $value = $data[0]['ID'];
            $expiry = strtotime( '+1 month' );
            setcookie(
                "wnbell_last_id",
                $value,
                $expiry,
                COOKIEPATH,
                COOKIE_DOMAIN
            );
        }
    
    } else {
        //get cookie
        
        if ( isset( $_COOKIE['wnbell_last_count'] ) ) {
            $lastcount = sanitize_text_field( $_COOKIE['wnbell_last_count'] );
            if ( $lastcount == 1 ) {
                $unseen_notification = 1;
            }
        }
        
        if ( $unseen_notification == 0 ) {
            
            if ( isset( $_COOKIE['wnbell_last_id'] ) ) {
                $last_id = sanitize_text_field( $_COOKIE['wnbell_last_id'] );
                global  $wpdb ;
                $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\n             LEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\n             LEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n                WHERE posts.post_type = 'wnbell_notifications'\r\n                AND posts.post_status = 'publish'\r\n                AND posts.ID>%d\r\n                AND (prole.user_role IS NULL OR prole.user_role LIKE 'all' OR prole.user_role LIKE 'wnbell_guest')\r\n                AND (pname.usernames IS NULL) ";
                $query .= apply_filters( "wnbell_conditions_visitor_count", '' );
                $query .= " GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT 1;";
                $query = apply_filters( "wnbell_count_notifications_visitor", $query, $options );
                $sql = $wpdb->prepare( $query, $last_id );
                $data = $wpdb->get_results( $sql, ARRAY_A );
                
                if ( count( $data ) > 0 ) {
                    $expiry = strtotime( '+1 month' );
                    setcookie(
                        "wnbell_last_count",
                        1,
                        $expiry,
                        COOKIEPATH,
                        COOKIE_DOMAIN
                    );
                    $unseen_notification = 1;
                } else {
                }
                
                //$latest_cpt = get_posts("post_type=wnbell_notifications&numberposts=1&fields=ids");
            } else {
                global  $wpdb ;
                $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\n      LEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\n      LEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n            WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            AND (prole.user_role IS NULL OR prole.user_role LIKE 'all' OR prole.user_role LIKE 'wnbell_guest')\r\n            AND (pname.usernames IS NULL) ";
                $query .= apply_filters( "wnbell_conditions_new_visitor_count", '' );
                $query .= " GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT 1;";
                $query = apply_filters( "wnbell_count_notifications_new_visitor", $query, $options );
                //$sql = $wpdb->prepare($query, $username);
                $data = $wpdb->get_results( $query, ARRAY_A );
                
                if ( count( $data ) > 0 ) {
                    $expiry = strtotime( '+1 month' );
                    setcookie(
                        "wnbell_last_count",
                        1,
                        $expiry,
                        COOKIEPATH,
                        COOKIE_DOMAIN
                    );
                    $unseen_notification = 1;
                } else {
                    $latest_query = "select posts.ID from {$wpdb->prefix}posts as posts\r\n                    WHERE posts.post_type = 'wnbell_notifications'\r\n                        AND posts.post_status = 'publish'\r\n                        ORDER BY posts.post_date DESC LIMIT 1 ";
                    $latest_cpt = $wpdb->get_var( $latest_query );
                    $value = $latest_cpt;
                    $expiry = strtotime( '+1 month' );
                    setcookie(
                        "wnbell_last_id",
                        $value,
                        $expiry,
                        COOKIEPATH,
                        COOKIE_DOMAIN
                    );
                }
            
            }
        
        }
    }
    
    // $interval = (isset($options['server_call_interval']) && $options['server_call_interval'] != '') ? intval($options['server_call_interval']) * 1000 : 15000;
    $data = array(
        'notification'        => $output,
        'unseen_notification' => $unseen_notification,
    );
    return json_encode( $data );
}

// function wnbell_set_cookie()
// {
//     if (!is_user_logged_in()) {
//         $value = "";
//         $expiry = strtotime('+1 month');
//         setcookie("wnbell_last_seen", $value, $expiry, COOKIEPATH, COOKIE_DOMAIN);
//     }
// }
function wnbell_list_ajax_visitor()
{
    check_ajax_referer( 'wnbell_ajax' );
    if ( !is_user_logged_in() ) {
        
        if ( isset( $_POST['view'] ) ) {
            
            if ( sanitize_text_field( $_POST["view"] ) != '' ) {
                //$value = time();
                $expiry = strtotime( '+1 month' );
                //setcookie("wnbell_last_seen", $value, $expiry, COOKIEPATH, COOKIE_DOMAIN);
                setcookie(
                    "wnbell_last_count",
                    0,
                    $expiry,
                    COOKIEPATH,
                    COOKIE_DOMAIN
                );
                $data = wnbell_notification_list_visitor( 'yes' );
            } else {
                $data = wnbell_notification_list_visitor();
            }
            
            echo  $data ;
        }
    
    }
    die;
}

function wnbell_script_lo( $interval )
{
    $nonce = wp_create_nonce( 'wnbell_ajax' );
    ob_start();
    ?>
    <script type='text/javascript'>
function wnbell_load_unseen_notification_lo(view = '') {
    jQuery.ajax({
        type: "POST",
        url: ajax_url,
        data: {
            action: 'wnbell_list_ajax_visitor',
            _ajax_nonce:"<?php 
    echo  $nonce ;
    ?>",
            view: view
        },
        dataType: 'JSON',
        success: function (data) {
            if (typeof wnbell_interval_lo == 'undefined' || !wnbell_interval_lo) {
                wnbell_interval_lo = setInterval(function () {
                    wnbell_load_unseen_notification_lo();
                }, <?php 
    echo  $interval ;
    ?>);
            }
            //jQuery('.wnbell-dropdown-box').html(data.notification);
            jQuery('.wnbell-spinner').hide();
            jQuery('.wnbell-spinner').removeClass('wnbell-active-spinner');
            if(data.notification != ''){
                if(wnb_mediaQuery.matches) {
   jQuery('.wnbell_dropdown_list_ss').html(data.notification);
   jQuery('.wnbell-spinner-wrap-ss').hide();
            jQuery('.wnbell-spinner-ss').removeClass('wnbell-active-spinner-ss');
}else{
            jQuery('.wnbell-dropdown-box').html(data.notification);
}
            }
            if (data.notification != '' || data.unseen_notification > 0) {
                clearInterval(wnbell_interval_lo);
                wnbell_interval_lo = null;
            }
            if (data.unseen_notification > 0 && data.notification == '') {
                if(!jQuery('.wnbell-count').hasClass('wnbell-dot')){
                    jQuery('.wnbell-count').addClass('wnbell-dot');
        }

            }
        }
    });
};
var wnb_mediaQuery = window.matchMedia('(max-width: 768px)');
jQuery(document).ready(function () {
    wnbell_load_unseen_notification_lo();
    jQuery('.wnbell-dropdown-toggle').click(function () {

        if (jQuery('.wnbell-dropdown-box').html() == '' && jQuery('.wnbell_dropdown_list_ss').html() == '') {
            if(wnb_mediaQuery.matches) {
                jQuery('.wnbell_dropdown_list_ss').css('display', 'inline-block');
              //  jQuery('body').css("overflow","hidden");
              jQuery('.wnbell-spinner-wrap-ss').css('display', 'inline-block');
         jQuery('.wnbell-spinner-ss').addClass('wnbell-active-spinner-ss');
            }else{
            jQuery('.wnbell-spinner').css('display', 'inline-block');
            jQuery('.wnbell-dropdown-box-wrap').css('display', 'inline-block');
            jQuery('.wnbell-spinner').addClass('wnbell-active-spinner');
            }
            //clearInterval(wnbell_interval);
            wnbell_load_unseen_notification_lo('yes');
        }
        jQuery('.wnbell-count').removeClass('wnbell-dot');
    });
    jQuery('html').click(function (e) {
        if (!jQuery(e.target).parent().hasClass('wnbell-dropdown-box')) {
            if (jQuery('.wnbell-dropdown-box').html() != '') {
                jQuery(".wnbell-spinner").removeClass("wnbell-active-spinner");
                jQuery('.wnbell-spinner').hide();
                jQuery('.wnbell-dropdown-box-wrap').hide();
                jQuery('.wnbell-dropdown-box').html('');
                wnbell_load_unseen_notification_lo();
            }
        }

    });
    jQuery(document).on('click', '.wnbell-closebtn', function (e) {
        jQuery('.wnbell_dropdown_list_ss').html('');
        jQuery('.wnbell-dropdown-box').html('');
        jQuery('.wnbell_dropdown_list_ss').hide();
        jQuery('.wnbell-dropdown-box-wrap').hide();
        wnbell_load_unseen_notification_lo();
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
