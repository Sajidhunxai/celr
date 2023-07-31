<?php

    /**
     * For full documentation, please visit: http://docs.reduxframework.com/
     * For a more extensive sample-config file, you may look at:
     * https://github.com/reduxframework/redux-framework/blob/master/sample/sample-config.php
     */

    if ( ! class_exists( 'weLaunch' ) && ! class_exists( 'Redux' ) ) {
        return;
    }

    if( class_exists( 'weLaunch' ) ) {
        $framework = new weLaunch();
    } else {
        $framework = new Redux();
    }

    // This is your option name where all the Redux data is stored.
    $opt_name = "woocommerce_single_variations_options";

    $attribute_taxonomy_names = wc_get_attribute_taxonomy_names();
    $attribute_taxonomy_names = array_combine($attribute_taxonomy_names, $attribute_taxonomy_names);

    $args = array(
        'opt_name' => 'woocommerce_single_variations_options',
        'use_cdn' => TRUE,
        'dev_mode' => FALSE,
        'display_name' => 'WooCommerce Single Variations',
        'display_version' => '1.3.19',
        'page_title' => 'WooCommerce Single Variations',
        'update_notice' => TRUE,
        'intro_text' => '',
        'footer_text' => '&copy; '.date('Y').' weLaunch',
        'admin_bar' => false,
        'menu_type' => 'submenu',
        'menu_title' => 'Single Variations',
        'allow_sub_menu' => TRUE,
        'page_parent' => 'woocommerce',
        'page_parent_post_type' => 'your_post_type',
        'customizer' => FALSE,
        'default_mark' => '*',
        'hints' => array(
            'icon_position' => 'right',
            'icon_color' => 'lightgray',
            'icon_size' => 'normal',
            'tip_style' => array(
                'color' => 'light',
            ),
            'tip_position' => array(
                'my' => 'top left',
                'at' => 'bottom right',
            ),
            'tip_effect' => array(
                'show' => array(
                    'duration' => '500',
                    'event' => 'mouseover',
                ),
                'hide' => array(
                    'duration' => '500',
                    'event' => 'mouseleave unfocus',
                ),
            ),
        ),
        'output' => TRUE,
        'output_tag' => TRUE,
        'settings_api' => TRUE,
        'cdn_check_time' => '1440',
        'compiler' => TRUE,
        'page_permissions' => 'manage_options',
        'save_defaults' => TRUE,
        'show_import_export' => TRUE,
        'database' => 'options',
        'transient_time' => '3600',
        'network_sites' => TRUE,
    );

    global $weLaunchLicenses;
    if( (isset($weLaunchLicenses['woocommerce-single-variations']) && !empty($weLaunchLicenses['woocommerce-single-variations'])) || (isset($weLaunchLicenses['woocommerce-plugin-bundle']) && !empty($weLaunchLicenses['woocommerce-plugin-bundle'])) ) {
        $args['display_name'] = '<span class="dashicons dashicons-yes-alt" style="color: #9CCC65 !important;"></span> ' . $args['display_name'];
    } else {
        $args['display_name'] = '<span class="dashicons dashicons-dismiss" style="color: #EF5350 !important;"></span> ' . $args['display_name'];
    }

    $framework::setArgs( $opt_name, $args );


    global $wpdb;
    
    $sql = "SELECT count(*)
    FROM $wpdb->posts 
    WHERE post_type = 'product_variation'
    ";

    $count = $wpdb->get_var($sql);

    $framework::setSection( $opt_name, array(
        'title'  => __( 'Single Variations', 'woocommerce-single-variations' ),
        'id'     => 'general',
        'desc'   => __( 'Need support? Please use the comment function on codecanyon.', 'woocommerce-single-variations' ),
        'icon'   => 'el el-home',
    ) );

    $framework::setSection( $opt_name, array(
        'title'      => __( 'General', 'woocommerce-single-variations' ),
        'desc'       => __( 'To get auto updates please <a href="' . admin_url('tools.php?page=welaunch-framework') . '">register your License here</a>.', 'woocommerce-pdf-catalog' ),
        'id'         => 'general-settings',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'enable',
                'type'     => 'switch',
                'title'    => __( 'Enable', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Enable variations table to use the options below', 'woocommerce-single-variations' ),
                'default'  => 1
            ),
            array(
                'id'       => 'variationMenuOrder',
                'type'     => 'checkbox',
                'title'    => __( 'Variation Menu Order', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Enable this to inherit the menu order of single variations from the main variable product. This ensures the listing in product categories works as normal variable products. Deactivate when you want to sort single variations when you edit a product (be aware this will break category listing order!).', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'shortcodeOnlyShowVariations',
                'type'     => 'checkbox',
                'title'    => __( 'Shortcode only show variations', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Only show variations in shortcode and hide all normal products. Only makes sense when you have only variable products.', 'woocommerce-single-variations' ),
                'default'  => 0,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'showVariationGalleryImagesInListing',
                'type'     => 'checkbox',
                'title'    => __( 'Show 2nd Variation Gallery images in Categories', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Add support for gallery images to show in listings. You will need our <a href="https://www.welaunch.io/en/product/woocommerce-gallery-images/" target="_blank">Gallery Images plugin</a> and your theme must support 2nd image in listings (e.g. Flatsome).', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'showVariationsInSearch',
                'type'     => 'checkbox',
                'title'    => __( 'Show Variations in Search', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Removes the main product and shows variations in your search.', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'showVariationsInRelated',
                'type'     => 'checkbox',
                'title'    => __( 'Show Variations in Related', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Removes the main product and shows variations in related products section.', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'variationRatings',
                'type'     => 'checkbox',
                'title'    => __( 'Show variation Rating', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Modify the ratings for variations based on variable rating to show in shop loop.', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'hideParentProducts',
                'type'     => 'checkbox',
                'title'    => __( 'Hide Parent Products', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Removes the main product and only shows variations in your shop.', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'doNotShowVariationsOnFilter',
                'type'     => 'checkbox',
                'title'    => __( 'Hide Variations when Filters are active', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Removes variations and just show variable products when customers filter.', 'woocommerce-single-variations' ),
                'default'  => 0,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'hideOnShopPage',
                'type'     => 'checkbox',
                'title'    => __( 'Hide Variations on Shop Page', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Do not show single variations on general shop page. Page must be set in WooCommerce > Settings > Products > Shop page', 'woocommerce-single-variations' ),
                'default'  => 0,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'onlyShowWhenFiltered',
                'type'     => 'checkbox',
                'title'    => __( 'Hide Variations on Shop & Show only when filtered', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Do not show single variations on all shop pages except when filtered.', 'woocommerce-single-variations' ),
                'default'  => 0,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'       => 'changeCount',
                'type'     => 'checkbox',
                'title'    => __( 'Change Counts', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Change the layered nav and category counts. Only activate when you also show them. Changing counts costs 3 queries and that decreases performance.', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('enable','equals','1'),
            ),
            array(
                'id'   => 'importer',
                'type' => 'info',
                'desc' => __(
                        '<div style="text-align:center;">

                            <p>Fresh install? Make sure you click on init variations once. Problems? Reset variations and then click on init variations again.</p>

                            <div id="init-variations-statistic">
                                Updating ...
                                <span id="init-variations-updated">0</span> Updated | <span id="init-variations-already-updated">0</span> Already Updated | <span id="init-variations-total">' . $count . '</span> Total Variations
                            </div>

                            <div id="reset-variations-statistic">
                                Resetting ...
                                <span id="reset-variations-updated">0</span> Resetted | <span id="reset-variations-total">' . $count . '</span> Total Variations
                            </div>

                            <a id="init-variations" href="' . get_admin_url() . 'admin.php?page=woocommerce_single_variations_options_options&update-variations=true" class="button button-success">INIT Variations</a>
                            <a id="reset-variations" href="' . get_admin_url() . 'admin.php?page=woocommerce_single_variations_options_options&reset-variations=true" class="button button-success">Reset Variations</a>
                            <a href="' . get_admin_url() . 'admin.php?page=woocommerce_single_variations_options_options&reset-variation-transients=true" class="button button-success">Reset Transients (Cache)</a>
                        </div>', 'woocommerce-single-variations')
            ),
        )
    ));

 $framework::setSection( $opt_name, array(
        'title'      => __( 'Variation Title', 'woocommerce-single-variations' ),
        // 'desc'       => __( '', 'woocommerce-single-variations' ),
        'id'         => 'variation-title',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'variationTitleEnabled',
                'type'     => 'switch',
                'title'    => __( 'Enable Custom Variation Title', 'woocommerce-single-variations' ),
                'subtitle' => __( 'The custom title can be overwritten in product variation settings.', 'woocommerce-single-variations' ),
                'default'  => 1
            ),
            array(
                'id'       => 'variationTitleEnabledOnSingleProductPages',
                'type'     => 'checkbox',
                'title'    => __( 'Custom Variation Title on Product Pages', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Enable the variation title to also change on single product pages.', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('variationTitleEnabled','equals','1'),
            ),
            array(
                'id'       => 'variationTitleChangeOnSingleProductPages',
                'type'     => 'checkbox',
                'title'    => __( 'Change Variation Title when Selected', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Change the variation title when anohter variation is selected.', 'woocommerce-single-variations' ),
                'default'  => 1,
                'required' => array('variationTitleEnabled','equals','1'),
            ),
           array(
                'id'       => 'variationTitleChangeOnSingleProductPagesSelector',
                'type'     => 'text',
                'title'    => __('Product Name Selector', 'woocommerce-advanced-categories'),
                'subtitle' => __('Product Name CSS Class or ID'),
                'default'  => 'h1',
                'required' => array('variationTitleChangeOnSingleProductPages','equals','1'),
            ),
            array(
                'id'       => 'variationTitleTemplate',
                'type'     => 'text',
                'title'    => __('SEO Title Template', 'woocommerce-advanced-categories'),
                'subtitle' => __('Here you can modify how the SEO Title should be shown. 
                                <br>E.g. T-Shirts in Color Black => {title} in {attributes}
                                <br>E.g. Black T-Shirts => {attributes} {title}'),
                'default'  => '{title} in {attributes}',
                'required' => array('variationTitleEnabled','equals','1'),
            ),
            array(
                'id'       => 'variationTitleAttributesTemplate',
                'type'     => 'text',
                'title'    => __('Attributes Template', 'woocommerce-advanced-categories'),
                'subtitle' => __('How Attributes should appear. 
                                <br>E.g. T-Shirts in Color Black => {attributes_name} {attribute_values}
                                <br>E.g. T-Shirts in Black => {attribute_values}
                                <br>E.g. Black T-Shirts => {attribute_values}'),
                'default'  => '{attributes_name} {attribute_values}',
                'required' => array('variationTitleEnabled','equals','1'),
            ),
            array(
                'id'       => 'variationTitleAttributeNamesAppendix',
                'type'     => 'text',
                'title'    => __('Attribute Names Appendix', 'woocommerce-advanced-categories'),
                'subtitle' => __('If more than 1 attribute name ist selected. <br>E.g. T-Shirts in Color Black and Size S.'),
                'default'  => ' and ',
                'required' => array('variationTitleEnabled','equals','1'),
            ),
            array(
                'id'   => 'excludedTitleAttributes',
                'type' => 'select',
                'options' => $attribute_taxonomy_names,
                'multi' => true,
                'title' => __('Exclude Attributes from Title', 'woocommerce-single-variations'), 
                'subtitle' => __('Which attributes should not show in the title. ', 'woocommerce-single-variations'),
                'required' => array('variationTitleEnabled','equals','1'),
            ),
        )
    ));

    $framework::setSection( $opt_name, array(
        'title'      => __( 'Exclusion', 'woocommerce-single-variations' ),
        'desc' => __( 'With the below settings you can exclude products / categories from showing single variations.', 'woocommerce-single-variations' ), 
        'id'         => 'exclusion',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'   => 'excludeProductCategories',
                'type' => 'select',
                'data' => 'categories',
                'args' => array('taxonomy' => array('product_cat')),
                'multi' => true,
                'title' => __('Exclude Product Categories', 'woocommerce-single-variations'), 
                'subtitle' => __('Which product categories should be excluded.', 'woocommerce-single-variations'),
            ),
            array(
                'id'     =>'excludeVariableProducts',
                'type' => 'select',
                // 'options' => $woocommerce_single_variations_options_variable_products,
                'data' => 'posts',
                'args' => array('post_type' => array('product'), 'posts_per_page' => -1),
                'ajax' => true,
                'multi' => true,
                'title' => __('Exclude Variable Products', 'woocommerce-single-variations'), 
                'subtitle' => __('Which Variable should be excluded.', 'woocommerce-single-variations'),
            ),
            array(
                'id'     =>'excludeVariationProducts',
                'type' => 'select',
                // 'options' => $woocommerce_single_variations_options_variation_products,
                'data' => 'posts',
                'args' => array('post_type' => array('product_variation'), 'posts_per_page' => -1),
                'ajax' => true,
                'multi' => true,
                'title' => __('Exclude Variation Products', 'woocommerce-single-variations'), 
                'subtitle' => __('Which Variation should be excluded.', 'woocommerce-single-variations'),
            ),
            array(
                'id'   => 'excludedAttributes',
                'type' => 'select',
                'options' => $attribute_taxonomy_names,
                'multi' => true,
                'title' => __('Exclude Attribute Taxonomies', 'woocommerce-single-variations'), 
                'subtitle' => __('Which attributes should be excluded. That means variations assigned to this category will not appear in the catalog.', 'woocommerce-single-variations'),
            ),
                array(
                    'id'   => 'excludedAttributesRelation',
                    'type' => 'select',
                    'options' => array(
                        'OR' => 'OR',
                        'AND' => 'AND',
                    ),
                    'title' => __('Exclude Attribute Taxonomies Query Relation', 'woocommerce-single-variations'),
                    'subtitle' => __( 'OR is more precise, but AND better for performance. Use AND if you only have 1-2 variation attributes.', 'woocommerce-single-variations' ),
                    'default'   => 'OR',
                ),
                array(
                    'id'       => 'excludedAttributesCaching',
                    'type'     => 'checkbox',
                    'title'    => __( 'Cache Excluded Products', 'woocommerce-single-variations' ),
                    'subtitle' => __( 'This will cache excluded attribute products. Disable to debug in frontend.', 'woocommerce-single-variations' ),
                    'default'  => 1
                ),
                array(
                    'id'       => 'excludedAttributesCachingCategory',
                    'type'     => 'checkbox',
                    'title'    => __( 'Cache Categories Separately', 'woocommerce-single-variations' ),
                    'subtitle' => __( 'Enable this when you have many products in your shop as this will get excluded attribute products per category (faster initial query). Disalbe when you see categories where exlusions does not work.', 'woocommerce-single-variations' ),
                    'default'  => 0,
                    'required' => array('excludedAttributesCaching','equals','1'),
                ),
                array(
                    'id'   => 'excludedAttributesCachingExpiration',
                    'type' => 'select',
                    'options' => array(
                        'MINUTE_IN_SECONDS' => 'Minute In Seconds',
                        'HOUR_IN_SECONDS' => 'Hour In Seconds',
                        'DAY_IN_SECONDS' => 'Day In Seconds',
                        'WEEK_IN_SECONDS' => 'Week In Seconds',
                        'MONTH_IN_SECONDS' => 'Month In Seconds',
                        'YEAR_IN_SECONDS' => 'Year In Seconds',
                    ),
                    'title' => __('Caching Expiration', 'woocommerce-single-variations'),
                    'default'   => 'DAY_IN_SECONDS',
                    'required' => array('excludedAttributesCaching','equals','1'),

                ),
                array(
                    'id'       => 'excludedAttributesKeepFirstVariation',
                    'type'     => 'checkbox',
                    'title'    => __( 'Keep First Variation', 'woocommerce-single-variations' ),
                    'subtitle' => __( 'It will keep the first variation of each attribute term.', 'woocommerce-single-variations' ),
                    'default'  => 1
                ),
                    array(
                        'id'       => 'excludedAttributesKeepOneAttributeProducts',
                        'type'     => 'checkbox',
                        'title'    => __( 'Keep All One Attribute Products', 'woocommerce-single-variations' ),
                        'subtitle' => __( 'For example when you have color + size products, but also you have a product with just size. Then size will still show.', 'woocommerce-single-variations' ),
                        'default'  => 1
                    ),
                    array(
                        'id'       => 'excludedAttributesKeepOnStock',
                        'type'     => 'checkbox',
                        'title'    => __( 'Only Keep in Stock Products', 'woocommerce-single-variations' ),
                        'subtitle' => __( 'It will keep the first variation only when it is also on stock. For hiding out of stock products at all go to WooCommerce Settings > Products > Inventory.', 'woocommerce-single-variations' ),
                        'default'  => 1,
                        'required' => array('excludedAttributesKeepFirstVariation','equals','1'),
                    ),
                    array(
                        'id'       => 'excludedNotOnFilter',
                        'type'     => 'checkbox',
                        'title'    => __( 'Do not Exclude on Filter', 'woocommerce-single-variations' ),
                        'subtitle' => __( 'When products are filtered, do not exclude attributes. For example on size filter still all colors show.', 'woocommerce-single-variations' ),
                        'default'  => 1
                    ),

                array(
                    'id'       => 'excludedAttributesRemoveFromQueryString',
                    'type'     => 'checkbox',
                    'title'    => __( 'Remove Term from Permalink', 'woocommerce-single-variations' ),
                    'subtitle' => __( 'Instead of linking to first "size" product for example it will just link to color.', 'woocommerce-single-variations' ),
                    'default'  => 1
                ),
            array(
                'id'     =>'includeVariationProducts',
                'type' => 'select',
                'data' => 'posts',
                'args' => array('post_type' => array('product'), 'posts_per_page' => -1),
                'multi' => true,
                'ajax'  => true,
                'title' => __('Always show these Variation Products', 'woocommerce-single-variations'), 
                'subtitle' => __('Which Variation should be excluded.', 'woocommerce-single-variations'),
            ),
        )
    ) );

    $framework::setSection( $opt_name, array(
        'title'      => __( 'SEO', 'woocommerce-single-variations' ),
        'desc' => __( 'SEO options.', 'woocommerce-single-variations' ), 
        'id'         => 'seo',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'seoCanonical',
                'type'     => 'switch',
                'title'    => __( 'Variation Canonicals', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Each variation product gets an own canonical.', 'woocommerce-single-variations' ),
                'default'  => 0
            ),
            array(
                'id'       => 'seoSitemap',
                'type'     => 'switch',
                'title'    => __( 'Variation Sitemap', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Add product_variation post type to your SEO sitemap.', 'woocommerce-single-variations' ),
                'default'  => 0
            ),
            array(
                'id'       => 'seoMetaTitle',
                'type'     => 'switch',
                'title'    => __( 'Change Meta Title', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Changes the meta title according to your variation title.', 'woocommerce-single-variations' ),
                'default'  => 0
            ),
            array(
                'id'       => 'seoMetaDesc',
                'type'     => 'switch',
                'title'    => __( 'Change Meta Description', 'woocommerce-single-variations' ),
                'subtitle' => __( 'Changes the meta title according to your variation description if set. Otherwise it still returns parent variable meta description.', 'woocommerce-single-variations' ),
                'default'  => 0
            ),
        )
    ) );


    /*
     * <--- END SECTIONS
     */
