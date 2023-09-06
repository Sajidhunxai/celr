<?php


namespace FilterEverything\Filter;

use FilterEverything\Filter\Pro\Api\ApiRequests;

if ( ! defined('WPINC') ) {
    wp_die();
}

class LicenseTab extends BaseSettings
{
    protected $page = 'wpc-filter-admin-license';

    protected $group = 'wpc_filter_license';

    //protected $optionName = 'wpc_filter_license';

    protected $api_link = 'https://api.envato.com/authorization?response_type=code&client_id='.FLRT_ENVATO_APP_CLIENT_ID.'&redirect_uri=https://connect.filtereverything.pro/envato.php';

    public function __construct( $optionName )
    {
        $this->optionName = $optionName;
    }

    public function init()
    {
        add_action( 'admin_init', array($this, 'initSettings') );
        add_action( 'wpc_after_settings_fields_title', array( $this, 'explanationMessage' ) );
        add_action( 'wpc_before_sections_settings_fields', array( $this, 'removeSubmitButton' ) );
        add_filter( 'pre_update_option_wpc_filter_license', [ $this, 'preUpdateLicense' ], 10, 3 );
    }

    public function initSettings()
    {
        register_setting($this->group, $this->optionName);
        /**
         * @see https://developer.wordpress.org/reference/functions/add_settings_field/
         */
        $saved_value = get_option( $this->optionName );
        $field_type  = ( isset( $saved_value['license_key'] ) && $saved_value['license_key'] ) ? 'license' : 'text';

        $settings = array(
            'license' => array(
                'label'  => esc_html__('License Information', 'filter-everything'),
                'fields' => array(
                    'license_key'        => array(
                        'type'      => $field_type,
                        'title'     => esc_html__('License Key', 'filter-everything'),
                        'id'        => 'license_key',
                        'default'   => '',
                        'label'     => ''
                    ),
                )
            )
        );

        $this->registerSettings($settings, $this->page, $this->optionName);
    }

    public function explanationMessage( $page )
    {
        if( $page === $this->page ){
            $saved_value = get_option( $this->optionName );

            if ( isset( $saved_value['license_key'] ) && $saved_value['license_key'] ) {
                $message = esc_html__( 'Everything is fine, you have activated license.', 'filter-everything' );
            } else {
                $message = '';
                $tri     = get_option( 'wpc_trident' );

                if ( isset( $tri[ 'first_install' ] ) ) {
                    $now = time();
                    if ( ( $tri[ 'first_install' ] + MONTH_IN_SECONDS * 2 ) < $now ) {
                        // L o c k e d
                        $message .= esc_html__( 'You have been using the plugin for more than two months without license key and now it is locked.', 'filter-everything' ).'<br />';
                    }
                }

                $message .= wp_kses(
                    sprintf( __( 'To enable automatic plugin updates, please enter your license key below. If you purchased the plugin, please click the “Get your License Key” button.<br />If you didn\'t do it yet, please <a href="%1$s" target="_blank">purchase it here</a>.', 'filter-everything' ), FLRT_LICENSE_SOURCE ),
                    array(
                        'a' => array(
                            'href' => true,
                            'target' => true
                        ),
                        'br' => array()
                    )
                );
            }
            echo '<p>'.$message.'</p>'."\r\n";
        }
    }

    public function removeSubmitButton( $page ){
        if( $page === $this->page ){
            add_filter( 'wpc_settings_submit_button', '__return_false' );
            add_action( 'wpc_after_settings_field', array( $this, 'submitButtons' ) );
        }else{
            remove_filter( 'wpc_settings_submit_button', '__return_false' );
        }
    }

    public function submitButtons( $field ){
        $saved_value = get_option( $this->optionName );
        $license     = isset( $saved_value['license_key'] ) ? $saved_value['license_key'] : '';

        if( $license ): ?>
            <tr>
                <th>&nbsp;</th>
                <td>
                    <input type="hidden" name="wpc_license_action" value="deactivate" />
                    <input type="submit" value="<?php esc_html_e( 'Deactivate License', 'filter-everything' ); ?>" class="button button-primary">
                </td>
            </tr>
        <?php else: ?>
        <tr>
            <th>&nbsp;</th>
            <td class="td-activate-license">
                <input type="hidden" name="wpc_license_action" value="activate" />
                <input type="submit" value="<?php esc_html_e( 'Activate License', 'filter-everything' ); ?>" class="button button-primary">
                <a id="wpc-get-license-key" href="<?php esc_html_e($this->api_link); ?>" class="button" target="_blank"><?php esc_html_e( 'Get your License Key', 'filter-everything' ); ?></a>
                <?php
                    echo flrt_tooltip( array(
                            'tooltip' => wp_kses(
                                __('You will need to log into your Envato account to generate the License Key.', 'filter-everything'),
                                array('br' => array() )
                            )
                        )
                    );
                ?>
            </td>
        </tr>
        <?php endif;
    }

    public function preUpdateLicense( $new_values, $old_values, $option )
    {
        if( isset( $new_values['license_key'] ) ) {

            // Let's try to activate license
            if ( $_POST['wpc_license_action'] === 'activate') {

                $apiRequest     = new ApiRequests();
                $activate_data  = $apiRequest->collectPluginData( $new_values['license_key'] );
                $result         = $apiRequest->sendRequest('POST', 'license', $activate_data);

                if (isset($result['error']) && $result['error'] === 1) {
                    foreach ($result['messages'] as $msg) {
                        add_settings_error('general', 'settings_updated', $msg, 'error');
                    }
                    return false;
                }

                if ( isset( $result['data']['id'] ) && $result['data']['id'] ) {
                    // We have to store in WPDB also id of the entry in connect.fitlereverything.pro
                    // to be available to delete the entry when needed.
                    $data = array(
                        'id'    => $result['data']['id'],
                        'key'   => $new_values['license_key'],
                    );

                    // Success message
                    add_settings_error('general', 'settings_updated', esc_html__('License was successfully activated.', 'filter-everything'), 'success');
                    // If license was activated, we have to refresh updates info
                    delete_transient(FLRT_VERSION_TRANSIENT );

                    $new_values['license_key'] = base64_encode( maybe_serialize( $data ) );
                    return $new_values;
                }else{
                    // Something went wrong
                    add_settings_error('general', 'settings_updated', esc_html__('Unknown error.', 'filter-everything'), 'error');
                    return false;
                }

            // Or let's try to delete existing license
            } elseif ( $_POST['wpc_license_action'] === 'deactivate') {

                $saved_value         = get_option( $this->optionName );
                $saved_value_arr     = maybe_unserialize( base64_decode( $saved_value['license_key'] ) );
                $to_send             = $saved_value_arr;
                $to_send['home_url'] = home_url();

                // Make data suitable to send as GET variables
                $to_send = array_map( 'urlencode', $to_send );

                if ( isset( $saved_value_arr['id'] ) && $saved_value_arr['id'] ) {
                    $apiRequest = new ApiRequests();
                    $result     = $apiRequest->sendRequest('DELETE', 'license', $to_send );

                    // Something went wrong
                    if ( isset( $result['error'] ) && $result['error'] === 1 ) {
                        foreach ( $result['messages'] as $msg ) {
                            add_settings_error('general', 'settings_updated', $msg, 'error' );
                        }

                        // License was not found on the server
                        if ( isset( $result['messages'][31] ) && $result['messages'][31] ) {
                            // If license was deactivated, we have to refresh updates info
                            delete_transient(FLRT_VERSION_TRANSIENT );
                            return false;
                        }

                        // Do not remove license key from the WPDB
                        return $old_values;
                    }

                    // Success message
                    add_settings_error('general', 'settings_updated', esc_html__('License was successfully deactivated.', 'filter-everything' ), 'info' );
                    // If license was deactivated, we have to refresh updates info
                    delete_transient(FLRT_VERSION_TRANSIENT );
                    return false;
                }

            } else {
                // Do not change anything
                return $old_values;
            }

        }

        return false;
    }

    public function getLabel()
    {
        return esc_html__('License', 'filter-everything');
    }

    public function getName()
    {
        return 'license';
    }

    public function valid()
    {
        return true;
    }
}