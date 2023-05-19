<?php

namespace WeDevs\DokanPro\Brands;

class Manager {

    /**
     * Is YITH plugin active or not
     *
     * @since 2.9.7
     *
     * @var bool
     */
    public $is_active = false;

    /**
     * Is YITH premium plugin active or not
     *
     * @since 2.9.7
     *
     * @var bool
     */
    public $is_premium_active = false;

    /**
     * Feature related admin settings
     *
     * @since 2.9.7
     *
     * @var array
     */
    public $settings = [];

    /**
     * Set is_active property
     *
     * @since 3.0.0
     *
     * @param bool $is_active
     *
     * @return void
     */
    public function set_is_active( $is_active ) {
        $this->is_active = $is_active;
    }

    /**
     * Set is_premium_active property
     *
     * @since 3.0.0
     *
     * @param bool $is_premium_active
     *
     * @return void
     */
    public function set_is_premium_active( $is_premium_active ) {
        $this->is_premium_active = $is_premium_active;
    }

    /**
     * Set settings property
     *
     * @since 3.0.0
     *
     * @param array $settings
     *
     * @return void
     */
    public function set_settings( $settings ) {
        $this->settings = $settings;
    }

    /**
     * Get Brand taxonomy
     *
     * When premium addon is active, admin can switch
     * taxonomy from admin panel settings
     *
     * @since 2.9.7
     *
     * @return string
     */
    public function get_taxonomy() {
        $yith_wcbr = YITH_WCBR();
        $taxonomy = $yith_wcbr::$brands_taxonomy;

        if ( $this->is_premium_active ) {
            $yith_wcbr_premium = YITH_WCBR_Premium();
            $taxonomy = $yith_wcbr_premium::$brands_taxonomy;
        }

        return $taxonomy;
    }
}
