<?php
/**
 * Dokan Edit Product SEO Content
 *
 * @since 2.9.0
 *
 * @package dokan
 */
?>
<?php do_action( 'dokan_product_seo_before', $post_id ); ?>

<div class="dokan-product-seo dokan-edit-row dokan-clearfix dokan-border-top">
    <div class="dokan-section-heading" data-togglehandler="dokan_product_seo">
        <h2><i class="fab fa-superpowers" aria-hidden="true"></i> <?php esc_html_e( 'SEO', 'dokan' ); ?></h2>
        <p><?php esc_html_e( 'Manage SEO for this product', 'dokan' ); ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-section-content">
        <div class="dokan-clearfix dokan-seo-container">
            <div class="dokan-seo-product-options">

                <div class="dokan-form-group">

                    <div class="dokan-form-group">
                        <i class="far fa-eye"></i> <?php esc_html_e( 'Snippet Preview', 'dokan' ); ?>
                    </div>

                    <div class="seo-snippet">
                        <div class="seo-title"></div>
                        <div class="seo-slug"><?php echo get_permalink( $post_id ); ?></div>
                        <div class="seo-meta"></div>
                    </div>

                </div>

                <div class="dokan-form-group">
                    <div class="dokan-btn snippet-btn"><i class="fas fa-pencil-alt"></i> <?php esc_html_e( 'Edit Snippet', 'dokan' ); ?></div>
                </div>
                <div class="dokan-seo-snippet-edit-wrap" style="display:none">
                    <div class="dokan-form-group" style="position: relative;">
                        <label class="dokan-control-label" for="_yoast_wpseo_title"><?php esc_html_e( 'SEO Title', 'dokan' ); ?></label>
                        <div class="wpseo-shortcode-wrap">
                            <select data-class="input.wpseo-title-input" class="wpseo_shortcode" id="wpseo_title_shortcode" name="wpseo_shortcode">
                                <option value=""><?php esc_html_e( 'Insert snippet variable', 'dokan' ); ?></option>
                                <option value="[title]"><?php esc_html_e( 'Title', 'dokan' ); ?></option>
                                <option value="[sep]"><?php esc_html_e( 'Seperator', 'dokan' ); ?></option>
                                <option value="[sitename]"><?php esc_html_e( 'Site Title', 'dokan' ); ?></option>
                            </select>
                        </div>
                        <?php echo get_bloginfo(); ?>
                        <input type="text" name="_yoast_wpseo_title" data-class=".seo-title" data-sitename="<?php echo esc_attr( get_bloginfo() ); ?>" data-sep="<?php echo esc_attr( $title_sep ); ?>" data-title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" id="wpseo_title" value="<?php echo esc_attr( $seo_title ); ?>" class="dokan-form-control wpseo-title-input" placeholder="<?php esc_attr_e( 'SEO Title', 'dokan' ); ?>">
                    </div>
                    <div class="dokan-form-group" style="position: relative;">
                        <label class="dokan-control-label" for="slug"><?php esc_html_e( 'Slug', 'dokan' ); ?></label>
                        <input type="text" name="slug" class="dokan-form-control" value="<?php echo esc_attr( $post->post_name ); ?>" disabled>
                    </div>
                    <div class="dokan-form-group" style="position: relative;">
                        <label class="dokan-control-label" for="_yoast_wpseo_metadesc"><?php esc_html_e( 'Meta description', 'dokan' ); ?></label>
                        <div class="wpseo-shortcode-wrap">
                            <select data-class="textarea.wpseo-meta-input" class="wpseo_shortcode" id="wpseo_meta_shortcode" name="wpseo_shortcode">
                                <option value=""><?php esc_html_e( 'Insert snippet variable', 'dokan' ); ?></option>
                                <option value="[title]"><?php esc_html_e( 'Title', 'dokan' ); ?></option>
                                <option value="[sep]"><?php esc_html_e( 'Seperator', 'dokan' ); ?></option>
                                <option value="[sitename]"><?php esc_html_e( 'Site Title', 'dokan' ); ?></option>
                            </select>
                        </div>
                        <textarea name="_yoast_wpseo_metadesc"  data-class=".seo-meta" data-sitename="<?php echo esc_attr( get_bloginfo() ); ?>" data-sep="<?php echo esc_attr( $title_sep ); ?>" data-title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" id="wpseo_meta" rows="4" class="dokan-form-control wpseo-meta-input" placeholder="<?php esc_attr_e( 'Meta description', 'dokan' ); ?>"><?php echo $seo_metadesc; ?></textarea>
                    </div>
                    <div class="dokan-clearfix"></div>
                </div>
            </div>

            <div class="show_if_needs_shipping dokan-form-group">
                <label class="control-label" for="_yoast_wpseo_focuskw"><?php esc_html_e( 'Focus keyword', 'dokan' ); ?></label>
                <?php
                dokan_post_input_box(
                    $post_id,
                    '_yoast_wpseo_focuskw',
                    [
                        'class'       => 'dokan-form-control',
                        'placeholder' => __( 'Focus keyword', 'dokan' ),
                    ],
                    'text'
                );
                ?>
                <div class="dokan-clearfix"></div>
            </div>
        </div>
    </div><!-- .dokan-side-right -->
</div><!-- .dokan-product-inventory -->

<?php do_action( 'dokan_product_edit_after_seo', $post_id ); ?>
