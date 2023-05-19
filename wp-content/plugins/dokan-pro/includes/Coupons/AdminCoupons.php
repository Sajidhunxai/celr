<?php

namespace WeDevs\DokanPro\Coupons;

use WC_Data_Store;
use WeDevs\DokanPro\Admin\Announcement;

/**
* Admin Coupons Class
*
* Loaded all hooks releated with coupon
*
* @since 3.4.0
*/
class AdminCoupons {

    /**
     * Load autometically when class initiate
     *
     * @since 3.4.0
     */
    public function __construct() {
        add_action( 'woocommerce_coupon_data_tabs', [ $this, 'dokan_admin_coupon_data_tabs' ], 15, 1 );
        add_action( 'woocommerce_coupon_data_panels', [ $this, 'dokan_admin_coupon_data_panels' ], 15, 2 );
        add_action( 'woocommerce_coupon_options_save', [ $this, 'admin_coupon_options_save' ], 15, 2 );
        add_action( 'wp_ajax_dokan_admin_coupons_search_vendors', [ $this, 'dokan_admin_coupons_search_vendors' ] );
        add_action( 'wp_ajax_dokan_json_search_products_and_variations_for_coupon', [ $this, 'dokan_json_search_products_and_variations_for_coupon' ] );
        add_action( 'woocommerce_coupon_is_valid_for_product', [ $this, 'coupon_is_valid_for_product' ], 15, 3 );
    }

    /**
     * Add vendor coupon tab on admin coupon area
     *
     * @param array $coupon_tabs
     *
     * @since 3.4.0
     *
     * @return array $coupon_tabs
     */
    public function dokan_admin_coupon_data_tabs( $coupon_tabs ) {
        $vendor_coupon_tab = apply_filters(
            'dokan_admin_coupon_data_tabs',
            array(
                'vendor_restriction' => array(
                    'label'  => __( 'Vendor limits', 'dokan' ),
                    'target' => 'vendor_usage_limit_coupon_data',
                    'class'  => 'vendor_usage_limit_coupon_data',
                ),
            )
        );

        array_splice( $coupon_tabs, 1, 0, $vendor_coupon_tab );

        // admin coupon script enqueue
        wp_enqueue_script( 'dokan_admin_coupon' );

        return $coupon_tabs;
    }

    /**
     * Add vendor coupon section on admin coupon area
     *
     * @param int $coupon_id
     * @param obj $coupon
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function dokan_admin_coupon_data_panels( $coupon_id, $coupon ) {
        $enabled_all_vendor       = $coupon->get_meta( 'admin_coupons_enabled_for_vendor' );
        $vendor_ids_data          = $coupon->get_meta( 'coupons_vendors_ids' );
        $vendor_ids               = ! empty( $vendor_ids_data ) ? explode( ',', $vendor_ids_data ) : [];
        $exclude_vendors_ids_data = $coupon->get_meta( 'coupons_exclude_vendors_ids' );
        $exclude_vendors_ids      = ! empty( $exclude_vendors_ids_data ) ? explode( ',', $exclude_vendors_ids_data ) : [];
        $vendor_include           = '';
        $vendor_exclude           = '';

        if ( 'yes' === $enabled_all_vendor && empty( $exclude_vendors_ids_data ) ) {
            $vendor_include = 'all';
        } elseif ( 'yes' === $enabled_all_vendor && ! empty( $exclude_vendors_ids_data ) ) {
            $vendor_include = 'all';
            $vendor_exclude = $exclude_vendors_ids_data;
        } elseif ( 'no' === $enabled_all_vendor && ! empty( $vendor_ids_data ) ) {
            $vendor_include = $vendor_ids_data;
        }

        ?>
        <div id="vendor_usage_limit_coupon_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                woocommerce_wp_checkbox(
                    array(
                        'id'          => 'admin_coupons_enabled_for_vendor',
                        'label'       => __( 'Enable for All Vendors', 'dokan' ),
                        'description' => __( 'Check this box if you want to apply this coupon for all vendors.', 'dokan' ),
                    )
                );

                echo '<div class="options_group">';

                woocommerce_wp_select(
                    array(
                        'id'          => 'coupon_commissions_type',
                        'label'       => __( 'Coupon Price Deduct', 'dokan' ),
                        'options'     => dokan_get_admin_coupon_commissions_type(),
                    )
                );
                ?>
                <p class="form-field">
                    <span class="coupon_commissions_type_label coupon_commissions_type_default"><?php esc_html_e( 'The coupon amount then calculate admin commission and vendor earning, it\'s the same as vendor coupon functions.', 'dokan' ); ?></span>
                    <span class="coupon_commissions_type_label coupon_commissions_type_from_vendor"><?php esc_html_e( 'The full coupon price will be deducted from vendor earnings.', 'dokan' ); ?></span>
                    <span class="coupon_commissions_type_label coupon_commissions_type_from_admin"><?php esc_html_e( 'The full coupon price will be deducted from admin earnings.', 'dokan' ); ?></span>
                    <span class="coupon_commissions_type_label coupon_commissions_type_shared_coupon"><?php esc_html_e( 'The coupon price will be deducted from admin earnings and vendor earnings as per shared coupon amount.', 'dokan' ); ?></span>
                </p>
                <?php
                echo '</div>';

                woocommerce_wp_select(
                    array(
                        'id'      => 'admin_shared_coupon_type',
                        'label'   => __( 'Shared Amount Type', 'dokan' ),
                        'options' => array(
                            'percentage' => __( 'Percentage', 'dokan' ),
                            'flat'       => __( 'Flat', 'dokan' ),
                        ),
                    )
                );

                woocommerce_wp_text_input(
                    array(
                        'id'          => 'admin_shared_coupon_amount',
                        'label'       => __( 'Admin Shared Coupon Amount', 'dokan' ),
                        'placeholder' => wc_format_localized_price( 0 ),
                        'description' => __( 'Value of the admin shared coupon amount.', 'dokan' ),
                        'data_type'   => 'price',
                        'desc_tip'    => true,
                    )
                );
                ?>
                <p class="form-field dokan-admin-coupons-include-vendors">
                    <label><?php esc_html_e( 'Vendors', 'dokan' ); ?></label>
                    <select class="wc-product-search dokan_admin_coupons_vendors_include_ids" multiple="multiple" style="width: 50%;" name="vendors_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a vendor&hellip;', 'dokan' ); ?>" data-action="dokan_admin_coupons_search_vendors">
                        <?php
                        if ( is_array( $vendor_ids ) ) {
                            foreach ( $vendor_ids as $vendor_id ) {
                                $vendor = dokan()->vendor->get( (int) $vendor_id );
                                if ( is_object( $vendor ) ) {
                                    $shop_name = empty( $vendor->get_shop_name() ) ? $vendor->get_name() : $vendor->get_shop_name();

                                    echo '<option value="' . esc_attr( $vendor->id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $shop_name ) ) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                    <?php echo wc_help_tip( __( 'Vendors that the coupon will be applied.', 'dokan' ) ); ?>
                </p>

                <p class="form-field dokan-admin-coupons-exclude-vendors" style="display: none;">
                    <label><?php esc_html_e( 'Exclude Vendors', 'dokan' ); ?></label>
                    <select class="wc-product-search dokan_admin_coupons_vendors_exclude_ids" multiple="multiple" style="width: 50%;" name="exclude_vendors_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a vendor&hellip;', 'dokan' ); ?>" data-action="dokan_admin_coupons_search_vendors">
                        <?php
                        if ( is_array( $exclude_vendors_ids ) ) {
                            foreach ( $exclude_vendors_ids as $vendor_id ) {
                                $vendor = dokan()->vendor->get( (int) $vendor_id );
                                if ( is_object( $vendor ) ) {
                                    $shop_name = empty( $vendor->get_shop_name() ) ? $vendor->get_name() : $vendor->get_shop_name();

                                    echo '<option value="' . esc_attr( $vendor->id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $shop_name ) ) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                    <?php echo wc_help_tip( __( 'Vendors that the coupon will not be applied.', 'dokan' ) ); ?>
                </p>

                <p class="form-field dokan-coupons-include-product-search-group">
                    <label><?php esc_html_e( 'Products', 'dokan' ); ?></label>
                    <select class="dokan-coupons-exclude-include-product-search" multiple="multiple" style="width: 50%;" name="product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'dokan' ); ?>" data-action="dokan_json_search_products_and_variations_for_coupon">
                        <?php
                        $product_ids = $coupon->get_product_ids( 'edit' );

                        foreach ( $product_ids as $product_id ) {
                            $product = wc_get_product( $product_id );
                            if ( is_object( $product ) ) {
                                echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <?php echo wc_help_tip( __( 'Products that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'dokan' ) ); ?>
                </p>

                <?php // Exclude Product ids. ?>
                <p class="form-field">
                    <label><?php esc_html_e( 'Exclude products', 'dokan' ); ?></label>
                    <select class="dokan-coupons-exclude-include-product-search" multiple="multiple" style="width: 50%;" name="exclude_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'dokan' ); ?>" data-action="dokan_json_search_products_and_variations_for_coupon">
                        <?php
                        $product_ids = $coupon->get_excluded_product_ids( 'edit' );

                        foreach ( $product_ids as $product_id ) {
                            $product = wc_get_product( $product_id );
                            if ( is_object( $product ) ) {
                                echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <?php echo wc_help_tip( __( 'Products that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'dokan' ) ); ?>
                </p>
                <?php

                echo '</div><div class="options_group">';

                // Categories.
                ?>

                <?php
                woocommerce_wp_checkbox(
                    array(
                        'id'          => 'admin_coupons_show_on_stores',
                        'label'       => __( 'Show on stores', 'dokan' ),
                        'description' => __( 'Check this box if you want to show the coupons on vendors store page.', 'dokan' ),
                    )
                );
                woocommerce_wp_checkbox(
                    array(
                        'id'          => 'admin_coupons_send_notify_to_vendors',
                        'label'       => __( 'Notify Vendors', 'dokan' ),
                        'value'       => 'no',
                        'description' => __( 'Check this box if you want to notify selected vendors.', 'dokan' ),
                    )
                );
                ?>
            </div>
            <input type="hidden" name="dokan_admin_vendor_nonce" value="<?php echo esc_attr( wp_create_nonce( 'dokan-admin-vendor-coupon' ) ); ?>" />
        </div>
        <?php do_action( 'dokan_admin_coupon_data_panels', $coupon_id, $coupon ); ?>
        <div class="clear"></div>
        <?php
    }

    /**
     * Search for products and echo json.
     *
     * @since 3.4.0
     *
     * @param string $term (default: '') Term to search for.
     * @param bool   $include_variations in search or not.
     */
    public function dokan_json_search_products_and_variations_for_coupon( $term = '', $include_variations = true ) {
        check_ajax_referer( 'search-products', 'security' );

        if ( ! isset( $_GET['search_products_for_vendor_coupon'] ) ) {
            wp_die();
        }

        if ( empty( $term ) && isset( $_GET['term'] ) ) {
            $term = (string) wc_clean( wp_unslash( $_GET['term'] ) );
        }

        if ( empty( $term ) ) {
            wp_die();
        }

        if ( ! empty( $_GET['limit'] ) ) {
            $limit = absint( $_GET['limit'] );
        } else {
            $limit = absint( apply_filters( 'dokan_json_search_limit', 3000 ) );
        }

        $include_ids        = ! empty( $_GET['include'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['include'] ) ) : array();
        $exclude_ids        = ! empty( $_GET['exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) : array();
        $include_vendor_ids = ! empty( $_GET['include_vendor_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['include_vendor_ids'] ) ) : array();
        $exclude_vendor_ids = ! empty( $_GET['exclude_vendor_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude_vendor_ids'] ) ) : array();
        $enable_all_vendor  = ! empty( $_GET['enable_all_vendor'] ) ? wp_unslash( sanitize_text_field( wp_unslash( $_GET['enable_all_vendor'] ) ) ) : '';

        $exclude_types = array();
        if ( ! empty( $_GET['exclude_type'] ) ) {
            // Support both comma-delimited and array format inputs.
            $exclude_types = wp_unslash( $_GET['exclude_type'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            if ( ! is_array( $exclude_types ) ) {
                $exclude_types = explode( ',', $exclude_types );
            }

            // Sanitize the excluded types against valid product types.
            foreach ( $exclude_types as &$exclude_type ) {
                $exclude_type = strtolower( trim( $exclude_type ) );
            }
            $exclude_types = array_intersect(
                array_merge( array( 'variation' ), array_keys( wc_get_product_types() ) ),
                $exclude_types
            );
        }

        $data_store = WC_Data_Store::load( 'product' );
        $ids        = $this->search_products( $term, '', (bool) $include_variations, false, $limit, $include_ids, $exclude_ids, $include_vendor_ids, $exclude_vendor_ids, $enable_all_vendor );

        $products = array();

        foreach ( $ids as $id ) {
            $product_object = wc_get_product( $id );

            if ( ! wc_products_array_filter_readable( $product_object ) ) {
                continue;
            }

            $formatted_name = $product_object->get_formatted_name();
            $managing_stock = $product_object->managing_stock();

            if ( in_array( $product_object->get_type(), $exclude_types, true ) ) {
                continue;
            }

            if ( $managing_stock && ! empty( $_GET['display_stock'] ) ) {
                $stock_amount = $product_object->get_stock_quantity();
                /* Translators: %d stock amount */
                $formatted_name .= ' &ndash; ' . sprintf( __( 'Stock: %d', 'dokan' ), wc_format_stock_quantity_for_display( $stock_amount, $product_object ) );
            }

            $products[ $product_object->get_id() ] = rawurldecode( wp_strip_all_tags( $formatted_name ) );
        }

        wp_send_json( apply_filters( 'dokan_json_search_found_products', $products ) );
    }

    /**
     * Search product data for a term and return ids.
     *
     * @since 3.4.0
     *
     * @param  string     $term Search term.
     * @param  string     $type Type of product.
     * @param  bool       $include_variations Include variations in search or not.
     * @param  bool       $all_statuses Should we search all statuses or limit to published.
     * @param  null|int   $limit Limit returned results.
     * @param  null|array $include Keep specific results.
     * @param  null|array $exclude Discard specific results.
     * @param  null|array $include_vendor_ids
     * @param  null|array $exclude_vendor_ids
     * @param  string     $enable_all_vendor
     * @return array of ids
     */
    public function search_products( $term, $type = '', $include_variations = false, $all_statuses = false, $limit = null, $include = null, $exclude = null, $include_vendor_ids = null, $exclude_vendor_ids = null, $enable_all_vendor = null ) {
        global $wpdb;

        $custom_results = apply_filters( 'dokan_product_pre_search_products', false, $term, $type, $include_variations, $all_statuses, $limit );

        if ( is_array( $custom_results ) ) {
            return $custom_results;
        }

        $post_types   = $include_variations ? array( 'product', 'product_variation' ) : array( 'product' );
        $join_query   = '';
        $type_where   = '';
        $status_where = '';
        $limit_query  = '';

        // When searching variations we should include the parent's meta table for use in searches.
        if ( $include_variations ) {
            $join_query = " LEFT JOIN {$wpdb->wc_product_meta_lookup} parent_wc_product_meta_lookup
             ON posts.post_type = 'product_variation' AND parent_wc_product_meta_lookup.product_id = posts.post_parent ";
        }

        /**
         * Hook woocommerce_search_products_post_statuses.
         *
         * @since 3.4.0
         *
         * @param array $post_statuses List of post statuses.
         */
        $post_statuses = apply_filters(
            'dokan_search_products_post_statuses',
            current_user_can( 'edit_private_products' ) ? array( 'private', 'publish' ) : array( 'publish' )
        );

        // See if search term contains OR keywords.
        if ( stristr( $term, ' or ' ) ) {
            $term_groups = preg_split( '/\s+or\s+/i', $term );
        } else {
            $term_groups = array( $term );
        }

        $search_where   = '';
        $search_queries = array();

        foreach ( $term_groups as $term_group ) {
            // Parse search terms.
            if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $term_group, $matches ) ) {
                $search_terms = $this->get_valid_search_terms( $matches[0] );
                $count        = count( $search_terms );

                // if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
                if ( 9 < $count || 0 === $count ) {
                    $search_terms = array( $term_group );
                }
            } else {
                $search_terms = array( $term_group );
            }

            $term_group_query = '';
            $searchand        = '';

            foreach ( $search_terms as $search_term ) {
                $like = '%' . $wpdb->esc_like( $search_term ) . '%';

                // Variations should also search the parent's meta table for fallback fields.
                if ( $include_variations ) {
                    $variation_query = $wpdb->prepare( " OR ( wc_product_meta_lookup.sku = '' AND parent_wc_product_meta_lookup.sku LIKE %s ) ", $like );
                } else {
                    $variation_query = '';
                }

                $term_group_query .= $wpdb->prepare( " {$searchand} ( ( posts.post_title LIKE %s) OR ( posts.post_excerpt LIKE %s) OR ( posts.post_content LIKE %s ) OR ( wc_product_meta_lookup.sku LIKE %s ) $variation_query)", $like, $like, $like, $like ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $searchand         = ' AND ';
            }

            if ( $term_group_query ) {
                $search_queries[] = $term_group_query;
            }
        }

        if ( ! empty( $search_queries ) ) {
            $search_where = ' AND (' . implode( ') OR (', $search_queries ) . ') ';
        }

        if ( ! empty( $include ) && is_array( $include ) ) {
            $search_where .= ' AND posts.ID IN(' . implode( ',', array_map( 'absint', $include ) ) . ') ';
        }

        if ( 'no' === $enable_all_vendor && ! empty( $include_vendor_ids ) && is_array( $include_vendor_ids ) ) {
            $search_where .= ' AND posts.post_author IN(' . implode( ',', array_map( 'absint', $include_vendor_ids ) ) . ') ';
        }

        if ( 'yes' === $enable_all_vendor && ! empty( $exclude_vendor_ids ) && is_array( $exclude_vendor_ids ) ) {
            $search_where .= ' AND posts.post_author NOT IN(' . implode( ',', array_map( 'absint', $exclude_vendor_ids ) ) . ') ';
        }

        if ( ! empty( $exclude ) && is_array( $exclude ) ) {
            $search_where .= ' AND posts.ID NOT IN(' . implode( ',', array_map( 'absint', $exclude ) ) . ') ';
        }

        if ( 'virtual' === $type ) {
            $type_where = ' AND ( wc_product_meta_lookup.virtual = 1 ) ';
        } elseif ( 'downloadable' === $type ) {
            $type_where = ' AND ( wc_product_meta_lookup.downloadable = 1 ) ';
        }

        if ( ! $all_statuses ) {
            $status_where = " AND posts.post_status IN ('" . implode( "','", $post_statuses ) . "') ";
        }

        if ( $limit ) {
            $limit_query = $wpdb->prepare( ' LIMIT %d ', $limit );
        }

        // phpcs:ignore WordPress.VIP.DirectDatabaseQuery.DirectQuery
        $search_results = $wpdb->get_results(
            // phpcs:disable
            "SELECT DISTINCT posts.ID as product_id, posts.post_parent as parent_id FROM {$wpdb->posts} posts
             LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON posts.ID = wc_product_meta_lookup.product_id
             $join_query
            WHERE posts.post_type IN ('" . implode( "','", $post_types ) . "')
            $search_where
            $status_where
            $type_where
            ORDER BY posts.post_parent ASC, posts.post_title ASC
            $limit_query
            "
            // phpcs:enable
        );

        $product_ids = wp_parse_id_list( array_merge( wp_list_pluck( $search_results, 'product_id' ), wp_list_pluck( $search_results, 'parent_id' ) ) );

        if ( is_numeric( $term ) ) {
            $post_id   = absint( $term );
            $post_type = get_post_type( $post_id );

            if ( 'product_variation' === $post_type && $include_variations ) {
                $product_ids[] = $post_id;
            } elseif ( 'product' === $post_type ) {
                $product_ids[] = $post_id;
            }

            $product_ids[] = wp_get_post_parent_id( $post_id );
        }

        return wp_parse_id_list( $product_ids );
    }

    /**
     * Check if the terms are suitable for searching.
     *
     * Uses an array of stopwords (terms) that are excluded from the separate
     * term matching when searching for posts. The list of English stopwords is
     * the approximate search engines list, and is translatable.
     *
     * @since 3.4.0
     *
     * @since 3.4.0
     * @param array $terms Terms to check.
     * @return array Terms that are not stopwords.
     */
    public function get_valid_search_terms( $terms ) {
        $valid_terms = array();
        $stopwords   = $this->get_search_stopwords();

        foreach ( $terms as $term ) {
            // keep before/after spaces when term is for exact match, otherwise trim quotes and spaces.
            if ( preg_match( '/^".+"$/', $term ) ) {
                $term = trim( $term, "\"'" );
            } else {
                $term = trim( $term, "\"' " );
            }

            // Avoid single A-Z and single dashes.
            if ( empty( $term ) || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
                continue;
            }

            if ( in_array( wc_strtolower( $term ), $stopwords, true ) ) {
                continue;
            }

            $valid_terms[] = $term;
        }

        return $valid_terms;
    }

    /**
     * Retrieve stopwords used when parsing search terms.
     *
     * @since 3.4.0
     *
     * @return array Stopwords.
     */
    public function get_search_stopwords() {
        // Translators: This is a comma-separated list of very common words that should be excluded from a search, like a, an, and the. These are usually called "stopwords". You should not simply translate these individual words into your language. Instead, look for and provide commonly accepted stopwords in your language.
        $stopwords = array_map(
            'wc_strtolower',
            array_map(
                'trim',
                explode(
                    ',',
                    _x(
                        'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
                        'Comma-separated list of search stopwords in your language',
                        'dokan'
                    )
                )
            )
        );

        return apply_filters( 'wp_search_stopwords', $stopwords );
    }

    /**
     * Search for products and echo json.
     *
     * @since 3.4.0
     *
     * @param string $term (default: '') Term to search for.
     * @param bool   $include_variations in search or not.
     */
    public function dokan_admin_coupons_search_vendors( $term = '' ) {
        check_ajax_referer( 'search-products', 'security' );

        if ( empty( $term ) && isset( $_GET['term'] ) ) {
            $term = (string) wc_clean( wp_unslash( $_GET['term'] ) );
        }

        if ( empty( $term ) ) {
            wp_die();
        }

        if ( ! empty( $_GET['limit'] ) ) {
            $limit = absint( $_GET['limit'] );
        } else {
            $limit = absint( apply_filters( 'dokan_admin_coupons_search_vendors_limit', 30 ) );
        }

        $seller_args = array(
            'number'     => $limit,
            'order'      => 'DESC',
            'meta_query' => [
                [
                    'key'     => 'dokan_store_name',
                    'value'   => $term,
                    'compare' => 'LIKE',
                ],
            ],
        );

        $vendors     = array();
        $get_vendors = dokan()->vendor->get_vendors( $seller_args );

        foreach ( $get_vendors as $vendor ) {
            $shop_name = empty( $vendor->get_shop_name() ) ? $vendor->get_name() : $vendor->get_shop_name();

            $vendors[ $vendor->id ] = rawurldecode( wp_strip_all_tags( $shop_name ) );
        }

        wp_send_json( apply_filters( 'dokan_admin_coupons_search_found_vendors', $vendors ) );
    }

    /**
     * Coupon is valid for vendor current product
     *
     * @since 3.4.0
     *
     * @param boolean $valid
     * @param obj $product
     * @param obj $coupon
     *
     * @return boolean
     */
    public function coupon_is_valid_for_product( $valid, $product, $coupon ) {
        if ( false === $valid ) {
            return $valid;
        }

        $vendors  = array( intval( get_post_field( 'post_author', $product->get_id() ) ) );
        $products = array( $product->get_id() );

        if ( $product->get_parent_id() > 0 ) {
            $products[] = $product->get_parent_id();
        }
        return dokan_pro()->coupon->is_admin_coupon_valid( $coupon, $vendors, $products );
    }

    /**
     * Save vendor coupon data from admin area
     *
     * @param int $post_id
     * @param obj $coupon
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function admin_coupon_options_save( $post_id, $coupon ) {
        if ( ! isset( $_POST['dokan_admin_vendor_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_admin_vendor_nonce'] ) ), 'dokan-admin-vendor-coupon' ) ) {
            return;
        }

        $enabled_for_vendor   = isset( $_POST['admin_coupons_enabled_for_vendor'] ) ? 'yes' : 'no';
        $coupon_type          = isset( $_POST['coupon_commissions_type'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_commissions_type'] ) ) : '';
        $shared_coupon_amount = isset( $_POST['admin_shared_coupon_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_shared_coupon_amount'] ) ) : '';
        $notify_to_vendors    = isset( $_POST['admin_coupons_send_notify_to_vendors'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_coupons_send_notify_to_vendors'] ) ) : 'no';
        $show_on_stores       = isset( $_POST['admin_coupons_show_on_stores'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_coupons_show_on_stores'] ) ) : 'no';
        $shared_coupon_type   = isset( $_POST['admin_shared_coupon_type'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_shared_coupon_type'] ) ) : '';

        if ( isset( $_POST['vendors_ids'] ) ) {
            $vendors_ids = implode( ',', array_filter( array_map( 'intval', (array) $_POST['vendors_ids'] ) ) );
        } else {
            $vendors_ids = '';
        }

        if ( isset( $_POST['exclude_vendors_ids'] ) ) {
            $exclude_vendors_ids = implode( ',', array_filter( array_map( 'intval', (array) $_POST['exclude_vendors_ids'] ) ) );
        } else {
            $exclude_vendors_ids = '';
        }

        /**
         * Do action if needed before saving coupon data
         *
         * @since 3.7.4
         */
        do_action(
            'dokan_admin_coupon_options_before_save',
            $post_id,
            [
                'admin_coupons_enabled_for_vendor' => $enabled_for_vendor,
                'coupon_commissions_type' => $coupon_type,
                'coupons_vendors_ids' => $vendors_ids,
                'coupons_exclude_vendors_ids' => $exclude_vendors_ids,
                'admin_shared_coupon_amount' => $shared_coupon_amount,
                'admin_shared_coupon_type' => $shared_coupon_type,
                'admin_coupons_send_notify_to_vendors' => $notify_to_vendors,
                'admin_coupons_show_on_stores' => $show_on_stores,
            ]
        );

        update_post_meta( $post_id, 'admin_coupons_enabled_for_vendor', $enabled_for_vendor );
        update_post_meta( $post_id, 'coupon_commissions_type', $coupon_type );
        update_post_meta( $post_id, 'coupons_vendors_ids', $vendors_ids );
        update_post_meta( $post_id, 'coupons_exclude_vendors_ids', $exclude_vendors_ids );
        update_post_meta( $post_id, 'admin_shared_coupon_amount', $shared_coupon_amount );
        update_post_meta( $post_id, 'admin_shared_coupon_type', $shared_coupon_type );
        update_post_meta( $post_id, 'admin_coupons_send_notify_to_vendors', $notify_to_vendors );
        update_post_meta( $post_id, 'admin_coupons_show_on_stores', $show_on_stores );

        if ( 'yes' === $notify_to_vendors ) {
            /**
             * @var $announcement Announcement
             */
            $announcement       = dokan_pro()->announcement;
            $vendors_ids        = array();
            $exclude_sellers    = array();
            $discount_type      = $coupon->get_discount_type();
            $get_coupon_type    = dokan_get_coupon_types();
            $expiry_date        = $coupon->get_date_expires();
            $expiry_date        = $expiry_date ? $expiry_date->date_i18n( 'F j, Y' ) : '&ndash;';
            $coupon_shared_type = dokan_get_admin_coupon_commissions_type();
            $usage_count        = absint( $coupon->get_usage_count() );
            $usage_limit        = esc_html( $coupon->get_usage_limit() );
            $coupons_list_link  = esc_url( add_query_arg( array( 'coupons_type' => 'marketplace_coupons' ), dokan_get_navigation_url( 'coupons' ) ) );
            // translators: %1$s: Coupon list link, %2$s: Coupon list link label
            $coupons_list_link = sprintf( '<a href="%s">%s</a>', esc_url( $coupons_list_link ), __( 'Check the coupons list', 'dokan' ) );

            if ( 'no' === $enabled_for_vendor ) {
                $vendors_ids = array_filter( array_map( 'intval', (array) $_POST['vendors_ids'] ) );
            } elseif ( 'yes' === $enabled_for_vendor && ! empty( $exclude_vendors_ids ) ) {
                $exclude_sellers = array_filter( array_map( 'intval', (array) $_POST['exclude_vendors_ids'] ) );
            }

            if ( 'percentage' === $shared_coupon_type ) {
                $shared_coupon_amount = esc_attr( $shared_coupon_amount ) . '%';
            } else {
                $shared_coupon_amount = wp_kses_post( wc_price( $shared_coupon_amount ) );
            }

            $content  = '<p>' . __( 'Coupon Type', 'dokan' ) . ': ' . $get_coupon_type[ $discount_type ] . '</p>';
            $content .= '<p>' . __( 'Coupon Code', 'dokan' ) . ': ' . $coupon->get_code() . '</p>';
            $content .= '<p>' . __( 'Coupon Price', 'dokan' ) . ': ' . wp_kses_post( wc_price( $coupon->get_amount() ) ) . '</p>';
            $content .= '<p>' . __( 'Coupon Price Deduct From', 'dokan' ) . ': ' . $coupon_shared_type[ $coupon_type ] . '</p>';

            if ( 'shared_coupon' === $coupon_type ) {
                $content .= '<p>' . __( 'Admin Shared', 'dokan' ) . ': ' . $shared_coupon_amount . '</p>';
            }

            if ( $usage_limit ) {
                // translators: %1$s: Usage count, %2$s: Usage limit
                $usage_limit_data = sprintf( __( '%1$s / %2$s', 'dokan' ), $usage_count, $usage_limit );
            } else {
                // translators: %s: Usage count
                $usage_limit_data = sprintf( __( '%s / &infin;', 'dokan' ), $usage_count );
            }

            $content .= '<p>' . __( 'Usage / Limit', 'dokan' ) . ': ' . $usage_limit_data . '</p>';
            $content .= '<p>' . __( 'Expiry date', 'dokan' ) . ': ' . $expiry_date . '</p>';
            $content .= '<p>' . $coupons_list_link . '</p>';

            $args = [
                'title'               => __( 'Admin Created a Coupon for Your Store.', 'dokan' ),
                'content'             => $content,
                'sender_type'         => 'yes' === $enabled_for_vendor ? 'all_seller' : 'selected_seller',
                'sender_ids'          => $vendors_ids,
                'exclude_sellers_ids' => $exclude_sellers,
                'status'              => 'publish',
            ];

            $notice = $announcement->create_announcement( $args );

            if ( ! is_wp_error( $notice ) ) {
                update_post_meta( $notice, 'dokan_admin_coupons_notify_vendors', $post_id );
                update_post_meta( $post_id, 'dokan_admin_coupons_announcement_id', $notice );
            }
        }

        do_action( 'dokan_admin_coupon_options_save', $post_id, $coupon );
    }
}
