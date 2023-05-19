<?php

namespace WeDevs\DokanPro\Modules\Razorpay\PaymentMethods;

use WC_Payment_Gateway;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
// use WeDevs\DokanPro\Modules\Razorpay\Webhook\WebhookHandler;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Razorpay Payment Gateway.
 *
 * @package WeDevs\DokanPro\Modules\Razorpay\PaymentMethods
 *
 * @since 3.5.0
 */
class Razorpay extends WC_Payment_Gateway {
    /**
     * Constructor for the razorpay gateway.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->supports = [
            'refunds',
        ];

        $this->init_fields();

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        $this->init_hooks();

        if ( ! $this->is_valid_for_use() ) {
            $this->enabled = 'no';
        }
    }

    /**
     * Init essential fields.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_fields() {
        $this->id                 = Helper::get_gateway_id();
        $this->icon               = false;
        $this->has_fields         = true;
        $this->method_title       = __( 'Dokan Razorpay', 'dokan' );
        $this->method_description = __( 'Pay Via Razorpay', 'dokan' );
        $this->icon               = apply_filters( 'woocommerce_razorpay_icon', DOKAN_RAZORPAY_ASSETS . 'images/razorpay.png' );

        $title                    = $this->get_option( 'title' );
        $this->title              = empty( $title ) ? __( 'Razorpay', 'dokan' ) : $title;
        $this->test_mode          = $this->get_option( 'test_mode' );
        $this->key_id             = $this->get_option( 'key_id' );
        $this->key_secret         = $this->get_option( 'key_secret' );
        $this->test_key_id        = $this->get_option( 'test_key_id' );
        $this->test_key_secret    = $this->get_option( 'test_key_secret' );
        $this->debug              = $this->get_option( 'debug' );
    }

    /**
     * Initialise Gateway Settings Form Fields.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = require DOKAN_RAZORPAY_TEMPLATE_PATH . 'admin-gateway-settings.php';
    }

    /**
     * Initialize necessary action hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( "woocommerce_update_options_payment_gateways_{$this->id}", [ &$this, 'process_admin_options' ] );
        add_action( 'admin_footer', [ $this, 'admin_script' ] );
    }

    /**
     * Check if this gateway is enabled and available in the user's country.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function is_valid_for_use() {
        if ( ! in_array( get_woocommerce_currency(), array_keys( Helper::get_supported_currencies() ), true ) ) {
            return false;
        }

        return true;
    }

    /**
     * Display information in frontend after checkout process button.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function payment_fields() {
        echo $this->get_option( 'description' );
    }

    /**
     * Process the payment and return the result.
     *
     * @since 3.5.0
     *
     * @param int $order_id
     *
     * @return array
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        // Get Woocommerce Order Key
        $order_key = $order->get_order_key();

        return [
            'result'   => 'success',
            'redirect' => add_query_arg( 'key', $order_key, $order->get_checkout_payment_url( true ) ),
        ];
    }

    /**
     * Admin options with extra information.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function admin_options() {
        if ( $this->is_valid_for_use() ) {
            parent::admin_options();
        } else {
            ?>
            <div class="error">
                <h2><?php esc_html_e( 'Dokan Razorpay Payment Gateway disabled', 'dokan' ); ?></h2>
                <p><?php esc_html_e( 'Dokan Razorpay does not support your store currency.', 'dokan' ); ?></p>

                <p>
                    <?php
                        echo wp_kses(
                            sprintf(
                                /* translators: 1: Razorpay supported currencies */
                                __( '<strong>Supported Currencies are: </strong> %s.', 'dokan' ),
                                implode( ', ', Helper::get_supported_currencies() )
                            ),
                            [
                                'strong' => [],
                            ]
                        );
                    ?>
                </p>
                <p>
                    <?php
                    echo wp_kses(
                        sprintf(
                            /* translators: 1: Woocommerce General Settings Page link */
                            __( 'To change your store currency, please go to <a href="%s">Woocommerce General Settings</a>.', 'dokan' ),
                            esc_url( admin_url( 'admin.php?page=wc-settings&tab=general' ) )
                        ),
                        [
                            'a' => [
                                'href' => [],
                            ],
                        ]
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Admin script.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function admin_script() {
        ?>
        <script type="text/javascript">
            ;(function ($, document) {
                const Dokan_Razorpay_Settings = {
                    payment_id_prefix: 'woocommerce_<?php echo Helper::get_gateway_id(); ?>_',

                    init: function() {
                        // Initially hide/toggle corresponding fields
                        Dokan_Razorpay_Settings.inputToggle();

                        //toggle sandbox and live api credentials
                        $(`#${Dokan_Razorpay_Settings.payment_id_prefix}test_mode`).on('change', Dokan_Razorpay_Settings.inputToggle);

                        Dokan_Razorpay_Settings.disbursementPeriodToggle();
                        // toggle disbursement mode
                        $(`#${Dokan_Razorpay_Settings.payment_id_prefix}disbursement_mode`).on('change', Dokan_Razorpay_Settings.disbursementPeriodToggle);

                        Dokan_Razorpay_Settings.noticeIntervalToggle();
                        //toggle notice interval fields
                        $(`#${Dokan_Razorpay_Settings.payment_id_prefix}display_notice_to_non_connected_sellers`).on('change', Dokan_Razorpay_Settings.noticeIntervalToggle);
                    },

                    inputToggle: function() {
                        const settings_input_ids = [
                            'key_id',
                            'key_secret'
                        ];

                        if ( $(`#${Dokan_Razorpay_Settings.payment_id_prefix}test_mode`).is(':checked') ) {
                            settings_input_ids.map(function (id) {
                                $('#' + Dokan_Razorpay_Settings.payment_id_prefix + 'test_' + id).closest('tr').show();
                                $('#' + Dokan_Razorpay_Settings.payment_id_prefix + id).closest('tr').hide();
                            });
                        } else {
                            settings_input_ids.map(function (id) {
                                $('#' + Dokan_Razorpay_Settings.payment_id_prefix + id).closest('tr').show();
                                $('#' + Dokan_Razorpay_Settings.payment_id_prefix + 'test_' + id).closest('tr').hide();
                            });
                        }
                    },

                    disbursementPeriodToggle: function() {
                        const val = $(`#${Dokan_Razorpay_Settings.payment_id_prefix}disbursement_mode`).val();
                        if ( val === 'DELAYED' ) {
                            $(`#${Dokan_Razorpay_Settings.payment_id_prefix}razorpay_disbursement_delay_period`).closest('tr').show();
                        } else {
                            $(`#${Dokan_Razorpay_Settings.payment_id_prefix}razorpay_disbursement_delay_period`).closest('tr').hide();
                        }
                    },

                    noticeIntervalToggle: function() {
                        const noticeCheckbox = $(`#${Dokan_Razorpay_Settings.payment_id_prefix}display_notice_to_non_connected_sellers`);
                        const noticeInterval = $(`#${Dokan_Razorpay_Settings.payment_id_prefix}display_notice_interval`);
                        if ( noticeCheckbox.prop('checked') ) {
                            noticeInterval.closest('tr').show();
                        } else {
                            noticeInterval.closest('tr').hide();
                        }
                    },
                };

                $( document ).ready( function ( $ ) {
                    Dokan_Razorpay_Settings.init();
                } );

            })(jQuery, document);
        </script>
        <?php
    }

    /**
     * Process admin options.
     *
     * @since 3.5.0
     *
     * @return bool|void
     */
    public function process_admin_options() {
        parent::process_admin_options();

        //create webhook automatically
        /**
         * @uses \WeDevs\DokanPro\Modules\Razorpay\WebhookHandler $instance
         */
        // $instance = dokan_pro()->module->razorpay->webhook;

        // if ( Helper::is_enabled() ) {
        //     // if gateway is enabled, automatically create webhook for this site
        //     if ( $instance instanceof WebhookHandler ) {
        //         $instance->register_webhook();
        //     }
        // } else {
        //     // if gateway is disabled, delete created webhook for this site
        //     if ( $instance instanceof WebhookHandler ) {
        //         $instance->deregister_webhook();
        //     }
        // }
    }

    /**
     * Check if this payment method is available with conditions.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function is_available() {
        $is_available = parent::is_available();

        if ( ! $is_available ) {
            return false;
        }

        // we are returning true for admin. otherwise admin will get error on the dashboard.
        if ( is_admin() ) {
            return true;
        }

        // check if admin provided all the api information right
        if ( ! Helper::is_ready() ) {
            return false;
        }

        if ( is_checkout_pay_page() ) {
            global $wp;

            //get order id if this is a order review page
            $order_id = isset( $wp->query_vars['order-pay'] ) ? $wp->query_vars['order-pay'] : null;

            $order = wc_get_order( $order_id );

            // return if this is not an order object
            if ( ! is_object( $order ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return whether or not this gateway still requires setup to function.
     *
     * When this gateway is toggled on via AJAX, if this returns true a
     * redirect will occur to the settings page instead.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function needs_setup() {
        if (
            empty( Helper::get_key_id() ) ||
            empty( Helper::get_key_secret() ) ) {
            return true;
        }

        return false;
    }

    /**
     * Process Response after getting payment from Razorpay.
     *
     * This is called after payment is made by user.
     *
     * @since 3.5.0
     *
     * @param int $order_id
     *
     * @return array
     */
    public function process_response( $order_id ) {
        $order = wc_get_order( $order_id );

        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        ];
    }
}

