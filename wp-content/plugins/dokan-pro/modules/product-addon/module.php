<?php

namespace WeDevs\DokanPro\Modules\ProductAddon;

use DependencyNotice;
use WC_Product_Addons_Admin;

class Module {

    /**
     * Constructor for the Dokan_Product_Addon class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        // Define Constant
        $this->define();

        require_once DOKAN_PRODUCT_ADDON_INC_DIR . '/DependencyNotice.php';

        $dependency = new DependencyNotice();

        if ( $dependency->is_missing_dependency() ) {
            return;
        }

        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    /**
     * Init the modules
     *
     * @since 3.1.2
     *
     * @return void
     */
    public function init() {
        $this->includes();
        $this->initiate();
        $this->hooks();
    }

    /**
     * Hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_PRODUCT_ADDON_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_PRODUCT_ADDON_INC_DIR', DOKAN_PRODUCT_ADDON_DIR . '/includes' );
        define( 'DOKAN_PRODUCT_ADDON_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
     * Get plugin path
     *
     * @since 1.5.1
     *
     * @return void
     **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Includes all necessary class a functions file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        require_once DOKAN_PRODUCT_ADDON_INC_DIR . '/class-frontend.php';
        require_once DOKAN_PRODUCT_ADDON_INC_DIR . '/class-vendor-product.php';

        // Load all helper functions
        require_once DOKAN_PRODUCT_ADDON_INC_DIR . '/functions.php';
    }

    /**
     * Initiate all classes
     *
     * @return void
     */
    public function initiate() {
        \Dokan_Product_Addon_Frontend::init();
        \Dokan_Product_Addon_Vendor_Product::init();
    }

    /**
     * Init all hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        add_action( 'init', [ $this, 'register_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_product_addon_templates' ], 10, 3 );
        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'load_vendor_staff_addons' ], 9 );
        add_action( 'wp_ajax_wc_pao_get_addon_field', [ $this, 'ajax_get_addon_field' ], 1 );
    }

    /**
     * Get add-on field.
     *
     * @since 1.0.0
     */
    public function ajax_get_addon_field() {
        check_ajax_referer( 'wc-pao-get-addon-field', 'security' );

        global $product_addons, $post, $options;

        ob_start();
        $addon                       = [];
        $addon['name']               = '';
        $addon['title_format']       = 'label';
        $addon['description_enable'] = '';
        $addon['description']        = '';
        $addon['required']           = '';
        $addon['type']               = 'multiple_choice';
        $addon['display']            = 'select';
        $addon['restrictions']       = '';
        $addon['restrictions_type']  = 'any_text';
        $addon['min']                = '';
        $addon['max']                = '';
        $addon['adjust_price']       = '';
        $addon['price_type']         = '';
        $addon['price']              = '';

        $addon['options'] = [
            WC_Product_Addons_Admin::get_new_addon_option(),
        ];

        $loop = '{loop}';

        include DOKAN_PRODUCT_ADDON_DIR . '/templates/product-addon/html-addon.php';

        $html = ob_get_clean();

        $html = str_replace( [ "\n", "\r" ], '', str_replace( "'", '"', $html ) );

        wp_send_json( [ 'html' => $html ] );
    }

    /**
     * Register Scripts
     *
     * @3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dokan-pa-style', DOKAN_PRODUCT_ADDON_ASSETS_DIR . '/css/main.css', false, $version, 'all' );
        wp_register_script( 'dokan-pa-script', DOKAN_PRODUCT_ADDON_ASSETS_DIR . '/js/scripts.js', [ 'jquery' ], $version, true );
        wp_register_script(
            'dokan-pa-addons-script', DOKAN_PRODUCT_ADDON_ASSETS_DIR . '/js/addons.js', [
                'jquery',
                'dokan-pa-script',
            ], $version, true
        );

        $params = [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => [
                'get_addon_options' => wp_create_nonce( 'wc-pao-get-addon-options' ),
                'get_addon_field'   => wp_create_nonce( 'wc-pao-get-addon-field' ),
            ],
            'i18n'     => [
                'required_fields'        => __( 'All fields must have a title and/or option name. Please review the settings highlighted in red border.', 'dokan' ),
                'limit_price_range'      => __( 'Limit price range', 'dokan' ),
                'limit_quantity_range'   => __( 'Limit quantity range', 'dokan' ),
                'limit_character_length' => __( 'Limit character length', 'dokan' ),
                'restrictions'           => __( 'Restrictions', 'dokan' ),
                'confirm_remove_addon'   => __( 'Are you sure you want remove this add-on field?', 'dokan' ),
                'confirm_remove_option'  => __( 'Are you sure you want delete this option?', 'dokan' ),
                'add_image_swatch'       => __( 'Add Image Swatch', 'dokan' ),
                'add_image'              => __( 'Add Image', 'dokan' ),
            ],
        ];

        wp_localize_script( 'dokan-pa-script', 'wc_pao_params', apply_filters( 'wc_pao_params', $params ) );
    }

    /**
     * Load global scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_scripts() {
        global $wp;

        if ( isset( $wp->query_vars['settings'] ) && 'product-addon' === $wp->query_vars['settings'] ) {
            $this->enqueue_scripts();
        }

        if ( isset( $wp->query_vars['booking'] ) && 'edit' === $wp->query_vars['booking'] ) {
            $this->enqueue_scripts();
        }

        if ( isset( $wp->query_vars['auction'] ) ) {
            $this->enqueue_scripts();
        }

        // Vendor product edit page when product already publish
        if ( dokan_is_product_edit_page() ) {
            $this->enqueue_scripts();
        }

        // Vendor product edit page when product is pending review
        if ( isset( $wp->query_vars['products'] ) && ! empty( $_GET['product_id'] ) && ! empty( $_GET['action'] ) && 'edit' === $_GET['action'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $this->enqueue_scripts();
        }
    }

    /**
     * Enqueue scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-pa-style' );
        wp_enqueue_script( 'dokan-pa-script' );
        wp_enqueue_script( 'dokan-pa-addons-script' );
    }

    /**
     * Load dokan pro templates
     *
     * @since 1.5.1
     *
     * @return void
     **/
    public function load_product_addon_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_product_addon'] ) && $args['is_product_addon'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    public function load_vendor_staff_addons() {
        add_action( 'pre_get_posts', [ $this, 'set_author_in_for_vendor_staff' ] );
    }

    /**
     * Set author in for vendor staff
     *
     * @param $query
     *
     * @since 3.1.4
     *
     * @return void
     */
    public function set_author_in_for_vendor_staff( $query ) {
        if ( isset( $query->query['post_type'] ) && $query->query['post_type'] === 'global_product_addon' ) {
            global $post;

            remove_action( 'pre_get_posts', [ $this, 'set_author_in_for_vendor_staff' ] );

            $vendor        = dokan_get_vendor_by_product( $post->ID );
            $vendor_staffs = dokan_get_vendor_staff( $vendor->get_id() );

            if ( ! in_array( $vendor->get_id(), $vendor_staffs, true ) ) {
                return;
            }

            add_action( 'pre_get_posts', [ $this, 'set_author_in_for_vendor_staff' ] );

            $query->set( 'author__in', $vendor_staffs );
        }
    }

}
