<?php

namespace WeDevs\DokanPro\Dashboard;

/**
 * Profile Progressbar Class.
 *
 * @since 3.7.13
 *
 * @author weDevs <info@wedevs.com>
 */
class ProfileProgress {

    /**
     * Get profile progressbar data.
     *
     * @since 3.7.13
     *
     * @param bool $new_dashboard
     *
     * @return array
     */
    public function get( $new_dashboard = false ) {
        $data = [
            'progress'           => 0,
            'next_todo'          => '',
            'next_todo_slug'     => $new_dashboard ? 'settings' : 'settings/store',
            'next_progress_text' => sprintf( __( 'Start with adding a Banner to gain profile progress', 'dokan' ) ),
            'progress_vals'      => 15,
            'progresses'         => [],
            'closed_by_user'     => false,
        ];

        $profile_info          = dokan_get_store_info( dokan_get_current_user_id() );
        $data['progress']      = isset( $profile_info['profile_completion']['progress'] ) ? $profile_info['profile_completion']['progress'] : 0;
        $data['next_todo']     = isset( $profile_info['profile_completion']['next_todo'] ) ? $profile_info['profile_completion']['next_todo'] : '';
        $data['progress_vals'] = isset( $profile_info['profile_completion']['progress_vals'] ) ? $profile_info['profile_completion']['progress_vals'] : 0;
        $data['progress']      = $data['progress'] > 100 ? 100 : $data['progress'];

        $is_closed_by_user      = isset( $profile_info['profile_completion']['closed_by_user'] ) ? $profile_info['profile_completion']['closed_by_user'] : false;
        $data['closed_by_user'] = $is_closed_by_user;

        if ( $data['progress'] >= 100 && $is_closed_by_user ) {
            return $data;
        }

        if ( $is_closed_by_user ) {
            $profile_info['profile_completion']['closed_by_user'] = false;
            update_user_meta( get_current_user_id(), 'dokan_profile_settings', $profile_info );
        }

        if ( strpos( $data['next_todo'], '-' ) !== false ) {
            $data['next_todo']     = substr( $data['next_todo'], strpos( $data['next_todo'], '-' ) + 1 );
            $data['progress_vals'] = isset( $profile_info['profile_completion']['progress_vals'] ) ? $profile_info['profile_completion']['progress_vals'] : 0;
            $data['progress_vals'] = isset( $data['progress_vals']['social_val'][ $data['next_todo'] ] ) ? $data['progress_vals']['social_val'][ $data['next_todo'] ] : 0;
        } else {
            $data['progress_vals'] = isset( $data['progress_vals'][ $data['next_todo'] ] ) ? $data['progress_vals'][ $data['next_todo'] ] : 15;
        }

        if ( 100 === absint( $data['progress'] ) ) {
            $data['next_progress_text'] = __( 'Congratulation, your profile is fully completed', 'dokan' );
        }

        $progress_info = $this->get_progress_info_by_key( $data['next_todo'] );

        $data['next_progress_text'] = sprintf(
            // translators: 1) Next progress bar title 2) Next progress bar value
            __( 'Add %1$s to gain %2$s%% progress', 'dokan' ),
            $progress_info['title'],
            number_format_i18n( $data['progress'] )
        );
        $data['next_todo_slug']     = $progress_info['slug'];
        $data['progresses']         = $this->get_progresses_with_completions( $profile_info );

        if ( ! $new_dashboard ) {
            $data['next_todo_slug'] = dokan_get_navigation_url( $data['next_todo_slug'] );
        }

        return $data;
    }

    /**
     * Process progress info data by key value.
     *
     * This will modify the $data array and
     * add next step message, URL slug etc.
     *
     * @since 3.7.13
     *
     * @param string $key_value
     *
     * @return array $data
     */
    private function get_progress_info_by_key( $key_value ) {
        $info = [
            'key'   => str_replace( '_val', '', $key_value ), // Remove _val from key_value
            'title' => '',
            'slug'  => 'settings/store',
        ];

        switch ( $key_value ) {
            case 'profile_picture_val':
                $info['title'] = __( 'Profile Picture', 'dokan' );
                break;

            case 'phone_val':
                $info['title'] = __( 'Phone', 'dokan' );
                break;

            case 'banner_val':
                $info['title'] = __( 'Banner', 'dokan' );
                break;

            case 'store_name_val':
                $info['title'] = __( 'Store Name', 'dokan' );
                break;

            case 'address_val':
                $info['title'] = __( 'Address', 'dokan' );
                break;

            case 'payment_method_val':
                $info['title']  = __( 'A Payment method', 'dokan' );
                $info['slug']   = 'settings/payment';
                break;

            case 'map_val':
                $info['title'] = __( 'Map location', 'dokan' );
                break;

            case 'fb':
                $info['title'] = __( 'Facebook', 'dokan' );
                break;

            case 'twitter':
                $info['title'] = __( 'Twitter', 'dokan' );
                break;

            case 'youtube':
                $info['title'] = __( 'Youtube', 'dokan' );
                break;

            case 'linkedin':
                $info['title'] = __( 'LinkedIn', 'dokan' );
                break;

            default:
                break;
        }

        return $info;
    }

    /**
     * Get Progress lists of a vendor profile with completed or not status.
     *
     * @since 3.7.13
     *
     * @param array $profile_info
     *
     * @return array
     */
    private function get_progresses_with_completions( $profile_info ) {
        $profile_completions = isset( $profile_info['profile_completion'] ) ? $profile_info['profile_completion'] : [];

        if ( ! count( $profile_completions ) ) {
            return [];
        }

        $progress_values = isset( $profile_completions['progress_vals'] ) ? $profile_completions['progress_vals'] : [];
        $progresses      = [];

        foreach ( $progress_values as $key => $progress_value ) {
            $progress = $this->get_progress_info_by_key( $key );

            if ( 'social_val' === $key ) {
                foreach ( $progress_value as $social_key => $social_value ) {
                    $social_progress              = $this->get_progress_info_by_key( $social_key );
                    $social_progress['value']     = $social_value;
                    $social_progress['completed'] = isset( $profile_completions[ $social_key ] ) ? 1 : 0;
                    array_push( $progresses, $social_progress );
                }
            } else {
                $progress['value']     = $progress_value;
                $progress['completed'] = isset( $profile_completions[ $progress['key'] ] ) ? 1 : 0;
                array_push( $progresses, $progress );
            }
        }

        return $progresses;
    }
}
