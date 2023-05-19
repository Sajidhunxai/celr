<?php

namespace WeDevs\DokanPro\Modules\Elementor\Abstracts;

use WeDevs\Dokan\Traits\Singleton;

abstract class ModuleBase {

    use Singleton;

    /**
     * Runs after first instance
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function boot() {
        add_action( 'elementor/widgets/register', [ $this, 'init_widgets' ] );
    }

    /**
     * Module name
     *
     * @since 2.9.11
     *
     * @return void
     */
    abstract public function get_name();

    /**
     * Module widgets
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_widgets() {
        return [];
    }

    /**
     * Register module widgets
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function init_widgets() {
        $widget_manager = dokan_elementor()->elementor()->widgets_manager;

        foreach ( $this->get_widgets() as $widget ) {
            $class_name = "\\WeDevs\\DokanPro\\Modules\\Elementor\\Widgets\\{$widget}";

            if ( class_exists( $class_name ) ) {
                $widget_manager->register( new $class_name() );
            }
        }
    }
}
