<?php
/**
 * No cheating please
 */
if ( ! defined( 'WPINC' ) ) exit;

interface Moip_Subscription_Interface {
    /**
     * Create moip plan
     *
     * @param  object $order
     * @param  object $product
     * @param  int $subscription_interval
     * @param  string $subscription_period
     * @param  int $subscription_length
     * @param  array $trial_details
     *
     * @return void
     */
    public function create_plan( $order, $product, $subscription_interval, $subscription_period, $subscription_length, $trial_details = [] );

    /**
     * Edit a plan
     *
     * @param  int $plan_id
     * @param  object $order
     * @param  object $product
     * @param  int $subscription_interval
     * @param  string $subscription_period
     * @param  int $subscription_length
     *
     * @return int $plan_id
     */
    public function edit_plan( $plan_id, $order, $product, $subscription_interval, $subscription_period, $subscription_length, $trial_details = [] );

    /**
     * Create moip subscription
     *
     * @param  object $order
     * @param  int $plan_id
     *
     * @return int subscriptoin_id
     */
    public function create_subscription( $order, $plan_id );

    /**
     * Cancel a subscription
     *
     * @param  int $user_id
     * @param  string $subcription_code
     *
     * @return boolean
     */
    public function cancel_subscription( $user_id, $subscription_code );

    /**
     * Activate a suspended subscription
     *
     * @param  int $user_id
     * @param  string $subcription_code
     *
     * @return boolean
     */
    public function activate_subscription( $user_id, $subscription_code );

    /**
     * Suspend a subscription
     *
     * @param  int $user_id
     * @param  string $subcription_code
     *
     * @return boolean
     */
    public function suspend_subscription( $user_id, $subscription_code );

    /**
     * Update subscription with new plan
     *
     * @param  string $subcription_code
     * @param  string $plan_id
     *
     * @return boolean
     */
    public function update_subscription( $subscription_code, $plan_id );

    /**
     * Get a invoice
     *
     * @param  int $invoice_id
     *
     * @return array
     */
    public function get_invoice( $invoice_id );

    /**
     * Retry to pay a delayed invoice payment
     *
     * @return void
     */
    public function retry_payment();
}
