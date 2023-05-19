<?php

namespace WeDevs\DokanPro\Modules\RankMath;

defined( 'ABSPATH' ) || exit;

use RankMath\Rest\Rest_Helper;

/**
 * Class for Rank math SEO integration module
 *
 * @since 3.4.0
 */
class Module {

    /**
     * Class constructor
     *
     * @since 3.4.0
     */
    public function __construct() {
        // Define constants
        $this->constants();

        // Verify rank math seo plugin and other dependency at the first place
        $dependency = new DependencyNotice();
        if ( $dependency->is_missing_dependency() ) {
            return;
        }

        // Check if current user has the permission to edit product
        if ( ! current_user_can( 'dokan_edit_product' ) ) {
            return;
        }

        // Initialize the module
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Defines the required constants
     *
     * @since 3.4.0
     *
     * @return void
     */
    private function constants() {
        define( 'DOKAN_RANK_MATH_FILE', __FILE__ );
        define( 'DOKAN_RANK_MATH_PATH', dirname( DOKAN_RANK_MATH_FILE ) );
        define( 'DOKAN_RANK_MATH_INC', dirname( DOKAN_RANK_MATH_FILE ) . '/includes' );
        define( 'DOKAN_RANK_MATH_TEMPLATE_PATH', dirname( DOKAN_RANK_MATH_FILE ) . '/templates' );
    }

    /**
     * Initializes all processing.
     *
     * @since 3.7.6
     *
     * @return void
     */
    public function init() {
        $this->hooks();
    }

    /**
     * Registers required hooks
     *
     * @since 3.4.0
     *
     * @return void
     */
    private function hooks() {
        /*
         * Load SEO content after inventory variants widget on the edit product page.
         * All other hooks and processes will be initialized inside the execution of
         * this hook to make sure the processing happens only when a product is being
         * edited from vendor dashboard.
         */
        add_action( 'dokan_product_edit_after_inventory_variants', [ $this, 'load_product_seo_content' ], 6, 2 );
        // Map meta cap for `vendor_staff` to bypass some primitive capability requirements.
        add_filter( 'map_meta_cap', [ $this, 'map_meta_cap_for_rank_math' ], 10, 4 );
        // Initiates Rank math's own rest api.
        add_action( 'rest_api_init', [ $this, 'init_rest_api' ] );
        // Initiates Rest API for dokan-rank-math module.
        add_filter( 'dokan_rest_api_class_map', [ $this, 'rest_api_class_map' ] );
       // Load product sections with ID inside vendor-dashboard footer.
        add_action( 'dokan_vendor_dashboard_after_footer', [ $this, 'load_hidden_product_sections' ] );
        // New Vendor dashboard product edit page load.
        add_action( 'dokan_vendor_dashboard_script_loaded', [ $this, 'load_product_scripts_for_blocks' ] );
        // Add product block config data for rank math seo
        add_filter( 'dokan_get_product_block_configurations', [ $this, 'get_product_block_configurations' ] );
    }

    /**
     * Maps meta cap for users with vendor staff role to bypass some primitive
     * capability requirements.
     *
     * To access the rank math rest api functionality, a user must have one or some
     * primitive capabilities which are `edit_products`, `edit_published_products`,
     * `edit_others_products`, and `edit_private_products`
     *
     * Often users with `vendor_staff` role miss those required capabilities that
     * would lead them to being unable to update the product although they are given
     * permission to edit product.
     *
     * So to ensure their ability to update product and to use the Rank Math SEO
     * functionalities, the required premitive capabilities are bypassed.
     *
     * Note that it is ensured the capabilities will be bypassed only while
     * the rest api endpoint for Rank Math SEO is being hit.
     *
     * Also for rank math redirection settings, all users need to have the
     * capability of `rank_math_redirections`. So it needs to be ensured all users
     * are given that capability while updating the rank math redirection settings
     * for products.
     *
     * @since 3.4.0
     *
     * @uses global   $wp          Used to retrieve \WP class data
     * @uses function get_userdata Used to retrieve userdata by id
     *
     * @param array   $caps    Premitive capabilities that must be possessed by user
     * @param string  $cap     Capability that is mapping the premitive capabilities
     * @param integer $user_id ID of the current user
     *
     * @return array List of premitive capabilities to be satisfied
     */
    public function map_meta_cap_for_rank_math( $caps, $cap, $user_id, $args ) {
        switch ( $cap ) {
            case 'edit_others_products':
                global $wp;

                if (
                    empty( $wp->query_vars['rest_route'] ) ||
                    false === strpos( $wp->query_vars['rest_route'], Rest_Helper::BASE )
                ) {
                    return $caps;
                }

                /*
                 * Here the userdata is being retrieved
                 * to get all capabilities of the user
                 * in order to check specific capability
                 * like `vendor_staff`.
                 */
                $user = get_userdata( $user_id );

                // Bypass the primitive caps only if the user is `vendor_staff`
                if ( ! empty( $user->allcaps['vendor_staff'] ) ) {
                    return [];
                }

                break;

            default:
                if ( 0 !== strpos( $cap, 'rank_math_' ) ) {
                    break;
                }

                if ( ! dokan_is_user_seller( dokan_get_current_user_id() ) ) {
                    break;
                }

                /*
                 * For Redirection user need to have the capability
                 * of `rank_math_redirections`. So here the users
                 * who can edit dokan products are given that
                 * capability so that they can edit redirect settings.
                 */
                add_filter(
                    'user_has_cap', function( $all_caps ) use ( $cap ) {
                        $all_caps[ $cap ] = true;
                        return $all_caps;
                    }, 10, 1
                );
        }

        return $caps;
    }

    /**
     * Loads rank math seo content for product update
     *
     * @since 3.4.0
     *
     * @param object $product
     * @param int    $product_id
     *
     * @return void
     */
    public function load_product_seo_content( $product, $product_id ) {
        // Load frontend scripts.
        $this->load_frontend();

        // Require the template for rank math seo content
        require_once DOKAN_RANK_MATH_TEMPLATE_PATH . '/product-seo-content.php';
    }

    /**
     * Load hidden product content in vendor dashboard.
     *
     * It's used to fix as if React DOM will through an exception
     * that it could not get valid DOM element.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function load_hidden_product_sections() {
        require_once DOKAN_RANK_MATH_TEMPLATE_PATH . '/product-seo-hidden-content.php';
    }

    /**
     * Load rank math's scripts in product-edit page.
     *
     * @since 3.7.13
     *
     * This will be only applied to new Vendor-Dashboard's
     * product edit page.
     *
     * @return void
     */
    public function load_product_scripts_for_blocks() {
        $cmb2 = \CMB2_Bootstrap_2101::initiate();
        $cmb2->include_cmb();

        // Load frontend scripts.
        $this->load_frontend();
    }

    /**
     * Load rank-math scripts and styles.
     *
     * Process the required functionality for frontend application
     * including all the styles and scripts
     *
     * @since 3.7.13
     *
     * @return void
     */
    private function load_frontend() {
        $frontend = new Frontend();
        $frontend->process();
    }

    /**
     * Registers necessary rest routes.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_rest_api() {
        $rest = new \RankMath\ContentAI\Rest();
        $rest->register_routes();
    }

    /**
     * Registers rank maths rest routes in dokan rest class-maps.
     *
     * @since 3.7.13
     *
     * @param array $classes Array of classes.
     *
     * @return array
     */
    public function rest_api_class_map( $classes ) {
        $classes[ DOKAN_RANK_MATH_INC . '/REST/RankMathController.php' ] = '\WeDevs\DokanPro\Modules\RankMath\REST\RankMathController';

        return $classes;
    }

    /**
     * Retrieves product block config data for rank math seo.
     *
     * @since 3.7.17
     *
     * @param array $config Block configuration.
     *
     * @return array
     */
    public function get_product_block_configurations( $config ) {
        $config['rank_math'] = [
            'is_active' => true,
        ];
        return $config;
    }
}
