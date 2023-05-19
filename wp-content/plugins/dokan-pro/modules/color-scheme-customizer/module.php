<?php

namespace WeDevs\DokanPro\Modules\ColorSchemeCustomizer;

/**
 * Dokan_Apperance class
 *
 * @class Dokan_Apperance The class that holds the entire Dokan_Apperance plugin
 */
class Module {

    /**
     * Constructor for the Dokan_Apperance class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        $this->define_constant();
        $this->load_actions();
    }

    /**
     * Define all constants
     *
     * @since 3.7.0
     *
     * @return void
     */
    public function define_constant() {
        define( 'DOKAN_COLOR_CUSTOMIZER_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_COLOR_CUSTOMIZER_INC_DIR', DOKAN_COLOR_CUSTOMIZER_DIR . '/includes' );
        define( 'DOKAN_COLOR_CUSTOMIZER_TEMPLATE_DIR', DOKAN_COLOR_CUSTOMIZER_DIR . '/templates' );
        define( 'DOKAN_COLOR_CUSTOMIZER_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
     * Loaded all actions & filters.
     *
     * @since 3.7.0
     *
     * @return void
     */
    public function load_actions() {
        add_filter( 'dokan_settings_sections', array( $this, 'render_apperance_section' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'render_apperance_settings' ) );
        add_filter( 'dokan_localized_args', [ $this, 'render_header_section' ], 10, 1 );

        add_action( 'wp_head', array( $this, 'load_styles' ) );
        add_action( 'dokan_setup_wizard_styles', array( $this, 'load_styles' ) );
        add_action( 'init', [ $this, 'register_admin_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_scripts' ] );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-ac-styles' );
    }

    /**
     * Registers admin scripts
     *
     * @since 3.7.0
     *
     * @return void
     */
    public function register_admin_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_script( 'dokan-admin-color-pallete', DOKAN_COLOR_CUSTOMIZER_ASSETS_DIR . '/js/admin' . $suffix . '.js', [ 'dokan-vue-bootstrap' ], $version, true );
        wp_register_style( 'dokan-admin-color-pallete', DOKAN_COLOR_CUSTOMIZER_ASSETS_DIR . '/css/admin' . $suffix . '.css', false, $version, 'all' );
        wp_register_style( 'dokan-ac-styles', plugins_url( 'assets/css/style.css', __FILE__ ), false, $version );

        $data = [
            'btn_text'             => [
                'label'        => __( 'Button Text', 'dokan' ),
                'default'      => '#FFFFFF',
                'show_pallete' => false,
            ],
            'btn_primary'          => [
                'label'        => __( 'Button Background', 'dokan' ),
                'default'      => '#F05025',
                'show_pallete' => false,
            ],
            'btn_primary_border'   => [
                'label'        => __( 'Button Border', 'dokan' ),
                'default'      => '#DA502B',
                'show_pallete' => false,
            ],
            'btn_hover_text'       => [
                'label'        => __( 'Button Hover Text', 'dokan' ),
                'default'      => '#FFFFFF',
                'show_pallete' => false,
            ],
            'btn_hover'            => [
                'label'        => __( 'Button Hover Background', 'dokan' ),
                'default'      => '#DD3B0F',
                'show_pallete' => false,
            ],
            'btn_hover_border'     => [
                'label'        => __( 'Button Hover Border', 'dokan' ),
                'default'      => '#C83811',
                'show_pallete' => false,
            ],
            'dash_nav_text'        => [
                'label'        => __( 'Dashboard Sidebar Menu Text', 'dokan' ),
                'default'      => '#CFCFCF',
                'show_pallete' => false,
            ],
            'dash_nav_bg'          => [
                'label'        => __( 'Dashboard Sidebar Background', 'dokan' ),
                'default'      => '#1B233B',
                'show_pallete' => false,
            ],
            'dash_nav_active_text' => [
                'label'        => __( 'Dashboard Sidebar Active/Hover Menu Text', 'dokan' ),
                'default'      => '#FFFFFF',
                'show_pallete' => false,
            ],
            'dash_active_link'     => [
                'label'        => __( 'Dashboard Sidebar Active Menu Background', 'dokan' ),
                'default'      => '#F05025',
                'show_pallete' => false,
            ],
        ];

        wp_localize_script( 'dokan-admin-color-pallete', 'dokanColorSettings', $data );
    }

    /**
     * Load admin scripts in dokan settings.
     *
     * @since 3.7.0
     *
     * @param string $hook
     *
     * @return array
     */
    public function load_admin_scripts( $hook ) {
        // load vue app inside the parent menu only
        if ( 'toplevel_page_dokan' === $hook ) {
            wp_enqueue_script( 'dokan-admin-color-pallete' );
            wp_enqueue_style( 'dokan-admin-color-pallete' );
        }
    }

    /**
     * Add Settings section in Dokan Settings
     *
     * @since 1.0
     *
     * @param array $sections
     *
     * @return array
     */
    public function render_apperance_section( $sections ) {
        $sections[] = [
            'id'                   => 'dokan_colors',
            'title'                => __( 'Colors', 'dokan' ),
            'icon_url'             => DOKAN_COLOR_CUSTOMIZER_ASSETS_DIR . '/images/colors.svg',
            'description'          => __( 'Store Color Customization', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/color-scheme/',
            'settings_title'       => __( 'Colors Settings', 'dokan' ),
            'settings_description' => __( 'You can configure your general site settings with the option to adjust the color of your dashboard.', 'dokan' ),
        ];

        return $sections;
    }

    /**
     * Get default colors for vendor dashboard.
     *
     * @since 3.7.0
     *
     * @return array
     */
    public function get_default_color_settings() {
        // return deafult dashboard colors.
        return [
            'value'                => 'default',
            'btn_text'             => '#FFFFFF',
            'btn_hover'            => '#DD3B0F',
            'btn_primary'          => '#F05025',
            'dash_nav_bg'          => '#1B233B',
            'dash_nav_text'        => '#CFCFCF',
            'pallete_status'       => 'template',
            'btn_hover_text'       => '#FFFFFF',
            'dash_active_link'     => '#F05025',
            'btn_hover_border'     => '#C83811',
            'btn_primary_border'   => '#DA502B',
            'dash_nav_active_text' => '#FFFFFF',
            'color_options'        => [
                'color-1' => '#1B233B',
                'color-2' => '#F05025',
                'color-3' => '#CFCFCF',
                'color-4' => '#DD3B0F',
            ],
        ];
    }

    /**
     * Add Color pick options on Dokan Settings under Color section
     *
     * @since 1.0
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function render_apperance_settings( $settings_fields ) {
        $settings_fields['dokan_colors'] = [
            'store_color_pallete' => [
                'name'    => 'store_color_pallete',
                'type'    => 'color_pallete',
                'desc'    => __( 'Select a color palette for your store.', 'dokan' ),
                'label'   => __( 'Store Colors', 'dokan' ),
                // Choose color templates from multiple color palletes.
                'options' => [
                    'default'       => $this->get_default_color_settings(),
                    'petal_party'   => [
                        'value'                => 'petal party',
                        'btn_text'             => '#FFFFFF',
                        'btn_hover'            => '#C33385',
                        'btn_primary'          => '#D43790',
                        'dash_nav_bg'          => '#870A30',
                        'dash_nav_text'        => '#F4BECF',
                        'pallete_status'       => 'template',
                        'btn_hover_text'       => '#FFFFFF',
                        'dash_active_link'     => '#D43790',
                        'btn_hover_border'     => '#BB3381',
                        'btn_primary_border'   => '#C83588',
                        'dash_nav_active_text' => '#FFFFFF',
                        'color_options'        => [
                            'color-1' => '#870A30',
                            'color-2' => '#D43790',
                            'color-3' => '#F4BECF',
                            'color-4' => '#C33385',
                        ],
                    ],
                    'pinky'         => [
                        'value'                => 'pinky',
                        'btn_text'             => '#FFFFFF',
                        'btn_hover'            => '#DF0070',
                        'btn_primary'          => '#FF0080',
                        'dash_nav_bg'          => '#A91B60',
                        'dash_nav_text'        => '#EBE0D0',
                        'pallete_status'       => 'template',
                        'btn_hover_text'       => '#FFFFFF',
                        'dash_active_link'     => '#D43790',
                        'btn_hover_border'     => '#C50063',
                        'btn_primary_border'   => '#D2006A',
                        'dash_nav_active_text' => '#FFFFFF',
                        'color_options'        => [
                            'color-1' => '#B52E5F',
                            'color-2' => '#FF0080',
                            'color-3' => '#EBE1D1',
                            'color-4' => '#FF21E1',
                        ],
                    ],
                    'ocean'         => [
                        'value'                => 'ocean',
                        'btn_text'             => '#FFFFFF',
                        'btn_hover'            => '#2FA3D1',
                        'btn_primary'          => '#34ABDB',
                        'dash_nav_bg'          => '#38748C',
                        'dash_nav_text'        => '#99C7DA',
                        'pallete_status'       => 'template',
                        'btn_hover_text'       => '#FFFFFF',
                        'dash_active_link'     => '#34ABDB',
                        'btn_hover_border'     => '#2C98C3',
                        'btn_primary_border'   => '#2AA1D1',
                        'dash_nav_active_text' => '#FFFFFF',
                        'color_options'        => [
                            'color-1' => '#38748C',
                            'color-2' => '#34ABDB',
                            'color-3' => '#99C7DA',
                            'color-4' => '#2FA3D1',
                        ],
                    ],
                    'sweety'        => [
                        'value'                => 'sweety',
                        'btn_text'             => '#FFFFFF',
                        'btn_hover'            => '#EC3661',
                        'btn_primary'          => '#FB4570',
                        'dash_nav_bg'          => '#FB4570',
                        'dash_nav_text'        => '#FFC8D5',
                        'pallete_status'       => 'template',
                        'btn_hover_text'       => '#FFFFFF',
                        'dash_active_link'     => '#C61740',
                        'btn_hover_border'     => '#D33A5E',
                        'btn_primary_border'   => '#E4446A',
                        'dash_nav_active_text' => '#FFFFFF',
                        'color_options'        => [
                            'color-1' => '#FB4570',
                            'color-2' => '#C61740',
                            'color-3' => '#FFC8D5',
                            'color-4' => '#FB4570',
                        ],
                    ],
                    'summer_splash' => [
                        'value'                => 'summer splash',
                        'btn_text'             => '#FFFFFF',
                        'btn_hover'            => '#228D9C',
                        'btn_primary'          => '#29A0B1',
                        'dash_nav_bg'          => '#29A0B1',
                        'dash_nav_text'        => '#BDECF2',
                        'pallete_status'       => 'template',
                        'btn_hover_text'       => '#FFFFFF',
                        'dash_active_link'     => '#167D7F',
                        'btn_hover_border'     => '#1E7683',
                        'btn_primary_border'   => '#218E9D',
                        'dash_nav_active_text' => '#FFFFFF',
                        'color_options'        => [
                            'color-1' => '#29A0B1',
                            'color-2' => '#167D7F',
                            'color-3' => '#BDECF2',
                            'color-4' => '#228D9C',
                        ],
                    ],
                    'tree'          => [
                        'value'                => 'tree',
                        'btn_text'             => '#FFFFFF',
                        'btn_hover'            => '#1DADA0',
                        'btn_primary'          => '#1CB6A7',
                        'dash_nav_bg'          => '#1BAC9E',
                        'dash_nav_text'        => '#ABF5EE',
                        'pallete_status'       => 'template',
                        'btn_hover_text'       => '#FFFFFF',
                        'dash_active_link'     => '#167D7F',
                        'btn_hover_border'     => '#148C81',
                        'btn_primary_border'   => '#1AA89B',
                        'dash_nav_active_text' => '#FFFFFF',
                        'color_options'        => [
                            'color-1' => '#1BAC9E',
                            'color-2' => '#167067',
                            'color-3' => '#ABF5EE',
                            'color-4' => '#1CB6A7',
                        ],
                    ],
                ],
                'default' => $this->get_default_color_settings(),
            ],
        ];

        return $settings_fields;
    }

    /**
     * Render header styles to override default styles
     *
     * @since 3.7.6
     *
     * return $args
     */
    public function render_header_section( $args ) {
        if ( ! isset( $args['modal_header_color'] ) ) {
            return $args;
        }

        $colors         = dokan_get_option( 'store_color_pallete', 'dokan_colors', [] );
        $default_colors = $this->get_default_color_settings();

        $btn_bg     = ! empty( $colors['btn_primary'] ) ? $colors['btn_primary'] : $default_colors['btn_primary'];

        $args['modal_header_color'] = $btn_bg;

        return $args;
    }

    /**
     * Render styles to override default styles
     *
     * @since 1.0
     *
     * return void
     */
    public function load_styles() {
        $page = ( isset( $_GET['page'] ) && $_GET['page'] == 'dokan-seller-setup' ) ? 'seller-setup' : '';

        if ( ( ! dokan_is_seller_dashboard() && get_query_var( 'post_type' ) !== 'product' ) && $page !== 'seller-setup' && ! dokan_is_store_listing() && ! is_account_page() ) {
            return;
        }

        $colors         = dokan_get_option( 'store_color_pallete', 'dokan_colors', [] );
        $default_colors = $this->get_default_color_settings();

        $btn_bg     = ! empty( $colors['btn_primary'] ) ? $colors['btn_primary'] : $default_colors['btn_primary'];
        $btn_text   = ! empty( $colors['btn_text'] ) ? $colors['btn_text'] : $default_colors['btn_text'];
        $btn_border = ! empty( $colors['btn_primary_border'] ) ? $colors['btn_primary_border'] : $default_colors['btn_primary_border'];

        $btn_h_bg     = ! empty( $colors['btn_hover'] ) ? $colors['btn_hover'] : $default_colors['btn_hover'];
        $btn_h_text   = ! empty( $colors['btn_hover_text'] ) ? $colors['btn_hover_text'] : $default_colors['btn_hover_text'];
        $btn_h_border = ! empty( $colors['btn_hover_border'] ) ? $colors['btn_hover_border'] : $default_colors['btn_hover_border'];

        $dash_nav_bg          = ! empty( $colors['dash_nav_bg'] ) ? $colors['dash_nav_bg'] : $default_colors['dash_nav_bg'];
        $submenu_nav_bg       = $dash_nav_bg . 'ed';
        $dash_nav_text        = ! empty( $colors['dash_nav_text'] ) ? $colors['dash_nav_text'] : $default_colors['dash_nav_text'];
        $dash_active_menu     = ! empty( $colors['dash_active_link'] ) ? $colors['dash_active_link'] : $default_colors['dash_active_link'];
        $dash_nav_active_text = ! empty( $colors['dash_nav_active_text'] ) ? $colors['dash_nav_active_text'] : $default_colors['dash_nav_active_text'];
        ?>
        <style>
            .dokan-dashboard-wrap .dokan-form-group #insert-media-button,
            input[type="submit"].dokan-btn-theme, a.dokan-btn-theme, .dokan-btn-theme {
                color: <?php echo esc_attr( $btn_text ); ?> !important;
                background-color: <?php echo esc_attr( $btn_bg ); ?> !important;
                border-color: <?php echo esc_attr( $btn_border ); ?> !important;
            }

            .dokan-btn-theme.active,
            .open .dropdown-toggle.dokan-btn-theme,
            .open .dropdown-togglea.dokan-btn-theme,
            input[type="submit"].dokan-btn-theme:hover,
            input[type="submit"].dokan-btn-theme:focus,
            input[type="submit"].dokan-btn-theme:active,
            a.dokan-btn-theme:hover, .dokan-btn-theme:hover,
            a.dokan-btn-theme:focus, .dokan-btn-theme:focus,
            a.dokan-btn-theme:active, .dokan-btn-theme:active,
            .dokan-geo-filters-column .dokan-geo-product-search-btn,
            .open .dropdown-toggleinput[type="submit"].dokan-btn-theme,
            .dokan-dashboard-wrap .dokan-subscription-content .pack_price,
            .dokan-dashboard-wrap .dokan-dashboard-content .wpo_wcpdf:hover,
            .dashboard-content-area .woocommerce-importer .wc-actions a.button,
            .dokan-dashboard-wrap .dokan-form-group #insert-media-button:hover,
            input[type="submit"].dokan-btn-theme.active, a.dokan-btn-theme.active,
            .dokan-dashboard-wrap .dokan-modal-content .modal-footer .inner button,
            .dashboard-content-area .woocommerce-importer .wc-actions button.button-next,
            .wc-setup .wc-setup-content .checkbox input[type=checkbox]:checked + label::before,
            .dokan-dashboard-wrap .dokan-dashboard-content .my_account_quotes td:last-child a:hover,
            .dokan-dashboard-wrap .dokan-dashboard-content .dokan-btn:not(.disconnect, .wc-pao-remove-option, .dokan-btn-success):hover,
            .dokan-dashboard-wrap .dokan-dashboard-content .dokan-btn:not(.disconnect, .wc-pao-remove-option, .dokan-btn-success):focus,
            .dokan-dashboard-wrap .dokan-dashboard-content #delivery-time-calendar .fc-button-primary:not(.fc-button-active):not(:disabled):hover {
                color: <?php echo esc_attr( $btn_h_text ); ?> !important;
                border-color: <?php echo esc_attr( $btn_h_border ); ?> !important;
                background-color: <?php echo esc_attr( $btn_h_bg ); ?> !important;
            }

            #dokan-store-listing-filter-wrap .right .toggle-view .active,
            .dokan-dashboard-wrap .dokan-settings-area .dokan-page-help p a,
            .dokan-dashboard-wrap .dokan-dashboard-header .entry-title small a,
            .dokan-dashboard-wrap .dokan-settings-area .dokan-ajax-response + a,
            .dokan-dashboard-wrap .dokan-settings-area .dokan-pa-all-addons div a,
            .dokan-dashboard-wrap .dokan-subscription-content .seller_subs_info p span,
            .dokan-table.product-listing-table .product-advertisement-th i.fa-stack-2x,
            .dokan-dashboard-wrap .dokan-stuffs-content .entry-title span.dokan-right a,
            .dokan-dashboard-wrap .dokan-settings-area #dokan-shipping-zone .router-link-active,
            .dokan-dashboard-wrap .dokan-settings-area .dokan-ajax-response ~ .dokan-text-left p a,
            .dokan-dashboard-wrap .dokan-settings-area .dokan-pa-create-addons .back-to-addon-lists-btn,
            .dokan-dashboard-wrap .dokan-withdraw-content .dokan-panel-inner-container .dokan-w8 strong a,
            .dokan-dashboard-wrap .dokan-settings-area #dokan-shipping-zone .dokan-form-group .limit-location-link,
            .dokan-dashboard-wrap .dashboard-content-area .woocommerce-importer .woocommerce-importer-done::before,
            .product-edit-new-container .dokan-proudct-advertisement .dokan-section-heading h2 span.fa-stack i.fa-stack-2x {
                color: <?php echo esc_attr( $btn_bg ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar,
            .wc-setup .wc-setup-steps li.done::before,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu,
            .dokan-dashboard-wrap .dashboard-widget .dokan-dashboard-announce-unread,
            .dokan-dashboard-wrap .dokan-dashboard-content #vendor-own-coupon .code:hover {
                background-color: <?php echo esc_attr( $dash_nav_bg ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li a,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active a,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li a:hover {
                color: <?php echo esc_attr( $dash_nav_text ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li a:hover,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active a {
                color: <?php echo esc_attr( $dash_nav_active_text ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li:hover,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.dokan-common-links a:hover {
                background-color: <?php echo esc_attr( $dash_active_menu ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu ul.navigation-submenu,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li ul.navigation-submenu li {
                background: <?php echo esc_attr( $submenu_nav_bg ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu ul.navigation-submenu li a {
                color: <?php echo esc_attr( $dash_nav_text ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu ul.navigation-submenu li:hover a {
                font-weight: 800 !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu ul.navigation-submenu li a:focus {
                outline: none !important;
                background: none !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li.active ul.navigation-submenu {
                border-bottom: 0.5px solid <?php echo esc_attr( $dash_active_menu ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li:hover:not(.active) ul.navigation-submenu {
                background: <?php echo esc_attr( $submenu_nav_bg ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li:hover:not(.active).has-submenu:after {
                border-color: transparent <?php echo esc_attr( $submenu_nav_bg ); ?> transparent transparent;
                border-left-color: <?php echo esc_attr( $submenu_nav_bg ); ?>;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li ul.navigation-submenu li:hover:before,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li ul.navigation-submenu li.current:before {
                border-color: <?php echo esc_attr( $dash_nav_active_text ); ?> !important;
            }

            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li ul.navigation-submenu li:hover a,
            .dokan-dashboard .dokan-dash-sidebar ul.dokan-dashboard-menu li ul.navigation-submenu li.current a {
                color: <?php echo esc_attr( $dash_nav_active_text ); ?> !important;
            }

            .dokan-dashboard-wrap .dokan-booking-wrapper ul.dokan_tabs .active {
                border-top: 2px solid <?php echo esc_attr( $btn_bg ); ?> !important;
            }

            .dokan-dashboard-wrap a:focus {
                outline-color: <?php echo esc_attr( $btn_bg ); ?> !important;
            }

            .wc-setup .wc-setup-steps li.done,
            .wc-setup .wc-setup-steps li.active,
            .wc-setup .wc-setup-steps li.done::before,
            .wc-setup .wc-setup-steps li.active::before,
            .dokan-dashboard-wrap .dashboard-content-area .wc-progress-steps li.done,
            .dokan-dashboard-wrap .dashboard-content-area .wc-progress-steps li.active,
            .dokan-dashboard-wrap .dashboard-content-area .wc-progress-steps li.done::before,
            .dokan-dashboard-wrap .dashboard-content-area .wc-progress-steps li.active::before,
            .store-lists-other-filter-wrap .range-slider-container input[type="range"]::-webkit-slider-thumb,
            .dokan-geolocation-location-filters .dokan-range-slider-value + input[type="range"]::-webkit-slider-thumb {
                color: <?php echo esc_attr( $btn_h_bg ); ?> !important;
                border-color: <?php echo esc_attr( $btn_h_bg ); ?> !important;
            }

            .dokan-subscription-content .pack_content_wrapper .product_pack_item.current_pack {
                border-color: <?php echo esc_attr( $btn_h_bg ); ?> !important;
            }
        </style>

        <?php
    }
}
