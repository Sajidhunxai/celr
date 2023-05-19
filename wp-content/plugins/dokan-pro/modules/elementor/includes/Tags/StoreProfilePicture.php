<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DataTagBase;
use Elementor\Controls_Manager;

class StoreProfilePicture extends DataTagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-profile-picture';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Profile Picture', 'dokan' );
    }

    /**
     * Store profile picture
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function get_value( array $options = [] ) {
        $picture = dokan_elementor()->get_store_data( 'profile_picture' );

        if ( empty( $picture['id'] ) ) {
            $settings = $this->get_settings();

            if ( ! empty( $settings['fallback'] ) ) {
                $picture = $settings['fallback'];
            }
        }

        return $picture;
    }

    /**
     * Register tag controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function register_controls() {
        $this->add_control(
            'fallback',
            [
                'label' => __( 'Fallback', 'dokan' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'id'  => 0,
                    'url' => get_avatar_url( 0 ),
                ],
            ]
        );
    }
}
