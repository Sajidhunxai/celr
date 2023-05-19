<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreSocialProfile extends TagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-social-profile-tag';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Social Profile', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render() {
        $links       = [];
        $network_map = dokan_elementor()->get_social_networks_map();

        if ( dokan_is_store_page() ) {
            $store       = dokan()->vendor->get( get_query_var( 'author' ) );
            $social_info = $store->get_social_profiles();

            foreach ( $network_map as $dokan_name => $elementor_name ) {
                if ( ! empty( $social_info[ $dokan_name ] ) ) {
                    $links[ $elementor_name ] = $social_info[ $dokan_name ];
                }
            }
        } else {
            foreach ( $network_map as $dokan_name => $elementor_name ) {
                $links[ $elementor_name ] = '#';
            }
        }

        echo wp_json_encode( $links );
    }
}
