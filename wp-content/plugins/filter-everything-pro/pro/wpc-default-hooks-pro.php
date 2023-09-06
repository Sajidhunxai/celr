<?php

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\Pro\Api\ApiRequests;
use FilterEverything\Filter\Pro\PluginPro;

add_filter('wpc_seo_vars_list', 'flrt_add_seo_vars');
function flrt_add_seo_vars( $seo_vars )
{
    $seo_vars['site_title']  = '{site_title}';
    $seo_vars['page_number'] = '{page_number}';
    return $seo_vars;
}

function flrt_remove_wpseo()
{
    $all_wp_hooks   = flrt_print_filters_for('wp_head' );
    $hooksToDisable = [];

    // Disable Squirrly SEO
    remove_all_filters( 'sq_buffer' );

    // Disable Rank Math SEO, All in One SEO, Yoast SEO, The SEO Framework, SEOPress on filtering pages
    if( ! empty( $all_wp_hooks->callbacks ) && $all_wp_hooks->callbacks ){
        foreach ( $all_wp_hooks->callbacks as $priority => $hooks ){
            if( is_array( $hooks ) ){
                foreach ($hooks as $function => $args ){

                    if( strpos( $function, 'call_wpseo_head' ) !== false
                        && isset( $args['function'][0] )
                        && ( $args['function'][0] instanceof Yoast\WP\SEO\Integrations\Front_End_Integration )
                    ){
                        $hooksToDisable[$function] = $priority;
                    }

                    if( strpos( $function, 'head' ) !== false
                        && isset( $args['function'][0] )
                        && ( $args['function'][0] instanceof RankMath\Frontend\Head )
                    ){
                        $hooksToDisable[$function] = $priority;
                    }

                    if( strpos( $function, 'init' ) !== false
                        && isset( $args['function'][0] )
                        && ( $args['function'][0] instanceof AIOSEO\Plugin\Common\Main\Head )
                    ){
                        $hooksToDisable[$function] = $priority;
                    }

                    if( strpos( $function, 'html_output' ) !== false
                        && isset( $args['function'][0] )
                        && ( $args['function'][0] instanceof The_SEO_Framework\Load )
                    ){
                        $hooksToDisable[$function] = $priority;
                    }

                    if( strpos( $function, 'seopress' ) !== false ){
                        $hooksToDisable[$function] = $priority;
                    }

                    if( strpos( $function, 'render' ) !== false
                        && isset( $args['function'][0] )
                        && ( $args['function'][0] instanceof SEOPress\Actions\Front\Metas\DescriptionMeta
                            ||
                            $args['function'][0] instanceof SEOPress\Actions\Front\Schemas\PrintHeadJsonSchema
                        )
                    ){
                        $hooksToDisable[$function] = $priority;
                    }

                }
            }
        }
    }

    if( ! empty( $hooksToDisable ) ){
        foreach ( $hooksToDisable as $hookName => $priority ){
            remove_action('wp_head', $hookName, $priority );
        }
    }

}

function flrt_plugin_update_message( $plugin_data, $response )
{
    if ( flrt_get_license_key() ) {
        return;
    }
    echo '<br />' . sprintf( __( 'To enable updates, please enter your license key on the <a href="%1$s">License</a> page. If you don\'t have a licence key, please <a href="%2$s" target="_blank">purchase it here</a>.', 'filter-everything' ), admin_url( 'edit.php?post_type=filter-set&page=filters-settings&tab=license' ), FLRT_LICENSE_SOURCE );
}

function flrt_plugin_details( $result, $action = null, $args = null )
{
    // only for 'plugin_information' action
    if ( $action !== 'plugin_information' ) {
        return $result;
    }

    if ( $args->slug !== FLRT_PLUGIN_SLUG ) {
        return $result;
    }

    $api    = new ApiRequests();
    $is_pro = flrt_get_license_key() ? true : false;

    $data   = array(
        'slug' => FLRT_PLUGIN_SLUG,
        'pro'  => $is_pro,
    );

    $response = $api->sendRequest('VIEW', 'updates', $data );

    if( isset( $response[ 'info' ] ) && $response[ 'info' ] ) {
        $result = (object) $response[ 'info' ];

        // sections
        $sections = array(
            'description'    => '',
            'installation'   => '',
            'changelog'      => '',
            'upgrade_notice' => '',
        );

        foreach ( $sections as $k => $v ) {
            $sections[ $k ] = $result->$k;
        }

        $result->sections = $sections;
    }

    // return
    return $result;
}

function flrt_increase_count( $count )
{
    if ( ! isset( $count->response ) ) {
        return $count;
    }

    $updates = flrt_ask_for_update();

    if ( is_array( $updates ) ) {
        foreach ( $updates as $basename => $plugin_data ) {
            $count->response[ $basename ] = (object) $plugin_data;
        }
    }

    return $count;
}

function flrt_after_increase_count( $handler, $options )
{
    if( isset( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
        foreach ( $options['plugins'] as $single_plugin ) {
            if( $single_plugin === FLRT_PLUGIN_BASENAME ) {
                // Lock checking for the new plugin version for 3 hours
                set_transient( FLRT_VERSION_TRANSIENT, '-1', 10800 );
                break;
            }
        }
    }

    return $handler;
}

if( ! function_exists('flrt_lowercase_seo_vars' ) ){
    add_filter( 'wpc_seo_var_term_name', 'flrt_lowercase_seo_vars', 10, 2 );
    function flrt_lowercase_seo_vars( $termName, $e_name )
    {
        $do_not_strtolower = flrt_get_option( 'terms_with_capital_letter' );
        $do_not_strtolower = array_map( 'trim', explode(',', $do_not_strtolower ) );

        if( ! empty( $do_not_strtolower ) ){
            foreach ( $do_not_strtolower as $e_name_as_is ){
                if( $e_name_as_is ){
                    if( mb_strpos( $e_name, $e_name_as_is ) !== false ){
                        return $termName;
                    }
                }
            }
        }

        return strtolower( $termName );
    }
}

if( ! function_exists('flrt_term_rating_stars') ){
    add_filter( 'wpc_filters_checkbox_term_html', 'flrt_term_rating_stars', 10, 4 );
    add_filter( 'wpc_filters_radio_term_html', 'flrt_term_rating_stars', 10, 4 );

    function flrt_term_rating_stars($html, $link_attributes, $term, $filter)
    {
        $rating_slugs = array(
            'rated-1',
            'rated-2',
            'rated-3',
            'rated-4',
            'rated-5'
        );

        if( $filter['e_name'] !== 'product_visibility' ){
            return $html;
        }

        if( ! isset( $term->slug ) ){
            return $html;
        }

        if( ! in_array( $term->slug, $rating_slugs, true ) ){
            return $html;
        }

        $rating = 0;
        if( mb_strpos( $term->slug, 'rated-' ) !== false){
            $pieces = explode("-", $term->slug);
            $rating = isset( $pieces[1] ) ? $pieces[1] : 0;
        }

        $rating_html = '<div class="star-rating"><span style="width:' . esc_attr( $rating * 20 ) . '%">' . sprintf( esc_html__( '%s out of 5', 'woocommerce' ), esc_html( $rating ) ) . '</span></div>';

        $html = '<a '.$link_attributes.'>'.$rating_html.'</a>';

        return $html;
    }
}

/**
 * @param false $force_check
 * @return int|mixed
 */
function flrt_ask_for_update( $force_check = false )
{
    $return            = -1;
    $transient_expires = DAY_IN_SECONDS;

    if ( ! $force_check ) {
        $transient = get_transient( FLRT_VERSION_TRANSIENT );
        if ( $transient !== false ) {
            return $transient;
        }
    }

    $api         = new ApiRequests();
    $license_key = flrt_get_license_key();
    $data        = $api->collectPluginData( $license_key );
    $response    = $api->sendRequest('POST', 'updates', $data );

    if ( isset( $response[ 'data' ][ 'updates' ] ) && $response[ 'data' ][ 'updates' ] ) {
        $return = $response[ 'data' ][ 'updates' ];
    }

    set_transient( FLRT_VERSION_TRANSIENT, $return, $transient_expires );

    return $return;
}

function flrt_get_license_key()
{
    $license_key  = false;
    $license_data = get_option( FLRT_LICENSE_KEY );

    if ( $license_data && isset( $license_data['license_key'] ) ) {
        $decoded = maybe_unserialize( base64_decode( $license_data['license_key'] ) );
        if ( $decoded['key'] ) {
            $license_key = $decoded['key'];
        }
    }

    return $license_key;
}

function flrt_show_license_notice()
{
    $dismiss_nonce = wp_create_nonce( PluginPro::LICENSE_DISMISS_NONCE_ACTION );
    ?>
    <div class="license-notice is-dismissible">
        <h2><img src="<?php echo esc_attr( flrt_get_icon_svg('#333333') ); ?>" alt="" width="20" /> <?php echo esc_html( flrt_get_plugin_name() ); ?></h2> <p><?php
        echo wp_kses(
                sprintf(
                        __( 'To allow the plugin to update automatically and avoid blocking some of its features, please enter your license key on the <a href="%1$s">License page</a>.', 'filter-everything' ),
                        admin_url( 'edit.php?post_type=filter-set&page=filters-settings&tab=license' )
                ),
            array(
                'a' => array(
                    'href' => true
                    )
                )
            );
        ?></p>
        <a href="<?php
                echo add_query_arg(
                    array(
                        'action'   => PluginPro::LICENSE_DISMISS_NONCE_ACTION,
                        '_wpnonce' => $dismiss_nonce,
                    )
                ); ?>" class="notice-dismiss wpc-notice-dismiss" data-nonce="<?php echo $dismiss_nonce; ?>">
            <span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.'); ?></span>
        </a>
    </div>
    <?php
}

function flrt_notify_hare( $valid, $object )
{
    $object->pushError( 90 );
    return false;
}

function flrt_notify_hare_multisite( $valid, $object )
{
    $object->pushError( 91 );
    return false;
}

/**
 * Handles data related with plugin installation dates
 * @return true if data were updated and false if not
 */
function flrt_set_the_trident()
{
    $apiRequest = new ApiRequests();
    $site_data  = array( 'home_url' => home_url() );
    $result     = $apiRequest->sendRequest('GET', 'license', $site_data );
    $to_save    = array();

    if ( isset( $result['data']['first_install'] ) && $result['data']['first_install'] ) {

        $first_install = strtotime( $result['data']['first_install'] );

        if ( $first_install ) {
            $to_save = array(
                'first_install'      => $first_install,
                'last_message'       => $first_install,
                'messages_count'     => 0,
                'last_license_check' => 0,
            );

            if ( isset( $result[ 'data' ][ 'license' ] ) && $result[ 'data' ][ 'license' ] ) {
                $to_save[ 'last_license_check' ] = time();
            }

            return update_option( 'wpc_trident', $to_save );
        }
    }

    return false;
}

add_action( 'wpc_admin_toolbar_right', 'flrt_license_status' );
function flrt_license_status()
{
?>
<div class="wpc-tab">
    <div class="wpc-license-status">
    <span class="wpc-plugin-version"><?php
        echo sprintf( esc_html__('v%s', 'filter-everything' ), FLRT_PLUGIN_VER );
        ?>
    </span>
    <?php
        $to_show_status = false;

        if ( is_multisite() ) {
            if ( is_main_site() ) {
                $to_show_status = true;
            }
        } else {
            $to_show_status = true;
        }

        if ( $to_show_status ) {
            $tri         = get_option( 'wpc_trident' );
            $license_key = flrt_get_license_key();

            if ( ( isset( $tri[ 'first_install' ] ) && $tri[ 'first_install' ] + MONTH_IN_SECONDS < time() ) || $license_key ) {

                if ( $license_key ) {
                    $status  = esc_html__('Registered', 'filter-everything');
                    $class   = 'wpc-registered';
                } else {
                    $status      = esc_html__('Unregistered', 'filter-everything' );
                    if ( ( $tri[ 'first_install' ] + MONTH_IN_SECONDS * 2 ) < time() ) {
                        $status  = esc_html__('Locked', 'filter-everything' );
                        $status .= flrt_tooltip( array(
                                'tooltip' => wp_kses(
                                    __( 'Locked status means that you can not update Filter Set and SEO Rule settings. All other plugin features works as usual.', 'filter-everything'),
                                    array('br' => array() )
                                )
                            )
                        );
                    }
                    $class   = 'wpc-unregistered';
                }

                echo '| <a href="'.admin_url( 'edit.php?post_type=filter-set&page=filters-settings&tab=license' ).'" class="wpc-plugin-status ' . $class . '">'.$status.'</a>';
            }
        }
    ?>
    </div>
</div>
<?php
}

add_filter( 'wpc_settings_field_checkbox', 'flrt_collapse_widget_checkbox_handler', 10, 2 );
function flrt_collapse_widget_checkbox_handler( $checkbox, $args )
{
    if( isset($args['id']) && $args['id'] === 'show_open_close_button' ){
        if(flrt_get_option('show_bottom_widget') === 'on' ){
            $checkbox = '<label class="wpc-inactive-settings-field"><input type="checkbox" name="%s[%s]" %s id="%s">%s</label>';
        }
    }
    return $checkbox;
}