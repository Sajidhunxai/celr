<?php

namespace DynamicContentForElementor\Widgets;

use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class DynamicCookie extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_content', ['label' => $this->get_title()]);
        $this->add_control('setcookie', ['label' => __('Mode', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'label_on' => __('Set', 'dynamic-content-for-elementor'), 'label_off' => __('Unset', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'default' => 'yes']);
        $this->add_control('cookie_name', ['label' => __('Cookie name', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT]);
        $this->add_control('cookie_if_exists', ['label' => __('If the cookie exists', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'append_comma', 'options' => ['append_comma' => __('Append the new value with a comma', 'dynamic-content-for-elementor'), 'overwrite' => __('Overwrite the cookie with the new value', 'dynamic-content-for-elementor')], 'condition' => ['setcookie' => 'yes']]);
        $this->add_control('cookie_value', ['label' => __('Value', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'dynamic' => ['active' => \true], 'condition' => ['setcookie' => 'yes']]);
        $this->add_control('cookie_expires', ['label' => __('Cookie expiration', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'separator' => 'before', 'default' => 30, 'min' => 0, 'description' => __('Set 0 or empty for session duration.', 'dynamic-content-for-elementor'), 'condition' => ['setcookie' => 'yes']]);
        $this->add_control('cookie_expires_value', ['label' => __('Cookie expiration value in', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'days', 'options' => ['minutes' => __('minutes', 'dynamic-content-for-elementor'), 'days' => __('days', 'dynamic-content-for-elementor')], 'condition' => ['setcookie' => 'yes']]);
        $this->end_controls_section();
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings) || !$settings['cookie_name'] || \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return;
        }
        $cookie_name = $settings['cookie_name'];
        $setcookie = $settings['setcookie'];
        // Unset mode
        if (!$setcookie) {
            unset($_COOKIE[$cookie_name]);
            @\setcookie($cookie_name, '', \time() - 3600, '/');
        }
        // Set mode
        $cookie_expires = \intval($settings['cookie_expires']);
        $cookie_expires_value = $settings['cookie_expires_value'];
        $value = sanitize_text_field($settings['cookie_value']);
        // Useful if the customer set same cookie name multiple times in the same page
        global $dce_cookies;
        $dce_cookies[$cookie_name][] = $value;
        if (!empty($dce_cookies[$cookie_name])) {
            $value = \implode(',', $dce_cookies[$cookie_name]);
        }
        if ($cookie_expires_value == 'days') {
            $cookie_expires = $cookie_expires ? \time() + 86400 * $cookie_expires : 0;
            // 86400 = 1 day
        } elseif ($cookie_expires_value == 'minutes') {
            $cookie_expires = $cookie_expires ? \time() + 60 * $cookie_expires : 0;
            // 60 = 1 minute
        }
        $arr_cookie_options = array('expires' => $cookie_expires, 'path' => '/', 'domain' => sanitize_text_field($_SERVER['HTTP_HOST']), 'secure' => is_ssl() ? \true : \false, 'httponly' => \true, 'samesite' => 'Lax');
        // The cookie already exists
        if (isset($_COOKIE[$cookie_name])) {
            $cookie_value = \explode(',', sanitize_text_field($_COOKIE[$cookie_name]));
            // If you choose to append the new value with a comma and it not already exists in the cookie
            if ($settings['cookie_if_exists'] == 'append_comma' && !\in_array($value, $cookie_value)) {
                \array_push($cookie_value, $value);
                @\setcookie($cookie_name, \implode(',', $cookie_value), $arr_cookie_options);
            } elseif ($settings['cookie_if_exists'] == 'overwrite') {
                // If you choose to overwrite the new value
                @\setcookie($cookie_name, $value, $arr_cookie_options);
            }
        } else {
            // The cookie doesn't exists. Set the value
            @\setcookie($cookie_name, $value, $arr_cookie_options);
        }
    }
}
