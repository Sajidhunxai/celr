<?php

namespace DokanPro\Modules\Subscription;

use DokanPro\Modules\Subscription\SubscriptionPack;
use DokanPro\Modules\Subscription\Helper;
use WeDevs\Dokan\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * DPS Shortcode Class
 */
class Shortcode {

    use Singleton;

    /**
     * Boot method
     */
    public function boot() {
        $this->init_hooks();
    }

    /**
     * Init all hooks
     *
     * @return void
     */
    private function init_hooks() {
        add_shortcode( 'dps_product_pack', [ __CLASS__, 'create_subscription_package_shortcode' ] );
        add_action( 'dokan_after_saving_settings', [ __CLASS__, 'insert_shortcode_into_page' ], 10, 2 );
    }

    /**
     * Create subscription package shortcode
     *
     * @return void
     */
    public static function create_subscription_package_shortcode() {
        global $post;

        wp_enqueue_style( 'dps-custom-style' );
        wp_enqueue_script( 'dps-custom-js' );

        $user_id            = dokan_get_current_user_id();
        $subscription_packs = dokan()->subscription->all();

        ob_start();
        ?>

        <?php wc_print_notices(); ?>

        <div class="dokan-subscription-content">
            <?php
            $subscription = dokan()->vendor->get( $user_id )->subscription;
            ?>

            <?php if ( $subscription && $subscription->has_pending_subscription() ) : ?>
                <div class="seller_subs_info">
                    <?php
                    printf(
                        // translators: 1. Subscription title; 2. Subscription id.
                        __( 'The intend <span>%1$s</span> subscription is inactive due to payment failure. Please <a href="?add-to-cart=%2$s">Pay Now</a> to active it again.', 'dokan' ),
                        $subscription->get_package_title(),
                        $subscription->get_id()
                    );
                    ?>
                </div>
            <?php elseif ( $subscription && $subscription->can_post_product() ) : ?>
                <div class="seller_subs_info">
                    <p>
                        <?php
                        if ( $subscription->is_trial() ) {
                            $trial_title = $subscription->get_trial_range() . ' ' . $subscription->get_trial_period_types();
                            // translators: 1. Subscription pack name; 2. Trial title
                            printf( __( 'You are using <span>%1$s (%2$s trial)</span> package.', 'dokan' ), $subscription->get_package_title(), $trial_title );
                        } else {
                            // translators: Package title.
                            printf( __( 'You are using <span>%s</span> package.', 'dokan' ), $subscription->get_package_title() );
                        }
                        ?>
                    </p>
                    <p>
                        <?php
                        $no_of_product = '-1' !== $subscription->get_number_of_products() ? $subscription->get_number_of_products() : __( 'unlimited', 'dokan' );

                        if ( $subscription->is_recurring() ) {
                            // translators: Number of product.
                            printf( __( 'You can add <span>%s</span> products', 'dokan' ), $no_of_product );
                        } elseif ( $subscription->get_pack_end_date() === 'unlimited' ) {
                            // translators: Number of product.
                            printf( __( 'You can add <span>%s</span> product(s) for <span> unlimited days</span> days.', 'dokan' ), $no_of_product );
                        } else {
                            // translators: 1. Number of product; 2. Package validity days.
                            printf( __( 'You can add <span>%1$s</span> product(s) for <span>%2$s</span> days.', 'dokan' ), $no_of_product, $subscription->get_pack_valid_days() );
                        }
                        ?>
                    </p>
                    <p>
                        <?php
                        if ( $subscription->has_active_cancelled_subscrption() ) {
                            $date   = dokan_format_date( $subscription->get_pack_end_date() );
                            // translators: Package expire date.
                            $notice = sprintf( __( 'Your subscription has been cancelled! However it\'s is still active till %s', 'dokan' ), $date );
                            printf( "<span>{$notice}</span>" );
                        } else {
                            if ( $subscription->is_trial() ) { //phpcs:ignore.
                                // don't show any text
                            } elseif ( $subscription->is_recurring() ) {
                                // translators: Package recurring interval.
                                echo sprintf( __( 'You will be charged in every %d', 'dokan' ), $subscription->get_recurring_interval() ) . ' ' . Helper::recurring_period( $subscription->get_period_type() );
                            } elseif ( $subscription->get_pack_end_date() === 'unlimited' ) {
                                printf( __( 'You have a lifetime package.', 'dokan' ) );
                            } else {
                                // translators: Package expiration date.
                                printf( __( 'Your package will expire on <span>%s</span>', 'dokan' ), dokan_format_date( $subscription->get_pack_end_date() ) );
                            }
                        }
                        ?>
                    </p>

                    <?php
                    if ( ! ( ! $subscription->is_recurring() && $subscription->has_active_cancelled_subscrption() ) ) {
                        ?>
                        <p>
                        <form id="dps_submit_form" action="" method="post">
                            <?php
                            $maybe_reactivate = $subscription->is_recurring() && $subscription->has_active_cancelled_subscrption();
                            $notice           = $maybe_reactivate ? __( 'activate', 'dokan' ) : __( 'cancel', 'dokan' );
                            $nonce            = $maybe_reactivate ? 'dps-sub-activate' : 'dps-sub-cancel';
                            $input_name       = $maybe_reactivate ? 'dps_activate_subscription' : 'dps_cancel_subscription';
                            $btn_class        = $maybe_reactivate ? 'dokan-btn-success' : 'dokan-btn-danger';
                            $again            = $maybe_reactivate ? __( 'again', 'dokan' ) : '';
                            ?>

                            <label>
                                <?php
                                /* translators: 1: Required PHP Version 2: Running php version */
                                echo sprintf( __( 'To %1$s your subscription %2$s click here &rarr;', 'dokan' ), $maybe_reactivate ? __( 'activate', 'dokan' ) : __( 'cancel', 'dokan' ), $again );
                                ?>
                            </label>

                            <?php wp_nonce_field( $nonce ); ?>
                            <input type="hidden" name="<?php echo esc_attr( $input_name ); ?>" value="1">
                            <input type="submit" name="dps_submit" class="<?php echo esc_attr( "btn btn-sm {$btn_class}" ); ?>" value="<?php echo esc_attr( ucfirst( $notice ) ); ?>">
                        </form>
                        </p>
                        <?php
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php
            if ( $subscription_packs->have_posts() ) {
                ?>

                <?php if ( isset( $_GET['msg'] ) && 'dps_sub_cancelled' === sanitize_text_field( wp_unslash( $_GET['msg'] ) ) ) : //phpcs:ignore ?>
                    <div class="dokan-message">
                        <?php
                        if ( $subscription && $subscription->has_active_cancelled_subscrption() ) {
                            $date = dokan_format_date( $subscription->get_pack_end_date() );
                            // translators: Package validity date.
                            $notice = sprintf( __( 'Your subscription has been cancelled! However the it\'s is still active till %s', 'dokan' ), $date );
                        } else {
                            $notice = __( 'Your subscription has been cancelled!', 'dokan' );
                        }
                        ?>

                        <p><?php printf( $notice ); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ( isset( $_GET['msg'] ) && 'dps_sub_activated' === sanitize_text_field( wp_unslash( $_GET['msg'] ) ) ) : //phpcs:ignore ?>
                    <div class="dokan-message">
                        <?php
                        esc_html_e( 'Your subscription has been re-activated!', 'dokan' );
                        ?>
                    </div>
                <?php endif; ?>

                <div class="pack_content_wrapper">

                    <?php
                    while ( $subscription_packs->have_posts() ) {
                        $subscription_packs->the_post();

                        // get individual subscriptoin pack details
                        $sub_pack           = dokan()->subscription->get( get_the_ID() );
                        $is_recurring       = $sub_pack->is_recurring();
                        $recurring_interval = $sub_pack->get_recurring_interval();
                        $recurring_period   = $sub_pack->get_period_type();
                        ?>

                        <div class="product_pack_item <?php echo ( Helper::is_vendor_subscribed_pack( get_the_ID() ) || Helper::pack_renew_seller( get_the_ID() ) ) ? 'current_pack ' : ''; ?><?php echo ( $sub_pack->is_trial() && Helper::has_used_trial_pack( get_current_user_id(), get_the_id() ) ) ? 'fp_already_taken' : ''; ?>">
                            <div class="pack_price">

                                <span class="dps-amount">
                                    <?php echo wc_price( $sub_pack->get_price() ); ?>
                                </span>

                                <?php if ( $is_recurring && $recurring_interval === 1 ) { ?>
                                    <span class="dps-rec-period">
                                        <span class="sep">/</span><?php echo Helper::recurring_period( $recurring_period, $recurring_interval ); ?>
                                    </span>
                                <?php } ?>
                            </div><!-- .pack_price -->

                            <div class="pack_content">
                                <h2><?php echo $sub_pack->get_package_title(); ?></h2>
                                <?php the_content(); ?>

                                <div class="pack_data_option">
                                    <?php
                                    $no_of_product = $sub_pack->get_number_of_products();

                                    if ( '-1' === $no_of_product ) {
                                        echo sprintf( '<strong>%s</strong> %s <br />', __( 'Unlimited', 'dokan' ), __( 'Products', 'dokan' ) );
                                    } else {
                                        echo sprintf( '<strong>%d</strong> %s <br />', $no_of_product, __( 'Products', 'dokan' ) );
                                    }
                                    ?>
                                    <?php if ( $is_recurring && $sub_pack->is_trial() && Helper::has_used_trial_pack( get_current_user_id() ) ) : ?>
                                        <span class="dps-rec-period">
                                            <?php esc_html_e( 'In every', 'dokan' ); ?>
                                            <?php echo number_format_i18n( $recurring_interval ); ?>
                                            <?php echo Helper::recurring_period( $recurring_period, $recurring_interval ); ?>
                                        </span>
                                    <?php elseif ( $is_recurring && $sub_pack->is_trial() ) : ?>
                                        <span class="dps-rec-period">
                                            <?php esc_html_e( 'In every', 'dokan' ); ?>
                                            <?php echo number_format_i18n( $recurring_interval ); ?>
                                            <?php echo Helper::recurring_period( $recurring_period, $recurring_interval ); ?>
                                            <p class="trail-details">
                                                <?php echo $sub_pack->get_trial_range(); ?>
                                                <?php echo Helper::recurring_period( $sub_pack->get_trial_period_types(), $sub_pack->get_trial_range() ); ?>
                                                <?php esc_html_e( 'trial', 'dokan' ); ?>
                                            </p>
                                        </span>
                                    <?php elseif ( $is_recurring && $recurring_interval >= 1 ) : ?>
                                        <span class="dps-rec-period">
                                            <?php esc_html_e( 'In every', 'dokan' ); ?>
                                            <?php echo number_format_i18n( $recurring_interval ); ?>
                                            <?php echo Helper::recurring_period( $recurring_period, $recurring_interval ); ?>
                                        </span>
                                        <?php
                                    else :
                                        if ( empty( $sub_pack->get_pack_valid_days() ) ) {
                                            echo sprintf( '%1$s<br /><strong>%2$s</strong> %3$s', __( 'For', 'dokan' ), __( 'Unlimited', 'dokan' ), __( 'Days', 'dokan' ) );
                                        } else {
                                            $pack_validity = $sub_pack->get_pack_valid_days();
                                            echo sprintf( '%1$s<br /><strong>%2$s</strong> %3$s', __( 'For', 'dokan' ), $pack_validity, __( 'Days', 'dokan' ) );
                                        }
                                    endif;
                                    ?>
                                </div><!-- .pack_data_option -->
                            </div><!-- .pack_content -->

                            <div class="buy_pack_button">
                                <?php if ( Helper::is_vendor_subscribed_pack( get_the_ID() ) ) : ?>

                                    <a href="<?php echo get_permalink( get_the_ID() ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php esc_html_e( 'Your Pack', 'dokan' ); ?></a>

                                <?php elseif ( Helper::pack_renew_seller( get_the_ID() ) ) : ?>

                                    <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php esc_html_e( 'Renew', 'dokan' ); ?></a>

                                <?php else : ?>

                                    <?php if ( $sub_pack->is_trial() && Helper::vendor_has_subscription( dokan_get_current_user_id() ) && Helper::has_used_trial_pack( dokan_get_current_user_id() ) ) : ?>
                                        <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php esc_html_e( 'Switch Plan', 'dokan' ); ?></a>
                                    <?php elseif ( $sub_pack->is_trial() && Helper::has_used_trial_pack( dokan_get_current_user_id() ) ) : ?>
                                        <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php esc_html_e( 'Buy Now', 'dokan' ); ?></a>

                                    <?php elseif ( ! Helper::vendor_has_subscription( dokan_get_current_user_id() ) ) : ?>
                                        <?php if ( $sub_pack->is_trial() ) : ?>
                                            <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack trial_pack"><?php esc_html_e( 'Start Free Trial', 'dokan' ); ?></a>
                                        <?php else : ?>
                                            <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php esc_html_e( 'Buy Now', 'dokan' ); ?></a>
                                        <?php endif; ?>

                                    <?php else : ?>
                                        <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php esc_html_e( 'Switch Plan', 'dokan' ); ?></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div><!-- .buy_pack_button -->
                        </div><!-- .product_pack_item -->
                        <?php
                    }
                    ?>
                </div><!-- .dokan-subscription-content -->
                <?php
            } else {
                echo '<h3>' . __( 'No subscription pack has been found!', 'dokan' ) . '</h3>';
            }

            wp_reset_postdata();
            ?>
            <div class="clearfix"></div>
        </div><!-- .pack_content_wrapper -->
        <?php

        $contents = ob_get_clean();

        return apply_filters( 'dokan_sub_shortcode', $contents, $subscription_packs );
    }

    /**
     * Insert subscription shortcode into specefied page
     *
     * @param  string $option
     * @param  array $value
     *
     * @return void
     */
    public static function insert_shortcode_into_page( $option, $value ) {
        if ( ! $option || 'dokan_product_subscription' !== $option ) {
            return;
        }

        $page_id = isset( $value['subscription_pack'] ) ? $value['subscription_pack'] : null;

        if ( ! $page_id ) {
            return;
        }

        $content = [
            'ID'           => $page_id,
            'post_content' => '[dps_product_pack]',
        ];

        $insert = wp_update_post( $content );

        if ( is_wp_error( $insert ) ) {
            return wp_send_json_error( $insert->get_error_message() );
        }
    }
}

Shortcode::instance();
