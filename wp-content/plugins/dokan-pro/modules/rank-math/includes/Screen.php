<?php

namespace WeDevs\DokanPro\Modules\RankMath;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Traits\Meta;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Locale;
use MyThemeShop\Helpers\Url;
use RankMath\Redirections\DB;
use RankMath\Redirections\Cache;
use RankMath\Admin\Admin_Helper;
use RankMath\Admin\Metabox\IScreen;

defined( 'ABSPATH' ) || exit;

/**
 * The base screen class
 *
 * @since 3.4.0
 */
class Screen implements IScreen {

    use Meta;
    use Hooker;

    /**
     * Current screen object.
     *
     * @var IScreen
     */
    private $screen = null;

    /**
     * Class constructor
     *
     * @since 3.4.0
     */
    public function __construct() {
        $this->load_screen();
    }

    /**
     * Is creen loaded.
     *
     * @since 3.4.0
     *
     * @return bool
     */
    public function is_loaded() {
        return ! is_null( $this->screen );
    }

    /**
     * Get object id
     *
     * @since 3.4.0
     *
     * @return int
     */
    public function get_object_id() {
        return $this->screen->get_object_id();
    }

    /**
     * Get object type
     *
     * @since 3.4.0
     *
     * @return string
     */
    public function get_object_type() {
        return $this->screen->get_object_type();
    }

    /**
     * Get object types to register metabox to
     *
     * @since 3.4.0
     *
     * @return array
     */
    public function get_object_types() {
        return $this->screen->get_object_types();
    }

    /**
     * Enqueue Styles and Scripts required for screen.
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function enqueue() {
        $this->screen->enqueue();
    }

    /**
     * Get analysis to run.
     *
     * @since 3.4.0
     *
     * @return array
     */
    public function get_analysis() {
        $analyses = $this->do_filter(
            'researches/tests',
            $this->screen->get_analysis(),
            $this->screen->get_object_type()
        );

        return array_keys( $analyses );
    }

    /**
     * Get values for localize.
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function localize() {
        $values = $this->get_values();

        if ( empty( $values ) ) {
            return;
        }

        foreach ( $values as $key => $value ) {
            Helper::add_json( $key, $value );
        }
    }

    /**
     * Get common values
     *
     * @since 3.4.0
     *
     * @return array
     */
    public function get_values() {
        $values = array_merge_recursive(
            $this->screen->get_values(),
            array(
                'homeUrl'            => home_url(),
                'objectID'           => $this->get_object_id(),
                'objectType'         => $this->get_object_type(),
                'locale'             => Locale::get_site_language(),
                'localeFull'         => get_locale(),
                'overlayImages'      => Helper::choices_overlay_images(),
                'defautOgImage'      => Helper::get_settings( 'titles.open_graph_image', rank_math()->plugin_url() . 'assets/admin/img/social-placeholder.jpg' ),
                'customPermalinks'   => (bool) get_option( 'permalink_structure', false ),
                'isUserRegistered'   => Helper::is_site_connected(),
                'connectSiteUrl'     => Admin_Helper::get_activate_url( Url::get_current_url() ),
                'maxTags'            => $this->do_filter( 'focus_keyword/maxtags', 5 ),
                'trendsIcon'         => Admin_Helper::get_trends_icon_svg(),
                'showScore'          => Helper::is_score_enabled(),
                'siteFavIcon'        => $this->get_site_icon(),
                'canUser'            => array(
                    'general'    => current_user_can( 'dokan_edit_product' ),
                    'advanced'   => current_user_can( 'dokan_edit_product' ) && Helper::is_advanced_mode(),
                    'snippet'    => current_user_can( 'dokan_edit_product' ),
                    'social'     => current_user_can( 'dokan_edit_product' ),
                    'analysis'   => current_user_can( 'dokan_edit_product' ),
                    'analytics'  => current_user_can( 'dokan_edit_product' ),
                    'content_ai' => current_user_can( 'dokan_edit_product' ),
                ),
                'assessor'           => array(
                    'serpData'              => $this->get_object_values(),
                    'powerWords'            => $this->power_words(),
                    'diacritics'            => $this->diacritics(),
                    'sentimentKbLink'       => KB::get( 'sentiments' ),
                    'hundredScoreLink'      => KB::get( 'score-100-ge' ),
                    'futureSeo'             => KB::get( 'pro-general-g' ),
                    'researchesTests'       => $this->get_analysis(),
                    'hasRedirection'        => Helper::is_module_active( 'redirections' ),
                    'hasBreadcrumb'         => Helper::is_breadcrumbs_enabled(),
                    'redirection'           => $this->get_redirection_data(),
                    'autoCreateRedirection' => Helper::get_settings( 'general.redirections_post_redirect' ),
                ),
                'isPro'              => defined( 'RANK_MATH_PRO_FILE' ),
                'is_front_page'      => Admin_Helper::is_home_page(),
                'trendsUpgradeLink'  => esc_url_raw( ' https://rankmath.com/pricing/?utm_source=Dokan-Multivendor-Plugin&utm_medium=CE%20General%20Tab%20Trends&utm_campaign=WP' ),
                'trendsPreviewImage' => esc_url( rank_math()->plugin_url() . 'assets/admin/img/trends-preview.jpg' ),
                'version'            => rank_math()->version,
                'ajaxurl'            => admin_url( 'admin-ajax.php' ),
                'adminurl'           => admin_url( 'admin.php' ),
                'endpoint'           => esc_url_raw( rest_url( 'rankmath/v1' ) ),
                'security'           => wp_create_nonce( 'rank-math-ajax-nonce' ),
                'restNonce'          => ( wp_installing() && ! is_multisite() ) ? '' : wp_create_nonce( 'wp_rest' ),
                'modules'            => \RankMath\Helper::get_active_modules(),
            )
        );

        $values = $this->do_filter( 'metabox/values', $values, $this );

        return $this->do_filter( 'metabox/' . $this->get_object_type() . '/values', $values, $this );
    }

    /**
     * Retrieves redirection data
     *
     * @since 3.4.3
     *
     * @return array
     */
    private function get_redirection_data() {
        $redirection = array(
            'id'          => '',
            'url_to'      => '',
            'header_code' => Helper::get_settings( 'general.redirections_header_code' ),
        );

        if ( ! Helper::is_module_active( 'redirections' ) ) {
            return $redirection;
        }

        $redirect_obj = Cache::get_by_object_id( $this->get_object_id(), $this->get_object_type() );
        if ( $redirect_obj ) {
            $redirection = DB::get_redirection_by_id( $redirect_obj->redirection_id, 'active' );
        }

        return $redirection;
    }

    /**
     * Get object values for localize
     *
     * @since 3.4.0
     *
     * @return array
     */
    public function get_object_values() {
        $keys = $this->do_filter(
            'metabox/' . $this->get_object_type() . '/meta_keys',
            array(
                'title'                    => 'title',
                'description'              => 'description',
                'focusKeywords'            => 'focus_keyword',
                'pillarContent'            => 'pillar_content',
                'canonicalUrl'             => 'canonical_url',
                'breadcrumbTitle'          => 'breadcrumb_title',
                'advancedRobots'           => 'advanced_robots',

                // Facebook.
                'facebookTitle'            => 'facebook_title',
                'facebookDescription'      => 'facebook_description',
                'facebookImage'            => 'facebook_image',
                'facebookImageID'          => 'facebook_image_id',
                'facebookHasOverlay'       => 'facebook_enable_image_overlay',
                'facebookImageOverlay'     => 'facebook_image_overlay',
                'facebookAuthor'           => 'facebook_author',

                // Twitter.
                'twitterCardType'          => 'twitter_card_type',
                'twitterUseFacebook'       => 'twitter_use_facebook',
                'twitterTitle'             => 'twitter_title',
                'twitterDescription'       => 'twitter_description',
                'twitterImage'             => 'twitter_image',
                'twitterImageID'           => 'twitter_image_id',
                'twitterHasOverlay'        => 'twitter_enable_image_overlay',
                'twitterImageOverlay'      => 'twitter_image_overlay',

                // Player.
                'twitterPlayerUrl'         => 'twitter_player_url',
                'twitterPlayerSize'        => 'twitter_player_size',
                'twitterPlayerStream'      => 'twitter_player_stream',
                'twitterPlayerStreamCtype' => 'twitter_player_stream_ctype',

                // App.
                'twitterAppDescription'    => 'twitter_app_description',
                'twitterAppIphoneName'     => 'twitter_app_iphone_name',
                'twitterAppIphoneID'       => 'twitter_app_iphone_id',
                'twitterAppIphoneUrl'      => 'twitter_app_iphone_url',
                'twitterAppIpadName'       => 'twitter_app_ipad_name',
                'twitterAppIpadID'         => 'twitter_app_ipad_id',
                'twitterAppIpadUrl'        => 'twitter_app_ipad_url',
                'twitterAppGoogleplayName' => 'twitter_app_googleplay_name',
                'twitterAppGoogleplayID'   => 'twitter_app_googleplay_id',
                'twitterAppGoogleplayUrl'  => 'twitter_app_googleplay_url',
                'twitterAppCountry'        => 'twitter_app_country',
            )
        );

        // Generate data.
        $data             = array();
        $object_id        = $this->get_object_id();
        $object_type      = $this->get_object_type();

        foreach ( $keys as $id => $key ) {
            $data[ $id ] = $this->get_meta( $object_type, $object_id, 'rank_math_' . $key );
        }

        $twitter_username           = Helper::get_settings( 'titles.twitter_author_names' );
        $data['twitterAuthor']      = $twitter_username ? $twitter_username : esc_html__( 'username', 'dokan' );
        $data['twitterUseFacebook'] = ! empty( $data['twitterUseFacebook'] ) && 'off' !== $data['twitterUseFacebook'];
        $data['twitterHasOverlay']  = ! empty( $data['twitterHasOverlay'] ) && 'off' !== $data['twitterHasOverlay'];
        $data['facebookHasOverlay'] = ! empty( $data['facebookHasOverlay'] ) && 'off' !== $data['facebookHasOverlay'];
        $data['robots']             = $this->normalize_robots( $this->get_meta( $object_type, $object_id, 'rank_math_robots' ) );
        $data['advancedRobots']     = $this->normalize_advanced_robots( $this->get_meta( $object_type, $object_id, 'rank_math_advanced_robots' ) );
        $data['pillarContent']      = 'on' === $data['pillarContent'];

        return wp_parse_args( $this->screen->get_object_values(), $data );
    }

    /**
     * Get site fav icon
     *
     * @since 3.4.0
     *
     * @return string
     */
    private function get_site_icon() {
        $favicon = get_site_icon_url( 16 );

        return ! empty( $favicon ) ? $favicon : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABs0lEQVR4AWL4//8/RRjO8Iucx+noO0MWUDo16FYABMGP6ZfUcRnWtm27jVPbtm3bttuH2t3eFPcY9pLz7NxiLjCyVd87pKnHyqXyxtCs8APd0rnyxiu4qSeA3QEDrAwBDrT1s1Rc/OrjLZwqVmOSu6+Lamcpp2KKMA9PH1BYXMe1mUP5qotvXTywsOEEYHXxrY+3cqk6TMkYpNr2FeoY3KIr0RPtn9wQ2unlA+GMkRw6+9TFw4YTwDUzx/JVvARj9KaedXRO8P5B1Du2S32smzqUrcKGEyA+uAgQjKX7zf0boWHGfn71jIKj2689gxp7OAGShNcBUmLMPVjZuiKcA2vuWHHDCQxMCz629kXAIU4ApY15QwggAFbfOP9DhgBJ+nWVJ1AZAfICAj1pAlY6hCADZnveQf7bQIwzVONGJonhLIlS9gr5mFg44Xd+4S3XHoGNPdJl1INIwKyEgHckEhgTe1bGiFY9GSFBYUwLh1IkiJUbY407E7syBSFxKTszEoiE/YdrgCEayDmtaJwCI9uu8TKMuZSVfSa4BpGgzvomBR/INhLGzrqDotp01ZR8pn/1L0JN9d9XNyx0AAAAAElFTkSuQmCC';
    }

    /**
     * Normalize robots.
     *
     * @since 3.4.0
     *
     * @param array $robots Array to normalize.
     *
     * @return array
     */
    private function normalize_robots( $robots ) {
        if ( ! is_array( $robots ) || empty( $robots ) ) {
            $robots = Helper::get_robots_defaults();
        }

        return array_fill_keys( $robots, true );
    }

    /**
     * Normalize advanced robots
     *
     * @since 3.4.0
     *
     * @param array $advanced_robots Array to normalize.
     *
     * @return array
     */
    private function normalize_advanced_robots( $advanced_robots ) {
        if ( ! empty( $advanced_robots ) ) {
            return $advanced_robots;
        }

        return Helper::get_advanced_robots_defaults();
    }

    /**
     * Return power words.
     *
     * @since 3.4.0
     *
     * @return array
     */
    private function power_words() {
        static $words;

        $locale = Locale::get_site_language();
        $file   = rank_math()->plugin_dir() . 'assets/vendor/powerwords/' . $locale . '.php';

        if ( ! file_exists( $file ) ) {
            return false;
        }

        $words = $words ? $words : include $file;

        return $this->do_filter( 'metabox/power_words', array_map( 'strtolower', $words ), $locale );
    }

    /**
     * Get diacritics (accents)
     *
     * @since 3.4.0
     *
     * @return array
     */
    private function diacritics() {
        $locale = Locale::get_site_language();
        $locale = in_array( $locale, array( 'en', 'de' ), true ) ? $locale : 'en';
        $file   = rank_math()->plugin_dir() . 'assets/vendor/diacritics/' . $locale . '.php';

        if ( ! file_exists( $file ) ) {
            return false;
        }

        $diacritics = include_once $file;

        return $this->do_filter( 'metabox/diacritics', $diacritics, $locale );
    }

    /**
     * Load required screen
     *
     * @since 3.4.0
     *
     * @param string $manual To load any screen manually.
     */
    public function load_screen( $manual = '' ) {
        $this->screen = new PostScreen();
    }
}
