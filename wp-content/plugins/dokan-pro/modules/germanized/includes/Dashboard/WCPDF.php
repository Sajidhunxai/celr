<?php
namespace WeDevs\DokanPro\Modules\Germanized\Dashboard;

use WeDevs\DokanPro\Modules\Germanized\Helper;
use WPO\WC\PDF_Invoices\Documents\Document_Number;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WCPDF {

    /**
     * Product constructor.
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function __construct() {
        $this->add_actions();
    }

    /**
     * Call all actions and filters
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function add_actions() {
        // enable pdf invoice number override by other plugin
        add_filter( 'wpo_wcpdf_external_invoice_number_enabled', '__return_true' );

        // add custom meta on vendor dashboard settings page
        add_action( 'dokan_settings_before_store_email', [ $this, 'vendor_dashboard_custom_pdf_fields' ], 10, 1 );

        //save vendor dashboard custom fields data
        add_action( 'dokan_store_profile_saved', [ $this, 'save_vendor_dashboard_custom_pdf_fields' ], 10, 1 );

        // generate dynamic invoice number for vendors
        add_filter( 'wpo_wcpdf_external_invoice_number', [ $this, 'generate_invoice_number' ], 10, 2 );

        // change pdf filename
        add_filter( 'wpo_wcpdf_filename', [ $this, 'wcpdf_filename' ], 10, 4 );
    }

    /**
     * This method will add vendor id and currenty year prefix to invoice filename
     *
     * @param string $filename
     * @param string $document_type
     * @param array $order_ids
     * @param string $context
     *
     * @since 3.3.1
     *
     * @return string
     */
    public function wcpdf_filename( $filename, $document_type, $order_ids, $context ) {
        $order_count = count( $order_ids );
        if ( $order_count > 1 ) {
            return $filename;
        }

        $seller_id = dokan_get_seller_id_by_order( $order_ids[0] );

        if ( ! $seller_id ) {
            return $filename;
        }

        $now = dokan_current_datetime();
        $new_filename = "invoice-vendor-{$seller_id}-" . $now->format( 'Y' ) . '-'; // file name with vendor id and current year

        return str_replace( 'invoice-', $new_filename, $filename );
    }

    /**
     * This method will override default invoice number for a vendor
     *
     * @param string|null $invoice_number
     * @param \WPO\WC\PDF_Invoices\Documents\Invoice $invoice
     *
     * @since 3.3.1
     *
     * @return string|Document_Number returning empty string will trigger creating invoice number from wcpdf plugin
     */
    public function generate_invoice_number( $invoice_number, $invoice ) {
        // check if admin settings is set to order number as invoice
        if ( isset( $invoice->settings['display_number'] ) && $invoice->settings['display_number'] === 'order_number' && ! empty( $invoice->order ) ) {
            return $invoice->order->get_order_number();
        }

        // validate if we got proper order number
        if ( empty( $invoice->order ) ) {
            return '';
        }

        if ( ! $invoice->order instanceof \WC_Order ) {
            return '';
        }

        // check if vendor wants to override invoice settings
        $seller_id = dokan_get_seller_id_by_order( $invoice->order->get_id() );

        if ( ! $seller_id ) {
            return '';
        }

        // get user invoice override settings
        $invoice_settings = get_user_meta( $seller_id, 'dokan_wcpdf_documents_settings_invoice', true );
        if ( empty( $invoice_settings ) || ( isset( $invoice_settings['override_invoice_num'] ) && absint( $invoice_settings['override_invoice_num'] ) === 0 ) ) {
            return '';
        }

        // get invoice number form vendor settings
        $number = isset( $invoice_settings['invoice_number'] ) ? absint( $invoice_settings['invoice_number'] ) : 0;
        if ( $number <= 0 ) {
            return '';
        }
        // prepare document settings arg
        $settings = [
            'prefix'  => $invoice_settings['number_format_prefix'],
            'suffix'  => $invoice_settings['number_format_suffix'],
            'padding' => $invoice_settings['number_format_padding'],
        ];

        // create new document number
        $invoice_number = new Document_Number( $number, $settings, $invoice, $invoice->order );

        // increment next invoice number
        $invoice_settings['invoice_number'] = $number + 1;

        // reset invoice number yearly
        if ( isset( $invoice_settings['reset_number_yearly'] ) && absint( $invoice_settings['reset_number_yearly'] ) === 1 ) {
            $last_order_date = Helper::get_vendor_last_order_date( $seller_id, $invoice->order->get_id() );
            if ( ! empty( $last_order_date ) ) {
                $today           = dokan_current_datetime();
                $last_order_date = $today->modify( $last_order_date );
                $current_year    = $today->format( 'Y' );
                $last_order_year = $last_order_date ? $last_order_date->format( 'Y' ) : $today->format( 'Y' );

                if ( $current_year !== $last_order_year ) {
                    $invoice_settings['invoice_number'] = 1;
                }
            }
        }

        update_user_meta( $seller_id, 'dokan_wcpdf_documents_settings_invoice', $invoice_settings );

        return $invoice_number;
    }
    /**
     * This method will add custom fields so that vendor's can modify their pdf invoice number
     *
     * @param int $store_id
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function vendor_dashboard_custom_pdf_fields( $store_id ) {
        $invoice_settings = get_user_meta( $store_id, 'dokan_wcpdf_documents_settings_invoice', true );
        if ( empty( $invoice_settings ) ) {
            $invoice_settings = [
                'override_invoice_num'  => 0,
                'invoice_number'        => 1,
                'number_format_prefix'  => '',
                'number_format_suffix'  => '',
                'number_format_padding' => 0,
                'reset_number_yearly'   => 0,
            ];
        }
        ?>
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="dokan_wcpdf_documents_settings_invoice_override"><?php esc_html_e( 'Override Invoice Number?', 'dokan' ); ?></label>
            <div class="dokan-w5 dokan-text-left">
                <input type="checkbox" id="dokan_wcpdf_documents_settings_invoice_override" name="dokan_wcpdf_documents_settings_invoice[override_invoice_num]" value="1" <?php echo checked( '1', $invoice_settings['override_invoice_num'] ); ?>>
            </div>
        </div>
        <div id="dokan_wcpdf_documents_settings_fields" style="display: <?php echo absint( $invoice_settings['override_invoice_num'] ) === 1 ? 'block' : 'none'; ?>;">
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan_wcpdf_documents_settings_invoice_number"><?php esc_html_e( 'Next invoice number (without prefix/suffix etc.)', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input id="dokan_wcpdf_documents_settings_invoice_number" value="<?php echo esc_attr( $invoice_settings['invoice_number'] ); ?>" name="dokan_wcpdf_documents_settings_invoice[invoice_number]" class="dokan-form-control input-md" type="number" size="10" min="1" step="1">
                    <div class="help-block"><?php esc_html_e( 'This is the number that will be used for the next document. By default, numbering starts from 1 and increases for every new document. Note that if you override this and set it lower than the current/highest number, this could create duplicate numbers!', 'dokan' ); ?></div>
                </div>
            </div>

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Invoice Number Format', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input type="text" id="dokan_wcpdf_documents_settings_invoice_prefix" name="dokan_wcpdf_documents_settings_invoice[number_format][prefix]" value="<?php echo esc_attr( $invoice_settings['number_format_prefix'] ); ?>" size="20" placeholder="<?php esc_attr_e( 'Prefix', 'dokan' ); ?>"><br>
                    <div class="help-block"><?php esc_html_e( 'to use the invoice year and/or month, use [invoice_year] or [invoice_month] respectively', 'dokan' ); ?></div><br>
                    <input type="text" id="dokan_wcpdf_documents_settings_invoice_suffix" name="dokan_wcpdf_documents_settings_invoice[number_format][suffix]" value="<?php echo esc_attr( $invoice_settings['number_format_suffix'] ); ?>" size="20" placeholder="<?php esc_attr_e( 'Suffix', 'dokan' ); ?>"><br><br>
                    <input type="number" id="dokan_wcpdf_documents_settings_invoice_padding" name="dokan_wcpdf_documents_settings_invoice[number_format][padding]" value="<?php echo esc_attr( $invoice_settings['number_format_padding'] ); ?>" size="20" min="0" step="1" placeholder="<?php esc_attr_e( 'Padding', 'dokan' ); ?>"><br>
                    <div class="help-block"><?php esc_html_e( 'enter the number of digits here - enter "6" to display 42 as 000042', 'dokan' ); ?></div>
                </div>
            </div>

            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="dokan_wcpdf_documents_settings_invoice_reset"><?php esc_html_e( 'Reset invoice number yearly', 'dokan' ); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input type="checkbox" id="dokan_wcpdf_documents_settings_invoice_reset" name="dokan_wcpdf_documents_settings_invoice[reset_number_yearly]" value="1" <?php echo checked( '1', $invoice_settings['reset_number_yearly'] ); ?>>
                </div>
            </div>
        </div>
        <script>
            ;(function( $, document ) {
                'use strict';
                const Dokan_PDF_Invoce_Settings = {
                    init: function () {
                        Dokan_PDF_Invoce_Settings.hide_invoice_fields();
                        $('#dokan_wcpdf_documents_settings_invoice_override').on( 'change', Dokan_PDF_Invoce_Settings.hide_invoice_fields );
                    },

                    hide_invoice_fields: function () {
                        if ( $('#dokan_wcpdf_documents_settings_invoice_override').is(':checked') ) {
                            $('#dokan_wcpdf_documents_settings_fields').fadeIn();
                        } else {
                            $('#dokan_wcpdf_documents_settings_fields').fadeOut();
                        }
                    },
                };

                $( document ).ready( function () {
                    Dokan_PDF_Invoce_Settings.init();
                } );
            })(jQuery, document);
        </script>
        <?php
    }

    /**
     * Save vendor dashboard custom fields data
     *
     * @param int $store_id
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function save_vendor_dashboard_custom_pdf_fields( $store_id ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'dokan_store_settings_nonce' ) ) {
            return;
        }

        if ( ! dokan_is_user_seller( $store_id ) ) {
            return;
        }
        // check if user choose to override invoice number
        $override_invoice_num = isset( $_POST['dokan_wcpdf_documents_settings_invoice']['override_invoice_num'] ) ? absint( wp_unslash( $_POST['dokan_wcpdf_documents_settings_invoice']['override_invoice_num'] ) ) : 0;
        if ( $override_invoice_num ) {
            $invoice_settings = [
                'override_invoice_num'  => $override_invoice_num,
                'invoice_number'        => isset( $_POST['dokan_wcpdf_documents_settings_invoice']['invoice_number'] ) ? absint( wp_unslash( $_POST['dokan_wcpdf_documents_settings_invoice']['invoice_number'] ) ) : 1,
                'number_format_prefix'  => isset( $_POST['dokan_wcpdf_documents_settings_invoice']['number_format']['prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_wcpdf_documents_settings_invoice']['number_format']['prefix'] ) ) : '',
                'number_format_suffix'  => isset( $_POST['dokan_wcpdf_documents_settings_invoice']['number_format']['suffix'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_wcpdf_documents_settings_invoice']['number_format']['suffix'] ) ) : '',
                'number_format_padding' => isset( $_POST['dokan_wcpdf_documents_settings_invoice']['number_format']['padding'] ) ? absint( wp_unslash( $_POST['dokan_wcpdf_documents_settings_invoice']['number_format']['padding'] ) ) : 0,
                'reset_number_yearly'   => isset( $_POST['dokan_wcpdf_documents_settings_invoice']['reset_number_yearly'] ) ? absint( wp_unslash( $_POST['dokan_wcpdf_documents_settings_invoice']['reset_number_yearly'] ) ) : 0,
            ];
        } else {
            $invoice_settings = (array) get_user_meta( $store_id, 'dokan_wcpdf_documents_settings_invoice', true );
            $invoice_settings['override_invoice_num'] = $override_invoice_num;
        }
        // store settings
        update_user_meta( $store_id, 'dokan_wcpdf_documents_settings_invoice', $invoice_settings );
    }
}
