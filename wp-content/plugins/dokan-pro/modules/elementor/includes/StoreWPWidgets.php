<?php

namespace WeDevs\DokanPro\Modules\Elementor;

use WeDevs\Dokan\Traits\Singleton;
use WeDevs\Dokan\Walkers\StoreCategory as StoreCategoryWalker;

/**
 * Render Store Widgets in editing or preview mode
 */
class StoreWPWidgets {

    use Singleton;

    /**
     * Run after first instance
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function boot() {
        add_filter( 'elementor/widgets/wordpress/widget_args', [ $this, 'add_widget_args' ], 10, 2 );
        add_action( 'dokan_pro_widget_store_support_render', [ $this, 'widget_store_support' ], 10, 3 );
        add_action( 'dokan_widget_store_contact_form_render', [ $this, 'widget_store_contact_form' ], 10, 3 );
        add_action( 'dokan_widget_store_location_render', [ $this, 'widget_store_location' ], 10, 3 );
        add_action( 'dokan_widget_store_categories_render', [ $this, 'widget_store_categories' ], 10, 3 );
        add_action( 'dokan_widget_store_open_close_render', [ $this, 'widget_store_open_close' ], 10, 3 );
        add_action( 'dokan_widget_store_vendor_verification_render', [ $this, 'widget_store_vendor_verification' ], 10, 3 );
    }

    /**
     * Add store widget args in Elementor ecosystem
     *
     * @since 1.0.0
     *
     * @param array             $default_widget_args
     * @param \Widget_WordPress $widget_wordpress
     *
     * @return array
     */
    public static function add_widget_args( $default_widget_args, $widget_wordpress ) {
        $widget_class_name = get_class( $widget_wordpress->get_widget_instance() );

        if ( dokan()->widgets->get_id( $widget_class_name ) ) {
            $widget = $widget_wordpress->get_widget_instance();

            $id = str_replace( 'REPLACE_TO_ID', $widget_wordpress->get_id(), $widget->id );
            $default_widget_args['before_widget'] = sprintf( '<aside id="%1$s" class="widget dokan-store-widget %2$s">', $id, $widget->widget_options['classname'] );
            $default_widget_args['after_widget']  = '</aside>';
            $default_widget_args['before_title']  = '<h3 class="widget-title">';
            $default_widget_args['after_title']   = '</h3>';
        }

        return $default_widget_args;
    }

    /**
     * Render dummy content for Store Support widget
     *
     * @since 2.9.11
     *
     * @param array                                    $args
     * @param array                                    $instance
     * @param \WeDevs\Dokan\Widgets\StoreSupportWidget $widget
     *
     * @return void
     */
    public function widget_store_support( $args, $instance, $widget ) {
        if ( ! dokan_is_store_page() && ! is_product() ) {
            $defaults = [
                'title'       => __( 'Contact Vendor', 'dokan' ),
                'description' => '',
            ];

            $instance    = wp_parse_args( $instance, $defaults );
            $title       = apply_filters( 'dokan_store_support_widget_title', $instance['title'] );
            $description = $instance['description'];

            echo $args['before_widget'];
            if ( ! empty( $title ) ) {
                echo $args['before_title'] . sanitize_text_field( $title ) . $args['after_title'];
            }
            if ( ! empty( $description ) ) {
                echo '<p class="store-support-widget-desc">' . esc_textarea( $description ) . '</p>';
            }

            $get_default_text = dokan_get_option( 'support_button_label', 'dokan_store_support_setting', __( 'Get Support', 'dokan' ) );

            ?>
                <button class="dokan-store-support-btn dokan-btn dokan-btn-theme dokan-btn-sm">
                    <?php echo $get_default_text; ?>
                </button>
            <?php

            echo $args['after_widget'];
        }
    }

    /**
     * Render dummy content for Store Contact Form widget
     *
     * @since 2.9.11
     *
     * @param array                                  $args
     * @param array                                  $instance
     * @param \WeDevs\Dokan\Widgets\StoreContactForm $widget
     *
     * @return void
     */
    public function widget_store_contact_form( $args, $instance, $widget ) {
        if ( ! dokan_is_store_page() && ! is_product() ) {
            $defaults = [
                'title' => __( 'Contact Vendor', 'dokan' ),
            ];

            $instance = wp_parse_args( $instance, $defaults );

            $title = apply_filters( 'widget_title', $instance['title'] );

            echo $args['before_widget'];

            if ( ! empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            dokan_get_template_part(
                'widgets/store-contact-form', '', [
					'seller_id'  => 0,
					'store_info' => [],
					'username'   => 'username',
					'email'      => 'email@example.com',
				]
            );

            echo $args['after_widget'];
        }
    }

    /**
     * Render dummy content for Store Location widget
     *
     * @since 2.9.11
     *
     * @param array                               $args
     * @param array                               $instance
     * @param \WeDevs\Dokan\Widgets\StoreLocation $widget
     *
     * @return void
     */
    public function widget_store_location( $args, $instance, $widget ) {
        if ( ! dokan_is_store_page() ) {
            $defaults = [
                'title' => __( 'Store Location', 'dokan' ),
            ];

            $instance = wp_parse_args( $instance, $defaults );

            $title = apply_filters( 'widget_title', $instance['title'] );

            echo $args['before_widget'];

            if ( ! empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            dokan_get_template_part(
                'widgets/store-map', '', [
					'seller_id'    => 0,
					'map_location' => '23.8374439,90.3746137',
				]
            );

            echo $args['after_widget'];
        }
    }

    /**
     * Render dummy content for Store Categories widget
     *
     * @since 2.9.11
     *
     * @param array                                   $args
     * @param array                                   $instance
     * @param \WeDevs\Dokan\Widgets\StoreCategoryMenu $widget
     *
     * @return void
     */
    public function widget_store_categories( $args, $instance, $widget ) {
        if ( ! dokan_is_store_page() ) {
            $defaults = [
                'title' => __( 'Store Category', 'dokan' ),
            ];

            $instance = wp_parse_args( $instance, $defaults );
            $title    = apply_filters( 'widget_title', $instance['title'] );

            echo $args['before_widget'];

            if ( ! empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            $categories = get_terms(
                [
					'taxonomy' => 'product_cat',
					'number'   => 20,
				]
            );

            $walker = new StoreCategoryWalker( 0 );
            echo '<ul>';
            echo call_user_func_array( [ &$walker, 'walk' ], [ $categories, 0, [] ] );
            echo '</ul>';

            echo $args['after_widget'];
        }
    }

    /**
     * Render dummy content for Store Open Close widget
     *
     * @since 2.9.11
     *
     * @param array                                   $args
     * @param array                                   $instance
     * @param \WeDevs\Dokan\Widgets\StoreCategoryMenu $widget
     *
     * @return void
     */
    public function widget_store_open_close( $args, $instance, $widget ) {
        if ( ! dokan_is_store_page() ) {
            $defaults = [
                'title' => __( 'Store Time', 'dokan' ),
            ];

            $instance = wp_parse_args( $instance, $defaults );
            $title    = apply_filters( 'widget_title', $instance['title'] );

            echo $args['before_widget'];

            if ( ! empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            $open = [
                'status'       => 'open',
                'opening_time' => [ '8:00 AM' ],
                'closing_time' => [ '05:00 PM' ],
            ];

            $close = [
                'status'       => 'close',
                'opening_time' => [],
                'closing_time' => [],
            ];

            $store_time = [
                'monday'    => $open,
                'tuesday'   => $open,
                'wednesday' => $open,
                'thursday'  => $open,
                'friday'    => $open,
                'saturday'  => $close,
                'sunday'    => $close,
            ];

            dokan_get_template_part(
                'widgets/store-open-close', '', [
					'dokan_store_time' => $store_time,
                    'dokan_days'       => dokan_get_translated_days(),
				]
            );

            echo $args['after_widget'];
        }
    }

    /**
     * Render dummy content for Store Vendor Verification widget
     *
     * @since 2.9.13
     *
     * @param array                          $args
     * @param array                          $instance
     * @param \Dokan_Store_Verification_list $widget
     *
     * @return void
     */
    public function widget_store_vendor_verification( $args, $instance, $widget ) {
        if ( ! dokan_is_store_page() ) {
            $defaults = [
                'title' => __( 'ID Verification', 'dokan' ),
            ];

            $instance = wp_parse_args( $instance, $defaults );

            $store_info = [
                'dokan_verification' => [
                    'info' => [
                        'photo_id'          => 0,
                        'dokan_v_id_type'   => 'passport',
                        'dokan_v_id_status' => 'approved',
                        'store_address'     => [
                            'street_1' => '',
                            'street_2' => '',
                            'city'     => '',
                            'zip'      => '',
                            'country'  => '',
                            'state'    => '',
                            'v_status' => 'approved',
                        ],
                    ],
                    'verified_info' => [
                        'photo'         => [
                            'photo_id'        => 0,
                            'dokan_v_id_type' => 'passport',
                        ],
                        'store_address' => [
                            'street_1'        => '',
                            'street_2'        => '',
                            'city'            => '',
                            'zip'             => '',
                            'country'         => '',
                            'state'           => '',
                        ],
                    ],
                ],
            ];

            $seller_info = [
                'address' => $store_info['dokan_verification']['verified_info']['store_address'],
            ];

            $widget->set_seller_info( $seller_info );

            dokan_get_template_part(
                'widgets/vendor-verification', '', [
					'pro'        => true,
					'data'       => $args,
					'instance'   => $instance,
					'store_info' => $store_info,
					'widget'     => $widget,
				]
            );
        }
    }
}
