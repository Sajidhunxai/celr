<?php

namespace WeDevs\DokanPro\Withdraw;

/**
 * Helper class for withdraw and disbursement functionality.
 */
class Helper {

    /**
     * Check if Biweekly schedule active.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_biweekly_schedule_active() {
        return in_array( 'biweekly', array_filter( self::get_active_schedules() ), true );
    }

    /**
     * Check if Monthly schedule active
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_monthly_schedule_active() {
        return in_array( 'monthly', array_filter( self::get_active_schedules() ), true );
    }

    /**
     * Get the months to run the quarterly schedule.
     *
     * @since 3.5.0
     *
     * @param string $quarter_month Starting month for the quarter.
     *
     * @return array
     */
    public static function get_quarterly_schedule_months( $quarter_month ) {
        $months_key = array_keys( self::get_month_list() );
        $selected   = array_search( $quarter_month, $months_key, true );

        $selected = ( $selected === false || $selected > 2 ) ? 0 : $selected; // month key expected (0-2).

        return [
            $months_key[ $selected ],
            $months_key[ $selected + 3 ],
            $months_key[ $selected + 6 ],
            $months_key[ $selected + 9 ],
        ];
    }

    /**
     * Get array of weeks in month.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_weeks_of_month_list() {
        return [
            '1' => __( 'First week', 'dokan' ),
            '2' => __( 'Second week', 'dokan' ),
            '3' => __( 'Third week', 'dokan' ),
            '4' => __( 'Fourth week', 'dokan' ),
            'L' => __( 'Last week', 'dokan' ),
        ];
    }

    /**
     * Get disbursement methods.
     *
     * @since 3.7.2
     *
     * @return array
     */
    public static function get_disbursement_schedules() {
        return [
            'quarterly' => __( 'Quarterly', 'dokan' ),
            'monthly'   => __( 'Monthly', 'dokan' ),
            'biweekly'  => __( 'Biweekly (Twice Per Month)', 'dokan' ),
            'weekly'    => __( 'Weekly', 'dokan' ),
        ];
    }

    /**
     * Get day of week for cron.
     *
     * @since 3.5.0
     *
     * @param string $week_day
     *
     * @return string
     */
    public static function get_cron_day_of_week( $week_day ) {
        $cron_days_of_week = self::get_cron_days_of_week_list();

        return isset( $cron_days_of_week[ $week_day ] ) ? $cron_days_of_week[ $week_day ] : $week_day;
    }

    /**
     * Get every schedule execution or start time on 24-hour format
     * or cron schedule format (minuit hour).
     *
     * @since 3.5.0
     *
     * @param bool $twenty_four_hour_format Want to get 24-hour format back?
     *
     * @return string
     */
    public static function get_schedule_start_time( $twenty_four_hour_format = false ) {
        $schedule_start_time = apply_filters( 'dokan_withdraw_disbursement_schedule_start_time', '23:00:00' );
        $schedule_datetime   = dokan_current_datetime()->modify( $schedule_start_time );
        if ( $twenty_four_hour_format ) {
            return $schedule_datetime->format( 'H:i:s' );
        }

        return intval( $schedule_datetime->format( 'i' ) ) . ' ' . $schedule_datetime->format( 'G' ); // for cron scheduler.
    }

    /**
     * Get human-readable week of month.
     *
     * @since 3.5.0
     *
     * @param string $week
     *
     * @return string
     */
    public static function get_human_readable_week_of_month( $week ) {
        $week_of_month = self::get_weeks_of_month_list();

        return isset( $week_of_month[ $week ] ) ? $week_of_month[ $week ] : $week;
    }

    /**
     * Get descriptive week of month like first, second, last etc.
     *
     * @since 3.5.0
     *
     * @param string $week
     *
     * @return string
     */
    public static function get_descriptive_week_of_month( $week ) {
        $week_of_month = self::get_descriptive_weeks_of_month_list();

        return isset( $week_of_month[ $week ] ) ? $week_of_month[ $week ] : $week;
    }

    /**
     * Check if weekly schedule active.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_weekly_schedule_active() {
        return in_array( 'weekly', array_filter( self::get_active_schedules() ), true );
    }

    /**
     * Check if withdraw disbursement enabled in admin settings.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_withdraw_disbursement_enabled() {
        return in_array( 'schedule', self::get_active_withdraw_systems(), true ) && ! empty( self::get_active_schedules() );
    }

    /**
     * Get the admin selected starting month for quarterly schedule.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_option_quarterly_schedule() {
        return dokan_get_option(
            'quarterly_schedule',
            'dokan_withdraw',
            [
                'month' => 'march',
                'week'  => '1',
                'days'  => 'monday',
            ]
        );
    }

    /**
     * Get the admin selected day of week for weekly schedule.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_option_weekly_schedule() {
        return dokan_get_option( 'weekly_schedule', 'dokan_withdraw', 'monday' );
    }

    /**
     * Get the admin selected starting week for biweekly schedule.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_option_biweekly_schedule() {
        return dokan_get_option(
            'biweekly_schedule',
            'dokan_withdraw',
            [
                'week' => '1',
                'days' => 'monday',
            ]
        );
    }

    /**
     * Get active withdraw system by admin.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_active_withdraw_systems() {
        return array_filter( dokan_get_option( 'disbursement', 'dokan_withdraw', [ 'manual' => 'manual' ] ) );
    }

    /**
     * Check if Quarterly schedule active
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_quarterly_schedule_active() {
        return in_array( 'quarterly', array_filter( self::get_active_schedules() ), true );
    }

    /**
     * Get vendor's currently selected schedule.
     *
     * @since 3.5.0
     *
     * @param int $vendor_id (Optional)
     *
     * @return string
     */
    public static function get_selected_schedule( $vendor_id = 0 ) {
        $vendor_id        = $vendor_id ? $vendor_id : dokan_get_current_user_id();
        $active_schedules = self::get_active_schedules();
        $schedule         = get_user_meta( $vendor_id, 'dokan_withdraw_selected_schedule', true );
        if ( ! empty( $schedule ) && in_array( $schedule, $active_schedules, true ) ) {
            return $schedule;
        }

        return ! empty( $active_schedules ) ? reset( $active_schedules ) : 'monthly';
    }

    /**
     * Get vendor's currently selected minimum withdraw amount.
     *
     * @since 3.5.0
     *
     * @param int $vendor_id (Optional)
     *
     * @return int
     */
    public static function get_selected_minimum_withdraw_amount( $vendor_id = 0 ) {
        $vendor_id        = $vendor_id ? $vendor_id : dokan_get_current_user_id();
        $amount_list      = self::get_nearest_minimum_withdraw_amount_list( self::get_minimum_withdraw_amount() );
        $minimum_amount   = get_user_meta( $vendor_id, 'dokan_withdraw_selected_minimum_balance', true );
        return ( ! empty( $minimum_amount ) && in_array( floatval( $minimum_amount ), $amount_list, true ) ) ? floatval( $minimum_amount ) : reset( $amount_list );
    }

    /**
     * Check if manual withdraw enabled in admin settings.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_manual_withdraw_enabled() {
        return in_array( 'manual', self::get_active_withdraw_systems(), true );
    }

    /**
     * Get array of days in week for cron.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_cron_days_of_week_list() {
        return [
            'saturday'  => 'SAT',
            'sunday'    => 'SUN',
            'monday'    => 'MON',
            'tuesday'   => 'TUE',
            'wednesday' => 'WED',
            'thursday'  => 'THU',
            'friday'    => 'FRI',
        ];
    }

    /**
     * Get human-readable day of week.
     *
     * @since 3.5.0
     *
     * @param string $day
     *
     * @return string
     */
    public static function get_human_readable_day_of_week( $day ) {
        $days_of_week = self::get_days_of_week_list();

        return isset( $days_of_week[ $day ] ) ? $days_of_week[ $day ] : $day;
    }

    /**
     * Get array of weeks in month like first, second, last etc.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_descriptive_weeks_of_month_list() {
        return [
            '1' => 'first',
            '2' => 'second',
            '3' => 'third',
            '4' => 'fourth',
            'L' => 'last',
        ];
    }

    /**
     * Get the admin selected day for monthly schedule.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_option_monthly_schedule() {
        return dokan_get_option(
            'monthly_schedule',
            'dokan_withdraw',
            [
                'week' => '1',
                'days' => 'monday',
            ]
        );
    }

    /**
     * Get array of days in week.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_days_of_week_list() {
        return [
            'saturday'  => __( 'Saturday', 'dokan' ),
            'sunday'    => __( 'Sunday', 'dokan' ),
            'monday'    => __( 'Monday', 'dokan' ),
            'tuesday'   => __( 'Tuesday', 'dokan' ),
            'wednesday' => __( 'Wednesday', 'dokan' ),
            'thursday'  => __( 'Thursday', 'dokan' ),
            'friday'    => __( 'Friday', 'dokan' ),
        ];
    }

    /**
     * Get the weeks to run the biweekly schedule.
     *
     * @since 3.5.0
     *
     * @param $biweekly_week
     *
     * @return array
     */
    public static function get_biweekly_schedule_weeks( $biweekly_week ) {
        $biweekly_keys = array_keys( self::get_weeks_of_month_list() );
        $selected_week = array_search( absint( $biweekly_week ), $biweekly_keys, true );

        $selected_week = ( ( false === $selected_week ) || ( $selected_week > 1 ) ) ? 0 : $selected_week;

        return [
            $biweekly_keys[ $selected_week ],
            $biweekly_keys[ $selected_week + 2 ],
        ];
    }

    /**
     * Get withdraw schedule title.
     *
     * @since 3.5.0
     *
     * @param string $schedule
     *
     * @return string
     */
    public static function get_schedule_title( $schedule ) {
        switch ( $schedule ) {
            case 'quarterly':
                $title = __( 'Quarterly', 'dokan' );
                break;
            case 'monthly':
                $title = __( 'Monthly', 'dokan' );
                break;
            case 'biweekly':
                $title = __( 'Twice Per Month', 'dokan' );
                break;
            case 'weekly':
                $title = __( 'Weekly', 'dokan' );
                break;
            default:
                $title = $schedule;
        }

        return apply_filters( 'dokan_withdraw_disbursement_schedule_title', $title, $schedule );
    }

    /**
     * Get array of month.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_month_list() {
        return [
            'january'   => __( 'January', 'dokan' ),
            'february'  => __( 'February', 'dokan' ),
            'march'     => __( 'March', 'dokan' ),
            'april'     => __( 'April', 'dokan' ),
            'may'       => __( 'May', 'dokan' ),
            'june'      => __( 'June', 'dokan' ),
            'july'      => __( 'July', 'dokan' ),
            'august'    => __( 'August', 'dokan' ),
            'september' => __( 'September', 'dokan' ),
            'october'   => __( 'October', 'dokan' ),
            'november'  => __( 'November', 'dokan' ),
            'december'  => __( 'December', 'dokan' ),
        ];
    }

    /**
     * Check if withdraw operation is enabled.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_withdraw_operation_enabled() {
        return 'on' !== dokan_get_option( 'hide_withdraw_option', 'dokan_withdraw', 'off' );
    }

    /**
     * Get quarterly starting month in `int`.
     *
     * @since 3.5.0
     *
     * @param string $month
     *
     * @return string
     */
    public static function get_quarterly_start_month( $month ) {
        switch ( $month ) {
            case 'february':
                return '2';
            case 'march':
                return '3';
            default:
                return '1';
        }
    }

    /**
     * Get Active Schedules from admin settings
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_active_schedules() {
        return array_filter( dokan_get_option( 'disbursement_schedule', 'dokan_withdraw', [] ) );
    }

    /**
     * Get minimum withdraw amount.
     * if no minimum amount return `false`.
     *
     * @since 3.5.0
     *
     * @return false|string
     */
    public static function get_minimum_withdraw_amount() {
        $minimum_amount = dokan_get_option( 'withdraw_limit', 'dokan_withdraw', - 1 );

        return ( - 1 !== $minimum_amount ) ? $minimum_amount : false;
    }

    /**
     * Get human-readable month
     *
     * @since 3.5.0
     *
     * @param string $month
     *
     * @return string
     */
    public static function get_human_readable_month( $month ) {
        $months = self::get_month_list();

        return isset( $months[ $month ] ) ? $months[ $month ] : $month;
    }

    /**
     * Get Minimum Withdraw Amount limit list.
     *
     * @since 3.5.0
     *
     * @param string|int|float $get_minimum_withdraw_amount
     *
     * @return array
     */
    public static function get_nearest_minimum_withdraw_amount_list( $get_minimum_withdraw_amount ) {
        return ( new Limiter( ceil( $get_minimum_withdraw_amount ) ) )->get_list();
    }

    /**
     * Get minimum remaining balance list.
     *
     * @since 3.5.0
     *
     * @return int[]
     */
    public static function get_minimum_reserve_balance_list() {
        return [ 0, 5, 10, 15, 50, 100, 200, 300, 500, 1000, 2000, 3000, 5000, 10000 ];
    }

    /**
     * Get vendor's selected minimum remaining balance after withdraw.
     *
     * @since 3.5.0
     *
     * @param $vendor_id
     *
     * @return int
     */
    public static function get_selected_reserve_balance( $vendor_id = 0 ) {
        $vendor_id        = $vendor_id ? $vendor_id : dokan_get_current_user_id();
        $amount_list      = self::get_minimum_reserve_balance_list();
        $minimum_amount   = get_user_meta( $vendor_id, 'dokan_withdraw_selected_reserve_balance', true );
        return ( ! empty( $minimum_amount ) && in_array( abs( $minimum_amount ), $amount_list, true ) ) ? abs( $minimum_amount ) : reset( $amount_list );
    }
}
