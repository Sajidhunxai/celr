<?php

namespace WeDevs\DokanPro;

use WPSEO_Options;
use WPSEO_Option_Titles;

/**
 * Dokan Pro Product SEO class
 *
 * @since 2.9.0
 *
 * @package dokan
 */
class ProductSeo {

    /**
     * Load autometically when class initiate
     *
     * @since 2.9.0
     *
     * @uses actions
     * @uses filters
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Init hooks and filters
     *
     * @return void
     */
    public function init_hooks() {
        if ( ! defined( 'WPSEO_VERSION' ) ) {
            return;
        }

        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_product_seo_content' ), 5, 2 );
        add_action( 'dokan_product_updated', array( $this, 'save_product_seo_data' ), 15 );
    }

    /**
     * Convert Yoast shortcode format to Dokan format
     *
     * @since 3.0.7
     *
     * @param string $str
     *
     * @return string
     */
    public function convert_yoast_to_dokan_format( $str ) {
        return str_replace(
            [
                '%%title%%',
                '%%sep%%',
                '%%sitename%%',
            ],
            [
                '[title]',
                '[sep]',
                '[sitename]',
            ],
            $str
        );
    }

    /**
     * Convert Dokan shortcode format to Yoast format
     *
     * @since 3.0.7
     *
     * @param string $str
     *
     * @return string
     */
    public function convert_dokan_to_yoast_format( $str ) {
        return str_replace(
            [
                '[title]',
                '[sep]',
                '[sitename]',
            ],
            [
                '%%title%%',
                '%%sep%%',
                '%%sitename%%',
            ],
            $str
        );
    }

    /**
     * Load SEO content
     *
     * @since 2.9.0
     *
     * @param  object $post
     * @param  integer $post_id
     *
     * @return void
     */
    public function load_product_seo_content( $post, $post_id ) {
        $seo_title    = get_post_meta( $post_id, '_yoast_wpseo_title', true );
        $seo_metadesc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );

        dokan_get_template_part(
            'products/product-seo-content', '', array(
                'pro'          => true,
                'post'         => $post,
                'post_id'      => $post_id,
                'title_sep'    => $this->get_title_separator(),
                'seo_title'    => $this->convert_yoast_to_dokan_format( $seo_title ),
                'seo_metadesc' => $this->convert_yoast_to_dokan_format( $seo_metadesc ),
            )
        );
    }

    /**
     * Get title separator
     *
     * @since 3.2.1
     *
     * @return string
     */
    public function get_title_separator() {
        if ( ! class_exists( 'WPSEO_Options' ) || ! class_exists( 'WPSEO_Option_Titles' ) ) {
            return '-';
        }

        // Get the titles option and the separator options.
        $separator         = WPSEO_Options::get( 'separator' );
        $seperator_options = WPSEO_Option_Titles::get_instance()->get_separator_options();

        // This should always be set, but just to be sure.
        if ( isset( $seperator_options[ $separator ] ) ) {
            // Set the new replacement.
            $replacement = $seperator_options[ $separator ];
        }

        /**
         * Filter: 'wpseo_replacements_filter_sep' - Allow customization of the separator character(s).
         *
         * @api string $replacement The current separator.
         */
        return apply_filters( 'wpseo_replacements_filter_sep', $replacement );
    }

    /**
     * Set product seo data
     *
     * @since 2.5.3
     *
     * @param integer $post_id
     */
    public function save_product_seo_data( $post_id ) {
        if ( ! $post_id ) {
            return;
        }

        $get_postdata = wp_unslash( $_POST ); // phpcs:ignore

        if ( isset( $get_postdata['_yoast_wpseo_focuskw'] ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', $get_postdata['_yoast_wpseo_focuskw'] );
            update_post_meta( $post_id, '_yoast_wpseo_focuskw_text_input', $get_postdata['_yoast_wpseo_focuskw'] );
        }

        if ( isset( $get_postdata['_yoast_wpseo_title'] ) ) {
            $seo_title = $this->convert_dokan_to_yoast_format( sanitize_text_field( $get_postdata['_yoast_wpseo_title'] ) );
            update_post_meta( $post_id, '_yoast_wpseo_title', $seo_title );
        }

        if ( isset( $get_postdata['_yoast_wpseo_metadesc'] ) ) {
            $seo_metadesc = $this->convert_dokan_to_yoast_format( sanitize_text_field( $get_postdata['_yoast_wpseo_metadesc'] ) );
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $seo_metadesc );
        }
    }
}
