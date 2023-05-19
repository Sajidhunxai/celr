<?php

namespace WeDevs\DokanPro\Admin\Notices;

/**
 * Admin notices handler class
 *
 * @since 3.4.3
 */
class Manager {
    /**
     * Class constructor
     */
    public function __construct() {
        $this->init_classes();
        // temporary disabling Dokan Pro Survey so not removing the existing codebase
        //$this->init_hooks();
    }

    /**
     * Register all notices classes to chainable container
     *
     * @since 3.4.3
     *
     * @return void
     */
    private function init_classes() {
        new DokanLiteMissing();
        new WhatsNew();
    }

    /**
     * Load Hooks
     *
     * @since 3.4.3
     *
     * @return void
     */
    private function init_hooks() {
        // dokan pro survey notices
        add_filter( 'dokan_admin_promo_notices', [ $this, 'dokan_pro_survey_notice' ] );
        add_action( 'wp_ajax_dismiss_dokan_pro_survey_notice', [ $this, 'ajax_dismiss_dokan_pro_survey_notice' ] );
    }

    /**
     * Display dismiss Table Rate Shipping module notice
     *
     * @since 3.4.3
     *
     * @param array $notices
     *
     * @return array
     */
    public function dokan_pro_survey_notice( $notices ) {
        if ( 'yes' === get_option( 'dismiss_dokan_pro_survey_notice', 'no' ) ) {
            return $notices;
        }

        $notices[] = [
            'type'        => 'info',
            'title'       => __( 'Would you mind spending 5-7 minutes to improve Dokan Pro by answering 7 simple questions?', 'dokan' ),
            /* translators: %s permalink settings url */
            'description' => '',
            'priority'    => 1,
            'show_close_button' => true,
            'ajax_data'   => [
                'action' => 'dismiss_dokan_pro_survey_notice',
                'nonce'  => wp_create_nonce( 'dismiss_dokan_pro_survey_removed_nonce' ),
            ],
            'actions'     => [
                [
                    'type'   => 'primary',
                    'text'   => __( 'Take the Survey', 'dokan' ),
                    'action' => 'https://wedevs.com/dokan/survey',
                    'target' => '_blank',
                ],
                [
                    'type'   => 'secondary',
                    'text'   => __( 'Already Participated', 'dokan' ),
                    'loading_text'   => __( 'Please wait...', 'dokan' ),
                    'completed_text' => __( 'Done', 'dokan' ),
                    'reload'         => true,
                    'ajax_data'   => [
                        'action' => 'dismiss_dokan_pro_survey_notice',
                        'nonce'  => wp_create_nonce( 'dismiss_dokan_pro_survey_removed_nonce' ),
                    ],
                ],
            ],
        ];

        return $notices;
    }

    /**
     * Dismiss Table Rate Shipping module ajax action.
     *
     * @since 3.4.3
     *
     * @return void
     */
    public function ajax_dismiss_dokan_pro_survey_notice() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dismiss_dokan_pro_survey_removed_nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        if ( ! current_user_can( 'activate_plugins' ) ) {
            wp_send_json_error( __( 'You do not have permission to perform this action.', 'dokan' ) );
        }

        update_option( 'dismiss_dokan_pro_survey_notice', 'yes' );

        wp_send_json_success();
    }
}
