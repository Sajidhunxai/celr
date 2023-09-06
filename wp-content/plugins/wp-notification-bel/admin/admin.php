<?php

defined( 'ABSPATH' ) || exit;
add_action( 'admin_menu', 'wnbell_admin_menu' );
add_action( 'admin_init', 'wnbell_admin_init' );
//add_action('admin_enqueue_scripts', 'wnbell_adding_scripts');
add_action(
    'save_post',
    'wnbell_add_notification_fields',
    10,
    2
);
add_filter( 'manage_edit-wnbell_notifications_columns', 'wnbell_add_columns' );
add_action( 'manage_wnbell_notifications_posts_custom_column', 'wnbell_populate_columns' );
add_action( 'add_meta_boxes_post', 'wnbell_post_meta_box' );
add_action(
    'add_meta_boxes',
    'wnbell_add_custom_meta_boxes',
    10,
    2
);
add_action(
    'save_post_post',
    'wnbell_add_post_notification_fields',
    10,
    2
);
add_action( 'admin_head-edit.php', 'wnbell_edit_post_change_title_in_list' );
//add_action('trash_post', 'wnbell_trash_post');
add_action( 'trashed_post', 'wnbell_trash_post' );
function wnbell_admin_menu()
{
    $settings_page = add_submenu_page(
        'edit.php?post_type=wnbell_notifications',
        'Settings',
        'Settings',
        'manage_options',
        'wnbell-sub-menu',
        'wnbell_main'
    );
    $settings_page_noedit = add_submenu_page(
        null,
        'Settings',
        'Settings',
        'manage_options',
        'wp-notification-bell',
        'wnbell_main'
    );
    add_meta_box(
        'wnbell_notification_details_meta_box',
        'Notification Details',
        'wnbell_add_new_menu',
        'wnbell_notifications',
        'normal',
        'high'
    );
    //to load scripts on a single admin page
    add_action( 'load-' . $settings_page, 'wnbell_load_admin_js' );
    add_action( 'load-' . $settings_page_noedit, 'wnbell_load_admin_js' );
}

function wnbell_adding_scripts()
{
    $custom_js_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'settings_script.js' ) );
    wp_enqueue_script(
        'wnbell_admin_script',
        plugin_dir_url( __FILE__ ) . 'settings_script.js',
        array( 'jquery' ),
        $custom_js_ver
    );
    // $custom_css_ver = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'stylesheet.css'));
    // wp_enqueue_style('admin_style', plugin_dir_url(__FILE__) . 'stylesheet.css', array(), $custom_css_ver);
    wp_register_style( 'wnbell_admin_style', plugin_dir_url( __FILE__ ) . 'stylesheet.css' );
    wp_enqueue_style( 'wnbell_admin_style' );
    // $translation_vars = array('string_val' => __('Add'));
    // wp_localize_script('admin_script', 'javascript_object', $translation_vars);
}

function wnbell_load_admin_js()
{
    add_action( 'admin_enqueue_scripts', 'wnbell_adding_scripts' );
}

function wnbell_admin_init()
{
    add_action( 'admin_post_save_wnbell_options', 'process_wnbell_options' );
    add_action( 'delete_post', 'wnbell_delete_post_data', 10 );
}

function process_wnbell_options()
{
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( 'Not allowed' );
    }
    check_admin_referer( 'wnbell' );
    // Retrieve original plugin options array
    $options = get_option( 'wnbell_options' );
    $settings = get_option( 'wnbell_settings' );
    $notif_options = get_option( 'wnbell_notif_options' );
    //$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    
    if ( isset( $_POST['remove'] ) && sanitize_text_field( $_POST['remove'] ) == "Remove" ) {
        
        if ( sanitize_text_field( $_POST['wnbell_get_tab'] ) === 'item' ) {
            //delete options last
            foreach ( $options as $key => &$value ) {
                if ( $key == 'wnbell_name' || $key == 'wnbell_default_value' || $key == 'wnbell_id_attribute' || $key == 'wnbell_class_attribute' ) {
                    if ( count( $value ) > 1 ) {
                        unset( $value[count( $value ) - 1] );
                    }
                }
            }
        } elseif ( sanitize_text_field( $_POST['wnbell_get_tab'] ) === 'wc_order_updates' ) {
            foreach ( $notif_options as $key => &$value ) {
                if ( $key == 'wcou_name' || $key == 'wcou_default_value' || $key == 'wcou_class_att' || $key == 'wcou_id_att' ) {
                    if ( count( $value ) > 1 ) {
                        unset( $value[count( $value ) - 1] );
                    }
                }
            }
        }
    
    } else {
        
        if ( isset( $_POST['wnbell_get_tab'] ) ) {
            $tab = sanitize_text_field( $_POST['wnbell_get_tab'] );
        } else {
            $tab = 'general';
        }
        
        switch ( $tab ) {
            case 'general':
                foreach ( array(
                    'wnbell_box_id_attribute',
                    'wnbell_box_class_attribute',
                    'menu_location',
                    'item_unseen_id_attribute',
                    'item_unseen_class_attribute',
                    'item_seen_id_attribute',
                    'item_seen_class_attribute',
                    'item_lo_id_attribute',
                    'item_lo_class_attribute'
                ) as $option_name ) {
                    if ( isset( $_POST[$option_name] ) ) {
                        $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
                    }
                }
                foreach ( array( 'menu_position', 'server_call_interval' ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) && (is_int( $_POST[$option_name] ) || ctype_digit( $_POST[$option_name] )) && (int) $_POST[$option_name] > 0 ) {
                        $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
                    } else {
                        if ( isset( $_POST[$option_name] ) && $_POST[$option_name] == '' ) {
                            $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
                        }
                    }
                
                }
                foreach ( array(
                    'box_position_top',
                    'box_position_bottom',
                    'box_position_left',
                    'box_position_right'
                ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) && (is_int( intval( $_POST[$option_name] ) ) || ctype_digit( intval( $_POST[$option_name] ) )) ) {
                        $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
                    } else {
                        if ( isset( $_POST[$option_name] ) && $_POST[$option_name] == '' ) {
                            $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
                        }
                    }
                
                }
                foreach ( array( 'wnbell_bell_icon', 'header', 'no_notifs' ) as $option_name ) {
                    if ( isset( $_POST[$option_name] ) ) {
                        $options[$option_name] = base64_encode( $_POST[$option_name] );
                    }
                }
                foreach ( array( 'box_position', 'bell_menu', 'bell_menu_lo' ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) && sanitize_text_field( $_POST[$option_name] ) === '0' ) {
                        $options[$option_name] = true;
                    } else {
                        $options[$option_name] = false;
                    }
                
                }
                foreach ( array(
                    'floating',
                    'floating_lo',
                    'menu_badge',
                    'menu_badge_lo'
                ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) && sanitize_text_field( $_POST[$option_name] ) === '0' ) {
                        $settings[$option_name] = true;
                    } else {
                        $settings[$option_name] = false;
                    }
                
                }
                break;
            case 'item':
                foreach ( array(
                    'wnbell_name',
                    'wnbell_default_value',
                    'wnbell_id_attribute',
                    'wnbell_class_attribute'
                ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) ) {
                        $options[$option_name] = filter_var_array( $_POST[$option_name], FILTER_SANITIZE_STRING );
                        for ( $i = 0 ;  $i < count( $_POST['wnbell_name'] ) ;  $i++ ) {
                            if ( $_POST['wnbell_name'][$i] == "" ) {
                                $options['wnbell_name'][$i] = 'default';
                            }
                        }
                    }
                
                }
                break;
            case 'wc_order_updates':
                break;
            case 'image':
                foreach ( array( 'image_width', 'image_height' ) as $option_name ) {
                    if ( isset( $_POST[$option_name] ) ) {
                        $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
                    }
                }
                foreach ( array( 'img_position' ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) && sanitize_text_field( $_POST[$option_name] ) === '0' ) {
                        $options[$option_name] = true;
                    } else {
                        $options[$option_name] = false;
                    }
                
                }
                break;
            case 'post':
                foreach ( array( 'enable_new_post', 'enable_new_custom_post_type' ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) && sanitize_text_field( $_POST[$option_name] ) === '0' ) {
                        $options[$option_name] = true;
                    } else {
                        $options[$option_name] = false;
                    }
                
                }
                foreach ( array( 'custom_post_type', 'meta_box_context' ) as $option_name ) {
                    if ( isset( $_POST[$option_name] ) ) {
                        $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
                    }
                }
                break;
            case 'triggers':
                foreach ( array(
                    'enable_new_comment',
                    'enable_new_post',
                    'enable_new_bbpress_reply',
                    'enable_new_woocommerce',
                    'enable_new_custom_post_type',
                    'enable_bbpress_engaged',
                    'enable_bp'
                ) as $option_name ) {
                    
                    if ( isset( $_POST[$option_name] ) && sanitize_text_field( $_POST[$option_name] ) === '0' ) {
                        $options[$option_name] = true;
                    } else {
                        $options[$option_name] = false;
                    }
                
                }
                break;
        }
    }
    
    update_option( 'wnbell_options', $options );
    update_option( 'wnbell_settings', $settings );
    $url_parameters = ( isset( $_POST['wnbell_get_tab'] ) ? 'message=1&tab=' . sanitize_text_field( $_POST['wnbell_get_tab'] ) : 'message=1' );
    wp_redirect( admin_url( 'edit.php?post_type=wnbell_notifications&page=wnbell-sub-menu&' . $url_parameters ) );
}

function wnbell_add_new_menu( $notification )
{
    wp_nonce_field( 'wnbell_new_notification_box', 'wnbell_new_notification_box_nonce' );
    $options = get_option( 'wnbell_options' );
    $length = sizeof( ( isset( $options['wnbell_name'] ) ? $options['wnbell_name'] : array() ) );
    $value = '';
    ?>
    <div id="wnbell-general" class="frap">
    <div id="items">
    <table class="form-table">
    <?php 
    for ( $i = 0 ;  $i < $length ;  $i++ ) {
        if ( isset( $options['wnbell_name'][$i] ) && !empty($options['wnbell_name'][$i]) ) {
            $value = get_post_meta( $notification->ID, 'wnbell_item_name_' . $i, true );
        }
        if ( !$value ) {
            
            if ( isset( $options['wnbell_default_value'][$i] ) && !empty($options['wnbell_default_value'][$i]) ) {
                $value = $options['wnbell_default_value'][$i];
            } else {
                $value = '';
            }
        
        }
        ?>
    <tr style="vertical-align:bottom">
    <th scope="row"><?php 
        echo  esc_html( ( isset( $options['wnbell_name'][$i] ) ? $options['wnbell_name'][$i] : 'Default' ) ) ;
        ?></th>
    <td>
       <input type="text" name=<?php 
        echo  esc_html( ( isset( $options['wnbell_name'][$i] ) ? 'wnbell_item_name_' . $i : '' ) ) ;
        ?>
    value="<?php 
        //echo esc_html($default_value) ?? '';
        printf( __( '%s', 'wp-notification-bell' ), esc_html( $value ) );
        ?>"/>
   </td>
    </tr>
    <?php 
    }
    if ( $length == 0 ) {
        ?>
<td colspan="2">Create a field in Settings => Notification item</td>
<?php 
    }
    ?>
 <tr style="vertical-align:bottom">
    <th scope="row"><?php 
    _e( 'Url', 'wp-notification-bell' );
    ?></th>
    <td>
        <?php 
    $get_link = get_post_meta( $notification->ID, 'wnbell_link', true );
    ?>
       <input type="text" name="link"
    value="<?php 
    echo  ( isset( $get_link ) && $get_link != false ? $get_link : '' ) ;
    ?>"/>
   </td>
    </tr>
    <tr style="vertical-align:bottom">
    <th scope="row"><?php 
    _e( 'Recipient role', 'wp-notification-bell' );
    ?></th>
    <td>
 <select name="recipient_role">
   <?php 
    $roles = wp_roles();
    global  $wpdb ;
    $query_recipient = "SELECT user_role\r\n    FROM  {$wpdb->prefix}wnbell_recipients_role\r\n        WHERE notification_id = %d";
    $recipient_role = $wpdb->get_var( $wpdb->prepare( $query_recipient, $notification->ID ) );
    //$recipients = get_post_meta($notification_id, 'wnbell_recipient_role', true);
    $recipient = ( isset( $recipient_role ) && $recipient_role != false ? $recipient_role : '' );
    echo  '<option value="' . 'all' . '" ' ;
    selected( $recipient, 'all' );
    echo  '>' . 'Everyone' ;
    foreach ( $roles->roles as $role => $name_array ) {
        echo  '<option value="' . $role . '" ' ;
        selected( $recipient, $role );
        echo  '>' . $name_array['name'] ;
    }
    ?>
</select>
 </td>
    </tr>
    <tr style="vertical-align:bottom">
    <th scope="row"><?php 
    _e( 'Recipient username', 'wp-notification-bell' );
    ?></th>
    <td>
        <?php 
    global  $wpdb ;
    $table_name = $wpdb->prefix . 'wnbell_recipients';
    $sql_recipients = "SELECT usernames FROM {$table_name} WHERE notification_id=%d LIMIT 1";
    $sql_recipients = $wpdb->prepare( $sql_recipients, $notification->ID );
    $results = $wpdb->get_results( $sql_recipients, ARRAY_A );
    $recipients = ( count( $results ) > 0 ? unserialize( $results[0]['usernames'] ) : false );
    //$recipients = get_post_meta($notification->ID, 'wnbell_recipient_username', true);
    $recipients = ( isset( $recipients ) && $recipients != false ? implode( ', ', $recipients ) : '' );
    if ( $recipients == "0" ) {
        $recipients = '';
    }
    ?>
       <input type="text" name="recipient_username"
    value="<?php 
    echo  $recipients ;
    ?>"/>
    <p class="description">Comma-separated list of usernames to target users (optional)</p>
   </td>
    </tr>
    <tr style="vertical-align:bottom">
    <th scope="row"><?php 
    _e( 'Notification title', 'wp-notification-bell' );
    ?></th>
    <td>
        <?php 
    $get_title = get_post_meta( $notification->ID, 'wnbell_title', true );
    ?>
       <input type="text" name="wnbell_title"
    value="<?php 
    echo  ( isset( $get_title ) && $get_title != false ? $get_title : '' ) ;
    ?>"/>
    <p class="description">Notification title for the admin page (optional)</p>
   </td>
    </tr>
    </table>
    </div>
<?php 
}

function wnbell_add_notification_fields( $notification_id, $notification )
{
    // if (!current_user_can('edit_posts')) {
    //     wp_die('Not allowed');
    // }
    do_action( 'wnbell_custom_post_type_notification', $notification_id, $notification );
    
    if ( $notification->post_type == 'wnbell_notifications' && ($notification->post_status == 'publish' || $notification->post_status == 'draft') ) {
        // Check if our nonce is set.
        if ( !isset( $_POST['wnbell_new_notification_box_nonce'] ) ) {
            return $post_id;
        }
        $nonce = $_POST['wnbell_new_notification_box_nonce'];
        // Verify that the nonce is valid.
        if ( !wp_verify_nonce( $nonce, 'wnbell_new_notification_box' ) ) {
            return $post_id;
        }
        if ( !current_user_can( 'edit_posts' ) ) {
            wp_die( 'Not allowed' );
        }
        $options = get_option( 'wnbell_options' );
        $length = sizeof( ( isset( $options['wnbell_name'] ) ? $options['wnbell_name'] : array() ) );
        $args = array(
            'fields' => 'all',
        );
        for ( $i = 0 ;  $i < $length ;  $i++ ) {
            // if (isset($_POST[$options['wnbell_name'][$i]]) && sanitize_text_field($_POST[$options['wnbell_name'][$i]]) != '') {
            //     update_post_meta($notification_id, 'wnbell_item_name_' . $i,
            //         sanitize_text_field($_POST[$options['wnbell_name'][$i]]));
            // if (isset($_POST['wnbell_item_name_' . $i]) && sanitize_text_field($_POST['wnbell_item_name_' . $i]) != '') {
            if ( isset( $_POST['wnbell_item_name_' . $i] ) ) {
                update_post_meta( $notification_id, 'wnbell_item_name_' . $i, sanitize_text_field( $_POST['wnbell_item_name_' . $i] ) );
            }
        }
        if ( isset( $_POST['link'] ) ) {
            update_post_meta( $notification_id, 'wnbell_link', sanitize_text_field( $_POST['link'] ) );
        }
        if ( isset( $_POST['wnbell_title'] ) ) {
            update_post_meta( $notification_id, 'wnbell_title', sanitize_text_field( $_POST['wnbell_title'] ) );
        }
        if ( isset( $_POST['recipient_role'] ) && sanitize_text_field( $_POST['recipient_role'] ) != '' && sanitize_text_field( $_POST['recipient_role'] ) != 'all' ) {
            update_post_meta( $notification_id, 'wnbell_recipient_role', sanitize_text_field( $_POST['recipient_role'] ) );
        }
        
        if ( isset( $_POST['recipient_username'] ) ) {
            $recipient_username = sanitize_text_field( $_POST['recipient_username'] );
            $recipient_username = trim( $recipient_username );
            //$recipient_username = preg_replace('/\s+/', '', $recipient_username);
            $recipient_username = preg_replace( '/\\s*,\\s*/', ',', $recipient_username );
            $recipient_username = explode( ',', $recipient_username );
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'wnbell_recipients';
            
            if ( sanitize_text_field( $_POST['recipient_username'] ) == "" ) {
                $recipient_username = array( "0" );
                // update_post_meta($notification_id, 'wnbell_recipient_username',
                //     $recipient_username);
                //delete
                $wpdb->delete( $table_name, array(
                    'notification_id' => $notification_id,
                ) );
            } else {
                update_post_meta( $notification_id, 'wnbell_recipient_username', $recipient_username );
                if ( !$wpdb->update(
                    $table_name,
                    array(
                    'usernames' => serialize( $recipient_username ),
                ),
                    array(
                    'notification_id' => $notification_id,
                ),
                    array( '%s' )
                ) ) {
                    $wpdb->insert( $table_name, array(
                        'notification_id' => $notification_id,
                        'usernames'       => serialize( $recipient_username ),
                    ) );
                }
            }
        
        }
        
        $created = new DateTime( $notification->post_date_gmt );
        $modified = new DateTime( $notification->post_modified_gmt );
        $diff = $created->diff( $modified );
        $seconds_difference = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i) * 60 + $diff->s;
        $increment = false;
        if ( $seconds_difference <= 1 ) {
            $increment = true;
        }
        
        if ( $notification->post_status == 'publish' ) {
            
            if ( isset( $_POST['recipient_role'] ) && sanitize_text_field( $_POST['recipient_role'] ) != '' && sanitize_text_field( $_POST['recipient_role'] ) != 'all' ) {
                update_post_meta( $notification_id, 'wnbell_recipient_role', sanitize_text_field( $_POST['recipient_role'] ) );
                //try
                global  $wpdb ;
                $table_name = $wpdb->prefix . 'wnbell_recipients_role';
                $recipient_role = sanitize_text_field( $_POST['recipient_role'] );
                $n_id = $notification_id;
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
                $wpdb->delete( $table_name, array(
                    'notification_id' => $notification_id,
                ) );
            }
            
            if ( isset( $_POST['recipient_username'] ) && sanitize_text_field( $_POST['recipient_username'] ) !== "" ) {
                
                if ( apply_filters( 'wnbell_increment_count_on_post_update', $increment, $notification_id ) ) {
                    $blogusers = get_users( $args );
                    foreach ( $blogusers as $key => $user ) {
                        $value = get_user_meta( $user->ID, 'wnbell_unseen', true );
                        if ( !$value ) {
                            $value = 0;
                        }
                        if ( in_array( $user->user_login, $recipient_username ) ) {
                            update_user_meta( $user->ID, 'wnbell_unseen', $value + 1 );
                        }
                        // elseif (sanitize_text_field($_POST['recipient_username']) !== "" && in_array($user->user_login, $recipient_username)
                        // ) {
                        //     update_user_meta($user->ID, 'wnbell_notification_id', $notification_id);
                        // }
                    }
                }
            
            }
        }
    
    }

}

function wnbell_add_columns( $columns )
{
    $options = get_option( 'wnbell_options' );
    $i = 0;
    if ( is_array( $options['wnbell_name'] ) ) {
        foreach ( $options['wnbell_name'] as $name ) {
            $columns['notifications_' . $name] = $name;
            $i++;
            if ( $i == 2 ) {
                break;
            }
        }
    }
    return $columns;
}

function wnbell_populate_columns( $column )
{
    $options = get_option( 'wnbell_options' );
    $i = 0;
    if ( is_array( $options['wnbell_name'] ) ) {
        foreach ( $options['wnbell_name'] as $name ) {
            
            if ( 'notifications_' . $name == $column ) {
                $field = esc_html( get_post_meta( get_the_ID(), 'wnbell_item_name_' . $i, true ) );
                //echo $field;
                printf( __( "%s", "wp-notification-bell" ), $field );
            }
            
            $i++;
            if ( $i == 2 ) {
                break;
            }
        }
    }
}

function wnbell_post_meta_box( $post )
{
    add_meta_box(
        'wnbell_post_notification_details_meta_box',
        __( 'Notification Details' ),
        'wnbell_render_post_meta_box',
        'post',
        'side',
        'default'
    );
}

function wnbell_add_custom_meta_boxes( $post_type, $post )
{
    do_action( 'wnbell_adding_custom_meta_boxes', $post_type, $post );
}

function wnbell_render_post_meta_box( $post )
{
    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'wnbell_post_custom_box', 'wnbell_post_custom_box_nonce' );
    $options = get_option( 'wnbell_options' );
    $length = sizeof( ( isset( $options['wnbell_name'] ) ? $options['wnbell_name'] : array() ) );
    $value = "";
    $notification_id = get_post_meta( $post->ID, "wnbell_notification_id", true );
    ?>
    <div id="wnbell-general" class="frap">
    <div id="items">
    <!-- <table class="form-table"> -->
    <label>
    <?php 
    _e( 'Enable new post notification', 'wp-notification-bell' );
    ?>
    <div style="margin-top:4px0padding:4px;margin:0 0 8px;;">
    <label class="wnbell_switch">
    <?php 
    
    if ( $post->post_type === 'post' ) {
        ?>
 <input type="checkbox" name="enable_new_post" value="0"
 <?php 
        checked( true, ( isset( $options['enable_new_post'] ) ? esc_html( $options['enable_new_post'] ) : false ) );
        ?> >
    <?php 
    } else {
        ?>
        <input type="checkbox" name="enable_new_custom_post_type" value="0"
 <?php 
        checked( true, ( isset( $options['enable_new_custom_post_type'] ) ? esc_html( $options['enable_new_custom_post_type'] ) : false ) );
        ?> >
        <?php 
    }
    
    ?>
    <span class="slider round"></span>
        </label></div></label>
    <?php 
    for ( $i = 0 ;  $i < $length ;  $i++ ) {
        if ( isset( $options['wnbell_name'][$i] ) && !empty($options['wnbell_name'][$i]) ) {
            $value = get_post_meta( $notification_id, 'wnbell_item_name_' . $i, true );
        }
        if ( !$value ) {
            
            if ( isset( $options['wnbell_default_value'][$i] ) && !empty($options['wnbell_default_value'][$i]) ) {
                $value = $options['wnbell_default_value'][$i];
            } else {
                $value = '';
            }
        
        }
        ?>
       <!-- <tr style="vertical-align:bottom">
    <th scope="row"> -->
    <label>
    <?php 
        echo  esc_html( ( isset( $options['wnbell_name'][$i] ) ? $options['wnbell_name'][$i] : 'Default' ) ) ;
        ?>

    <!-- <td> -->
    <div style="margin-top:4px;">
       <input type="text" name=<?php 
        echo  esc_html( ( isset( $options['wnbell_name'][$i] ) ? 'wnbell_item_name_' . $i : '' ) ) ;
        ?>
    value="<?php 
        //echo esc_html($default_value) ?? '';
        printf( __( '%s', 'wp-notification-bell' ), esc_html( $value ) );
        ?>" style="width:100%; padding:4px;margin:0 0 8px;"/>
        </div></label>
   <!-- </td>
    </tr> -->
    <?php 
    }
    ?>
 <?php 
    if ( $length == 0 ) {
        ?>
<div>Create a field in Settings => Notification item</div>
<?php 
    }
    ?>
    <!-- </table> -->
    <label><?php 
    _e( 'Recipient role', 'wp-notification-bell' );
    ?>
    <div style="margin-top:4px;">
 <select name="recipient_role">
   <?php 
    $roles = wp_roles();
    global  $wpdb ;
    $query_recipient = "SELECT user_role\r\n    FROM  {$wpdb->prefix}wnbell_recipients_role\r\n        WHERE notification_id = %d";
    $recipient_role = $wpdb->get_var( $wpdb->prepare( $query_recipient, $notification_id ) );
    //$recipients = get_post_meta($notification_id, 'wnbell_recipient_role', true);
    $recipient = ( isset( $recipient_role ) && $recipient_role != false ? $recipient_role : '' );
    echo  '<option value="' . 'all' . '" ' ;
    selected( $recipient, 'all' );
    echo  '>' . 'Everyone' ;
    foreach ( $roles->roles as $role => $name_array ) {
        echo  '<option value="' . $role . '" ' ;
        selected( $recipient, $role );
        echo  '>' . $name_array['name'] ;
    }
    ?>
</select>
</div>
 </label>

    </div>
    </div>
<?php 
}

function wnbell_add_post_notification_fields( $post_id, $post )
{
    // if (!current_user_can('edit_posts')) {
    //     wp_die('Not allowed');
    // }
    if ( 'post' !== $post->post_type ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $post->post_status ) && 'auto-draft' == $post->post_status ) {
        return;
    }
    // Check if our nonce is set.
    
    if ( !isset( $_POST['wnbell_post_custom_box_nonce'] ) ) {
        wnbell_add_post_default( $post_id, $post, false );
        return $post_id;
    }
    
    $nonce = $_POST['wnbell_post_custom_box_nonce'];
    // Verify that the nonce is valid.
    if ( !wp_verify_nonce( $nonce, 'wnbell_post_custom_box' ) ) {
        return $post_id;
    }
    if ( !current_user_can( 'edit_posts' ) ) {
        wp_die( 'Not allowed' );
    }
    
    if ( $post->post_status == 'publish' || $post->post_status == 'draft' ) {
        $options = get_option( 'wnbell_options' );
        //  if(isset($_POST['enable_new_post']) && $_POST['enable_new_post'])
        if ( !isset( $_POST['enable_new_post'] ) ) {
            return;
        }
        //used to save 'enable' only from true to false
        // else if (sanitize_text_field($_POST['enable_new_post']) == '1') {
        //     if (isset($options['enable_new_post']) && $options['enable_new_post'] == false) {
        //         return;
        //     }
        // }
        if ( !apply_filters( 'wnbell_restrict_post_term', true, $post_id ) ) {
            return;
        }
        foreach ( array( 'enable_new_post' ) as $option_name ) {
            
            if ( isset( $_POST[$option_name] ) && sanitize_text_field( $_POST[$option_name] ) === '0' ) {
                $options[$option_name] = true;
            } else {
                $options[$option_name] = false;
            }
        
        }
        update_option( 'wnbell_options', $options );
        if ( get_option( 'wnbell_options' )['enable_new_post'] == false ) {
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
            // if (isset($_POST['wnbell_item_name_' . $i]) && sanitize_text_field($_POST['wnbell_item_name_' . $i]) != '') {
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
            //try
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
        
        if ( !$notification_exists ) {
            update_post_meta( $notification_id, 'wnbell_link', get_post_permalink( $post_id ) );
        } else {
            update_post_meta( $notification_exists, 'wnbell_link', get_post_permalink( $post_id ) );
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
    'transition_post_status',
    'wnbell_first_publish_time_register',
    10,
    3
);
function wnbell_first_publish_time_register( $new, $old, $post )
{
    
    if ( $new == 'publish' && $old != 'publish' && $post->post_type == 'wnbell_notifications' ) {
        $firstPublishTime = get_post_meta( $post->ID, 'first_publish_time', true );
        
        if ( empty($firstPublishTime) ) {
            // First time the post is publish, register the time
            add_post_meta(
                $post->ID,
                'first_publish_time',
                time(),
                true
            );
            $time = strtotime( "now" );
            $my_post = array(
                'ID'            => $post->ID,
                'post_date'     => date( 'Y-m-d H:i:s', $time ),
                'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $time ),
            );
            wp_update_post( $my_post );
        }
    
    }

}

function wnbell_edit_post_change_title_in_list()
{
    global  $post_type ;
    if ( $post_type == 'wnbell_notifications' ) {
        add_filter(
            'the_title',
            'wnbell_construct_new_title',
            100,
            2
        );
    }
}

function wnbell_construct_new_title( $title, $id )
{
    $post_id = get_post_meta( $id, "post_id", true );
    //$post_title = "#".$id;
    
    if ( $post_id ) {
        global  $wpdb ;
        $post = $wpdb->get_results( $wpdb->prepare( "SELECT post_title FROM {$wpdb->posts} WHERE ID = '%d'", $post_id ) );
        $post_title = $post[0]->post_title;
    } else {
        $get_title = get_post_meta( $id, "wnbell_title", true );
        if ( $get_title ) {
            $post_title = $get_title;
        }
    }
    
    // if(strlen($post_title)==0){
    //     $post_title="no title";
    // }
    return $post_title;
}

function wnbell_delete_post_data( $pid )
{
    
    if ( get_post_type( $pid ) == 'wnbell_notifications' ) {
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'wnbell_recipients_role';
        $wpdb->delete( $table_name, array(
            'notification_id' => $pid,
        ) );
        $table_name = $wpdb->prefix . 'wnbell_recipients';
        $wpdb->delete( $table_name, array(
            'notification_id' => $pid,
        ) );
    } else {
    }

}

function wnbell_trash_post( $post_id )
{
}
