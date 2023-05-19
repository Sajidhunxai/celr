<?php

namespace WeDevs\DokanPro;

/**
 * Dokan Store Social Share class
 *
 * Integrates Social sharing buttons inside store page
 * Settings for single stores
 *
 * @since 2.6.6
 */

class StoreShare {

    private $share_text;
    /**
     * Loads automatically when class initiate
     *
     * @uses actions hook
     * @uses filter hook
     */
    public function __construct() {
        $this->init_hooks();
        $this->share_text = apply_filters( 'dokan_share_text', __( 'Share', 'dokan' ) );
    }

    /**
     * Init hooks and filters
     *
     * @return void
     */
    public function init_hooks() {
        //register scripts
        add_action( 'init', array( $this, 'register_scripts' ), 30 );
        //render
        add_action( 'dokan_enqueue_scripts', array( $this, 'enqueue_scripts' ), 30 );
        add_action( 'dokan_after_store_tabs', array( $this, 'render_share_button' ), 1 );
        add_action( 'wp_footer', array( $this, 'render_script' ), 30 );
    }

    /**
    * Register all scripts
    *
    * @return void
    **/
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        // register styles
        wp_register_style( 'dokan-social-style', DOKAN_PRO_PLUGIN_ASSEST . '/vendor/jssocials/jssocials.css', [], $version, 'all' );
        wp_register_style( 'dokan-social-theme-minimal', DOKAN_PRO_PLUGIN_ASSEST . '/vendor/jssocials/jssocials-theme-minima.css', [], $version, 'all' );
        wp_register_style( 'dokan-social-theme-flat', DOKAN_PRO_PLUGIN_ASSEST . '/vendor/jssocials/jssocials-theme-flat.css', [], $version, 'all' );

        // register scripts
        wp_register_script( 'dokan-social-script', DOKAN_PRO_PLUGIN_ASSEST . '/vendor/jssocials/jssocials.min.js', array( 'jquery', 'dokan-script' ), $version, true );
    }

    /**
    * Enqueue all scripts
    *
    * @return void
    **/
    public function enqueue_scripts() {
        if ( dokan_is_store_page() ) {
            wp_enqueue_script( 'dokan-social-script' );
            wp_enqueue_style( 'dokan-social-style' );
            wp_enqueue_style( 'dokan-magnific-popup' );
            wp_enqueue_script( 'dokan-magnific-popup' );
            wp_enqueue_style( 'dokan-social-theme-minimal' );
        }

        if ( ( is_account_page() && ! is_user_logged_in() ) || is_checkout() ) {
            wp_enqueue_style( 'dokan-social-style' );
            wp_enqueue_style( 'dokan-social-theme-flat' );
        }
    }

    /**
     * Render Share Buttons HTML
     *
     * @return string
     */
    public function render_html() {
        ob_start();
        ?>
        <div class="dokan-share-wrap">
            <?php echo $this->share_text; ?>
            <div class="dokan-share">

            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Share pop up button
     *
     * @return void
     */
    public function render_share_button() {
        ?>
        <li class="dokan-share-btn-wrap dokan-right">
            <button class="dokan-share-btn dokan-btn dokan-btn-theme dokan-btn-sm"><?php echo esc_html( $this->share_text ); ?>  <i class="fas fa-external-link-alt"></i></button>
        </li>
        <?php
    }

    /**
     * Render JS
     *
     * @return void
     */
    public function render_script() {
        if ( ! dokan_is_store_page() ) {
            return;
        }
        ?>
        <script>
            (function($){

                var Dokan_share = {

                    init : function(){
                        this.init_share();
                        $('.dokan-share-btn').on( 'click', this.showPopup );
                    },

                    init_share : function(){
                        $(".dokan-share").jsSocials({
                        showCount: false,
                        showLabel: false,
                           shares: ["facebook", "twitter", "linkedin", "pinterest", "email"]
                        });
                    },

                    showPopup : function(){
                        var content = <?php echo wp_json_encode( $this->render_html() ); ?>;
                        $.magnificPopup.open({
                            items: {
                                src: '<div class="white-popup dokan-share-popup-wrapper"><div id="ds-error-msg" ></div>'+ content +'</div>',
                                type: 'inline'
                           }
                        });

                        Dokan_share.init_share();
                    }
                }
                $(function() {
                    Dokan_share.init();
                });
            })(jQuery);
        </script>
        <?php
    }

}
