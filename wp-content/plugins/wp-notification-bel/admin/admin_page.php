<?php

defined( 'ABSPATH' ) || exit;
function wnbell_main()
{
    $options = get_option( 'wnbell_options' );
    $settings = get_option( 'wnbell_settings' );
    if ( isset( $_GET['message'] ) && sanitize_text_field( $_GET['message'] ) == '1' ) {
        ?>
 <div id='message' class='updated fade'><p><strong>Settings
 Saved</strong></p></div>
<?php 
    }
    ?>
    <div id="wnbell-general" class="frap">
    <h2><?php 
    _e( 'WP Notification Bell', 'wp-notification-bell' );
    ?></h2>
    <?php 
    
    if ( isset( $_GET['tab'] ) ) {
        wnbell_admin_tabs( sanitize_text_field( $_GET['tab'] ) );
    } else {
        wnbell_admin_tabs( 'general' );
    }
    
    ?>
    <form method="post" action="admin-post.php">
    <input type="hidden" name="action" value="save_wnbell_options" />
    <!-- Adding security through hidden referrer field -->
    <?php 
    wp_nonce_field( 'wnbell' );
    ?>
    <?php 
    
    if ( isset( $_GET['tab'] ) ) {
        $tab = sanitize_text_field( $_GET['tab'] );
    } else {
        $tab = 'general';
    }
    
    ?>
    <input type="hidden" name="wnbell_get_tab" value="<?php 
    echo  esc_html( $tab ) ;
    ?>" />
     <?php 
    switch ( $tab ) {
        case 'general':
            ?> <table class="form-table">
 <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Server call interval', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="server_call_interval"
    value="<?php 
            echo  esc_html( ( isset( $options['server_call_interval'] ) ? $options['server_call_interval'] : '' ) ) ;
            ?>"/>
    <p class="description">Ajax call interval to get notification count in seconds, by default the interval is 600 seconds (10min) (optional)</p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;"><?php 
            _e( 'Display Options', 'wp-notification-bell' );
            ?></h2></th>
        </tr>
        <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;text-decoration: underline;"><h2 style="font-size: 1.1em;"><?php 
            _e( '1. Add to a menu', 'wp-notification-bell' );
            ?></h2></th>
        </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Add bell icon to menu for logged-in users', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="bell_menu" value="0"
 <?php 
            checked( true, ( isset( $options['bell_menu'] ) ? esc_html( $options['bell_menu'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable to add notification at the end of the menu in the location selected below.</p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Add bell icon to menu for visitors', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="bell_menu_lo" value="0"
 <?php 
            checked( true, ( isset( $options['bell_menu_lo'] ) ? esc_html( $options['bell_menu_lo'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable to add notification at the end of the menu in the location selected below.
        The plugin uses cookies when enabling this option.
        </p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;text-decoration: underline;"><h2 style="font-size: 1.1em;"><?php 
            _e( '2. Add a floating icon', 'wp-notification-bell' );
            ?></h2></th>
        </tr>
        <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Add floating bell icon for logged-in users', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="floating" value="0"
 <?php 
            checked( true, ( isset( $settings['floating'] ) ? esc_html( $settings['floating'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable to add the notification bell icon on all logged-in pages.</p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Add floating bell icon for visitors', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="floating_lo" value="0"
 <?php 
            checked( true, ( isset( $settings['floating_lo'] ) ? esc_html( $settings['floating_lo'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable to add the notification bell icon on all logged out pages.
        The plugin uses cookies when enabling this option.
        </p>
   </td>
   </tr>
        <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;text-decoration: underline;"><h2 style="font-size: 1.1em;"><?php 
            _e( '3. Add a shortcode', 'wp-notification-bell' );
            ?></h2></th>
        </tr>
        <tr style="vertical-align:bottom">
   <td colspan="2" style="margin:0;padding:0;"><p style="font-size: 1em;">Add the shortcode <b>[wp-notification-bell]</b> or <b>[wp-notification-bell-logged-out]</b> manually.</p></td>
        </tr>
        <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;text-decoration: underline;"><h2 style="font-size: 1.1em;"><?php 
            _e( '4. Add badge count to existing menu item', 'wp-notification-bell' );
            ?></h2></th>
        </tr>
        <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Add badge to menu item for logged-in users', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="menu_badge" value="0"
 <?php 
            checked( true, ( isset( $settings['menu_badge'] ) ? esc_html( $settings['menu_badge'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable to add a badge on a menu item on all logged-in pages.
            Then add these css classes to your menu item 'wnbell-menu-item wnbell-dropdown-toggle-menu'.
            <a href="https://wpsimpleplugins.wordpress.com/2021/10/28/how-to-add-badge-count-to-menu-item/">Read more</a>
        </p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Add badge to menu item for visitors', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="menu_badge_lo" value="0"
 <?php 
            checked( true, ( isset( $settings['menu_badge_lo'] ) ? esc_html( $settings['menu_badge_lo'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable to add a badge on a menu item on all logged out pages.
        Then add these css classes to your menu item 'wnbell-menu-item wnbell-dropdown-toggle-menu'.
        <a href="https://wpsimpleplugins.wordpress.com/2021/10/28/how-to-add-badge-count-to-menu-item/">Read more</a>
        The plugin uses cookies when enabling this option.
        </p>
   </td>
   </tr>
        <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;"><?php 
            _e( 'More Options', 'wp-notification-bell' );
            ?></h2></th>
        </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Drop-down box position', 'wp-notification-bell' );
            ?></th>
               <td>
 <label style="margin-right: 4em;">
 <input type="radio" name="box_position" value="0"
 <?php 
            checked( true, ( isset( $options['box_position'] ) ? $options['box_position'] : false ) );
            ?>>Left</input></label>
    <label><input type="radio" name="box_position" value="1"
 <?php 
            checked( false, ( isset( $options['box_position'] ) ? $options['box_position'] : false ) );
            ?>>Right</input></label>
            <p class="description">Position of drop-down box relative to bell icon</p>
</td>
   </tr>
   <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Add to menu location', 'wp-notification-bell' );
            ?></th>
 <td>
 <select name="menu_location">
   <?php 
            // $menus = wp_get_nav_menus();
            $locations = get_nav_menu_locations();
            $options['menu_location'] = ( isset( $options['menu_location'] ) ? $options['menu_location'] : '' );
            foreach ( $locations as $location => $menu ) {
                // echo '<option value="' . $menu->term_id . '" ';
                // selected($options['menu'], $menu->term_id);
                // echo '>' . $menu->name;
                echo  '<option value="' . $location . '" ' ;
                selected( $options['menu_location'], $location );
                echo  '>' . $location ;
            }
            ?>
</select>
<p class="description">If add bell icon to menu is enabled (option 1), it will be add in this location (optional)</p>
 </td>
 </tr>
   <!-- <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            //_e('Bell icon or text', 'wp-notification-bell');
            ?></th>
    <td>
       <input type="text" name="bell_icon"
    value="<?php 
            // echo stripslashes(isset($options['bell_icon']) ? $options['bell_icon'] : '');
            ?>"/>
   </td>
    </tr> -->
    <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Bell icon or text', 'wp-notification-bell' );
            ?></th>
    <td>
    <textarea name="wnbell_bell_icon"  rows="3" cols="50" ><?php 
            $options['wnbell_bell_icon'] = ( isset( $options['wnbell_bell_icon'] ) ? $options['wnbell_bell_icon'] : '' );
            echo  stripslashes( base64_decode( $options['wnbell_bell_icon'] ) ) ;
            ?></textarea>
    <p class="description">Text or svg element to replace bell icon (optional)</p>
            </td>
             </tr>
             <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Box header', 'wp-notification-bell' );
            ?></th>
    <td>
    <input type="text" name="header"
    value="<?php 
            echo  esc_html( ( isset( $options['header'] ) ? stripslashes( base64_decode( $options['header'] ) ) : '' ) ) ;
            ?>"/>
            <p class="description">Html or text as title in the notification box (optional)</p>
            </td>
             </tr>
             <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Empty notification box text', 'wp-notification-bell' );
            ?></th>
    <td>
    <input type="text" name="no_notifs"
    value="<?php 
            echo  esc_html( ( isset( $options['no_notifs'] ) ? stripslashes( base64_decode( $options['no_notifs'] ) ) : '' ) ) ;
            ?>"/>
            <p class="description">Html or text to show in an empty notification box (optional)</p>
            </td>
             </tr>

 <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Menu position', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="menu_position"
    value="<?php 
            echo  esc_html( ( isset( $options['menu_position'] ) ? $options['menu_position'] : '' ) ) ;
            ?>"/>
    <p class="description">If add bell icon to menu is enabled (option 1), it will be add in this position (optional)</p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Drop-down box position', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="box_position_top" placeholder="top" style="width:80px;margin-right:10px;"
    value="<?php 
            echo  esc_html( ( isset( $options['box_position_top'] ) ? $options['box_position_top'] : '' ) ) ;
            ?>"/>
    <input type="text" name="box_position_bottom" placeholder="bottom" style="width:80px;margin-right:10px;"
    value="<?php 
            echo  esc_html( ( isset( $options['box_position_bottom'] ) ? $options['box_position_bottom'] : '' ) ) ;
            ?>"/>
    <input type="text" name="box_position_right" placeholder="left" style="width:80px;margin-right:10px;"
    value="<?php 
            echo  esc_html( ( isset( $options['box_position_right'] ) ? $options['box_position_right'] : '' ) ) ;
            ?>"/>
    <input type="text" name="box_position_left" placeholder="right" style="width:80px;margin-right:10px;"
    value="<?php 
            echo  esc_html( ( isset( $options['box_position_left'] ) ? $options['box_position_left'] : '' ) ) ;
            ?>"/>
    <p class="description">Overrides box postion: left/right option. Leave empty for default (optional)</p>
   </td>
   </tr>
   <?php 
            ?>
   <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;">Drop-down box</h2></th>
        </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Id attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="wnbell_box_id_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['wnbell_box_id_attribute'] ) ? $options['wnbell_box_id_attribute'] : '' ) ) ;
            ?>"/>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Class attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="wnbell_box_class_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['wnbell_box_class_attribute'] ) ? $options['wnbell_box_class_attribute'] : '' ) ) ;
            ?>"/>
    <p class="description">Overrides existing css (optional)</p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;">Visited notification item (for logged-in users)</h2></th>
        </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Id attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="item_seen_id_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['item_seen_id_attribute'] ) ? $options['item_seen_id_attribute'] : '' ) ) ;
            ?>"/>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Class attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="item_seen_class_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['item_seen_class_attribute'] ) ? $options['item_seen_class_attribute'] : '' ) ) ;
            ?>"/>
    <p class="description">Overrides existing css (optional)</p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;">Non visited notification item</h2></th>
        </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Id attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="item_unseen_id_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['item_unseen_id_attribute'] ) ? $options['item_unseen_id_attribute'] : '' ) ) ;
            ?>"/>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Class attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="item_unseen_class_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['item_unseen_class_attribute'] ) ? $options['item_unseen_class_attribute'] : '' ) ) ;
            ?>"/>
    <p class="description">Overrides existing css (optional)</p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;">Notification item (Visitors)</h2></th>
        </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Id attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="item_lo_id_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['item_lo_id_attribute'] ) ? $options['item_lo_id_attribute'] : '' ) ) ;
            ?>"/>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Class attribute', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="item_lo_class_attribute"
    value="<?php 
            echo  esc_html( ( isset( $options['item_lo_class_attribute'] ) ? $options['item_lo_class_attribute'] : '' ) ) ;
            ?>"/>
    <p class="description">Overrides existing css (optional)</p>
   </td>
   </tr>
        </table>
        <?php 
            break;
        case 'item':
            //vertical
            wnbell_item_settings( $options, $tab );
            break;
        case 'wc_order_updates':
            break;
        case 'image':
            ?>
 <table class="form-table">
    <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Width', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="image_width"
    value="<?php 
            echo  esc_html( ( isset( $options['image_width'] ) ? $options['image_width'] : '' ) ) ;
            ?>"/>
   </td>
    </tr>
    <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Height', 'wp-notification-bell' );
            ?></th>
    <td>
       <input type="text" name="image_height"
       value="<?php 
            echo  esc_html( ( isset( $options['image_height'] ) ? $options['image_height'] : '' ) ) ;
            ?>"/>
   </td>
    </tr>
    <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Position', 'wp-notification-bell' );
            ?></th>
               <td>
 <label style="margin-right: 4em;">
 <input type="radio" name="img_position" value="0"
 <?php 
            checked( true, ( isset( $options['img_position'] ) ? $options['img_position'] : true ) );
            ?>>Left</input></label>
    <label><input type="radio" name="img_position" value="1"
 <?php 
            checked( false, ( isset( $options['img_position'] ) ? $options['img_position'] : true ) );
            ?>>Right</input></label>
            <p class="description">Position of image relative to other fields in a notification item</p>
</td>
   </tr>
</table>
    <?php 
            break;
        case 'post':
            ?>
            <table class="form-table">
            <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;">Posts</h2></th>
        </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Enable new post notification', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="enable_new_post" value="0"
 <?php 
            checked( true, ( isset( $options['enable_new_post'] ) ? esc_html( $options['enable_new_post'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable this option and fill the notification content in the metabox that will appear in the post editor.
        <br>To create a notification for a post published before the plugin was activated, check this option and update the post.
        </p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
   <th colspan="2" style="margin:0;padding:0;"><h2 style="font-size: 1.2em;">Custom Post Type Notifications</h2></th>
        </tr>
        <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Enable new custom post type post notification', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="enable_new_custom_post_type" value="0"
 <?php 
            checked( true, ( isset( $options['enable_new_custom_post_type'] ) ? esc_html( $options['enable_new_custom_post_type'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">Enable this option and fill the notification content in the metabox that will appear in the custom post type (selected below) post editor.
            <br>To create a notification for a post published before the plugin was activated, check this option and update the post.
        </p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Custom post type', 'wp-notification-bell' );
            ?></th>
 <td>
 <select name="custom_post_type">
   <?php 
            $args = array(
                'public'   => true,
                '_builtin' => false,
            );
            $output = 'names';
            // names or objects, note names is the default
            $operator = 'and';
            // 'and' or 'or'
            $post_types = get_post_types( $args, $output, $operator );
            $options['custom_post_type'] = ( isset( $options['custom_post_type'] ) ? $options['custom_post_type'] : '' );
            $exclude = array( 'wnbell_notifications' );
            foreach ( $post_types as $post_type ) {
                if ( true === in_array( $post_type, $exclude ) ) {
                    continue;
                }
                echo  '<option value="' . $post_type . '" ' ;
                selected( $options['custom_post_type'], $post_type );
                echo  '>' . $post_type ;
            }
            ?>
</select>
 </td>
 </tr>
 <?php 
            ?>
 <tr style="vertical-align:bottom">
    <th scope="row"><?php 
            _e( 'Meta box position', 'wp-notification-bell' );
            ?></th>
 <td>
 <select name="meta_box_context">
   <?php 
            $meta_box_contexts = array( 'normal', 'side' );
            $options['meta_box_context'] = ( isset( $options['meta_box_context'] ) ? $options['meta_box_context'] : '' );
            foreach ( $meta_box_contexts as $context ) {
                echo  '<option value="' . $context . '" ' ;
                selected( $options['meta_box_context'], $context );
                echo  '>' . $context ;
            }
            ?>
</select>
 </td>
 </tr>
    </table>
<?php 
            break;
        case 'triggers':
            ?>
<table class="form-table">
<tr style="vertical-align:bottom">
   <th scope="row"><?php 
            _e( 'Enable new comment reply notification (logged-in users)', 'wp-notification-bell' );
            ?></th>
<td>
<label  style="margin-right: 4em;" class="wnbell_switch">
<input type="checkbox" name="enable_new_comment" value="0"
<?php 
            checked( true, ( isset( $options['enable_new_comment'] ) ? esc_html( $options['enable_new_comment'] ) : false ) );
            ?>> <span class="slider round"></span>
</label>
<p class="description">Users will be notified if someone replies to one of their comments.
            And post authors will be notified if someone comments on one of their posts.
            Logged out users will be notified in premium plan.
        </p>
</td>
</tr>
<tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Enable bbPress new reply notification (logged-in users)', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="enable_new_bbpress_reply" value="0"
 <?php 
            checked( true, ( isset( $options['enable_new_bbpress_reply'] ) ? esc_html( $options['enable_new_bbpress_reply'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">A reminder to disable this option if you deactivate bbPress.
            Users will only get notified if someone replies in a topic they're subcribed to.
        </p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Enable bbPress new reply notification for any topic where user replied (logged-in users)', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="enable_bbpress_engaged" value="0"
 <?php 
            checked( true, ( isset( $options['enable_bbpress_engaged'] ) ? esc_html( $options['enable_bbpress_engaged'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">A reminder to disable this option if you deactivate bbPress.
            Users will get notified if someone replies in a topic where they posted a reply (overrides option above).
        </p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Enable BuddyPress notifications (logged-in users)', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="enable_bp" value="0"
 <?php 
            checked( true, ( isset( $options['enable_bp'] ) ? esc_html( $options['enable_bp'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">A reminder to disable this option if you deactivate BuddyPress.
        </p>
   </td>
   </tr>
   <tr style="vertical-align:bottom">
               <th scope="row"><?php 
            _e( 'Enable new WooCommerce order update notification (logged-in users)', 'wp-notification-bell' );
            ?></th>
    <td>
    <label  style="margin-right: 4em;" class="wnbell_switch">
 <input type="checkbox" name="enable_new_woocommerce" value="0"
 <?php 
            checked( true, ( isset( $options['enable_new_woocommerce'] ) ? esc_html( $options['enable_new_woocommerce'] ) : false ) );
            ?>> <span class="slider round"></span>
        </label>
        <p class="description">A reminder to disable this option if you deactivate WooCommerce</p>
   </td>
   </tr>
   </table>
   <?php 
            break;
    }
    ?>
   <?php 
    submit_button();
    ?>
</form>
<?php 
}

function wnbell_admin_tabs( $current = 'general' )
{
    $tabs = array(
        'general'  => __( 'General', "wp-notification-bell" ),
        'item'     => __( 'Notification item', "wp-notification-bell" ),
        'image'    => __( 'Featured image', "wp-notification-bell" ),
        'post'     => __( 'Posts and custom post types', "wp-notification-bell" ),
        'triggers' => __( 'User notifications', "wp-notification-bell" ),
    );
    echo  '<div id="icon-themes" class="icon32"><br></div>' ;
    echo  '<h2 class="nav-tab-wrapper">' ;
    foreach ( $tabs as $tab => $name ) {
        $class = ( $tab == $current ? 'nav-tab nav-tab-active' : "nav-tab" );
        echo  "<a class='{$class}' href='?post_type=wnbell_notifications&page=wnbell-sub-menu&tab={$tab}'>{$name}</a>" ;
    }
    echo  '</h2>' ;
}

function wnbell_item_tabs( $current = 'item' )
{
    $tabs = array(
        'item'             => __( 'Custom/posts/cpts', "wp-notification-bell" ),
        'wc_order_updates' => __( 'WooCommerce order updates', "wp-notification-bell" ),
    );
    echo  '<div id="icon-themes" class="icon32"><br></div>' ;
    echo  '<h2 class="nav-tab-wrapper">' ;
    foreach ( $tabs as $tab => $name ) {
        $class = ( $tab == $current ? 'nav-tab nav-tab-active' : "nav-tab" );
        echo  "<a class='{$class}' href='?post_type=wnbell_notifications&page=wnbell-sub-menu&tab={$tab}'>{$name}</a>" ;
    }
    echo  '</h2>' ;
}

function wnbell_item_settings( $options = array(), $tab = 'item' )
{
    $notif_options = get_option( 'wnbell_notif_options' );
    $length = array( 1 );
    $custom_length = sizeof( ( isset( $options['wnbell_name'] ) ? $options['wnbell_name'] : array( 1 ) ) );
    $wcou_length = sizeof( ( isset( $notif_options['wcou_name'] ) ? $notif_options['wcou_name'] : array( 1 ) ) );
    $name = '';
    $default_value = '';
    $class = '';
    $id = '';
    $settings = array();
    
    if ( $tab == 'item' ) {
        $length = $custom_length;
        $name = 'wnbell_name';
        $default_value = 'wnbell_default_value';
        $class = 'wnbell_class_attribute';
        $id = 'wnbell_id_attribute';
        $settings = $options;
    } elseif ( $tab == 'wc_order_updates' ) {
    }
    
    ?>

<div id="items">
<?php 
    
    if ( $tab == 'item' ) {
        ?>
<p>You can add these placeholders <b>{{date}}</b> , <b>{{human_date}}</b> or <b>{{time}}</b> to the default value to display the date on which the post was published.
<br>Or <b>{{post_title}}</b> to display post title. (optional)</p>
<p><a href="https://wpsimpleplugins.wordpress.com/2021/02/10/how-to-add-a-notification-manually/">How to add a notification</a>
<br><a href="https://wpsimpleplugins.wordpress.com/2021/02/11/how-to-add-new-post-notifications/">How to add a new post notification</a></p>
<?php 
    } elseif ( $tab == 'wc_order_updates' && function_exists( 'wc_get_order_statuses' ) ) {
        $wc_statuses = wc_get_order_statuses();
        foreach ( $wc_statuses as $status => $status_name ) {
            $result[substr( $status, 3 )] = $wc_statuses[$status];
        }
        $statuses = array_keys( $result );
        ?>
    <p>Available placeholders: <br/>
     <b>{{wcou_order_id}} :</b> Order ID<br/>
     <b>{{wcou_status}} :</b> Order status<br/>
     <b>{{wcou_date}} : </b>Modified status date
     <br/>(optional)</p>
<?php 
    }
    
    for ( $i = 0 ;  $i < $length ;  $i++ ) {
        ?>
<table class="form-table">
<tr style="vertical-align:bottom">
<th scope="row"><?php 
        _e( 'Name', 'wp-notification-bell' );
        ?></th>
<td>
<input type="text" name="<?php 
        echo  $name ;
        ?>[]" value="<?php 
        echo  esc_html( ( isset( $settings[$name][$i] ) ? $settings[$name][$i] : '' ) ) ;
        ?>"/>
</td>
</tr>
<tr style="vertical-align:bottom" id="default_value">
<th scope="row"><?php 
        _e( 'Default value', 'wp-notification-bell' );
        ?></th>
<td>
<?php 
        
        if ( $tab === 'item' || !$settings || !is_array( $settings[$default_value] ) || !is_array( $settings[$default_value][$i] ) ) {
            ?>
<input type="text" name="<?php 
            echo  $default_value ;
            ?>[]" value="<?php 
            esc_html( ( isset( $settings[$default_value][$i] ) ? printf( __( '%s', 'wp-notification-bell' ), $settings[$default_value][$i] ) : '' ) );
            ?>"/>
<?php 
        } else {
            if ( $settings && is_array( $settings[$default_value] ) && is_array( $settings[$default_value][$i] ) ) {
                ?>
        <div class="wnbell_custom_wc">Default</div>
    <?php 
            }
            ?>
    <input type="text" name="<?php 
            echo  $default_value ;
            ?>[]" value="<?php 
            esc_html( ( isset( $settings[$default_value][$i]['wnbell_default'] ) ? printf( __( '%s', 'wp-notification-bell' ), $settings[$default_value][$i]['wnbell_default'] ) : '' ) );
            ?>"/>
<?php 
        }
        
        if ( $tab == 'wc_order_updates' && function_exists( 'wc_get_order_statuses' ) ) {
            
            if ( $settings && is_array( $settings[$default_value] ) && is_array( $settings[$default_value][$i] ) ) {
                for ( $j = 0 ;  $j < count( $statuses ) ;  $j++ ) {
                    ?>
                    <p id="wnb_field<?php 
                    echo  $j ;
                    ?>">
<br><div class="wnbell_custom_wc"><?php 
                    echo  $statuses[$j] ;
                    ?></div><input type="text" name="wcou_custom_dv_<?php 
                    echo  $i ;
                    ?>[]" value="<?php 
                    echo  $settings[$default_value][$i][$statuses[$j]] ;
                    ?>"/>
                </p><?php 
                }
            } else {
                $s = json_encode( $statuses );
                ?>
<button type="button" class="wnbell-btn-element wnbell-add-default" onclick='wnbell_show_statuses(this, <?php 
                echo  $s ;
                ?>,<?php 
                echo  esc_html( $i ) ;
                ?>)'>+</button>
<?php 
            }
        
        }
        ?>
</td>
</tr>
<tr style="vertical-align:bottom">
       <th scope="row"><?php 
        _e( 'Id attribute', 'wp-notification-bell' );
        ?></th>
<td>
<input type="text" name="<?php 
        echo  $id ;
        ?>[]"
value="<?php 
        echo  esc_html( ( isset( $settings[$id][$i] ) ? $settings[$id][$i] : '' ) ) ;
        ?>"/>
</td>
</tr>
<tr style="vertical-align:bottom">
       <th scope="row"><?php 
        _e( 'Class attribute', 'wp-notification-bell' );
        ?></th>
<td>
<input type="text" name="<?php 
        echo  $class ;
        ?>[]"
value="<?php 
        echo  esc_html( ( isset( $settings[$class][$i] ) ? $settings[$class][$i] : '' ) ) ;
        ?>"/>
</td>
</tr>
</table>
<?php 
        if ( $i != $length - 1 ) {
            ?><tr style="height:1px;"><td colspan="2"><div style="background-color:#bbb;;width:80%;height:1px;margin:0 auto;"></div></td></tr>
<?php 
        }
        
        if ( $i != 0 && $i == $length - 1 ) {
            ?>
            <input type="submit" name="remove" class="wnbell-btn-element" value="<?php 
            _e( 'Remove', 'wp-notification-bell' );
            ?>"/>
    <?php 
        }
    
    }
    ?>
<button type="button" class="wnbell-btn-element wnbell-addElement"><?php 
    _e( 'Add', 'wp-notification-bell' );
    ?></button>
</div>
<?php 
}
