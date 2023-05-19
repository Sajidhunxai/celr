<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;
use Elementor\Controls_Manager;

class StoreTabItems extends TagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-tab-items';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Tab Items', 'dokan' );
    }

    /**
     * Render Tag
     *
     * @since 2.9.11
     *
     * @return array
     */
    protected function get_value() {
        if ( dokan_is_store_page() ) {
            $store = dokan()->vendor->get( get_query_var( 'author' ) );

            if ( $store->id ) {
                $store_id = $store->id;
            }

            $store_tab_items = dokan_get_store_tabs( $store_id );
        } else {
            $store_tab_items = $this->get_store_tab_items();
        }

        $tab_items = [];

        foreach ( $store_tab_items as $item_key => $item ) {
            $url = $item['url'];

            if ( empty( $url ) && ! $store_id ) {
                $url = '#';
            }

            $tab_items[] = [
                'key'         => $item_key,
                'title'       => $item['title'],
                'text'        => $item['title'],
                'url'         => $url,
                'icon'        => '',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $item['title'],
                    'url'  => $url,
                ],
            ];
        }

        /**
         * Filter to modify tag values
         *
         * @since 2.9.11
         *
         * @param array $tab_items
         */
        return apply_filters( 'dokan_elementor_tags_store_tab_items_value', $tab_items );
    }

    protected function render() {
        echo wp_json_encode( $this->get_value() );
    }

    /**
     * Store tab items for Elementor Builder
     *
     * @since 2.9.14
     *
     * @return array
     */
    protected function get_store_tab_items() {
        return [
            'products' => [
                'title' => __( 'Products', 'dokan' ),
                'url'   => '#',
            ],
            'terms_and_conditions' => [
                'title' => __( 'Terms and Conditions', 'dokan' ),
                'url'   => '#',
            ],
            'reviews' => [
                'title' => __( 'Reviews', 'dokan' ),
                'url'   => '#',
            ],
            'vendor_biography' => [
                'title' => apply_filters( 'dokan_vendor_biography_title', __( 'Vendor Biography', 'dokan' ) ),
                'url'   => '#',
            ],
        ];
    }
}
