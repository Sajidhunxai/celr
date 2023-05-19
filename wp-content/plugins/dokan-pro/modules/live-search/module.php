<?php

namespace WeDevs\DokanPro\Modules\LiveSearch;

/**
 * Dokan_Live_Search class
 *
 * @class Dokan_Live_Search The class that holds the entire Dokan_Live_Search plugin
 */
class Module {

    /**
     * Constructor for the Dokan_Live_Search class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        include_once 'classes/class-dokan-live-search.php';

        $this->define_constants();

        // Widget initialization hook
        add_action( 'widgets_init', array( $this, 'initialize_widget_register' ) );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_filter( 'dokan_settings_sections', array( $this, 'render_live_search_section' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'render_live_search_settings' ) );

        // removing redirection to single product page
        add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

        add_action( 'wp_ajax_dokan_suggestion_search_action', array( $this, 'dokan_suggestion_search_action' ) );
        add_action( 'wp_ajax_nopriv_dokan_suggestion_search_action', array( $this, 'dokan_suggestion_search_action' ) );

        add_action( 'init', [ $this, 'register_scripts' ] );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style()
     */
    public function enqueue_scripts() {
    }

    /**
     * Callback for Ajax Action Initialization
     *
     * @return void
     */
    public function dokan_suggestion_search_action() {
        global $wpdb, $woocommerce;

        $return_result              = array();
        $return_result['type']      = 'error';
        $return_result['data_list'] = '';
        $output                     = '';
        $get_postdata               = wp_unslash( $_POST );

        // _wpnonce check for an extra layer of security, the function will exit if it fails
        if ( ! isset( $get_postdata['_wpnonce'] ) || ! wp_verify_nonce( $get_postdata['_wpnonce'], 'dokan_suggestion_search_nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan' ) );
        }

        if ( isset( $get_postdata['textfield'] ) && ! empty( $get_postdata['textfield'] ) ) {
            $keyword = $get_postdata['textfield'];

            if ( isset( $get_postdata['selectfield'] ) && ! empty( $get_postdata['selectfield'] ) ) {
                $category = get_term_by( 'slug', $get_postdata['selectfield'], 'product_cat' );
                $category = $category->term_id;

                $querystr = "SELECT DISTINCT * FROM $wpdb->posts AS p
                LEFT JOIN $wpdb->term_relationships AS r ON (p.ID = r.object_id)
                INNER JOIN $wpdb->term_taxonomy AS x ON (r.term_taxonomy_id = x.term_taxonomy_id)
                INNER JOIN $wpdb->terms AS t ON (r.term_taxonomy_id = t.term_id)
                WHERE p.post_type IN ('product')
                AND p.post_status = 'publish'
                AND x.taxonomy = 'product_cat'
                AND (
                    (x.term_id = {$category})
                    OR
                    (x.parent = {$category})
                )
                AND (
                    (p.ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_sku' AND meta_value LIKE '%{$keyword}%'))
                    OR
                    (p.post_content LIKE '%{$keyword}%')
                    OR
                    (p.post_title LIKE '%{$keyword}%')
                )
                ORDER BY t.name ASC, p.post_date DESC LIMIT 250;";
            } else {
                $querystr = "SELECT DISTINCT $wpdb->posts.*
                FROM $wpdb->posts, $wpdb->postmeta
                WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
                AND (
                    ($wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value LIKE '%{$keyword}%')
                    OR
                    ($wpdb->posts.post_content LIKE '%{$keyword}%')
                    OR
                    ($wpdb->posts.post_title LIKE '%{$keyword}%')
                )
                AND $wpdb->posts.post_status = 'publish'
                AND $wpdb->posts.post_type = 'product'
                ORDER BY $wpdb->posts.post_date DESC LIMIT 250";
            }

            $query_results = $wpdb->get_results( $querystr );

            if ( ! empty( $query_results ) ) {
                foreach ( $query_results as $result ) {
                    $product    = wc_get_product( $result->ID );
                    $price      = wc_price( $product->get_price() );
                    $price_sale = $product->get_sale_price();
                    $stock      = $product->get_stock_status();
                    $sku        = $product->get_sku();
                    $categories = wp_get_post_terms( $result->ID, 'product_cat' );

                    if ( 'variable' === $product->get_type() ) {
                        $price = wc_price( $product->get_variation_price() ) . ' - ' . wc_price( $product->get_variation_price( 'max' ) );
                    }

                    $get_product_image = esc_url( get_the_post_thumbnail_url( $result->ID, 'thumbnail' ) );

                    if ( empty( $get_product_image ) && function_exists( 'wc_placeholder_img_src' ) ) {
                        $get_product_image = wc_placeholder_img_src();
                    }

                    $output .= '<li>';
                    $output .= '<a href="' . get_post_permalink( $result->ID ) . '">';
                    $output .= '<div class="dokan-ls-product-image">';
                    $output .= '<img src="' . $get_product_image . '">';
                    $output .= '</div>';
                    $output .= '<div class="dokan-ls-product-data">';
                    $output .= '<h3>' . $result->post_title . '</h3>';

                    if ( ! empty( $price ) ) {
                        $output .= '<div class="product-price">';
                        $output .= '<span class="dokan-ls-regular-price">' . $price . '</span>';
                        if ( ! empty( $price_sale ) ) {
                            $output .= '<span class="dokan-ls-sale-price">' . wc_price( $price_sale ) . '</span>';
                        }
                        $output .= '</div>';
                    }

                    if ( ! empty( $categories ) ) {
                        $output .= '<div class="dokan-ls-product-categories">';
                        foreach ( $categories as $category ) {
                            if ( $category->parent ) {
                                $parent  = get_term_by( 'id', $category->parent, 'product_cat' );
                                $output .= '<span>' . $parent->name . '</span>';
                            }
                            $output .= '<span>' . $category->name . '</span>';
                        }
                        $output .= '</div>';
                    }

                    if ( ! empty( $sku ) ) {
                        $output .= '<div class="dokan-ls-product-sku">' . esc_html__( 'SKU:', 'dokan' ) . ' ' . $sku . '</div>';
                    }

                    $output .= '</div>';
                    $output .= '</a>';
                    $output .= '</li>';
                }
            }
        }

        // If above action fails, result type is set to 'error' set to value, if success, updated
        if ( $output ) {
            $return_result['type']      = 'success';
            $return_result['data_list'] = $output;
        }
        echo wp_json_encode( $return_result );
        die();
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
    public function render_live_search_section( $sections ) {
        $sections[] = [
            'id'                   => 'dokan_live_search_setting',
            'title'                => __( 'Live Search', 'dokan' ),
            'icon_url'             => plugins_url( 'assets/images/search.svg', __FILE__ ),
            'description'          => __( 'Ajax Live Search Control', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/how-to-install-configure-use-dokan-live-search/',
            'settings_title'       => __( 'Live Search Settings', 'dokan' ),
            'settings_description' => __( 'You can configure your site settings for customers to utilize when navigating stores for specific products.', 'dokan' ),
        ];

        return $sections;
    }

    /**
     * Add live search options on Dokan Settings under General section
     *
     * @since 1.0
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function render_live_search_settings( $settings_fields ) {
        $settings_fields['dokan_live_search_setting'] = [
            'live_search_option' => [
                'name'    => 'live_search_option',
                'label'   => __( 'Live Search Options', 'dokan' ),
                'desc'    => __( 'Select one option which one will apply on search box', 'dokan' ),
                'type'    => 'select',
                'default' => 'suggestion_box',
                'options' => [
                    'suggestion_box'  => __( 'Search with Suggestion Box', 'dokan' ),
                    'old_live_search' => __( 'Autoload Replace Current Content', 'dokan' ),
                ],
                'tooltip' => __( 'Select one option which one will apply on search box.', 'dokan' ),
            ],
        ];

        return $settings_fields;
    }

    /**
     * Callback for Widget Initialization
     *
     * @return void
     */
    public function initialize_widget_register() {
        register_widget( 'Dokan_Live_Search_Widget' );
    }

    /**
     * Define constants
     *
     * @since 3.7.4
     */
    public function define_constants() {
        define( 'DOKAN_LIVE_SEARCH_FILE', __FILE__ );
        define( 'DOKAN_LIVE_SEARCH_ASSETS', plugin_dir_url( DOKAN_LIVE_SEARCH_FILE ) . 'assets/' );
    }

    /**
     * Register scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        wp_register_style( 'dokan-ls-custom-style', DOKAN_LIVE_SEARCH_ASSETS . 'css/style.css', false, DOKAN_PLUGIN_VERSION );
        wp_register_script( 'dokan-ls-custom-js', DOKAN_LIVE_SEARCH_ASSETS . 'js/script.js', array( 'jquery' ), DOKAN_PLUGIN_VERSION, true );

        wp_localize_script(
            'dokan-ls-custom-js', 'dokanLiveSearch', array(
                'ajaxurl'             => admin_url( 'admin-ajax.php' ),
                'loading_img'         => plugins_url( 'assets/images/loading.gif', __FILE__ ),
                'currentTheme'        => wp_get_theme()->stylesheet,
                'themeTags'           => apply_filters( 'dokan_ls_theme_tags', array() ),
                'dokan_search_action' => 'dokan_suggestion_search_action',
                'dokan_search_nonce'  => wp_create_nonce( 'dokan_suggestion_search_nonce' ),
            )
        );
    }
}
