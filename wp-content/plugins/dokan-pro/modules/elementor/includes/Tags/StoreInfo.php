<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;
use Elementor\Controls_Manager;

class StoreInfo extends TagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-info';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Info', 'dokan' );
    }

    /**
     * Render Tag
     *
     * @since 2.9.11
     *
     * @return array
     */
    protected function get_value() {
        $store_data = dokan_elementor()->get_store_data();

        $store_info = [
            [
                'key'         => 'address',
                'title'       => __( 'Address', 'dokan' ),
                'text'        => $store_data['address'],
                'icon'        => 'fas fa-map-marker',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['address'],
                ],
            ],
            [
                'key'         => 'phone',
                'title'       => __( 'Phone No', 'dokan' ),
                'text'        => $store_data['phone'],
                'icon'        => 'fas fa-mobile',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['phone'],
                ],
            ],
            [
                'key'         => 'email',
                'title'       => __( 'Email', 'dokan' ),
                'text'        => $store_data['email'],
                'icon'        => 'fas fa-envelope-open',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['email'],
                ],
            ],
            [
                'key'         => 'rating',
                'title'       => __( 'Rating', 'dokan' ),
                'text'        => $store_data['rating'],
                'icon'        => 'fas fa-star',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['rating'],
                ],
            ],
            [
                'key'         => 'open_close_status',
                'title'       => __( 'Open/Close Status', 'dokan' ),
                'text'        => $store_data['open_close'],
                'icon'        => 'fas fa-shopping-cart',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['open_close'],
                ],
            ],
        ];

        /**
         * Filter to modify tag values
         *
         * @since 2.9.11
         *
         * @param array $store_info
         */
        return apply_filters( 'dokan_elementor_tags_store_info_value', $store_info );
    }

    protected function render() {
        echo wp_json_encode( $this->get_value() );
    }
}
