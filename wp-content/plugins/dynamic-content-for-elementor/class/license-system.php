<?php

namespace DynamicContentForElementor;

use DynamicContentForElementor\Plugin;
if (!\defined('ABSPATH')) {
    exit;
}
class LicenseSystem
{
    const LICENSE_STATUS_OPTION = 'dce_license_status';
    const LICENSE_ERROR_OPTION = 'dce_license_error';
    const LICENSE_DOMAIN_OPTION = 'dce_license_domain';
    const LICENSE_KEY_OPTION = 'dce_license_key';
    /**
     * @var bool
     */
    private $should_attempt_auto_activation = \false;
    /**
     * @var bool
     */
    private $is_staging = \false;
    public function __construct()
    {
        add_action('admin_post_dce_rollback', [$this, 'post_rollback']);
        $this->activation_advisor();
        // gestisco lo scaricamento dello zip aggiornato inviando i dati della licenza
        add_filter('upgrader_pre_download', array($this, 'filter_upgrader_pre_download'), 10, 3);
    }
    public function activation_advisor()
    {
        $tab_license = isset($_GET['page']) && $_GET['page'] === 'dce-license';
        if (!$this->is_license_active() && !$tab_license && current_user_can('administrator')) {
            $message = \sprintf(__('It seems that your copy is not activated, please %1$sactivate it%2$s or %3$sbuy a new license%4$s.', 'dynamic-content-for-elementor'), '<a href="' . admin_url() . 'admin.php?page=dce-license">', '</a>', '<a href="https://www.dynamic.ooo/pricing" target="blank">', '</a>');
            \DynamicContentForElementor\Plugin::instance()->admin_pages->notices->error($message);
            add_filter('plugin_action_links_' . DCE_PLUGIN_BASE, [$this, 'plugin_action_links_license']);
        }
    }
    /**
     * Define the upgrader_pre_download callback
     */
    public function filter_upgrader_pre_download($false, $package, $instance)
    {
        $plugin = \false;
        if (\property_exists($instance, 'skin')) {
            if ($instance->skin) {
                if (\property_exists($instance->skin, 'plugin')) {
                    // Update from page
                    if ($instance->skin->plugin) {
                        $pezzi = \explode('/', $instance->skin->plugin);
                        $plugin = \reset($pezzi);
                    }
                }
                // Update via Ajax
                if (!$plugin && isset($instance->skin->plugin_info['TextDomain'])) {
                    $plugin = $instance->skin->plugin_info['TextDomain'];
                }
            }
        }
        if ('dynamic-content-for-elementor' === $plugin || isset($_POST['dce_version'])) {
            return $this->upgrader_pre_download($package, $instance);
        }
        return $false;
    }
    public function upgrader_pre_download($package, $instance = null)
    {
        // Check the license
        $this->refresh_license_status();
        if (!$this->is_license_active()) {
            $linkmsg = ' <a href="' . admin_url() . 'admin.php?page=dce-license">' . __('Check it on the license page', 'dynamic-content-for-elementor') . '</a>.';
            return new \WP_Error($this->get_license_error() . $linkmsg);
        }
        // Additional info required to permit download
        $package .= \strpos($package, '?') === \false ? '?' : '&';
        $package .= 'license_key=' . $this->get_license_key() . '&license_instance=' . $this->get_current_domain();
        if (get_option('dce_beta', \false)) {
            $package .= '&beta=true';
        }
        $download_file = download_url($package);
        if (is_wp_error($download_file)) {
            return new \WP_Error('download_failed', __('Error downloading the update package', 'dynamic-content-for-elementor'), $download_file->get_error_message());
        }
        return $download_file;
    }
    /**
     * @param bool $fresh false gets cache version, true checks remote status
     * @return bool
     */
    public function is_license_active($fresh = \false)
    {
        if ($fresh) {
            $this->refresh_license_status();
        }
        return get_option(self::LICENSE_STATUS_OPTION, '') === 'active';
    }
    /**
     * Summary
     *
     * @param string $status either 'active' or 'inactive'
     * @return void
     */
    private function set_license_status($status)
    {
        update_option(self::LICENSE_STATUS_OPTION, $status);
    }
    /**
     * Get error message from last failed status check.
     *
     * @return string
     */
    private function get_license_error()
    {
        return get_option(self::LICENSE_ERROR_OPTION, '');
    }
    /**
     * Set license status to inactive and save error message.
     *
     * @param string $error
     */
    private function set_license_error($error)
    {
        $this->set_license_status('inactive');
        update_option(self::LICENSE_ERROR_OPTION, $error);
    }
    /**
     * Set License Key
     *
     * @param string $key
     * @return void
     */
    private function set_license_key($key)
    {
        update_option(self::LICENSE_KEY_OPTION, $key);
    }
    /**
     * Get License Key
     *
     * @return string
     */
    public function get_license_key()
    {
        return get_option(self::LICENSE_KEY_OPTION, '');
    }
    /**
     * Get last 4 digits of License Key
     *
     * @return string
     */
    public function get_license_key_last_4_digits()
    {
        return \substr($this->get_license_key(), -4);
    }
    /**
     * Activate new License Key
     *
     * @param string $key
     * @return array
     */
    public function activate_new_license_key($key)
    {
        // TODO: check if valid.
        $this->set_license_key($key);
        return $this->activate_license();
    }
    /**
     * Get License Domain
     *
     * @return string|bool
     */
    public function get_last_active_domain()
    {
        return get_option(self::LICENSE_DOMAIN_OPTION);
    }
    /**
     * Set License Domain
     *
     * @param string $domain
     * @return void
     */
    public function set_last_active_domain($domain)
    {
        update_option(self::LICENSE_DOMAIN_OPTION, $domain);
    }
    /**
     * Get current domain without protocol
     *
     * @return string
     */
    public function get_current_domain()
    {
        $protocol = !empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || !empty($_SERVER['SERVER_PORT']) && 443 === $_SERVER['SERVER_PORT'] ? 'https://' : 'http://';
        return \str_replace($protocol, '', get_bloginfo('wpurl'));
    }
    /**
     * Update the license system options and variables based on the server response.
     *
     * @param array $response
     * @return void
     */
    public function handle_status_check_response($response)
    {
        $this->should_attempt_auto_activation = \false;
        $this->is_staging = \false;
        if (!$response) {
            // trouble contacting the server. No changes:
            return;
        }
        if (($response['staging'] ?? '') === 'yes') {
            $this->is_staging = \true;
        }
        $status_code = $response['status_code'] ?? '';
        if ('e002' === $status_code) {
            // key is invalid:
            $this->set_license_error($response['message']);
            return;
        }
        if (\in_array($status_code, ['s203', 'e204'], \true)) {
            // key is not active for current domain, we should not attempt activation:
            $this->set_license_error($response['message']);
            return;
        }
        if (\in_array($status_code, ['s205', 's215'], \true)) {
            // if license is valid and active for domain:
            if (($response['license_status'] ?? '') === 'expired') {
                // But expired:
                $this->set_license_error($response['message']);
                $this->should_attempt_auto_activation = \true;
                return;
            }
            $this->set_license_status('active');
            $this->set_last_active_domain($this->get_current_domain());
            return;
        }
        // other cases, just set the error with message:
        $this->set_license_error($response['message'] ?? esc_html__('Unknown', 'dynamic-content-for-elementor'));
    }
    /**
     * @return bool|array
     */
    public function remote_status_check()
    {
        return $this->call_api('status-check', $this->get_license_key(), $this->get_current_domain());
    }
    /**
     * Refresh license status.
     *
     * @return void
     */
    public function refresh_license_status()
    {
        if (!$this->get_license_key()) {
            $this->set_license_error(esc_html__('No license present', 'dynamic-content-for-elementor'));
            return;
        }
        $response = $this->remote_status_check();
        $this->handle_status_check_response($response);
    }
    /**
     * Refresh license status. If license was not deliberately deactivated try
     * to reactivate the license for this domain.
     *
     * @return void
     */
    public function refresh_and_repair_license_status()
    {
        $this->refresh_license_status();
        if ($this->should_attempt_auto_activation) {
            $this->activate_license();
            // TODO: refresh again?
        }
    }
    /**
     * Ask to the server to activate the license
     *
     * @return string activation message
     */
    private function activate_license_request()
    {
        $response = $this->call_api('activate', $this->get_license_key(), $this->get_current_domain());
        if ($response) {
            return $response['message'];
        }
        return esc_html__('Problem contacting the server, try again in a few minutes.', 'dynamic-content-for-elementor');
    }
    /**
     * Ask the server to deactivate the license
     *
     * @return string activation message
     */
    private function deactivate_license_request()
    {
        $response = $this->call_api('deactivate', $this->get_license_key(), $this->get_current_domain());
        if ($response) {
            return $response['message'];
        }
        return esc_html__('Problem contacting the server, try again in a few minutes.', 'dynamic-content-for-elementor');
    }
    /**
     * Ask the server to deactivate the license. Refresh license status.
     * Delete the key for staging sites.
     *
     * @return array [success, msg]
     */
    public function deactivate_license()
    {
        $msg = $this->deactivate_license_request();
        $success = !$this->is_license_active(\true);
        if ($this->is_staging) {
            $this->set_license_key('');
            $this->refresh_license_status();
            return [\true, esc_html__('Success', 'dynamic-content-for-elementor')];
        }
        return [$success, $msg];
    }
    /**
     * Ask the server to activate the license. Refresh license status.
     *
     * @return array [success, msg]
     */
    public function activate_license()
    {
        $msg = $this->activate_license_request();
        $success = $this->is_license_active(\true);
        return [$success, $msg];
    }
    /**
     * Active beta releases
     *
     * @return void
     */
    public function activate_beta_releases()
    {
        update_option('dce_beta', \true);
    }
    /**
     * Deactivate beta releases
     *
     * @return void
     */
    public function deactivate_beta_releases()
    {
        update_option('dce_beta', \false);
    }
    /**
     * Check if beta releases are activated
     *
     * @return boolean
     */
    public function is_beta_releases_activated()
    {
        return get_option('dce_beta');
    }
    /**
     * Make a request to license server to activate, deactivate or check the status of the license
     *
     * @param string $action
     * @param string $license_key
     * @param string $domain
     * @return bool|array
     */
    public function call_api(string $action, string $license_key, string $domain)
    {
        global $wp_version;
        $args = ['woo_sl_action' => $action, 'licence_key' => $license_key, 'product_unique_id' => 'WP-DCE-1', 'domain' => $domain, 'api_version' => '1.1', 'wp-version' => $wp_version, 'version' => DCE_VERSION];
        $request_uri = DCE_LICENSE_URL . '/api.php?' . \http_build_query($args);
        $data = wp_remote_get($request_uri);
        if (is_wp_error($data)) {
            return \false;
        }
        if ($data['response']['code'] !== 200) {
            return \false;
        }
        $body = \json_decode($data['body'], \true);
        if (\is_array($body)) {
            return \reset($body);
        }
        return \false;
    }
    /**
     * Retrieve all versions of the plugin
     *
     * @copyright Elementor
     * @license GPLv3
     */
    private function get_versions()
    {
        $url = DCE_LICENSE_URL . '/versions.php';
        $body_args = ['item_name' => DCE_SLUG, 'version' => DCE_VERSION, 'license' => $this->get_license_key(), 'url' => $this->get_current_domain(), 'action' => 'list'];
        $response = wp_remote_get($url, ['timeout' => 40, 'body' => $body_args]);
        if (is_wp_error($response)) {
            return $response;
        }
        $response_code = (int) wp_remote_retrieve_response_code($response);
        $data = \json_decode(wp_remote_retrieve_body($response), \true);
        if (401 === $response_code) {
            return new \WP_Error($response_code, $data['message']);
        }
        if (200 !== $response_code) {
            return new \WP_Error($response_code, esc_html__('HTTP Error', 'dynamic-content-for-elementor'));
        }
        if (empty($data) || !\is_array($data)) {
            return new \WP_Error('no_json', esc_html__('An error occurred, please try again', 'dynamic-content-for-elementor'));
        }
        return $data;
    }
    /**
     * Retrieve all previous versions so you can make a rollback
     *
     * @copyright Elementor
     * @license GPLv3
     */
    public function get_rollback_versions()
    {
        $rollback_versions = get_transient('dce_rollback_versions_' . DCE_VERSION);
        if (\false === $rollback_versions) {
            $max_versions = 30;
            $versions = $this->get_versions();
            if (is_wp_error($versions)) {
                return [];
            }
            $rollback_versions = [];
            $current_index = 0;
            foreach ($versions as $version) {
                if ($max_versions <= $current_index) {
                    break;
                }
                $lowercase_version = \strtolower($version);
                $is_valid_rollback_version = !\preg_match('/(trunk|beta|rc|dev)/i', $lowercase_version);
                if (!$is_valid_rollback_version) {
                    continue;
                }
                if (\version_compare($version, DCE_VERSION, '>=')) {
                    continue;
                }
                $current_index++;
                $rollback_versions[] = $version;
            }
            set_transient('dce_rollback_versions_' . DCE_VERSION, $rollback_versions, WEEK_IN_SECONDS);
        }
        return $rollback_versions;
    }
    /**
     * Return the URL to download a specific version
     *
     * @copyright Elementor
     * @license GPLv3
     */
    protected function get_plugin_package_url($version)
    {
        $url = DCE_LICENSE_URL . '/versions.php';
        $body_args = ['item_name' => DCE_SLUG, 'version' => $version, 'license' => $this->get_license_key(), 'url' => $this->get_current_domain(), 'action' => 'download'];
        $response = wp_remote_get($url, ['timeout' => 40, 'body' => $body_args]);
        if (is_wp_error($response)) {
            return $response;
        }
        $response_code = (int) wp_remote_retrieve_response_code($response);
        $data = \json_decode(wp_remote_retrieve_body($response), \true);
        if (401 === $response_code) {
            return new \WP_Error($response_code, $data['message']);
        }
        if (200 !== $response_code) {
            return new \WP_Error($response_code, esc_html__('HTTP Error', 'dynamic-content-for-elementor'));
        }
        if (empty($data) || !\is_array($data)) {
            return new \WP_Error('no_json', esc_html__('An error occurred, please try again', 'dynamic-content-for-elementor'));
        }
        return $data['package_url'];
    }
    /**
     * Function fired by 'admin_post_dce_rollback' action
     *
     * @return void
     */
    public function post_rollback()
    {
        if (!wp_verify_nonce($_POST['dce-settings-page'], 'dce-settings-page')) {
            wp_die(__('Nonce verification error.', 'dynamic-content-for-elementor'));
        }
        $rollback_versions = $this->get_rollback_versions();
        if (empty($_POST['version']) || !\in_array($_POST['version'], $rollback_versions, \true)) {
            wp_die(esc_html__('Error occurred, the version selected is invalid. Try selecting different version.', 'dynamic-content-for-elementor'));
        }
        $package_url = $this->get_plugin_package_url($_POST['version']);
        if (is_wp_error($package_url)) {
            wp_die($package_url);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        $rollback = new \DynamicContentForElementor\Rollback(['version' => sanitize_text_field($_POST['version']), 'plugin_name' => DCE_PLUGIN_BASE, 'plugin_slug' => DCE_SLUG, 'package_url' => $package_url]);
        $rollback->run();
        wp_die('', esc_html__('Rollback to Previous Version', 'dynamic-content-for-elementor'), ['response' => 200]);
    }
    public function plugin_action_links_license($links)
    {
        $links['license'] = '<a style="color:brown;" title="' . __('Activate license', 'dynamic-content-for-elementor') . '" href="' . admin_url() . 'admin.php?page=dce-license"><b>' . __('License', 'dynamic-content-for-elementor') . '</b></a>';
        return $links;
    }
    public function domain_mismatch_check()
    {
        if ($this->get_license_key() && !$this->is_license_active() && $this->get_last_active_domain() && $this->get_last_active_domain() !== $this->get_current_domain()) {
            \DynamicContentForElementor\Plugin::instance()->admin_pages->notices->warning(\sprintf(__('License Mismatch. Your license key doesn\'t match your current domain. This is likely due to a change in the domain URL. You can reactivate your license now. Remember to deactivate the one for the old domain from your license area on Dynamic.ooo\'s site', 'dynamic-content-for-elementor'), '<a class="btn button" href="' . admin_url() . 'admin.php?page=dce-license">', '</a>'));
        }
    }
}
