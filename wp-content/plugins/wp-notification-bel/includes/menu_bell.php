<?php

defined( 'ABSPATH' ) || exit;
add_filter(
    'wp_nav_menu_items',
    'wnbell_menu_notification_bell',
    10,
    2
);
add_action( 'wp_ajax_wnbell_seen_notification_ajax', 'wnbell_seen_notification_ajax' );
add_action( 'wp_ajax_wnbell_list_ajax_menu', 'wnbell_list_ajax_menu' );
// Function to add bell as a menu item.
function wnbell_menu_notification_bell( $items, $args )
{
    $options = get_option( 'wnbell_options' );
    $options['menu_location'] = ( isset( $options['menu_location'] ) ? $options['menu_location'] : '' );
    $enable_bell_menu = ( isset( $options['bell_menu'] ) ? $options['bell_menu'] : '0' );
    $enable_bell_menu_lo = ( isset( $options['bell_menu_lo'] ) ? $options['bell_menu_lo'] : '0' );
    $menu_position = ( isset( $options['menu_position'] ) ? $options['menu_position'] : -1 );
    if ( $enable_bell_menu == '1' ) {
        
        if ( is_user_logged_in() && $args->theme_location == $options['menu_location'] ) {
            //$items .= wnbell_notification_menu_display();
            
            if ( strlen( $items ) > $menu_position && $menu_position > 0 ) {
                $items_array = array();
                while ( false !== ($item_pos = strpos( $items, '<li', $menu_position )) ) {
                    $items_array[] = substr( $items, 0, $item_pos );
                    $items = substr( $items, $item_pos );
                }
                $items_array[] = $items;
                array_splice(
                    $items_array,
                    $menu_position - 1,
                    0,
                    wnbell_notification_menu_display()
                );
                $items = implode( '', $items_array );
            } else {
                $items .= wnbell_notification_menu_display();
            }
            
            //return $items;
        }
    
    }
    if ( $enable_bell_menu_lo == '1' ) {
        
        if ( !is_user_logged_in() && $args->theme_location == $options['menu_location'] ) {
            //$items .= wnbell_notification_menu_display();
            
            if ( strlen( $items ) > $menu_position && $menu_position > 0 ) {
                $items_array = array();
                while ( false !== ($item_pos = strpos( $items, '<li', $menu_position )) ) {
                    $items_array[] = substr( $items, 0, $item_pos );
                    $items = substr( $items, $item_pos );
                }
                $items_array[] = $items;
                array_splice(
                    $items_array,
                    $menu_position - 1,
                    0,
                    wnbell_notification_display_logged_out_menu()
                );
                $items = implode( '', $items_array );
            } else {
                $items .= wnbell_notification_display_logged_out_menu();
            }
            
            //return $items;
        }
    
    }
    return $items;
}

function wnbell_notification_menu_display()
{
    // if (!is_user_logged_in()) {
    //     return false;
    // }
    //$nonce = wp_create_nonce('wnbell_ajax');
    $options = get_option( 'wnbell_options' );
    //$list_link = '#';
    $toggle_class = 'wnbell-dropdown-toggle-menu';
    $toggle = '';
    ob_start();
    ?>
     <li class="wnbell-dropdown-nav" id="wnbell-dropdown-nav-id">
     <div class="wnbell-icon-badge-container" id="wnbell-icon-badge-container-id">
     <div class="<?php 
    echo  $toggle_class ;
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
    <span class="wnbell-count-menu" id="wnbell-count-menu-id"></span>
</div>
<?php 
    $style = '';
    
    if ( isset( $options['wnbell_box_class_attribute'] ) && $options['wnbell_box_class_attribute'] != '' ) {
        $box_class = esc_html( $options['wnbell_box_class_attribute'] );
    } else {
        $box_class = 'wnbell_notifications_lists_menu';
        $style = ( isset( $options['box_position'] ) && $options['box_position'] == true ? 'position: absolute; right:-10px;' : 'position: absolute; left:-10px;' );
    }
    
    
    if ( isset( $options['wnbell_box_id_attribute'] ) && $options['wnbell_box_id_attribute'] != '' ) {
        $box_id = esc_html( $options['wnbell_box_id_attribute'] );
    } else {
        $box_id = '';
        $style = ( isset( $options['box_position'] ) && $options['box_position'] == true ? 'position: absolute; right:-10px;' : 'position: absolute; left:-10px;' );
    }
    
    $style_top = ( isset( $options['box_position_top'] ) && $options['box_position_top'] != '' ? 'position: absolute;top:' . $options['box_position_top'] . 'px;' : '' );
    $style_bottom = ( isset( $options['box_position_top'] ) && $options['box_position_bottom'] != '' ? 'position: absolute;bottom:' . $options['box_position_bottom'] . 'px;' : '' );
    $style_left = ( isset( $options['box_position_left'] ) && $options['box_position_left'] != '' ? 'position: absolute;left:' . $options['box_position_left'] . 'px;' : '' );
    $style_right = ( isset( $options['box_position_right'] ) && $options['box_position_right'] != '' ? 'position: absolute;right:' . $options['box_position_right'] . 'px;' : '' );
    $style .= $style_top . $style_bottom . $style_left . $style_right;
    ?>
    <div class="wnbell-dropdown-menu-wrap" id="wnbell-dropdown-menu-wrap-id">
        <div class="<?php 
    echo  $box_class ;
    ?>" id="<?php 
    echo  $box_id ;
    ?>"  style="<?php 
    echo  $style ;
    ?>" >
        <div class="wnbell-spinner-wrap-menu">
<span class="wnbell-spinner-menu"></span>
</div>
    <div class="wnbell-dropdown-menu" id="wnbell-dropdown-menu-id"></div>
    </div></div>
    </li>
    <?php 
    wnbell_menu_script( $interval );
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function wnbell_notification_list_menu( $view = '' )
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
        $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\n        LEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\n        LEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n            WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            AND (prole.user_role  IN(" . wnbell_escape_array( $roles ) . ") OR prole.user_role IS NULL)\r\n            AND (LOCATE('{$username_value}',pname.usernames) > 0 OR pname.usernames IS NULL)";
        $query .= apply_filters( "wnbell_notification_conditions", '' );
        $query .= " GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT {$limit};";
        //$sql = $wpdb->prepare($query, $username);
        $query = apply_filters(
            "wnbell_select_notifications",
            $query,
            $roles,
            $username_value,
            $options
        );
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
        $output .= wnbell_output_menu_bell(
            $options,
            $data,
            $trigger_array,
            $current_user_id
        );
        //$latest_cpt = get_posts("post_type=wnbell_notifications&numberposts=1&fields=ids");
        // select MAX(posts.ID)...
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
        $current_user = wp_get_current_user();
        $roles = (array) $current_user->roles;
        $start_query = true;
        
        if ( $_POST['last_seen'] ) {
            $user_last_seen = $_POST['last_seen'];
        } else {
            $user_last_seen = get_user_meta( $user_id, 'wnbell_last_seen', true );
            
            if ( !$user_last_seen ) {
                global  $wpdb ;
                // $latest_cpt = get_posts("post_type=wnbell_notifications&numberposts=1&fields=ids");
                $latest_query = "select posts.ID from {$wpdb->prefix}posts as posts\r\n               WHERE posts.post_type = 'wnbell_notifications'\r\n                   AND posts.post_status = 'publish'\r\n                   ORDER BY posts.post_date DESC LIMIT 1 ";
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
            $count_query = "SELECT count(*) from (select posts.ID from {$wpdb->prefix}posts AS posts\r\nLEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\nLEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n            WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            AND posts.ID>%d\r\n            AND (prole.user_role  IN(" . wnbell_escape_array( $roles ) . ") OR prole.user_role IS NULL)\r\n            AND (pname.usernames IS NULL)";
            $count_query .= apply_filters( "wnbell_notification_count_conditions", '' );
            $count_query .= " GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT 100) as subquery;";
            $count_query = apply_filters(
                "wnbell_count_notifications",
                $count_query,
                $roles,
                $options
            );
            $count = $wpdb->get_var( $wpdb->prepare( $count_query, $user_last_seen ) );
            $count = ( is_numeric( $count ) ? $count : 0 );
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
    
    // $interval = (isset($options['server_call_interval']) && $options['server_call_interval'] != '') ? intval($options['server_call_interval']) * 1000 : 15000;
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

//add_action('wp_ajax_nopriv_wnbell_list_ajax_menu', 'wnbell_list_ajax_menu');
function wnbell_list_ajax_menu()
{
    check_ajax_referer( 'wnbell_ajax' );
    
    if ( isset( $_POST['view'] ) ) {
        
        if ( sanitize_text_field( $_POST["view"] ) != '' ) {
            $latest_cpt = get_posts( "post_type=wnbell_notifications&numberposts=1&fields=ids" );
            $current_user_id = get_current_user_id();
            update_user_meta( $current_user_id, 'wnbell_unseen', 0 );
            // update_user_meta($current_user_id, 'wnbell_last_seen', $latest_cpt); //unused for now
            $data = wnbell_notification_list_menu( 'yes' );
        } else {
            $data = wnbell_notification_list_menu();
        }
        
        echo  $data ;
    }
    
    die;
}

function wnbell_menu_script( $interval )
{
    //$nonce = wp_create_nonce('wnbell_ajax');
    ob_start();
    wnbell_menu_script_s( $interval );
    $output = ob_get_contents();
    ob_end_clean();
    echo  $output ;
    return;
}

function wnbell_menu_script_s( $interval )
{
    $nonce = wp_create_nonce( 'wnbell_ajax' );
    ob_start();
    ?>
    <script type='text/javascript'>
function wnbell_menu_load_unseen_notification(view = '') {
 jQuery.ajax({
     type: "POST",
     url: ajax_url,
     data: {
         action: 'wnbell_list_ajax_menu',
         _ajax_nonce:"<?php 
    echo  $nonce ;
    ?>",
         view: view,
        last_seen:last_seen_id
     },
     dataType: 'JSON',
     success: function (data) {

         if (typeof wnbell_interval_menu == 'undefined' || !wnbell_interval_menu) {
             wnbell_interval_menu = setInterval(function () {
                 wnbell_menu_load_unseen_notification();
             }, <?php 
    echo  $interval ;
    ?>);
         }
//          const mediaQuery = window.matchMedia('(max-width: 768px)');
if(data.last_seen){
                last_seen_id=data.last_seen;
            }
         jQuery('.wnbell-spinner-menu').hide();
         jQuery('.wnbell-spinner-menu').removeClass('wnbell-active-spinner-menu');
         if (data.notification != '') {
            if(wnb_mediaQuery.matches) {
   jQuery('.wnbell_dropdown_list_ss').html(data.notification);
   jQuery('.wnbell-spinner-wrap-ss').hide();
            jQuery('.wnbell-spinner-ss').removeClass('wnbell-active-spinner-ss');
}else{
            jQuery('.wnbell-dropdown-menu').html(data.notification);
}

             clearInterval(wnbell_interval_menu);
             wnbell_interval_menu = null;
         }
         if (data.unseen_notification > 0 && data.notification == '') {
             // if(!jQuery('.wnbell-count-menu').hasClass('wnbell-badge')){
                // if(data.unseen_notification>wnbell_unseen_notification){

             jQuery('.wnbell-count-menu').addClass('wnbell-badge wnbell-badge-menu wnbell-badge-danger');
             jQuery('.wnbell-count-menu').html(data.unseen_notification);
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
 wnbell_menu_load_unseen_notification();

 jQuery('.wnbell-dropdown-toggle-menu').click(function () {
     if (jQuery('.wnbell-dropdown-menu').html() == '' && jQuery('.wnbell_dropdown_list_ss').html() == '') {
        if(wnb_mediaQuery.matches) {
                jQuery('.wnbell_dropdown_list_ss').css('display', 'inline-block');
                jQuery('.wnbell-spinner-wrap-ss').css('display', 'inline-block');
         jQuery('.wnbell-spinner-ss').addClass('wnbell-active-spinner-ss');
              //  jQuery('body').css("overflow","hidden");
            }else{
         jQuery('.wnbell-dropdown-menu-wrap').css('display', 'inline-block');
         jQuery('.wnbell-spinner-menu').css('display', 'inline-block');
         jQuery('.wnbell-spinner-menu').addClass('wnbell-active-spinner-menu');
            }

         wnbell_menu_load_unseen_notification('yes');
     }
     jQuery('.wnbell-count-menu').html('');
     jQuery('.wnbell-count-menu').removeClass('wnbell-badge wnbell-badge-menu wnbell-badge-danger');
 });
 jQuery('html').click(function (e) {
     if (!jQuery(e.target).parent().hasClass('wnbell-dropdown-menu')) {
         if (jQuery('.wnbell-dropdown-menu').html() != '') {
             jQuery(".wnbell-spinner").removeClass("wnbell-active-spinner");
             jQuery('.wnbell-spinner').hide();

             jQuery('.wnbell-spinner-ss').hide();
            jQuery('.wnbell-spinner-ss').removeClass('wnbell-active-spinner-ss');

             jQuery('.wnbell-dropdown-menu-wrap').hide();
             jQuery('.wnbell-dropdown-menu').html('');
             wnbell_menu_load_unseen_notification();
         }
     }

 });
 jQuery(document).on('click', '.wnbell-closebtn-menu', function (e) {
        jQuery('.wnbell_dropdown_list_ss').html('');
        jQuery('.wnbell-dropdown-menu').html('');
        jQuery('.wnbell_dropdown_list_ss').hide();
        jQuery('.wnbell-dropdown-menu-wrap').hide();
        wnbell_menu_load_unseen_notification();
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
