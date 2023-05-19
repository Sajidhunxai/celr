<?php

namespace WeDevs\DokanPro\Blocks;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Block manager class for PRO.
 *
 * @since 3.7.13
 */
class Manager {

    /**
     * Block class mapping.
     *
     * @since 3.7.13
     *
     * @var array
     */
    protected $block_classes;

    /**
     * Constructor class.
     *
     * @since 3.7.13
     */
    public function __construct() {
        /**
         * Include classes for blocks or sections.
         */
        $this->init_block_classes();
        $this->include_block_classes();
    }

    /**
     * Init block classes.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function init_block_classes() {
        if ( ! empty( $this->block_classes ) ) {
            return;
        }

        $this->block_classes = apply_filters(
            'dokan_block_classes', [
                Product::class,
                ProductShipping::class,
                ProductAttribute::class,
            ]
        );
    }

    /**
     * Include block classes.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function include_block_classes() {
        foreach ( $this->block_classes as $block ) {
            new $block();
        }
    }
}
