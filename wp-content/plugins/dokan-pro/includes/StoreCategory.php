<?php

namespace WeDevs\DokanPro;

use WP_Tax_Query;

class StoreCategory {

    /**
     * Class constructor
     *
     * @since 2.9.2
     */
    public function __construct() {
        $this->register_taxonomy();

        add_filter( 'dokan_settings_general_vendor_store_options', array( $this, 'add_admin_settings' ) );
        add_action( 'dokan_after_saving_settings', array( $this, 'set_default_category' ), 10, 2 );
        add_filter( 'dokan_admin_localize_script', array( $this, 'add_localized_data' ) );
        add_filter( 'dokan_localized_args', array( $this, 'set_localized_data' ) );

        if ( dokan_is_store_categories_feature_on() ) {
            add_action( 'dokan_settings_after_store_name', array( $this, 'add_store_category_option' ) );
            add_action( 'dokan_seller_wizard_store_setup_after_address_field', array( $this, 'seller_wizard_add_store_category_option' ) );
            add_action( 'dokan_new_seller_created', array( $this, 'after_dokan_new_seller_created' ) );
            add_action( 'dokan_new_vendor', array( $this, 'after_dokan_new_seller_created' ), 10, 1 );
            add_action( 'user_register', array( $this, 'add_store_category_to_new_seller' ), 10, 2 );
            add_action( 'dokan_store_profile_saved', array( $this, 'after_store_profile_saved' ) );
            add_action( 'dokan_seller_wizard_store_field_save', array( $this, 'after_seller_wizard_store_field_save' ) );
            add_filter( 'dokan_vendor_shop_data', array( $this, 'add_store_categories_in_vendor_shop_data' ), 10, 2 );
            add_action( 'dokan_vendor_to_array', array( $this, 'add_store_categories_vendor_to_array' ), 10, 2 );
            add_action( 'dokan_rest_prepare_store_item_for_response', array( $this, 'rest_prepare_store_item_for_response' ), 10, 2 );
            add_action( 'dokan_rest_stores_update_store', array( $this, 'rest_stores_update_store_category' ), 10, 2 );
            add_filter( 'dokan_seller_listing_search_args', array( $this, 'add_store_category_query_arg' ), 10, 2 );
            add_filter( 'dokan_seller_listing_args', array( $this, 'add_store_category_query_arg' ), 10, 2 );
            add_filter( 'dokan_rest_get_stores_args', array( $this, 'add_store_category_query_arg' ), 10, 2 );
            add_action( 'pre_user_query', array( $this, 'add_store_category_query' ) );
            add_action( 'deleted_user', [ $this, 'remove_store_categories_by_user_id' ] );
        }
    }

    /**
     * Register store category
     *
     * @since 2.9.2
     *
     * @return void
     */
    private function register_taxonomy() {
        register_taxonomy(
            'store_category',
            'dokan_seller',
            array(
                'hierarchical' => false,
                'label'        => __( 'Store Categories', 'dokan' ),
                'show_ui'      => false,
                'query_var'    => dokan_is_store_categories_feature_on(),
                'capabilities' => array(
                    'manage_terms' => 'manage_woocommerce',
                    'edit_terms'   => 'manage_woocommerce',
                    'delete_terms' => 'manage_woocommerce',
                    'assign_terms' => 'manage_woocommerce',
                ),
                'rewrite'      => array(
                    'slug'         => 'store-category',
                    'with_front'   => false,
                    'hierarchical' => false,
                ),
                'show_in_rest' => dokan_is_store_categories_feature_on(),
            )
        );
    }

    /**
     * Add admin settings
     *
     * @since 2.9.2
     *
     * @param array $dokan_settings_fields
     *
     * @return array
     */
    public function add_admin_settings( $dokan_settings_fields ) {
        $dokan_settings_fields['store_category_type'] = [
            'name'    => 'store_category_type',
            'label'   => __( 'Store Category', 'dokan' ),
            'type'    => 'radio',
            'options' => [
                'none'     => __( 'None', 'dokan' ),
                'single'   => __( 'Single', 'dokan' ),
                'multiple' => __( 'Multiple', 'dokan' ),
            ],
            'default' => 'none',
            'tooltip' => __( 'Only admin can create store categories from Dashboard -> Vendors -> Store Categories to assign categories from vendor listing page. If you select single, vendor will only have one category available during store setup or when navigating to vendor Dashboard -> Store -> Store categories. If you select multiple, multiple categories will be available. Select none if you don\'t want either.', 'dokan' ),
        ];

        return $dokan_settings_fields;
    }

    /**
     * Set default category
     *
     * @since 2.9.2
     *
     * @param string $option_key
     * @param array  $option_value
     */
    public function set_default_category( $option_key, $option_value ) {
        if ( 'dokan_general' !== $option_key ) {
            return;
        }

        if ( ! empty( $option_value['store_category_default'] ) ) {
            update_option( 'default_store_category', $option_value['store_category_default'], false );
        }
    }

    /**
     * Add localized script data in admin panel
     *
     * @since 2.9.8
     *
     * @param array $data
     *
     * @return array
     */
    public function add_localized_data( $data ) {
        $data['store_category_type'] = dokan_get_option( 'store_category_type', 'dokan_general', 'none' );
        return $data;
    }

    /**
     * Set localized data
     *
     * @since 3.0.0
     *
     * @param array $data
     *
     * @return array
     */
    public function set_localized_data( $data ) {
        $data['all_categories'] = __( 'All Categories', 'dokan' );

        return $data;
    }

    /**
     * Add store category option in provided template
     *
     * @since 2.9.2
     *
     * @param int    $current_user
     * @param array  $args
     * @param string $template_name
     *
     * @return void
     */
    public function add_store_category_option( $current_user, $args = array(), $template_name = 'settings/store-form-categories' ) {
        // We're not gonna use this id, but this will create
        // a category, if none exists.
        dokan_get_default_store_category_id();

        $categories = get_terms(
            array(
				'taxonomy'   => 'store_category',
				'hide_empty' => false,
            )
        );

        $store_categories = wp_get_object_terms( $current_user, 'store_category', array( 'fields' => 'ids' ) );
        $category_type    = dokan_get_option( 'store_category_type', 'dokan_general', 'none' );
        $is_multiple      = ( 'multiple' === $category_type ) || false;

        $defaults = array(
            'pro'              => true,
            'categories'       => $categories,
            'store_categories' => $store_categories,
            'is_multiple'      => $is_multiple,
            'label'            => $is_multiple ? __( 'Store Categories', 'dokan' ) : __( 'Store Category', 'dokan' ),
        );

        $args = wp_parse_args( $args, $defaults );

        dokan_get_template_part( $template_name, '', $args );
    }

    /**
     * Add store categories option in seller wizard
     *
     * @since 2.9.2
     *
     * @param \WeDevs\Dokan\Vendor\SetupWizard $wizard
     *
     * @return void
     */
    public function seller_wizard_add_store_category_option( $wizard ) {
        $current_user = get_current_user_id();
        $this->add_store_category_option( $current_user, array(), 'settings/seller-wizard-store-form-categories' );
    }

    /**
     * Set default category to a newly created store
     *
     * @since 2.9.2
     *
     * @param int $user_id
     *
     * @return void
     */
    public function after_dokan_new_seller_created( $user_id ) {
        dokan_set_store_categories( $user_id );
    }

    /**
     * Set store categories after store file is saved
     *
     * @since 2.9.2
     *
     * @param int $store_id
     *
     * @return void
     */
    public function after_store_profile_saved( $store_id ) {
        $get_postdata = wp_unslash( $_POST ); // phpcs:ignore
        $store_categories = ! empty( $get_postdata['dokan_store_categories'] ) ? $get_postdata['dokan_store_categories'] : null;

        if ( $store_categories ) {
            dokan_set_store_categories( $store_id, $store_categories );
        }
    }

    /**
     * Set store categories after wizard settings is saved
     *
     * @since 2.9.2
     *
     * @param \WeDevs\Dokan\Vendor\SetupWizard $wizard
     *
     * @return void
     */
    public function after_seller_wizard_store_field_save( $wizard ) {
        $get_postdata = wp_unslash( $_POST ); // phpcs:ignore
        $store_categories = ! empty( $get_postdata['dokan_store_categories'] ) ? $get_postdata['dokan_store_categories'] : null;
        if ( is_array( $store_categories ) ) {
            array_walk( $store_categories, function ( &$value ) {
                $value = intval( $value );
            } );
        }
        dokan_set_store_categories( $wizard->store_id, $store_categories );
    }

    /**
     * Add store categories in \WeDevs\Dokan\Vendor\Vendor shop_data
     *
     * @since 2.9.2
     *
     * @param array        $shop_info
     * @param \WeDevs\Dokan\Vendor\Vendor $vendor
     *
     * @return array
     */
    public function add_store_categories_in_vendor_shop_data( $shop_info, $vendor ) {
        $user_meta  = get_userdata( $vendor->id );
        $user_roles = $user_meta->roles;

        if ( ! $vendor->is_vendor() || in_array( 'shop_manager', $user_roles, true ) ) {
            return $shop_info;
        }

        $store_categories = wp_get_object_terms( $vendor->get_id(), 'store_category' );

        if ( empty( $store_categories ) ) {
            $default_category_id   = dokan_get_default_store_category_id();
            $default_category_term = get_term( $default_category_id, 'store_category' );

            if ( is_wp_error( $default_category_term ) ) {
                $store_categories = [];
            } else {
                $store_categories = [ $default_category_term ];

                // Set default store category to the user.
                dokan_set_store_categories( $vendor->get_id(), [ $default_category_id ] );
            }
        }

        $shop_info['categories'] = $store_categories;

        return $shop_info;
    }

    /**
     * Add store categories in \WeDevs\Dokan\Vendor\Vendor to_array data
     *
     * @since 2.9.2
     *
     * @param array        $data
     * @param \WeDevs\Dokan\Vendor\Vendor $vendor
     *
     * @return array
     */
    public function add_store_categories_vendor_to_array( $data, $vendor ) {
        $data['categories'] = $vendor->get_categories();

        return $data;
    }

    /**
     * Add store category to new seller.
     *
     * @since 3.7.17
     *
     * @param int   $user_id  User id
     * @param array $userdata User data
     *
     * @return void
     */
    public function add_store_category_to_new_seller( $user_id, $userdata ) {
        // Check if the user is seller or admin.
        if ( ! in_array( $userdata['role'], [ 'seller', 'administrator' ], true ) ) {
            return;
        }

        // Check if the seller has no assigned category.
        if ( ! empty( wp_get_object_terms( $user_id, 'store_category' ) ) ) {
            return;
        }

        $default_category_id   = dokan_get_default_store_category_id();
        $default_category_term = get_term( $default_category_id, 'store_category' );

        if ( is_wp_error( $default_category_term ) ) {
            return;
        }

        // Set default store category to the user.
        dokan_set_store_categories( $user_id, [ $default_category_id ] );
    }

    /**
     * Remove store categories by user ID.
     *
     * @since 3.7.21
     *
     * @param int $user_id  User id
     *
     * @return void
     */
    public function remove_store_categories_by_user_id( $user_id ) {
        $assigned_store_categories = wp_get_object_terms( $user_id, 'store_category' );

        // Check if the user has no assigned store category.
        if ( is_wp_error( $assigned_store_categories ) || empty( $assigned_store_categories ) ) {
            return;
        }

        $category_args = [
            'taxonomy' => 'store_category',
            'fields'   => 'ids',
        ];

        $term_query         = new \WP_Term_Query( $category_args );
        $store_category_ids = $term_query->get_terms();

        // Remove store categories from the user.
        wp_remove_object_terms( $user_id, $store_category_ids, 'store_category' );
    }

    /**
     * Transform store categories data in REST response
     *
     * @since 2.9.2
     *
     * @param WP_REST_Response $response
     *
     * @return WP_REST_Response
     */
    public function rest_prepare_store_item_for_response( $response ) {
        $data = $response->get_data();

        if ( ! empty( $data['categories'] ) && is_array( $data['categories'] ) ) {
            $categories = array();

            $category_type = dokan_get_option( 'store_category_type', 'dokan_general', 'none' );

            if ( 'multiple' === $category_type ) {
                foreach ( $data['categories'] as $category ) {
                    $categories[] = array(
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    );
                }
            } else {
                $category = $data['categories'][0];
                $categories[] = array(
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                );
            }

            $data['categories'] = $categories;
        } else {
            $data['categories'] = array();
        }

        $response->set_data( $data );

        return $response;
    }

    /**
     * Store update hook to update store categories
     *
     * @since 2.9.2
     *
     * @param \WeDevs\Dokan\Vendor\Vendor    $store
     * @param WP_REST_Request $request
     *
     * @return void
     */
    public function rest_stores_update_store_category( $store, $request ) {
        $store_categories = ! empty( $request->get_param( 'categories' ) ) ? $request->get_param( 'categories' ) : null;

        if ( is_array( $store_categories ) ) {
            $store_categories = array_map(
                function ( $category ) {
                    return $category['id'];
                }, $store_categories
            );
        }

        dokan_set_store_categories( $store->get_id(), $store_categories );
    }

    /**
     * Add store category dropdown in seller search form
     *
     * @since 2.9.2
     *
     * @return void
     */
    public function add_category_dropdown_in_seller_search_form() {
        $get_data = wp_unslash( $_GET );
        $category_query = ! empty( $get_data['dokan_seller_category'] ) ? sanitize_text_field( $get_data['dokan_seller_category'] ) : null;

        $args = array(
            'category_query' => $category_query,
        );

        $this->add_store_category_option( 0, $args, 'seller-search-form-categories' );
    }

    /**
     * Add tax_query arg in WP_User_Query used in dokan()->vendor->get_vendors()
     *
     * @since 2.9.2
     *
     * @param array $args
     *
     * @return array
     */
    public function add_store_category_query_arg( $args, $request ) {
        if ( ! empty( $request['store_categories'] ) ) {
            $args['store_category_query'][] = [
                'taxonomy' => 'store_category',
                'field'     => 'slug',
                'terms'    => ! is_array( $request['store_categories'] )
                    ? explode( ',', sanitize_text_field( wp_unslash( $request['store_categories'] ) ) )
                    : array_map( 'sanitize_text_field', wp_unslash( $request['store_categories'] ) ),
            ];
        }

        return $args;
    }

    /**
     * Add store category filter to WP_User_Query
     *
     * @since 2.9.2
     *
     * @param WP_User_Query $wp_user_query
     *
     * @return void
     */
    public function add_store_category_query( $wp_user_query ) {
        if ( ! empty( $wp_user_query->query_vars['store_category_query'] ) ) {
            global $wpdb;

            $store_category_query = new WP_Tax_Query( $wp_user_query->query_vars['store_category_query'] );
            $clauses = $store_category_query->get_sql( $wpdb->users, 'ID' );

            $wp_user_query->query_fields = 'DISTINCT ' . $wp_user_query->query_fields;
            $wp_user_query->query_from   .= $clauses['join'];
            $wp_user_query->query_where  .= $clauses['where'];
        }
    }
}
