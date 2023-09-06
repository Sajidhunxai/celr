<?php

defined( 'ABSPATH' ) || exit;
//add_filter('the_title', 'do_shortcode'); // for adding in page title
add_action( 'wp_ajax_nopriv_wnbell_list_ajax_visitor_menu', 'wnbell_list_ajax_visitor_menu' );
//add_action('init', 'wnbell_set_cookie');
function wnbell_notification_display_logged_out_menu()
{
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
        <path fill="currentColor" d="M439.39 362.29c-19.32-20.76-55.47-51.99-55.47-154.29 0-77.7-54.48-139.9-127.94-155.16V32c0-17.67-14.32-32-31.98-32s-31.98 14.33-31.98 32v20.84C118.56 68.1 64.08 130.3 64.08 208c0 102.3-36.15 133.53-55.47 154.29-6 6.45-8.66 14.16-8.61 21.71.11 16.4 12.98 32 32.1 32h383.8c19.12 0 32-15.6 32.1-32 .05-7.55-2.61-15.27-8.61-21.71zM67.53 368c21.22-27.97 44.42-74.33 44.53-159.42 0-.2-.06-.38-.06-.58 0-61.86 50.14-112 112-112s112 50.14 112 112c0 .2-.06.38-.06.58.11 85.1 23.31 131.46 44.53 159.42H67.53zM224 512c35.32 0 63.97-28.65 63.97-64H160.03c0 35.35 28.65 64 63.97 64z">
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
        $style = ( isset( $options['box_position'] ) && $options['box_position'] == true ? 'position: absolute; right:40px;' : '' );
    }
    
    
    if ( isset( $options['wnbell_box_id_attribute'] ) && $options['wnbell_box_id_attribute'] != '' ) {
        $box_id = esc_html( $options['wnbell_box_id_attribute'] );
    } else {
        $box_id = '';
        $style = ( isset( $options['box_position'] ) && $options['box_position'] == true ? 'position: absolute; right:40px;' : '' );
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
    ?>" style="<?php 
    echo  $style ;
    ?>">
        <div class="wnbell-spinner-wrap-menu">
<span class="wnbell-spinner-menu"></span>
</div>
    <div class="wnbell-dropdown-menu" id="wnbell-dropdown-menu-id"></div>
    </div></div>
    </li>

    	<?php 
    wnbell_menu_script_lo( $interval );
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function wnbell_notification_list_visitor_menu( $view = '' )
{
    $options = get_option( 'wnbell_options' );
    $output = '';
    $unseen_notification = 0;
    
    if ( $view === 'yes' ) {
        $limit = filter_var( apply_filters( 'wnbell_notifications_display_count', 5 ), FILTER_SANITIZE_NUMBER_INT );
        global  $wpdb ;
        $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\nLEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\nLEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n            WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            AND (prole.user_role IS NULL OR prole.user_role LIKE 'all' OR prole.user_role LIKE 'wnbell_guest')\r\n            AND (pname.usernames IS NULL)\r\n            GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT {$limit};";
        $query = apply_filters( "wnbell_select_notifications_visitor", $query, $options );
        //$sql = $wpdb->prepare($query, $username);
        $data = $wpdb->get_results( $query, ARRAY_A );
        $trigger_array = array();
        // $current_user_id = get_current_user_id();
        $output .= wnbell_output_menu_bell_lo( $options, $data, $trigger_array );
        
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
                $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\n            LEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\n            LEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n                WHERE posts.post_type = 'wnbell_notifications'\r\n                AND posts.post_status = 'publish'\r\n                AND posts.ID>%d\r\n                AND (prole.user_role IS NULL OR prole.user_role LIKE 'all' OR prole.user_role LIKE 'wnbell_guest')\r\n                AND (pname.usernames IS NULL) ";
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
                $query = "SELECT posts.ID FROM {$wpdb->prefix}posts AS posts\r\n            LEFT JOIN {$wpdb->prefix}wnbell_recipients_role AS prole ON (posts.ID=prole.notification_id)\r\n            LEFT JOIN {$wpdb->prefix}wnbell_recipients AS pname ON (posts.ID=pname.notification_id)\r\n                WHERE posts.post_type = 'wnbell_notifications'\r\n                AND posts.post_status = 'publish'\r\n                AND (prole.user_role IS NULL OR prole.user_role LIKE 'all' OR prole.user_role LIKE 'wnbell_guest')\r\n                AND (pname.usernames IS NULL) ";
                $query .= apply_filters( "wnbell_conditions_new_visitor_count", '' );
                $query .= " GROUP BY posts.ID ORDER BY posts.post_date DESC LIMIT 1;";
                //$sql = $wpdb->prepare($query, $username);
                $query = apply_filters( "wnbell_count_notifications_new_visitor", $query, $options );
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
                    //$latest_cpt = get_posts("post_type=wnbell_notifications&numberposts=1&fields=ids");
                    $latest_query = "select posts.ID from {$wpdb->prefix}posts as posts\r\n        WHERE posts.post_type = 'wnbell_notifications'\r\n            AND posts.post_status = 'publish'\r\n            ORDER BY posts.post_date DESC LIMIT 1 ";
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
    //$data = array();
    $data = array(
        'notification'        => $output,
        'unseen_notification' => $unseen_notification,
    );
    return json_encode( $data );
}

function wnbell_list_ajax_visitor_menu()
{
    check_ajax_referer( 'wnbell_ajax' );
    if ( !is_user_logged_in() ) {
        
        if ( isset( $_POST['view'] ) ) {
            
            if ( sanitize_text_field( $_POST["view"] ) != '' ) {
                // $value = time();
                $expiry = strtotime( '+1 month' );
                // setcookie("wnbell_last_seen", $value, $expiry, COOKIEPATH, COOKIE_DOMAIN);
                setcookie(
                    "wnbell_last_count",
                    0,
                    $expiry,
                    COOKIEPATH,
                    COOKIE_DOMAIN
                );
                $data = wnbell_notification_list_visitor_menu( 'yes' );
            } else {
                $data = wnbell_notification_list_visitor_menu();
            }
            
            echo  $data ;
        }
    
    }
    die;
}

function wnbell_menu_script_lo( $interval )
{
    // $nonce = wp_create_nonce('wnbell_ajax');
    ob_start();
    wnbell_menu_script_lo_s( $interval );
    $output = ob_get_contents();
    ob_end_clean();
    echo  $output ;
    return;
}

function wnbell_menu_script_lo_s( $interval )
{
    $nonce = wp_create_nonce( 'wnbell_ajax' );
    ob_start();
    ?>
    <script type='text/javascript'>
function wnbell_menu_load_unseen_notification_lo(view = '') {
    jQuery.ajax({
        type: "POST",
        url: ajax_url,
        data: {
            action: 'wnbell_list_ajax_visitor_menu',
            _ajax_nonce:"<?php 
    echo  $nonce ;
    ?>",
            view: view
        },
        dataType: 'JSON',
        success: function (data) {
            if (typeof wnbell_interval_menu == 'undefined' || !wnbell_interval_menu) {
                wnbell_interval_menu = setInterval(function () {
                    wnbell_menu_load_unseen_notification_lo();
                }, <?php 
    echo  $interval ;
    ?>);
            }
            //jQuery('.wnbell-dropdown-menu').html(data.notification);
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
}

            if (data.notification != '' || data.unseen_notification > 0) {
                clearInterval(wnbell_interval_menu);
                wnbell_interval_menu = null;
            }
            if (data.unseen_notification > 0 && data.notification == '') {
                if(!jQuery('.wnbell-count-menu').hasClass('wnbell-dot-menu')){
                    jQuery('.wnbell-count-menu').addClass('wnbell-dot-menu');
            }

            }
        }
    });
};
var wnb_mediaQuery = window.matchMedia('(max-width: 768px)');

jQuery(document).ready(function () {
    wnbell_menu_load_unseen_notification_lo();
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
            wnbell_menu_load_unseen_notification_lo('yes');
        }
        jQuery('.wnbell-count-menu').removeClass('wnbell-dot-menu');
    });
    jQuery('html').click(function (e) {
        if (!jQuery(e.target).parent().hasClass('wnbell-dropdown-menu')) {
            if (jQuery('.wnbell-dropdown-menu').html() != '') {
                jQuery(".wnbell-spinner").removeClass("wnbell-active-spinner");
                jQuery('.wnbell-spinner').hide();
                jQuery('.wnbell-dropdown-menu-wrap').hide();
                jQuery('.wnbell-dropdown-menu').html('');
                wnbell_menu_load_unseen_notification_lo();
            }
        }

    });
    jQuery(document).on('click', '.wnbell-closebtn-menu', function (e) {
        jQuery('.wnbell_dropdown_list_ss').html('');
        jQuery('.wnbell-dropdown-menu').html('');
        jQuery('.wnbell_dropdown_list_ss').hide();
        jQuery('.wnbell-dropdown-menu-wrap').hide();
        wnbell_menu_load_unseen_notification_lo();
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
