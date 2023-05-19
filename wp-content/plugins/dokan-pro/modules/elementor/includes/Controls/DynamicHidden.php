<?php

namespace WeDevs\DokanPro\Modules\Elementor\Controls;

use Elementor\Control_Hidden;

class DynamicHidden extends Control_Hidden {

    /**
     * Control type
     *
     * @since 2.9.11
     *
     * @var string
     */
    const CONTROL_TYPE = 'dynamic_hidden';

    /**
     * Get repeater control type.
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_type() {
        return self::CONTROL_TYPE;
    }

    /**
     * Get default settings for the control
     *
     * @since 2.9.11
     *
     * @return array
     */
    protected function get_default_settings() {
        $default_settings = parent::get_default_settings();

        $default_settings['dynamic'] = [];

        return $default_settings;
    }
}
