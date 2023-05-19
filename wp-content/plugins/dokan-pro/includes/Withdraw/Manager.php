<?php

namespace WeDevs\DokanPro\Withdraw;

use WeDevs\Dokan\Withdraw\Withdraw;
use WP_User;

/**
 * Withdraw Functionality class
 *
 * @since 2.4
 * @since 3.5.0 Automatic Withdraw Disbursement added.
 *
 * @author weDevs <info@wedevs.com>
 */
class Manager {

    /**
     * Constructor for the Manager class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return void
     */
    public function __construct() {
        if ( is_user_logged_in() ) {
            add_filter( 'dokan_withdraw_methods', [ $this, 'load_withdraw_method' ], 10 );
            add_filter( 'dokan_settings_fields', [ $this, 'withdraw_disbursement_schedule_settings' ], 40 );
            add_action( 'dokan_withdraw_content_after_last_payment_section', [ $this, 'add_withdraw_schedule_section' ], 5 );
            add_action( 'dokan_withdraw_content_after', [ $this, 'add_withdraw_schedule_popup_template' ], 10 );
            add_action( 'dokan_before_saving_settings', [ $this, 'validate_withdraw_schedule_option' ], 30, 2 );
        }

        add_action( 'init', [ $this, 'set_schedules' ], 30 );
        add_action( 'dokan_withdraw_quarterly_scheduler', [ $this, 'process_quarterly_schedule' ] );
        add_action( 'dokan_withdraw_monthly_scheduler', [ $this, 'process_monthly_schedule' ] );
        add_action( 'dokan_withdraw_biweekly_scheduler', [ $this, 'process_biweekly_schedule' ] );
        add_action( 'dokan_withdraw_weekly_scheduler', [ $this, 'process_weekly_schedule' ] );
        add_action( 'dokan_withdraw_individual_scheduler', [ $this, 'process_individual_schedule' ] );
        add_action( 'dokan_withdraw_disbursement_announcement_scheduler', [ $this, 'process_announcement_schedule' ] );
        add_action( 'dokan_withdraw_disbursement_schedule_announcement_scheduler', [ $this, 'process_schedule_change_announcement_schedule' ] );

        add_action( 'update_option_timezone_string', [ $this, 'handle_timezone_change' ], 10, 3 );
        add_action( 'update_option_gmt_offset', [ $this, 'handle_timezone_change' ], 10, 3 );
        add_action( 'update_option_dokan_withdraw', [ $this, 'handle_schedule_change' ], 10, 3 );
        add_action( 'update_option_dokan_withdraw', [ $this, 'handle_schedule_settings_change' ], 10, 3 );
        add_action( 'update_option_dokan_withdraw', [ $this, 'handle_withdraw_operation_enable_disable' ], 10, 3 );
        add_action( 'update_option_dokan_withdraw', [ $this, 'handle_admin_withdraw_method_change' ], 10, 3 );
        add_filter( 'dokan_withdraw_manual_request_enable', [ $this, 'enable_manual_withdraw' ] );
        add_filter( 'dokan_get_dashboard_nav', [ $this, 'unset_withdraw_page_menu' ] );

        if ( wp_doing_ajax() ) {
            add_action( 'wp_ajax_dokan_handle_withdraw_schedule_change_request', [ $this, 'handle_withdraw_schedule_change_request' ], 10 );
            add_action( 'wp_ajax_dokan_handle_withdraw_schedule_remove', [ $this, 'handle_withdraw_schedule_remove_request' ], 10 );
        }

        add_filter( 'dokan_withdraw_method_settings_title', [ $this, 'get_heading' ], 10, 2 );
        add_filter( 'dokan_withdraw_method_icon', [ $this, 'get_icon' ], 10, 2 );
        add_action( 'dokan_store_profile_saved', [ $this, 'save_skrill_progress' ], 10, 2 );
        add_filter( 'dokan_payment_settings_required_fields', [ $this, 'map_required_fields' ], 10, 3 );
        add_filter( 'dokan_withdraw_withdrawable_payment_methods', [ $this, 'include_skrill_to_payment_methods' ] );
    }

    /**
     * Load withdraw method
     *
     * @since 2.4
     *
     * @param  array $methods
     *
     * @return array
     */
    public function load_withdraw_method( $methods ) {
        $methods['skrill'] = [
            'title'    => __( 'Skrill', 'dokan' ),
            'callback' => [ $this, 'dokan_withdraw_method_skrill' ],
        ];

        return $methods;
    }

    /**
     * Callback for Skrill in store settings
     *
     * @since 2.4
     *
     * @global WP_User $current_user
     *
     * @param array $store_settings
     *
     * @return void
     */
    public function dokan_withdraw_method_skrill( $store_settings ) {
        $email = isset( $store_settings['payment']['skrill']['email'] ) ? esc_attr( $store_settings['payment']['skrill']['email'] ) : '';
        ob_start();
        ?>
        <div class="dokan-form-group">
            <div class="dokan-w8">
                <div class="dokan-input-group">
                    <span class="dokan-input-group-addon"><?php esc_html_e( 'E-mail', 'dokan' ); ?></span>
                    <input value="<?php echo esc_attr( $email ); ?>" name="settings[skrill][email]" class="dokan-form-control email" placeholder="you@domain.com" type="text">
                </div>
            </div>
        </div>

        <?php if ( dokan_is_seller_dashboard() ) : ?>
            <div class="dokan-form-group">
                <div class="dokan-w8">
                    <input name="dokan_update_payment_settings" type="hidden">
                    <button class="ajax_prev disconnect dokan_payment_disconnect_btn dokan-btn dokan-btn-danger <?php echo empty( $email ) ? 'dokan-hide' : ''; ?>" type="button" name="settings[skrill][disconnect]">
                        <?php esc_attr_e( 'Disconnect', 'dokan' ); ?>
                    </button>
                </div>
            </div>
            <?php
        endif;
        echo ob_get_clean();
    }

    /**
     * Add withdraw disbursement schedule settings subsection.
     *
     * @since 3.5.0
     *
     * @param array $settings
     *
     * @return array
     */
    public function withdraw_disbursement_schedule_settings( $settings ) {
        $months               = Helper::get_month_list();
        $schedules            = Helper::get_disbursement_schedules();
        $days_of_week         = Helper::get_days_of_week_list();
        $week_of_month        = Helper::get_weeks_of_month_list();
        $week_of_month_ex_4th = wp_array_slice_assoc(
            $week_of_month,
            [
                '1',
                '2',
                '3',
                'L',
            ]
        );

        $settings['dokan_withdraw']['disbursement_schedule_settings'] = [
            'name'          => 'disbursement_schedule_settings',
            'type'          => 'disbursement_sub_section',
            'label'         => __( 'Disbursement Schedule', 'dokan' ),
            'description'   => __( 'Select suitable Schedule for Auto Withdraw Process for Vendors.', 'dokan' ),
            'content_class' => 'sub-section-styles',
        ];
        $settings['dokan_withdraw']['disbursement'] = [
            'name'         => 'disbursement',
            'label'        => __( 'Withdraw Disbursement', 'dokan' ),
            'desc'         => __( 'Select suitable Withdraw Process for Vendors', 'dokan' ),
            'type'         => 'disbursement_method',
            'tooltip'      => __( 'Admin can enable one or both and the vendor will use which is convenient for them', 'dokan' ),
            'default'      => [ 'manual' => 'manual' ],
            'options'      => [
                'manual'   => __( 'Manual Withdraw', 'dokan' ),
                'schedule' => __( 'Schedule Disbursement', 'dokan' ),
            ],
            'common_class' => 'withdraw_disbursement',
        ];
        $settings['dokan_withdraw']['disbursement_schedule'] = [
            'name'         => 'disbursement_schedule',
            'label'        => __( 'Disbursement Schedule', 'dokan' ),
            'desc'         => __( 'Select suitable Schedule for Auto Withdraw Process for Vendors', 'dokan' ),
            'type'         => 'disbursement_type',
            'tooltip'      => __( 'Admin can enable multiple schedules, but the vendor can choose to use only one', 'dokan' ),
            'default'      => array_fill_keys( array_keys( $schedules ), '' ),
            'options'      => $schedules,
            'common_class' => 'withdraw_disbursement',
        ];
        $settings['dokan_withdraw']['quarterly_schedule'] = [
            'name'         => 'quarterly_schedule',
            'label'        => __( 'Quarterly Schedule', 'dokan' ),
            'desc'         => __( 'Select suitable months, weeks and day of week for Auto Withdraw Quarterly schedule execution', 'dokan' ),
            'type'         => 'schedule_quarterly',
            'default'      => [
                'month' => 'march',
                'week'  => '1',
                'days'  => 'monday',
            ],
            'options'      => [
                'first'  => array_slice( $months, 0, 3, true ),
                'second' => array_slice( $months, 3, 3, true ),
                'third'  => array_slice( $months, 6, 3, true ),
                'fourth' => array_slice( $months, 9, 3, true ),
                'week'   => $week_of_month_ex_4th,
                'days'   => $days_of_week,
            ],
            'common_class' => 'withdraw_disbursement',
        ];

        $settings['dokan_withdraw']['monthly_schedule'] = [
            'name'         => 'monthly_schedule',
            'label'        => __( 'Monthly Schedule', 'dokan' ),
            'desc'         => __( 'Select suitable weeks and day of week for Auto Withdraw Monthly schedule execution', 'dokan' ),
            'type'         => 'schedule_monthly',
            'default'      => [
                'week' => '1',
                'days' => 'monday',
            ],
            'options'      => [
                'week' => $week_of_month_ex_4th,
                'days' => $days_of_week,
            ],
            'common_class' => 'withdraw_disbursement',
        ];
        $settings['dokan_withdraw']['biweekly_schedule'] = [
            'name'         => 'biweekly_schedule',
            'label'        => __( 'Biweekly Schedule', 'dokan' ),
            'desc'         => __( 'Select suitable week for Auto Withdraw Biweekly schedule execution', 'dokan' ),
            'type'         => 'schedule_biweekly',
            'default'      => [
                'week'  => '1',
                'days'  => 'monday',
            ],
            'options'      => [
                'first'  => array_slice( $week_of_month, 0, 2, true ),
                'second' => array_slice( $week_of_month, 2, 2, true ),
                'days'   => $days_of_week,
            ],
            'common_class' => 'withdraw_disbursement',
        ];

        $settings['dokan_withdraw']['weekly_schedule'] = [
            'name'         => 'weekly_schedule',
            'label'        => __( 'Weekly Schedule', 'dokan' ),
            'desc'         => __( 'Select suitable day of the week for Auto Withdraw Weekly schedule execution', 'dokan' ),
            'type'         => 'schedule_weekly',
            'default'      => 'monday',
            'options'      => $days_of_week,
            'common_class' => 'withdraw_disbursement',
        ];

        return $settings;
    }

    /**
     * Include Withdraw schedule Section on Withdraw dashboard.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function add_withdraw_schedule_section() {
        if ( ! Helper::is_withdraw_disbursement_enabled() ) {
            return;
        }

        $vendor_id             = dokan_get_current_user_id();
        $default_method        = dokan_withdraw_get_default_method( $vendor_id );
        $minimum_amount_needed = Helper::get_selected_minimum_withdraw_amount( $vendor_id );
        $saved_schedule        = get_user_meta( $vendor_id, 'dokan_withdraw_selected_schedule', true );
        $is_schedule_selected  = ! empty( $saved_schedule ) && in_array( $saved_schedule, Helper::get_active_schedules(), true );
        $schedule_information  = __( 'Please update your withdraw schedule selection to get payment automatically.', 'dokan' );
        $threshold_information = '';

        if ( $is_schedule_selected ) {
            $schedule_information = sprintf(
                // translators: 1: Vendor's selected withdraw schedule 2: Selected scheduled day 3: Default Withdraw method, 4: Withdraw method information.
                __( '<strong>%1$s</strong> <small>( next on %2$s )</small> to <strong>%3$s</strong> <small>%4$s</small>', 'dokan' ),
                Helper::get_schedule_title( Helper::get_selected_schedule( $vendor_id ) ),
                $this->next_scheduled_day_for_withdraw( Helper::get_selected_schedule( $vendor_id ) ),
                dokan_withdraw_get_method_title( $default_method ),
                dokan_withdraw_get_method_additional_info( $default_method )
            );

            // translators: 1: Withdraw amount threshold.
            $threshold_information = ! empty( $minimum_amount_needed ) ? sprintf( __( 'Only when the balance is <strong>%1$s</strong> or more.', 'dokan' ), wc_price( $minimum_amount_needed ) ) : '';
        }

        dokan_pro_get_template(
            'withdraw/payment-details-schedule',
            [
                'schedule_information'  => $schedule_information,
                'threshold_information' => $threshold_information,
                'is_schedule_selected'  => $is_schedule_selected,
            ]
        );
    }

    /**
     * Include Withdraw schedule popup.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function add_withdraw_schedule_popup_template() {
        if ( ! Helper::is_withdraw_disbursement_enabled() ) {
            return;
        }
        $active_schedules = Helper::get_active_schedules();
        $schedules        = [];

        foreach ( $active_schedules as $schedule ) {
            $schedules[ $schedule ] = [
                'next'        => $this->next_scheduled_day_for_withdraw( $schedule ),
                'title'       => Helper::get_schedule_title( $schedule ),
                'description' => $this->get_schedule_description( $schedule ),
            ];
        }

        dokan_pro_get_template(
            'withdraw/tmpl-payment-details-schedule',
            [
                'selected_schedule'        => Helper::get_selected_schedule(),
                'schedules'                => $schedules,
                'minimum_amount_list'      => Helper::get_nearest_minimum_withdraw_amount_list( Helper::get_minimum_withdraw_amount() ),
                'minimum_amount_selected'  => Helper::get_selected_minimum_withdraw_amount(),
                'reserve_balance_list'     => Helper::get_minimum_reserve_balance_list(),
                'reserve_balance_selected' => Helper::get_selected_reserve_balance(),
                'active_methods'           => dokan_withdraw_get_withdrawable_active_methods(),
                'default_method'           => dokan_withdraw_get_default_method(),
            ]
        );
    }

    /**
     * Get the next scheduled run day of timestamp for selected schedule.
     *
     * @since 3.5.0
     *
     * @param string $schedule_type
     * @param bool $is_timestamp
     *
     * @return int|string
     */
    public function next_scheduled_day_for_withdraw( $schedule_type, $is_timestamp = false ) {
        $schedule_run_time = Helper::get_schedule_start_time( true );
        $now               = dokan_current_datetime();
        $next_year         = $now->modify( 'next year' )->format( 'Y' );

        switch ( $schedule_type ) {
            case 'quarterly':
                $quarter_option = Helper::get_option_quarterly_schedule();
                $quarter_month  = $quarter_option['month'];
                $quarter_day    = $quarter_option['days'];
                $quarter_week   = Helper::get_descriptive_week_of_month( $quarter_option['week'] );
                $quarter_months = Helper::get_quarterly_schedule_months( $quarter_month );

                $first_quarter = dokan_current_datetime()
                    ->modify( "{$quarter_week} {$quarter_day} of {$quarter_months[0]} this year {$schedule_run_time}" );

                $second_quarter = dokan_current_datetime()
                    ->modify( "{$quarter_week} {$quarter_day} of {$quarter_months[1]} this year {$schedule_run_time}" );

                $third_quarter = dokan_current_datetime()
                    ->modify( "{$quarter_week} {$quarter_day} of {$quarter_months[2]} this year {$schedule_run_time}" );

                $fourth_quarter = dokan_current_datetime()
                    ->modify( "{$quarter_week} {$quarter_day} of {$quarter_months[3]} this year {$schedule_run_time}" );

                if ( $now->getTimestamp() < $first_quarter->getTimestamp() ) {
                    $date = $first_quarter;
                    break;
                }
                if ( $now->getTimestamp() < $second_quarter->getTimestamp() ) {
                    $date = $second_quarter;
                    break;
                }
                if ( $now->getTimestamp() < $third_quarter->getTimestamp() ) {
                    $date = $third_quarter;
                    break;
                }
                if ( $now->getTimestamp() < $fourth_quarter->getTimestamp() ) {
                    $date = $fourth_quarter;
                    break;
                }

                $date = dokan_current_datetime()
                    ->modify( "{$quarter_week} {$quarter_day} of {$quarter_months[0]} {$next_year} {$schedule_run_time}" );
                break;
            case 'monthly':
                $month           = Helper::get_option_monthly_schedule();
                $month_week      = Helper::get_descriptive_week_of_month( $month['week'] );
                $month_day       = $month['days'];
                $this_month_time = dokan_current_datetime()
                    ->modify( "{$month_week} {$month_day} of this month {$schedule_run_time}" );

                if ( $now->getTimestamp() < $this_month_time->getTimestamp() ) {
                    $date = $this_month_time;
                    break;
                }
                $date = dokan_current_datetime()
                    ->modify( "{$month_week} {$month_day} of next month {$schedule_run_time}" );
                break;
            case 'biweekly':
                $biweekly_option = Helper::get_option_biweekly_schedule();
                $biweekly_week   = $biweekly_option['week'];
                $biweekly_day    = $biweekly_option['days'];
                $first_week      = dokan_current_datetime()
                    ->modify( "first {$biweekly_day} of this month" )
                    ->modify( $schedule_run_time );
                $second_week     = dokan_current_datetime()
                    ->modify( "second {$biweekly_day} of this month" )
                    ->modify( $schedule_run_time );

                if ( '1' === $biweekly_week ) {
                    if ( $now->getTimestamp() < $first_week->getTimestamp() ) {
                        $date = $first_week;
                        break;
                    }
                    if ( $now->getTimestamp() < $first_week->modify( '+2 weeks' )->getTimestamp() ) {
                        $date = $first_week->modify( '+2 weeks' );
                        break;
                    }
                    $date = dokan_current_datetime()
                        ->modify( "first {$biweekly_day} of next month" )
                        ->modify( $schedule_run_time );
                    break;
                }
                if ( $now->getTimestamp() < $second_week->getTimestamp() ) {
                    $date = $second_week;
                    break;
                }
                if ( $now->getTimestamp() < $second_week->modify( '+2 weeks' )->getTimestamp() ) {
                    $date = $second_week->modify( '+2 weeks' );
                    break;
                }
                $date = dokan_current_datetime()
                    ->modify( "second {$biweekly_day} of next month" )
                    ->modify( $schedule_run_time );
                break;
            case 'weekly':
                $weekly_day = Helper::get_option_weekly_schedule();
                $this_week  = dokan_current_datetime()
                    ->modify( 'this ' . $weekly_day )
                    ->modify( $schedule_run_time );
                if ( $now->getTimestamp() < $this_week->getTimestamp() ) {
                    $date = $this_week;
                    break;
                }
                $date = $this_week->modify( '+ 1 week' );
                break;
            default:
                $date = $now;
        }

        return $is_timestamp ? $date->getTimestamp() : dokan_format_date( $date->getTimestamp() );
    }

    /**
     * Handle Withdraw schedule change request.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function handle_withdraw_schedule_change_request() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_withdraw_schedule_nonce' ) ) {
            wp_send_json_error( esc_html__( 'Are you cheating?', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( esc_html__( 'You have no permission to do this action', 'dokan' ) );
        }

        $method = isset( $_POST['method'] ) ? sanitize_key( wp_unslash( $_POST['method'] ) ) : '';
        if ( empty( $method ) ) {
            wp_send_json_error( esc_html__( 'Please provide Withdrew method.', 'dokan' ) );
        }

        if ( ! in_array( $method, dokan_withdraw_get_active_methods(), true ) ) {
            wp_send_json_error( esc_html__( 'Method not active.', 'dokan' ) );
        }

        $schedule = isset( $_POST['schedule'] ) ? sanitize_key( wp_unslash( $_POST['schedule'] ) ) : '';
        if ( empty( $schedule ) ) {
            wp_send_json_error( esc_html__( 'Provide a schedule to set as default.', 'dokan' ) );
        }

        $minimum_withdraw_amount = isset( $_POST['minimum'] ) ? floatval( sanitize_text_field( wp_unslash( $_POST['minimum'] ) ) ) : 0;
        if ( $minimum_withdraw_amount < floatval( Helper::get_minimum_withdraw_amount() ) ) {
            wp_send_json_error( esc_html__( 'Please check minimum withdraw balance.', 'dokan' ) );
        }

        $reserve_amount = isset( $_POST['reserve'] ) ? absint( wp_unslash( $_POST['reserve'] ) ) : 0;
        if ( $reserve_amount < 0 ) {
            wp_send_json_error( esc_html__( 'Please check reserve amount.', 'dokan' ) );
        }

        update_user_meta( dokan_get_current_user_id(), 'dokan_withdraw_selected_schedule', $schedule );
        update_user_meta( dokan_get_current_user_id(), 'dokan_withdraw_selected_minimum_balance', abs( $minimum_withdraw_amount ) );
        update_user_meta( dokan_get_current_user_id(), 'dokan_withdraw_selected_reserve_balance', absint( $reserve_amount ) );
        update_user_meta( dokan_get_current_user_id(), 'dokan_withdraw_default_method', $method );

        wp_send_json_success( __( 'Withdraw schedule changed successfully.', 'dokan' ) );
    }

    /**
     * Handle Withdraw schedule remove request.
     *
     * @since 3.7.16
     *
     * @return void
     */
    public function handle_withdraw_schedule_remove_request() {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'remove-withdraw-schedule' ) ) {
            wp_send_json_error( esc_html__( 'Security validation failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( esc_html__( 'You have no permission to do this action.', 'dokan' ) );
        }

        update_user_meta( dokan_get_current_user_id(), 'dokan_withdraw_selected_schedule', '' );

        wp_send_json_success( __( 'Withdraw schedule removed successfully.', 'dokan' ) );
    }

    /**
     * Get the human-readable schedule timing.
     *
     * @since 3.5.0
     *
     * @param string $schedule
     *
     * @return string
     */
    public function get_schedule_description( $schedule ) {
        $quarter_option  = Helper::get_option_quarterly_schedule();
        $month_option    = Helper::get_option_monthly_schedule();
        $biweekly_option = Helper::get_option_biweekly_schedule();
        $weekly_day      = Helper::get_option_weekly_schedule();
        $months          = Helper::get_quarterly_schedule_months( $quarter_option['month'] );
        $weeks           = Helper::get_biweekly_schedule_weeks( $biweekly_option['week'] );

        switch ( $schedule ) {
            case 'quarterly':
                $info = sprintf(
                    // translators: 1: Selected week for quarterly schedule 2: Selected week day for quarterly schedule 3: Selected month for quarterly schedule separated by comma (,).
                    __( 'on %1$s %2$s of %3$s', 'dokan' ),
                    Helper::get_descriptive_week_of_month( $quarter_option['week'] ),
                    Helper::get_human_readable_day_of_week( $quarter_option['days'] ),
                    implode( ', ', array_map( [ Helper::class, 'get_human_readable_month' ], $months ) )
                );
                break;
            case 'monthly':
                $info = sprintf(
                    // translators: 1: Selected week for monthly schedule. 2: Selected day of week for monthly schedule.
                    __( 'on %1$s %2$s of every month.', 'dokan' ),
                    Helper::get_descriptive_week_of_month( $month_option['week'] ),
                    Helper::get_human_readable_day_of_week( $month_option['days'] )
                );
                break;
            case 'biweekly':
                $info = sprintf(
                    // translators: 1: Selected day for biweekly schedule 2: Selected week for biweekly schedule separated by comma (,).
                    __( 'on %1$s of %2$s of each month', 'dokan' ),
                    Helper::get_human_readable_day_of_week( $biweekly_option['days'] ),
                    implode( ', ', array_map( [ Helper::class, 'get_human_readable_week_of_month' ], $weeks ) )
                );
                break;
            case 'weekly':
                $info = sprintf(
                    // translators: 1: Selected day for biweekly schedule
                    __( 'on %1$s of every week.', 'dokan' ),
                    Helper::get_human_readable_day_of_week( $weekly_day )
                );
                break;
            default:
                $info = $schedule;
        }

        return apply_filters( 'dokan_withdraw_disbursement_schedule_description', $info, $schedule );
    }

    /**
     * Reschedule on some events like settings save or WP timestamp change.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function reschedule() {
        self::cancel_all_schedules();
        $this->set_schedules();
    }

    /**
     * Register or set admin selected schedules for withdraw.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function set_schedules() {
        if ( ! Helper::is_withdraw_operation_enabled() || ! Helper::is_withdraw_disbursement_enabled() ) {
            return;
        }

        if ( Helper::is_quarterly_schedule_active() ) {
            $this->set_quarterly_schedule();
        }

        if ( Helper::is_monthly_schedule_active() ) {
            $this->set_monthly_schedule();
        }

        if ( Helper::is_biweekly_schedule_active() ) {
            $this->set_biweekly_schedule();
        }

        if ( Helper::is_weekly_schedule_active() ) {
            $this->set_weekly_schedule();
        }
    }

    /**
     * Set single onetime disbursement schedule for withdraw.
     * It will run as soon as possible.
     *
     * @since 3.5.0
     *
     * @param array $args Schedule argument.
     * @param string $group Schedule group.
     *
     * @return void
     */
    public function set_single_disbursement_schedule( $args, $group ) {
        as_enqueue_async_action( 'dokan_withdraw_individual_scheduler', $args, $group );
    }

    /**
     * Cancel all schedules for withdraw.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public static function cancel_all_schedules() {
        if ( function_exists( 'as_unschedule_action' ) ) {
            as_unschedule_action( 'dokan_withdraw_quarterly_scheduler' );
            as_unschedule_action( 'dokan_withdraw_monthly_scheduler' );
            as_unschedule_action( 'dokan_withdraw_biweekly_scheduler' );
            as_unschedule_action( 'dokan_withdraw_weekly_scheduler' );
        }
    }

    /**
     * Register or set quarterly schedules for withdraw.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function set_quarterly_schedule() {
        $quarterly = Helper::get_option_quarterly_schedule();
        $starting  = Helper::get_quarterly_start_month( $quarterly['month'] );
        $timestamp = $this->next_scheduled_day_for_withdraw( 'quarterly', true );
        $week      = 'L' === $quarterly['week'] ? $quarterly['week'] : '#' . $quarterly['week'];
        $schedule  = Helper::get_schedule_start_time() . ' ? ' . $starting . '/3 ' . Helper::get_cron_day_of_week( $quarterly['days'] ) . $week;

        if ( false === as_next_scheduled_action( 'dokan_withdraw_quarterly_scheduler' ) ) {
            as_schedule_cron_action( $timestamp, $schedule, 'dokan_withdraw_quarterly_scheduler', [], 'dokan_withdraw_disbursement' );
        }
    }

    /**
     * Set monthly schedules for withdraw.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function set_monthly_schedule() {
        $monthly   = Helper::get_option_monthly_schedule();
        $timestamp = $this->next_scheduled_day_for_withdraw( 'monthly', true );
        $week      = ( $monthly['week'] === 'L' ) ? $monthly['week'] : '#' . $monthly['week'];
        $schedule  = Helper::get_schedule_start_time() . ' ? 1/1 ' . Helper::get_cron_day_of_week( $monthly['days'] ) . $week;

        if ( false === as_next_scheduled_action( 'dokan_withdraw_monthly_scheduler' ) ) {
            as_schedule_cron_action( $timestamp, $schedule, 'dokan_withdraw_monthly_scheduler', [], 'dokan_withdraw_disbursement' );
        }
    }

    /**
     * Set biweekly schedules for withdraw.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function set_biweekly_schedule() {
        $biweekly  = Helper::get_option_biweekly_schedule();
        $timestamp = $this->next_scheduled_day_for_withdraw( 'biweekly', true );
        $schedule  = Helper::get_schedule_start_time() . ( $biweekly['week'] === '1' ? ' 1-7,15-21 ' : ' 8-14,22-28 ' ) . '* ' . Helper::get_cron_day_of_week( $biweekly['days'] );

        if ( false === as_next_scheduled_action( 'dokan_withdraw_biweekly_scheduler' ) ) {
            as_schedule_cron_action( $timestamp, $schedule, 'dokan_withdraw_biweekly_scheduler', [], 'dokan_withdraw_disbursement' );
        }
    }

    /**
     * Set weekly schedules for withdraw.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function set_weekly_schedule() {
        $timestamp = $this->next_scheduled_day_for_withdraw( 'weekly', true );
        $schedule  = Helper::get_schedule_start_time() . ' ? * ' . Helper::get_cron_day_of_week( Helper::get_option_weekly_schedule() );

        if ( false === as_next_scheduled_action( 'dokan_withdraw_weekly_scheduler' ) ) {
            as_schedule_cron_action( $timestamp, $schedule, 'dokan_withdraw_weekly_scheduler', [], 'dokan_withdraw_disbursement' );
        }
    }

    /**
     * Process quarterly schedule.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function process_quarterly_schedule() {
        $this->process_schedule( 'quarterly' );

        /**
         * Action hook `dokan_withdraw_disbursement_after_quarterly_schedule`
         *
         * @since 3.5.0
         */
        do_action( 'dokan_withdraw_disbursement_after_quarterly_schedule' );
    }

    /**
     * Process monthly schedule.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function process_monthly_schedule() {
        $this->process_schedule( 'monthly' );

        /**
         * Action hook `dokan_withdraw_disbursement_after_monthly_schedule`
         *
         * @since 3.5.0
         */
        do_action( 'dokan_withdraw_disbursement_after_monthly_schedule' );
    }

    /**
     * Process biweekly schedule.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function process_biweekly_schedule() {
        $this->process_schedule( 'biweekly' );

        /**
         * Action hook `dokan_withdraw_disbursement_after_biweekly_schedule`
         *
         * @since 3.5.0
         */
        do_action( 'dokan_withdraw_disbursement_after_biweekly_schedule' );
    }

    /**
     * Process weekly schedule.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function process_weekly_schedule() {
        $this->process_schedule( 'weekly' );

        /**
         * Action hook `dokan_withdraw_disbursement_after_weekly_schedule`
         *
         * @since 3.5.0
         */
        do_action( 'dokan_withdraw_disbursement_after_weekly_schedule' );
    }

    /**
     * Process schedule.
     *
     * @since 3.5.0
     *
     * @param string $group_key
     *
     * @return void
     */
    public function process_schedule( $group_key ) {
        // @codingStandardsIgnoreStart
        $args = [
            'role__in'      => [
                'administrator',
                'seller',
            ],
            'meta_key'      => 'dokan_withdraw_selected_schedule',
            'meta_value'    => $group_key,
            'post_per_page' => - 1,
        ];
        // @codingStandardsIgnoreEnd

        $user_query = new \WP_User_Query( $args );

        if ( empty( $user_query->get_results() ) ) {
            return;
        }

        foreach ( $user_query->get_results() as $user ) {
            $this->set_single_disbursement_schedule(
                [
                    'user_id' => $user->ID,
                ],
                'dokan_withdraw_disbursement_' . $group_key
            );
        }
    }

    /**
     * Process individual onetime schedule.
     *
     * @since 3.5.0
     *
     * @param int $user_id
     *
     * @return void|\WP_Error
     */
    public function process_individual_schedule( $user_id ) {
        $default_withdraw_method = dokan_withdraw_get_default_method( $user_id );

        if (
            dokan()->withdraw->has_pending_request( $user_id )
            || ! dokan()->withdraw->has_withdraw_balance( $user_id )
            || ! in_array( $default_withdraw_method, dokan_withdraw_get_withdrawable_active_methods( $user_id ), true )
        ) {
            return;
        }

        $vendor_total_balance = dokan()->withdraw->get_user_balance( $user_id );
        $minimum_amount       = dokan()->withdraw->get_withdraw_limit();

        if ( empty( $vendor_total_balance ) ) {
            return;
        }

        if ( ! empty( $minimum_amount ) && $minimum_amount > $vendor_total_balance ) {
            return;
        }

        $vendor_minimum_amount  = Helper::get_selected_minimum_withdraw_amount( $user_id );
        $vendor_reserve_balance = Helper::get_selected_reserve_balance( $user_id );
        $withdraw_amount        = $vendor_total_balance - $vendor_reserve_balance;

        if ( $withdraw_amount < $minimum_amount || $vendor_total_balance < $vendor_minimum_amount ) {
            return;
        }

        $args = [
            'user_id' => $user_id,
            'amount'  => $withdraw_amount,
            'method'  => $default_withdraw_method,
        ];

        $validate_request = dokan()->withdraw->is_valid_approval_request( $args );

        if ( is_wp_error( $validate_request ) ) {
            return;
        }

        $data = [
            'user_id' => $user_id,
            'amount'  => $withdraw_amount,
            'status'  => dokan()->withdraw->get_status_code( 'pending' ),
            'method'  => $default_withdraw_method,
            'ip'      => 'UNKNOWN',
            'note'    => '',
        ];

        $withdraw = dokan()->withdraw->create( $data );
        if ( is_wp_error( $withdraw ) ) {
            return $withdraw;
        }

        /**
         * Action hook `dokan_withdraw_disbursement_after_request_create`
         *
         * @since 3.5.0
         *
         * @param Withdraw $withdraw Created Withdraw Request.
         */
        do_action( 'dokan_withdraw_disbursement_after_request_create', $withdraw );
    }

    /**
     * Process announcement schedule.
     *
     * @since 3.5.0
     *
     * @param array $options
     *
     * @return void
     */
    public function process_announcement_schedule( $options ) {
        list( $methods, $admin_id ) = $options;

        // @codingStandardsIgnoreStart
        $args = [
            'role__in'      => [
                'administrator',
                'seller',
            ],
            'meta_key'      => 'dokan_withdraw_default_method',
            'meta_value'    => array_keys( $methods ),
            'meta_compare'  => 'IN',
            'post_per_page' => -1,
            'fields'        => 'ID',
        ];
        // @codingStandardsIgnoreEnd

        $user_query = new \WP_User_Query( $args );

        if ( empty( $user_query->get_results() ) ) {
            return;
        }

        $args = [
            'title'       => __( 'Withdraw method disabled', 'dokan' ),
            'status'      => 'publish',
            'author'      => $admin_id,
            'sender_type' => 'selected_seller',
            'sender_ids'  => $user_query->get_results(),
            'content'     => __( 'The withdraw method you have set as default is disabled by admin.', 'dokan' ),
        ];

        dokan_pro()->announcement->create_announcement( $args );
    }

    /**
     * Process disbursement schedule change announcement schedule.
     *
     * @since 3.7.16
     *
     * @param array $options
     *
     * @return void
     */
    public function process_schedule_change_announcement_schedule( $options ) {
        list( $methods, $admin_id ) = $options;

        // @codingStandardsIgnoreStart
        $args = [
            'role__in'      => [
                'administrator',
                'seller',
            ],
            'meta_key'      => 'dokan_withdraw_selected_schedule',
            'meta_value'    => array_keys( $methods ),
            'meta_compare'  => 'IN',
            'post_per_page' => -1,
            'fields'        => 'ID',
        ];
        // @codingStandardsIgnoreEnd

        $user_query = new \WP_User_Query( $args );

        if ( empty( $user_query->get_results() ) ) {
            return;
        }

        $args = [
            'title'       => __( 'Your preferred withdrawal schedule is disabled.', 'dokan' ),
            'status'      => 'publish',
            'author'      => $admin_id,
            'sender_type' => 'selected_seller',
            'sender_ids'  => $user_query->get_results(),
            'content'     => __( "The admin has disabled the withdrawal schedule that you had set as your preferred payment schedule. Please reschedule it from the vendor dashboard's withdrawal page.", 'dokan' ),
        ];

        dokan_pro()->announcement->create_announcement( $args );
    }

    /**
     * Handle timezone change.
     *
     * @since 3.5.0
     *
     * @param array $old_value
     * @param array $value
     * @param string $option
     *
     * @return void
     */
    public function handle_timezone_change( $old_value, $value, $option ) {
        $this->reschedule();
    }

    /**
     * Handle Schedule change.
     *
     * @since 3.5.0
     * @since 3.7.16 Handle admin withdrawal schedule change.
     *
     * @param array $old_value
     * @param array $value
     * @param string $option
     *
     * @return void
     */
    public function handle_schedule_change( $old_value, $value, $option ) {
        if ( ! isset( $old_value['disbursement_schedule'], $value['disbursement_schedule'] ) || empty( array_diff_assoc( $old_value['disbursement_schedule'], $value['disbursement_schedule'] ) ) ) {
            return;
        }
        $this->reschedule();
        $this->handle_admin_withdraw_schedule_change( $old_value, $value, $option );
    }

    /**
     * Handle Schedule settings change.
     *
     * @since 3.5.0
     *
     * @param array $old_value
     * @param array $value
     * @param string $option
     *
     * @return void
     */
    public function handle_schedule_settings_change( $old_value, $value, $option ) {
        if (
            (
                isset( $old_value['quarterly_schedule'], $value['quarterly_schedule'] )
                && ! empty( array_diff_assoc( $old_value['quarterly_schedule'], $value['quarterly_schedule'] ) )
            )
            || (
                isset( $old_value['monthly_schedule'], $value['monthly_schedule'] )
                && ! empty( array_diff_assoc( $old_value['monthly_schedule'], $value['monthly_schedule'] ) )
            )
            || (
                isset( $old_value['biweekly_schedule'], $value['biweekly_schedule'] )
                && ! empty( array_diff_assoc( $old_value['biweekly_schedule'], $value['biweekly_schedule'] ) )
            )
            || (
                isset( $old_value['weekly_schedule'], $value['weekly_schedule'] )
                && $old_value['weekly_schedule'] !== $value['weekly_schedule']
            )
        ) {
            $this->reschedule();
        }
    }

    /**
     * Handle withdraw operation enable disable.
     *
     * @since 3.5.0
     *
     * @param array $old_value
     * @param array $value
     * @param string $option
     *
     * @return void
     */
    public function handle_withdraw_operation_enable_disable( $old_value, $value, $option ) {
        if ( ! isset( $old_value['hide_withdraw_option'], $value['hide_withdraw_option'] ) || $old_value['hide_withdraw_option'] === $value['hide_withdraw_option'] ) {
            return;
        }

        $this->reschedule();
    }


    /**
     * Handle withdraw methods enable disable.
     *
     * @since 3.5.0
     *
     * @param array $old_value
     * @param array $value
     * @param string $option
     *
     * @return void
     */
    public function handle_admin_withdraw_method_change( $old_value, $value, $option ) {
        if (
            ! isset( $value['send_announcement_for_payment_change'] )
            || empty( $value['send_announcement_for_payment_change'] )
            || ! is_array( $value['send_announcement_for_payment_change'] )
        ) {
            return;
        }

        as_enqueue_async_action(
            'dokan_withdraw_disbursement_announcement_scheduler',
            [
                [
                    $value['send_announcement_for_payment_change'],
                    dokan_get_current_user_id(),
                ],
            ],
            'dokan_withdraw_disbursement_announcement'
        );
    }


    /**
     * Handle withdraw disbursement schedule enable disable.
     *
     * @since 3.7.16
     *
     * @param array $old_value
     * @param array $value
     * @param string $option
     *
     * @return void
     */
    public function handle_admin_withdraw_schedule_change( $old_value, $value, $option ) {
        if (
            empty( $value['send_announcement_for_disbursement_schedule_change'] )
            || ! is_array( $value['send_announcement_for_disbursement_schedule_change'] )
        ) {
            return;
        }

        as_enqueue_async_action(
            'dokan_withdraw_disbursement_schedule_announcement_scheduler',
            [
                [
                    $value['send_announcement_for_disbursement_schedule_change'],
                    dokan_get_current_user_id(),
                ],
            ],
            'dokan_withdraw_disbursement_announcement'
        );
    }


    /**
     * Unset Seller dashboard withdraw page.
     *
     * @since 3.5.0
     *
     * @param array $urls
     *
     * @return array
     */
    public function unset_withdraw_page_menu( $urls ) {
        if ( ! Helper::is_withdraw_operation_enabled() ) {
            if ( array_key_exists( 'withdraw', $urls ) ) {
                unset( $urls['withdraw'] );
            }

            if ( array_key_exists( 'withdraw-requests', $urls ) ) {
                unset( $urls['withdraw-requests'] );
            }
        }

        return $urls;
    }

    /**
     * Validate Withdraw Disbursement system admin settings.
     *
     * @since 3.5.0
     *
     * @param array $option_name
     * @param array $option_value
     *
     * @return void
     */
    public function validate_withdraw_schedule_option( $option_name, $option_value ) {
        if ( 'dokan_withdraw' !== $option_name ) {
            return;
        }

        if ( empty( $option_value['disbursement'] ) ) {
            $errors[] = [
                'name'  => 'disbursement',
                'error' => __( 'Here must be at least one Withdraw Disbursement system that needs to be activated.', 'dokan' ),
            ];
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error(
                [
                    'settings' => [
                        'name'  => $option_name,
                        'value' => $option_value,
                    ],
                    'message'  => __( 'Validation error', 'dokan' ),
                    'errors'   => $errors,
                ],
                400
            );
        }
    }

    /**
     * Disable manual withdraw system.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function enable_manual_withdraw() {
        return Helper::is_manual_withdraw_enabled();
    }

    /**
     * Save Skrill progress settings data
     *
     * @since 3.5.6
     *
     * @return void
     **/
    public function save_skrill_progress( $store_id, $dokan_settings ) {
        if ( ! $store_id ) {
            return;
        }

        if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'dokan_payment_settings_nonce' ) ) {
            return;
        }

        if ( isset( $_POST['settings']['skrill'] ) && isset( $_POST['settings']['skrill']['email'] ) ) {
            $dokan_settings['payment']['skrill'] = array(
                'email' => sanitize_email( wp_unslash( $_POST['settings']['skrill']['email'] ) ),
            );

            update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
        }
    }

    /**
     * Get the Withdrawal method icon
     *
     * @since 3.5.6
     *
     * @param string $method_icon
     * @param string $method_key
     *
     * @return string
     */
    public function get_icon( $method_icon, $method_key ) {
        if ( 'skrill' === $method_key ) {
            $method_icon = DOKAN_PRO_PLUGIN_ASSEST . '/images/skrill-withdraw-method.svg';
        }

        return $method_icon;
    }

    /**
     * Get the heading for this payment's settings page
     *
     * @since 3.5.6
     *
     * @param string $heading
     * @param string $slug
     *
     * @return string
     */
    public function get_heading( $heading, $slug ) {
        if ( false !== strpos( $slug, 'skrill' ) ) {
            $heading = __( 'Skrill Settings', 'dokan' );
        }

        return $heading;
    }

    /**
     * Maps the required fields for custom withdraw method settings.
     *
     * @since 3.6.1
     *
     * @param array      $required_fields
     * @param string     $method_key
     * @param int|string $seller_id
     *
     * @return array
     */
    public function map_required_fields( $required_fields, $method_key, $seller_id ) {
        if ( 'skrill' === $method_key ) {
            $required_fields = [ 'email' ];
        }

        return $required_fields;
    }

    /**
     * Include Skrill to withdrawable payment methods
     *
     * @since 3.7.1
     *
     * @param array $payment_methods
     *
     * @return array
     */
    public function include_skrill_to_payment_methods( $payment_methods ) {
        $payment_methods[] = 'skrill';

        return $payment_methods;
    }
}
