<?php
namespace WeDevs\DokanPro;

use WeDevs\DokanPro\Refund\Refund;
use WeDevs\Dokan\ReverseWithdrawal\SettingsHelper;
use WeDevs\Dokan\ReverseWithdrawal\Manager as ReverseWithdrawalManager;
use WeDevs\Dokan\ReverseWithdrawal\Helper as ReverseWithdrawalHelper;

/**
 * Class ReverseWithdrawal
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro
 */
class ReverseWithdrawal {
    /**
     * Class constructor
     *
     * @since 3.6.1
     */
    public function __construct() {
        // check if version match
        if ( ! class_exists( ReverseWithdrawalManager::class ) ) {
            return;
        }
        // call hooks
        $this->init_hooks();
    }

    /**
     * Initialize the hooks
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function init_hooks() {
        // send announcement email if balance threshold is exceeded
        add_action( 'dokan_reverse_withdrawal_balance_threshold_exceed', [ $this, 'send_balance_threshold_exceeded_announcement' ], 10, 2 );
        add_action( 'dokan_reverse_withdrawal_invoice_email_sent', [ $this, 'send_balance_threshold_exceeded_announcement' ], 10, 2 );

        // return commission amount after refund
        add_action( 'dokan_pro_refund_approved', [ $this, 'after_refund_request_approved' ], 10, 3 );

        // remove delivery time module section from checkout page
        add_action( 'woocommerce_review_order_before_payment', [ $this, 'remove_delivery_time_section_from_checkout' ], 9 );
    }

    /**
     * This method will remove delivery time module section from checkout page
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function remove_delivery_time_section_from_checkout() {
        if ( dokan_pro()->module->is_active( 'delivery_time' ) && ReverseWithdrawalHelper::has_reverse_withdrawal_payment_in_cart() ) {
            remove_action( 'woocommerce_review_order_before_payment', [ dokan_pro()->module->delivery_time->dt_frontend, 'render_delivery_time_template' ], 10 );
        }
    }

    /**
     * This method will send an announcement to vendor when balance threshold is exceeded
     *
     * @since 3.6.1
     *
     * @param int $vendor_id
     * @param array $balance
     *
     * @return void
     */
    public function send_balance_threshold_exceeded_announcement( $vendor_id, $due_status ) {
        if ( ! SettingsHelper::send_balance_exceeded_announcement() ) {
            return;
        }
        // if due is immediate then do not send announcement
        if ( 'immediate' === $due_status['due_date'] ) {
            return;
        }
        // prepare announcement message
        $message = sprintf(
            // translators: %1$s: balance amount, %2$s: reverse withdrawal url
            __( 'You have a reverse withdrawal balance of %1$s to be paid. Please <a href="%2$s">pay</a> it before %3$s.', 'dokan' ),
            wc_price( $due_status['balance']['payable_amount'] ),
            dokan_get_navigation_url( 'reverse-withdrawal' ),
            $due_status['due_date']
        );

        /**
         * @var $announcement \WeDevs\DokanPro\Admin\Announcement
         */
        $announcement = dokan_pro()->announcement;
        // sent announcement message
        $args = [
            'title'         => html_entity_decode( $message ),
            'sender_type'   => 'selected_seller',
            'sender_ids'    => [ $vendor_id ],
            'status'        => 'publish',
        ];
        $notice = $announcement->create_announcement( $args );

        if ( is_wp_error( $notice ) ) {
            dokan_log( sprintf( 'Reverse Withdrawal: Error Creating Announcement For Seller %1$s, Error Message: %2$s', $vendor_id, $notice->get_error_message() ) );
        }
    }

    /**
     * After refund request approved
     *
     * @since 3.6.1
     *
     * @param Refund $refund
     * @param array $args
     * @param float $vendor_refund
     *
     * @return void
     */
    public function after_refund_request_approved( $refund, $args, $vendor_refund ) {
        $manager = new ReverseWithdrawalManager();
        $order   = wc_get_order( $refund->get_order_id() );

        if ( ! $order ) {
            return;
        }

        // check if reverse withdrawal is added for this transaction
        if ( ! $manager->is_reverse_withdrawal_added( $refund->get_order_id() ) ) {
            return;
        }

        // we don't need to check if reverse withdrawal feature is enabled or not, previous check make sure that,
        // we've got commission for this order, so we need to refund to vendor

        $admin_refund = $refund->get_refund_amount() - $vendor_refund;
        if ( $admin_refund <= 0 ) {
            return;
        }

        // check the amount admin got from vendor as commission for this order
        $commission = $manager->get_commission_amount_by_order( $refund->get_order_id() );
        // get all refund transactions with given order id, because we are not going to refund more than what we got
        $refunded_amount = $manager->get_total_refunded_amount_by_order( $refund->get_order_id() );

        if ( wc_format_decimal( $order->get_total_refunded(), 2 ) === wc_format_decimal( $order->get_total( 'edit' ), 2 ) ) {
            // in case of full refund, we need to refund the commission amount minus already refunded amount
            $final_amount_to_refund = $commission - $refunded_amount;
        } elseif ( ( $refunded_amount + $admin_refund ) > $commission ) {
            // final refund amount will be the difference between refunded amount and commission
            $final_amount_to_refund = $commission - ( $refunded_amount + $admin_refund );
        } else {
            $final_amount_to_refund = $admin_refund;
        }

        // return if refund amount is less than 0
        if ( $final_amount_to_refund <= 0 ) {
            return;
        }

        // finally, insert into reverse withdrawal table
        $args = [
            'trn_id'    => $refund->get_order_id(),
            'trn_type'  => 'order_refund',
            'vendor_id' => $refund->get_seller_id(),
            'credit'    => $final_amount_to_refund,
            'note'      => 'Refunded to vendor',
        ];
        $manager->insert( $args );
    }
}
