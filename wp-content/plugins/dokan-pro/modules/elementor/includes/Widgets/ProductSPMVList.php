<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Base;

class ProductSPMVList extends Widget_Base {

    /**
     * Widget name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-product-spmv-list';
    }

    /**
     * Widget title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Dokan Single Product MultiVendor List', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-kit-details';
    }

    /**
     * Widget categories
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_categories() {
        return [ 'woocommerce-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'product', 'vendor', 'spmv-list' ];
    }

    /**
     * Register HTML widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.3.0
     * @access protected
     */
    protected function register_controls() {
        parent::register_controls();

        $this->start_controls_section(
            'section_title',
            [
                'label' => __( 'Single Product MultiVendor List', 'dokan' ),
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => __( 'Title', 'dokan' ),
                'default'     => __( 'Other Available Vendor', 'dokan' ),
                'placeholder' => __( 'Other Available Vendor', 'dokan' ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend render method
     *
     * @since 3.3.0
     *
     * @return void
     */
    protected function render() {
        if ( ! dokan_pro()->module->is_active( 'spmv' ) ) {
            return;
        }

        if ( ! is_singular( 'product' ) ) {
            return;
        }

        $enable_option = dokan_get_option( 'enable_pricing', 'dokan_spmv', 'off' );

        if ( 'off' === $enable_option && ! dokan_elementor()->is_edit_or_preview_mode() ) {
            return;
        }

        global $product;

        if ( ! $product ) {
            return;
        }

        $lists = $this->get_other_reseller_vendors( $product->get_id() );

        if ( ! $lists && ! dokan_elementor()->is_edit_or_preview_mode() ) {
            return;
        }

        $get_title_txt = $this->get_settings( 'text' );

        if ( $lists ) {
            ?>
            <div class="dokan-other-vendor-camparison">

                <h3>
                    <?php echo $get_title_txt; ?>
                </h3>

                <div class="table dokan-table dokan-other-vendor-camparison-table">

                    <?php foreach ( $lists as $key => $list ) : ?>
                        <?php
                            $product_obj    = wc_get_product( $list->product_id );
                            $post_author_id = get_post_field( 'post_author', $product_obj->get_id() );
                            $seller_info    = dokan_get_store_info( $post_author_id );
                            $rating_count   = $product_obj->get_rating_count();
                            $review_count   = $product_obj->get_review_count();
                            $average        = $product_obj->get_average_rating();

                        if ( ! $product_obj->is_visible() ) {
                            continue;
                        }
                        ?>

                        <div class="table-row <?php echo ( (int) $list->product_id === (int) $product->get_id() ) ? 'active' : ''; ?>">
                            <div class="table-cell vendor">
                                <?php echo get_avatar( $post_author_id, 52 ); ?>
                                <a href="<?php echo dokan_get_store_url( $post_author_id ); ?>"><?php echo $seller_info['store_name']; ?></a>
                            </div>
                            <div class="table-cell price">
                                <span class="cell-title"><?php esc_html_e( 'Price', 'dokan' ); ?></span>
                                <?php echo $product_obj->get_price_html(); ?>
                            </div>
                            <div class="table-cell rating">
                                <span class="cell-title"><?php esc_html_e( 'Rating', 'dokan' ); ?></span>
                                <div class="woocommerce-product-rating">
                                    <?php echo wc_get_rating_html( $average, $rating_count ); ?>
                                    <?php
                                    if ( comments_open() ) :
                                        ?>
                                        <a href="#reviews" class="woocommerce-review-link" rel="nofollow">
                                            (
                                            <?php
                                                // translators: %s : Review count, %s: Review count
                                                printf( _n( '%s customer review', '%s customer reviews', $review_count, 'dokan' ), '<span class="count">' . esc_html( $review_count ) . '</span>' );
                                            ?>
                                            )
                                        </a>
                                    <?php endif ?>
                                </div>
                            </div>
                            <div class="table-cell action-area">
                                <a href="<?php echo dokan_get_store_url( $post_author_id ); ?>" class="dokan-btn tips link" title="<?php esc_html_e( 'View Store', 'dokan' ); ?>">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="<?php echo $product_obj->get_permalink(); ?>" class="dokan-btn tips view" title="<?php esc_html_e( 'View Product', 'dokan' ); ?>">
                                    <i class="far fa-eye" aria-hidden="true"></i>
                                </a>
                                <?php if ( 'simple' === $product_obj->get_type() ) : ?>
                                    <?php
                                    echo sprintf(
                                        '<a href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s" title="%s">%s</a>',
                                        esc_url( $product_obj->add_to_cart_url() ),
                                        1,
                                        esc_attr( $product_obj->get_id() ),
                                        esc_attr( $product_obj->get_sku() ),
                                        'dokan-btn tips cart',
                                        __( 'Add to cart', 'dokan' ),
                                        '<i class="fas fa-shopping-cart"></i>'
                                    );
                                    ?>
                                <?php elseif ( 'variable' === $product_obj->get_type() ) : ?>
                                    <a href="<?php echo $product_obj->get_permalink(); ?>" class="dokan-btn tips bars" title="<?php esc_html_e( 'Select Options', 'dokan' ); ?>"><i class="fas fa-bars"></i></a>
                                <?php endif ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <style>
                .dokan-other-vendor-camparison {
                    clear: both;
                    margin: 10px 0px 20px;
                }

                .dokan-other-vendor-camparison h3 {
                    margin-bottom: 15px;
                }

                .dokan-other-vendor-camparison-table {
                    margin:50px 0;
                }
                .table-row {
                    display: table;
                    background: white;
                    border-radius: 5px;
                    border: 1px solid #edf2f7;
                    padding: 20px;
                    width: 100%;
                    margin-bottom: 15px;
                    box-shadow: 1.21px 4.851px 27px 0px rgba(202, 210, 240, 0.2);
                }

                .table-row.active {
                    border: 1px solid #e3e3e3;
                }

                .table-cell {
                    display: table-cell;
                    vertical-align: middle;
                }
                .table-cell.vendor {
                    width: 45%;
                }
                .table-cell.price {
                    width: 15%;
                }
                .table-cell.rating {
                    width: 20%;
                }
                .table-cell.action-area {
                    width: 20%;
                    text-align: center;
                }

                .table-cell.vendor img{
                    display: inline-block;
                    vertical-align: middle;
                    border-radius: 3px;
                }
                .table-cell.vendor a{
                    display: inline-block;
                    vertical-align: middle;
                    text-decoration: none;
                    color: black;
                    font-size: 20px;
                    line-height: 1.2em;
                    margin-left: 15px;
                }
                .table-cell .woocommerce-product-rating{
                    margin-bottom:0 !important;
                }
                span.cell-title {
                    display: block;
                    font-size: 16px;
                    margin-bottom: 10px;
                    color: #82959b;
                }
                .table-cell .woocommerce-Price-amount{
                    color: #e74c3c;
                    font-size: 20px;
                    line-height: 1.2em;
                }

                .table-cell .dokan-btn {
                    padding: 5px 8px;
                    font-size: 16px;
                }
                .table-cell .dokan-btn.link {
                    color: #8e44ad;
                }
                .table-cell .dokan-btn.view {
                    color: #008fd5;
                }
                .table-cell .dokan-btn.cart {
                    color: #d35400;
                }
                .table-cell .dokan-btn:hover {
                    background-color: #f5f7fa;
                    color: inherit;
                }

                @media screen and (max-width: 767px){
                    .table-row {
                        display: block;
                        padding:0;
                        width: 100%;
                    }
                    .table-cell {
                        display: block;
                        width: 100% !important;
                        text-align: center;
                    }
                    .table-cell.vendor img{
                        display: block;
                        margin: 30px auto;
                    }
                    .table-cell.vendor a{
                        display: block;
                        margin: 0 20px;
                    }
                    .table-cell.price{
                        padding: 20px 0;
                    }
                    span.cell-title{
                        display: none;
                    }

                    .action-area{
                        border-top: 1px solid #e5edf0;
                        margin-top: 20px;
                        padding: 10px 0;
                    }
                }
            </style>

            <script>
                ;(function($) {
                    $(document).ready( function() {
                        $('.tips').tooltip();
                    })
                })(jQuery);
            </script>
            <?php
        }
    }

    /**
     * Get other reseller vendors
     *
     * @since 3.3.0
     *
     * @param integer $product_id
     *
     * @return bool
     */
    public function get_other_reseller_vendors( $product_id ) {
        global $wpdb;

        if ( ! $product_id ) {
            return false;
        }

        $has_multivendor = get_post_meta( $product_id, '_has_multi_vendor', true );

        if ( empty( $has_multivendor ) ) {
            return false;
        }

        $sql = $wpdb->prepare(
            "SELECT `product_id` FROM `{$wpdb->prefix}dokan_product_map` WHERE `map_id`= %d AND `product_id` != %d AND `is_trash` = 0",
            $has_multivendor,
            $product_id
        );
        $results = $wpdb->get_results( $sql );

        if ( $results ) {
            return $results;
        }

        return false;
    }
}
