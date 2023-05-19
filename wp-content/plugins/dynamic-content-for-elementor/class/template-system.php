<?php

namespace DynamicContentForElementor;

use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
}
class TemplateSystem
{
    public static $instance;
    public static $template_id;
    public static $options = [];
    private $types_registered = [];
    private $taxonomyes_registered = [];
    public static $supported_types = ['elementor_library', 'oceanwp_library', 'ae_global_templates'];
    public static $excluded_cpts = [
        // JET
        'jet-engine',
        'jet-menu',
        'jet-popup',
        'jet-smart-filters',
    ];
    public static $excluded_taxonomies = [
        // CORE
        'nav_menu',
        'link_category',
        'post_format',
        // ELEMENTOR
        'elementor_library_type',
        'elementor_library_category',
        'elementor_font_type',
        // YOAST
        'yst_prominent_words',
        // WOOCOMMERCE
        'product_shipping_class',
        'product_visibility',
        'action-group',
        'pa_*',
        // LOCO
        'translation_priority',
        // FLAMINGO
        'flamingo_contact_tag',
        'flamingo_inbound_channel',
    ];
    public static $template_styles = [];
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('elementor/init', [$this, 'template_shortcode']);
        add_action('elementor/init', [$this, 'css_fix_loop']);
        self::$options = get_option(DCE_TEMPLATE_SYSTEM_OPTION, []);
        if ($this->is_active()) {
            // Add Template Columns on Terms and Posts
            add_action('init', [$this, 'add_template_system_columns']);
            if (!is_admin()) {
                self::add_content_filter();
            }
            // Use the hook inside template > archive.php
            add_action('dce_before_content_inner', array($this, 'add_template_before_content'));
            add_action('dce_after_content_inner', array($this, 'add_template_after_content'));
            // Manage the layout with add_filter
            $this->manage_filters_layout();
            if (!is_admin()) {
                add_action('pre_get_posts', array($this, 'enfold_customization_author_archives'));
            }
            if (current_user_can('manage_options')) {
                new \DynamicContentForElementor\Metabox();
            }
        }
        self::$instance = $this;
    }
    /**
     * Check if Template System is active
     *
     * @return bool
     */
    public function is_active()
    {
        return 'active' === get_option('dce_template');
    }
    /**
     * If Template System is active, call the filter for the_content to show the template
     *
     * @return void
     */
    public function add_content_filter()
    {
        if ($this->is_active()) {
            add_filter('the_content', array($this, 'remove_elementor_content_filter_priority'), 1);
            add_filter('the_content', array($this, 'filter_the_content_in_the_main_loop'), 999999);
        }
    }
    /**
     * Remove the filter for the_content
     *
     * @return void
     */
    public function remove_content_filter()
    {
        remove_filter('the_content', array($this, 'filter_the_content_in_the_main_loop'), 999999);
    }
    /**
     * Fix for CSS in a loop
     *
     * @return void
     */
    public function css_fix_loop()
    {
        if (!\defined('DCE_DISABLE_CSS_FIX_LOOP')) {
            // Add a data attribute to permit to add an inline CSS in a loop
            // It shouldn't be added all the times but only in a loop. TODO
            add_action('elementor/frontend/widget/before_render', array($this, 'add_dce_background_data_attributes'));
            add_action('elementor/frontend/column/before_render', array($this, 'add_dce_background_data_attributes'));
            add_action('elementor/frontend/section/before_render', array($this, 'add_dce_background_data_attributes'));
            add_action('elementor/frontend/container/before_render', array($this, 'add_dce_background_data_attributes'));
            // CSS Fix for Background Images in a loop
            add_action('elementor/frontend/widget/after_render', array($this, 'fix_style'));
            add_action('elementor/frontend/column/after_render', array($this, 'fix_style'));
            add_action('elementor/frontend/section/after_render', array($this, 'fix_style'));
            add_action('elementor/frontend/container/after_render', array($this, 'fix_style'));
        }
    }
    /**
     * Add dce-elementor-template shortcode
     *
     * @return void
     */
    public function template_shortcode()
    {
        add_shortcode('dce-elementor-template', array($this, 'add_shortcode_template'));
    }
    /**
     * Add Template System Columns on terms and posts for users with 'manage_options' capabilities
     *
     * @return void
     */
    public function add_template_system_columns()
    {
        $args = ['public' => \true];
        if (!current_user_can('manage_options')) {
            return;
        }
        // Template Column for terms
        $taxonomies_registered = get_taxonomies($args, 'names', 'and');
        $taxonomies_registered = \array_diff($taxonomies_registered, self::$excluded_taxonomies);
        foreach ($taxonomies_registered as $taxonomy) {
            add_filter('manage_edit-' . $taxonomy . '_columns', [$this, 'taxonomy_columns_head']);
            add_filter('manage_' . $taxonomy . '_custom_column', [$this, 'taxonomy_columns_content'], 10, 3);
        }
        // Template Column for Posts/Pages
        $cpt_registered = self::get_registered_types();
        foreach ($cpt_registered as $cpt) {
            add_filter('manage_' . $cpt . '_posts_columns', [$this, 'columns_head']);
            add_action('manage_' . $cpt . '_posts_custom_column', [$this, 'columns_content'], 10, 2);
        }
    }
    /**
     * Add columns heading for Template System
     *
     * @param [type] $columns
     * @return array
     */
    public function columns_head($columns)
    {
        $columns['dce_template'] = __('Dynamic.ooo Template System', 'dynamic-content-for-elementor');
        return $columns;
    }
    public function columns_content($column_name, $post_ID)
    {
        if ('dce_template' !== $column_name) {
            return;
        }
        $template = get_post_meta($post_ID, 'dyncontel_elementor_templates', \true);
        if ($template) {
            if ($template != 1) {
                echo '<a href="' . get_permalink($template) . '" target="blank">' . wp_kses_post(get_the_title($template)) . '</a> - ';
                echo '<a href="' . admin_url('post.php?post=' . $template . '&action=edit') . '" target="blank">' . __('Edit', 'dynamic-content-for-elementor') . '</a>';
            } else {
                echo '<b>' . __('None', 'dynamic-content-for-elementor') . '</b>';
            }
        } else {
            echo '-';
        }
    }
    /**
     * Column heading for Template System on Taxonomies
     *
     * @param string[] $columns
     * @return string[]
     */
    public function taxonomy_columns_head($columns)
    {
        $columns['dce_template'] = DCE_PRODUCT_NAME . ' ' . __('Template', 'dynamic-content-for-elementor');
        return $columns;
    }
    public function taxonomy_columns_content($content, $column_name, $term_id = \false)
    {
        if ('dce_template' == $column_name && $term_id) {
            $head_term = get_term_meta($term_id, 'dynamic_content_head', \true);
            $block_term = get_term_meta($term_id, 'dynamic_content_block', \true);
            $single_term = get_term_meta($term_id, 'dynamic_content_single', \true);
            if ($head_term) {
                if ($head_term != 1) {
                    $content .= '<b>' . __('HEAD', 'dynamic-content-for-elementor') . '</b> <a href="' . get_permalink($head_term) . '" target="blank">' . wp_kses_post(get_the_title($head_term)) . '</a> - ';
                    $content .= '<a href="' . admin_url('post.php?post=' . $head_term . '&action=edit') . '" target="blank">' . __('Edit', 'dynamic-content-for-elementor') . '</a><br>';
                } else {
                    $content = '<b>' . __('None', 'dynamic-content-for-elementor') . '</b>';
                }
            }
            if ($block_term) {
                if ($head_term != 1) {
                    $content .= '<b>' . __('BLOCK', 'dynamic-content-for-elementor') . '</b> <a href="' . get_permalink($block_term) . '" target="blank">' . wp_kses_post(get_the_title($block_term)) . '</a> - ';
                    $content .= '<a href="' . admin_url('post.php?post=' . $block_term . '&action=edit') . '" target="blank">' . __('Edit', 'dynamic-content-for-elementor') . '</a><br>';
                } else {
                    $content = '<b>' . __('None', 'dynamic-content-for-elementor') . '</b>';
                }
            }
            if ($single_term) {
                if ($head_term != 1) {
                    $content .= '<b>' . __('SINGLE', 'dynamic-content-for-elementor') . '</b> <a href="' . get_permalink($single_term) . '" target="blank">' . wp_kses_post(get_the_title($single_term)) . '</a> - ';
                    $content .= '<a href="' . admin_url('post.php?post=' . $single_term . '&action=edit') . '" target="blank">' . __('Edit', 'dynamic-content-for-elementor') . '</a><br>';
                } else {
                    $content = '<b>' . __('None', 'dynamic-content-for-elementor') . '</b>';
                }
            }
            if (!$head_term && !$block_term && !$single_term) {
                $content = ' - ';
            }
        }
        return $content;
    }
    /**
     * Shortcode [dce-elementor-template] to show a template
     *
     * @param [type] $atts
     * @return void
     */
    public function add_shortcode_template($atts)
    {
        $atts = shortcode_atts(array('id' => '', 'post_id' => '', 'author_id' => '', 'user_id' => '', 'term_id' => '', 'ajax' => '', 'loading' => '', 'inlinecss' => \false), $atts, 'dce-elementor-template');
        $atts['id'] = \intval($atts['id']);
        $atts['post_id'] = \intval($atts['post_id']);
        $atts['author_id'] = \intval($atts['author_id']);
        $atts['user_id'] = \intval($atts['user_id']);
        $atts['term_id'] = \intval($atts['term_id']);
        if ($atts['id'] !== '') {
            global $wp_query;
            $original_queried_object = $wp_query->queried_object;
            $original_queried_object_id = $wp_query->queried_object_id;
            if (!empty($atts['post_id'])) {
                global $post;
                $original_post = $post;
                $post = get_post($atts['post_id']);
                if ($post) {
                    $wp_query->queried_object = $post;
                    $wp_query->queried_object_id = $atts['post_id'];
                }
            }
            if (!empty($atts['author_id'])) {
                global $authordata;
                $original_author = $authordata;
                $authordata = get_user_by('ID', $atts['author_id']);
                if ($authordata) {
                    $wp_query->queried_object = $authordata;
                    $wp_query->queried_object_id = $atts['author_id'];
                }
            }
            if (!empty($atts['user_id'])) {
                global $user;
                global $current_user;
                $original_user = $current_user;
                $current_user = get_user_by('ID', $atts['user_id']);
                $user = $current_user;
            }
            if (!empty($atts['term_id'])) {
                global $term;
                $term = get_term($atts['term_id']);
                if ($term) {
                    $wp_query->queried_object = $term;
                    $wp_query->queried_object_id = $atts['term_id'];
                }
            }
            $inlinecss = $atts['inlinecss'] == 'true';
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                $inlinecss = \true;
            }
            if (!empty($atts['ajax']) && WP_DEBUG) {
                if (empty(\DynamicContentForElementor\Elements::$elements)) {
                    add_action('elementor/frontend/widget/after_render', function ($widget = \false) {
                        $styles = $widget->get_style_depends();
                        if (!empty($styles)) {
                            foreach ($styles as $key => $style) {
                                if (wp_doing_ajax()) {
                                    self::$template_styles[] = $style;
                                } else {
                                    \DynamicContentForElementor\Assets::wp_print_styles($style);
                                }
                            }
                        }
                    });
                    if (wp_doing_ajax()) {
                        add_action('elementor/frontend/the_content', function ($content = \false) {
                            $styles = self::$template_styles;
                            $add_styles = '';
                            if (!empty($styles)) {
                                foreach ($styles as $key => $style) {
                                    $add_styles .= \DynamicContentForElementor\Assets::wp_print_styles($style, \false);
                                }
                            }
                            // add also current document file
                            return $content . $add_styles;
                        });
                    }
                }
            }
            $dce_default_template = $atts['id'];
            if (!empty($atts['loading']) && $atts['loading'] == 'lazy') {
                $attributes = wp_json_encode($atts);
                $attributes = wp_slash($attributes);
                $optionals = '';
                if (!empty($atts['post_id'])) {
                    $optionals .= ' data-post="' . $atts['post_id'] . '"';
                }
                if (!empty($atts['user_id'])) {
                    $optionals .= ' data-user="' . $atts['user_id'] . '"';
                }
                if (!empty($atts['term_id'])) {
                    $optionals .= ' data-term="' . $atts['term_id'] . '"';
                }
                if (!empty($atts['author_id'])) {
                    $optionals .= ' data-author="' . $atts['author_id'] . '"';
                }
                $template_page = '<div class="dce-elementor-template-placeholder" data-id="' . $atts['id'] . '"' . $optionals . '></div>';
            } else {
                $template_page = self::get_template($dce_default_template, $inlinecss);
            }
            if (!empty($atts['post_id'])) {
                $post = $original_post;
            }
            if (!empty($atts['author_id'])) {
                $authordata = $original_author;
            }
            if (!empty($atts['user_id'])) {
                $user = $original_user;
                $current_user = $original_user;
            }
            $wp_query->queried_object = $original_queried_object;
            $wp_query->queried_object_id = $original_queried_object_id;
            return $template_page;
        }
    }
    /**
     * Add filters to manage layout on single (full width, canvas) and archives (boxed, full width, canvas)
     *
     * @return void
     */
    private function manage_filters_layout()
    {
        add_filter('template_include', array($this, 'layout_static_templates'), 999999);
        add_filter('archive_template', array($this, 'layout_archive_templates'), 999999);
    }
    public function enfold_customization_author_archives($query)
    {
        if ($query->is_author && $query->post_type == 'post') {
            $query->set('post_type', 'any');
            $query->set('posts_per_page', -1);
        }
        remove_action('pre_get_posts', 'enfold_customization_author_archives');
    }
    public function add_template_before_content()
    {
        global $post;
        global $default_template;
        if ($post) {
            $cptype = $post->post_type;
        } else {
            // in caso di nessun post associato a questa taxonomy
            $taxObject = get_taxonomy(get_queried_object()->taxonomy);
            // leggo le proprietà della taxonomy e ricavo il primo type associato (sarebbe bello confrontare tutto l'array)
            $postTypeArray = $taxObject->object_type;
            $cptype = $postTypeArray[0];
        }
        $dce_default_template = '';
        $inlinecss = \false;
        $dce_elementor_templates = '';
        $template_page = '';
        if ($cptype != '') {
            // 4- Type
            if (!empty(self::$options['dyncontel_before_field_archive' . $cptype])) {
                $dce_default_template = self::$options['dyncontel_before_field_archive' . $cptype];
            }
            if (get_queried_object() instanceof \WP_Term) {
                $taxo = get_queried_object()->taxonomy;
                // 3 - Taxonomy
                if (isset(self::$options['dyncontel_before_field_archive_taxonomy_' . $taxo]) && self::$options['dyncontel_before_field_archive_taxonomy_' . $taxo] > 0) {
                    $dce_default_template = self::$options['dyncontel_before_field_archive_taxonomy_' . $taxo];
                }
                // 2 - Termine
                $termine_id = get_queried_object()->term_id;
                if (!is_post_type_archive()) {
                    $dce_default_template_term = get_term_meta($termine_id, 'dynamic_content_head', \true);
                    if (!empty($dce_default_template_term)) {
                        $dce_default_template = $dce_default_template_term;
                    }
                }
            }
            $default_template = $dce_default_template;
            if ($dce_default_template) {
                echo do_shortcode('[dce-elementor-template id="' . $dce_default_template . '"]');
            }
        }
    }
    public function add_template_after_content()
    {
        global $post;
        global $default_template;
        if ($post) {
            $cptype = $post->post_type;
        } else {
            // in caso di nessun post associato a questa taxonomy
            $taxObject = get_taxonomy(get_queried_object()->taxonomy);
            // leggo le proprietà della taxonomy e ricavo il primo type associato (sarebbe bello confrontare tutto l'array)
            $postTypeArray = $taxObject->object_type;
            $cptype = $postTypeArray[0];
        }
        $dce_default_template = '';
        $dce_elementor_templates = '';
        $template_page = '';
        if ($cptype != '') {
            // 4- Type
            if (!empty(self::$options['dyncontel_after_field_archive' . $cptype])) {
                $dce_default_template = self::$options['dyncontel_after_field_archive' . $cptype];
            }
            if (isset(get_queried_object()->taxonomy)) {
                $taxo = get_queried_object()->taxonomy;
                // 3 - Taxonomy
                if (!empty(self::$options['dyncontel_after_field_archive_taxonomy_' . $taxo])) {
                    $dce_default_template = self::$options['dyncontel_after_field_archive_taxonomy_' . $taxo];
                }
            }
            $default_template = $dce_default_template;
            if ($dce_default_template) {
                echo do_shortcode('[dce-elementor-template id="' . $dce_default_template . '"]');
            }
        }
    }
    /**
     * Layout for archives
     *
     * @param string $single_template
     * @return string
     */
    public function layout_archive_templates($single_template)
    {
        global $post;
        if (!is_author()) {
            // Retrieves all CPTs that can have a template
            $typesRegistered = self::get_registered_types();
            foreach ($typesRegistered as $chiave) {
                if (isset($post->post_type) && $post->post_type == $chiave && !empty(self::$options['dyncontel_field_archive' . $chiave]) && !empty(self::$options['dyncontel_field_archive' . $chiave . '_template']) && !is_404()) {
                    $single_template = DCE_PATH . 'template/archive.php';
                }
            }
        } elseif (is_author()) {
            if (!empty(self::$options['dyncontel_field_archiveuser_template']) && !empty(self::$options['dyncontel_field_archiveuser']) || !empty(self::$options['dyncontel_before_field_archiveuser']) || !empty(self::$options['dyncontel_after_field_archiveuser'])) {
                $single_template = DCE_PATH . 'template/user.php';
            }
        }
        return $single_template;
    }
    /**
     * Load from Elementor folder '/modules/page-templates/templates/' the layout between 'header-footer' (Full-Width in our settings) and 'canvas'
     *
     * @param string $my_template
     * @return string
     */
    public function layout_static_templates($my_template)
    {
        global $post;
        // Single post of any post type
        if (is_singular() && !get_page_template_slug()) {
            // Check if the post is part of a taxonomy that has a template associated with it
            $postTaxonomyes = Helper::get_post_terms($post->ID);
            if (!empty($postTaxonomyes)) {
                foreach ($postTaxonomyes as $tKey => $aTaxo) {
                    $aTaxName = $aTaxo->taxonomy;
                    if (!empty(self::$options['dyncontel_field_single_taxonomy_' . $aTaxName]) && 'publish' === get_post_status(self::$options['dyncontel_field_single_taxonomy_' . $aTaxName]) && !empty(self::$options['dyncontel_field_single_taxonomy_' . $aTaxName . '_blank'])) {
                        $_blank = self::$options['dyncontel_field_single_taxonomy_' . $aTaxName . '_blank'];
                        if ($_blank == 1 || $_blank == '1') {
                            $_blank = 'header-footer';
                        }
                        // retrocompatibility
                        $my_template = ELEMENTOR_PATH . '/modules/page-templates/templates/' . $_blank . '.php';
                        break;
                    }
                }
            }
            // Posts (not WooCommerce Products)
            $typesRegistered = self::get_registered_types();
            foreach ($typesRegistered as $chiave) {
                if (isset($post->post_type) && $post->post_type == $chiave && $chiave != 'product') {
                    if (!empty(self::$options['dyncontel_field_single' . $chiave]) && 'publish' === get_post_status(self::$options['dyncontel_field_single' . $chiave]) && !empty(self::$options['dyncontel_field_single' . $chiave . '_blank'])) {
                        $_blank = self::$options['dyncontel_field_single' . $chiave . '_blank'];
                        if ($_blank == 1 || $_blank == '1') {
                            $_blank = 'header-footer';
                        }
                        // retrocompatibility
                        $my_template = ELEMENTOR_PATH . 'modules/page-templates/templates/' . $_blank . '.php';
                        break;
                    }
                }
            }
        }
        $datopagina = get_post_meta(get_the_ID(), 'dyncontel_elementor_templates', \true);
        // PRODUCT Archive Taxonomy
        // 2 - verifico se una tassonomia associata ha il template
        $taxonomyesRegistered = get_taxonomies(array('public' => \true));
        $taxoRegistred = \array_diff($taxonomyesRegistered, self::$excluded_taxonomies);
        if (isset(get_queried_object()->taxonomy)) {
            $taxo = get_queried_object()->taxonomy;
            foreach ($taxoRegistred as $chiave) {
                if (isset($taxo) && $taxo == $chiave) {
                    if (!empty(self::$options['dyncontel_field_archive_taxonomy_' . $chiave]) && !empty(self::$options['dyncontel_field_archive_taxonomy_' . $chiave . '_template'])) {
                        if (!is_404()) {
                            $my_template = DCE_PATH . '/template/archive.php';
                        }
                    }
                }
            }
        }
        // WooCommerce
        if (Helper::is_woocommerce_active()) {
            // WooCommerce Product - Single Page
            if (\is_product()) {
                if (!empty(self::$options['dyncontel_field_singleproduct']) && 'publish' === get_post_status(self::$options['dyncontel_field_singleproduct']) && !empty(self::$options['dyncontel_field_singleproduct_blank'])) {
                    if (!get_page_template_slug()) {
                        $my_template = DCE_PATH . '/template/woocommerce.php';
                    }
                }
                if ($datopagina != 1 && !empty(self::$options['dyncontel_field_singleproduct'])) {
                    $my_template = DCE_PATH . '/template/woocommerce.php';
                }
            }
            // WooCommerce Archives
            if (is_product_category() || is_product_tag()) {
                if (!empty(self::$options['dyncontel_field_archiveproduct']) && 'publish' === get_post_status(self::$options['dyncontel_field_archiveproduct']) && !empty(self::$options['dyncontel_field_archiveproduct_blank'])) {
                    if (!get_page_template_slug()) {
                        if (!is_404()) {
                            $my_template = DCE_PATH . '/template/archive.php';
                        }
                    }
                }
                if ($datopagina != 1 && !empty(self::$options['dyncontel_field_archiveproduct'])) {
                    if (!is_404()) {
                        $my_template = DCE_PATH . '/template/archive.php';
                    }
                }
            }
        }
        // Attachment pages
        if (is_attachment() && !get_page_template_slug()) {
            if (!empty(self::$options['dyncontel_field_singleattachment']) && 'publish' === get_post_status(self::$options['dyncontel_field_singleattachment']) && !empty(self::$options['dyncontel_field_singleattachment_blank'])) {
                $_blank = self::$options['dyncontel_field_singleattachment_blank'];
                if ($_blank == 1 || $_blank == '1') {
                    $_blank = 'header-footer';
                }
                // retrocompatibility
                $my_template = ELEMENTOR_PATH . 'modules/page-templates/templates/' . $_blank . '.php';
            }
        }
        // Search Page
        if (is_search()) {
            if (!empty(self::$options['dyncontel_field_archivesearch_template']) && !empty(self::$options['dyncontel_field_archivesearch']) && 'publish' === get_post_status(self::$options['dyncontel_field_archivesearch'])) {
                $my_template = DCE_PATH . '/template/search.php';
            }
        }
        // Author Archive
        if (is_author()) {
            if (!empty(self::$options['dyncontel_field_archiveuser_template']) && !empty(self::$options['dyncontel_field_archiveuser']) && 'publish' === get_post_status(self::$options['dyncontel_field_archiveuser'])) {
                $single_template = DCE_PATH . '/template/user.php';
            }
        }
        // Homepage
        if (is_home() || \function_exists('is_shop') && \is_shop()) {
            // Le home's di Archivio
            if (!empty(self::$options['dyncontel_field_archive' . get_post_type()]) && 'publish' === get_post_status(self::$options['dyncontel_field_archive' . get_post_type()]) && !empty(self::$options['dyncontel_field_archive' . get_post_type() . '_template']) && !is_404()) {
                $my_template = DCE_PATH . '/template/archive.php';
            }
            // Check if it's a page and doesn't have a specific template on the theme folder
            if (is_page() && !get_page_template_slug()) {
                if (!empty(self::$options['dyncontel_field_singlepage']) && 'publish' === get_post_status(self::$options['dyncontel_field_singlepage']) && !empty(self::$options['dyncontel_field_singlepage_blank'])) {
                    $_blank = self::$options['dyncontel_field_singlepage_blank'];
                    if ($_blank == 1 || $_blank == '1') {
                        $_blank = 'header-footer';
                    }
                    $my_template = ELEMENTOR_PATH . 'modules/page-templates/templates/' . $_blank . '.php';
                }
            }
        }
        return $my_template;
    }
    /**
     * Remove content filter priority from Elementor
     *
     * @param string $content
     * @return string
     */
    public function remove_elementor_content_filter_priority($content)
    {
        if (!empty(\Elementor\Frontend::THE_CONTENT_FILTER_PRIORITY)) {
            if (self::get_template_id()) {
                \Elementor\Frontend::instance()->remove_content_filter();
                global $wp_filter;
                if (!empty($wp_filter['the_content']->callbacks[\Elementor\Frontend::THE_CONTENT_FILTER_PRIORITY])) {
                    foreach ($wp_filter['the_content']->callbacks[\Elementor\Frontend::THE_CONTENT_FILTER_PRIORITY] as $key => $value) {
                        if (\strpos($key, 'apply_builder_in_content') !== \false) {
                            unset($wp_filter['the_content']->callbacks[\Elementor\Frontend::THE_CONTENT_FILTER_PRIORITY][$key]);
                        }
                    }
                }
            }
        }
        return $content;
    }
    /**
     * Filter the Content in the Main Loop
     *
     * @param string $content
     * @return string
     */
    public function filter_the_content_in_the_main_loop($content)
    {
        // if current post has not its Elementor Template
        $dce_default_template = self::get_template_id();
        if ($dce_default_template) {
            $content = self::get_template($dce_default_template);
            // fix Elementor PRO Post Content Widget
            static $did_posts = [];
            $did_posts[get_the_ID()] = \true;
            add_filter('elementor/widget/render_content', array($this, 'fix_elementor_pro_post_content_widget'), 11, 2);
        }
        return $content;
    }
    public function fix_elementor_pro_post_content_widget($content, $widget = \false)
    {
        if ($widget && 'theme-post-content' === $widget->get_name()) {
            return get_the_content();
        }
        return $content;
    }
    public static function get_post_template_id()
    {
        if (is_singular()) {
            $queried_object = get_queried_object();
            if (!empty($queried_object) && 'WP_Post' === \get_class($queried_object)) {
                $post = get_post();
                if ($post === null) {
                    return \false;
                }
                $doc = \Elementor\Plugin::$instance->documents->get($post->ID);
                if ($doc && $doc->is_built_with_elementor()) {
                    return $post->ID;
                }
            }
        }
        return \false;
    }
    /**
     * Get Template ID
     *
     * @param boolean $head
     * @return int|void
     */
    public static function get_template_id($head = \false)
    {
        if (self::$template_id) {
            // Return if the template doesn't exists
            if ('publish' !== get_post_status(self::$template_id)) {
                return;
            }
            return self::$template_id;
        }
        $dce_template = 0;
        // Check if we're inside the main loop in a single post page.
        if (in_the_loop() && is_main_query() || $head) {
            global $post;
            $cptype = \false;
            if ($post) {
                $cptype = $post->post_type;
                $cptaxonomy = get_post_taxonomies($post->ID);
            }
            $dce_default_template = '';
            $template_page = '';
            // ciclo i termini e ne ricavo l'id del template
            $taxonomyesRegistered = get_taxonomies(array('public' => \true));
            if ($cptype && !\in_array($cptype, self::$supported_types)) {
                // SINGULAR
                if (is_singular()) {
                    $custom_template = \false;
                    // 1 - se nella pagina il metabox template è impostato diversamente da "default"
                    $datopagina = get_post_meta(get_the_ID(), 'dyncontel_elementor_templates', \true);
                    if ($datopagina) {
                        if ($datopagina > 1) {
                            $dce_default_template = $datopagina;
                            $dce_template = $dce_default_template;
                            $custom_template = \true;
                        } else {
                            $custom_template = \true;
                        }
                    }
                    // 2 - se esiste un template associato a un termine associato
                    if (!$custom_template) {
                        // leggo le taxonomy del post (I)
                        foreach ($cptaxonomy as $chiave) {
                            // leggo il temine del post (II)
                            $terms_list_of_post = wp_get_post_terms(get_the_ID(), $chiave, array('fields' => 'all'));
                            if (\count($terms_list_of_post) > 0) {
                                foreach ($terms_list_of_post as $term_single) {
                                    $dce_default_template = get_term_meta($term_single->term_id, 'dynamic_content_single', \true);
                                    if (!empty($dce_default_template)) {
                                        if ($dce_default_template > 1) {
                                            $dce_template = $dce_default_template;
                                            $custom_template = \true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // 3 - se esiste un template associato alla tassonomia collegata
                    if (!$custom_template) {
                        foreach ($cptaxonomy as $aTaxo) {
                            if (isset(self::$options['dyncontel_field_single_taxonomy_' . $aTaxo]) && is_taxonomy_hierarchical($aTaxo)) {
                                // leggo il temine del post (II)
                                $terms_list_of_post = wp_get_post_terms(get_the_ID(), $aTaxo, array('fields' => 'all'));
                                if (\count($terms_list_of_post) > 0) {
                                    $dce_default_template = self::$options['dyncontel_field_single_taxonomy_' . $aTaxo];
                                    if (!empty($dce_default_template)) {
                                        if ($dce_default_template > 1) {
                                            $dce_template = $dce_default_template;
                                            $custom_template = \true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // 4 - se esiste un template associato al post type
                    if (!$custom_template && isset(self::$options['dyncontel_field_single' . $cptype])) {
                        // altrimenti il dato è prelevato dai settings di "DynamicaContent"
                        $dce_default_template = self::$options['dyncontel_field_single' . $cptype];
                        if (!empty($dce_default_template)) {
                            if ($cptype != 'product' || !Helper::is_woocommerce_active()) {
                                if ($dce_default_template > 1) {
                                    $dce_template = $dce_default_template;
                                    $custom_template = \true;
                                }
                            }
                        }
                    }
                }
                // ------ ENTRY o archive Blocks --------------
                if (is_archive() || is_home()) {
                    if (!is_author()) {
                        // 4 - Type
                        if (!empty(self::$options['dyncontel_field_archive' . $cptype])) {
                            $dce_default_template = self::$options['dyncontel_field_archive' . $cptype];
                        }
                        if (!is_post_type_archive() && !is_home()) {
                            // qui sono nell'archivio del termine
                            // 3 - Taxonomy
                            foreach ($cptaxonomy as $chiave) {
                                // 3 - Taxonomy
                                if (isset(self::$options['dyncontel_field_archive_taxonomy_' . $chiave])) {
                                    $dce_default_template_taxo = self::$options['dyncontel_field_archive_taxonomy_' . $chiave];
                                    if (!empty($dce_default_template_taxo) && $dce_default_template_taxo > 0) {
                                        $dce_default_template = $dce_default_template_taxo;
                                    }
                                }
                            }
                            if (is_tax() || is_category() || is_tag()) {
                                $termine_id = get_queried_object()->term_id;
                                $chiave = get_queried_object()->taxonomy;
                                if (\in_array($chiave, $cptaxonomy)) {
                                    // 3 bis - Taxonomy Current
                                    if (isset(self::$options['dyncontel_field_archive_taxonomy_' . $chiave])) {
                                        $dce_default_template_taxo = self::$options['dyncontel_field_archive_taxonomy_' . $chiave];
                                        if (!empty($dce_default_template_taxo) && $dce_default_template_taxo > 0) {
                                            $dce_default_template = $dce_default_template_taxo;
                                        }
                                    }
                                }
                                // 2 - Termine
                                $dce_default_template_term = get_term_meta($termine_id, 'dynamic_content_block', \true);
                                if (!empty($dce_default_template_term) && $dce_default_template_term > 1) {
                                    $dce_default_template = $dce_default_template_term;
                                }
                            }
                        } else {
                            // qui sono nella home page dell'archivio
                            foreach ($cptaxonomy as $chiave) {
                                // 3 - Tayonomy
                                if (isset(self::$options['dyncontel_field_archive_taxonomy_' . $chiave])) {
                                    $dce_default_template_taxo = self::$options['dyncontel_field_archive_taxonomy_' . $chiave];
                                    if (!empty($dce_default_template_taxo) && $dce_default_template_taxo > 0) {
                                        $dce_default_template = $dce_default_template_taxo;
                                    }
                                }
                            }
                            foreach ($cptaxonomy as $chiave) {
                                // 2 - Termine
                                $terms_list_of_post = wp_get_post_terms(get_the_ID(), $chiave, array('fields' => 'all'));
                                if (\count($terms_list_of_post) > 0) {
                                    foreach ($terms_list_of_post as $term_single) {
                                        if ($term_single->taxonomy != 'post_format') {
                                            $dce_default_template_term = get_term_meta($term_single->term_id, 'dynamic_content_block', \true);
                                        }
                                        if (!empty($dce_default_template_term) && $dce_default_template_term > 1) {
                                            $dce_default_template = $dce_default_template_term;
                                        }
                                    }
                                }
                            }
                        }
                        // > conclusione
                        if (!empty($dce_default_template)) {
                            if ($dce_default_template > 1) {
                                $dce_template = $dce_default_template;
                            }
                        }
                    }
                }
                if (is_attachment() && isset(self::$options['dyncontel_field_singleattachment'])) {
                    $dce_default_template = self::$options['dyncontel_field_singleattachment'];
                    if (!empty($dce_default_template)) {
                        if ($dce_default_template > 1) {
                            $dce_template = $dce_default_template;
                        }
                    }
                }
                if (is_author() && isset(self::$options['dyncontel_field_archiveuser'])) {
                    $dce_default_template = self::$options['dyncontel_field_archiveuser'];
                    if (!empty($dce_default_template)) {
                        if ($dce_default_template > 1) {
                            $dce_template = $dce_default_template;
                        }
                    }
                }
                if (is_search() && isset(self::$options['dyncontel_field_archivesearch'])) {
                    $dce_default_template = self::$options['dyncontel_field_archivesearch'];
                    if (!empty($dce_default_template)) {
                        if ($dce_default_template > 1) {
                            $dce_template = $dce_default_template;
                        }
                    }
                }
            }
        }
        self::$template_id = $dce_template;
        return $dce_template;
    }
    public static function get_template($template_id, $inline_css = \false)
    {
        if (!$template_id) {
            return;
        }
        // Return if the template doesn't exists
        if ('publish' !== get_post_status($template_id)) {
            return;
        }
        // If WPML is active, retrieve the translation of the current template
        $template_id = apply_filters('wpml_object_id', $template_id, 'elementor_library', \true);
        $doc = \Elementor\Plugin::$instance->documents->get($template_id);
        if ($doc && $doc->is_built_with_elementor()) {
            $template_page = \Elementor\Plugin::instance()->frontend->get_builder_content($template_id, $inline_css);
            $template_page = self::css_class_fix($template_page, $template_id);
            return $template_page;
        } else {
            $post_n = get_post($template_id);
            $content_n = apply_filters('the_content', $post_n->post_content);
            echo $content_n;
            return;
        }
    }
    /**
     * Add Data Attributes to fix issue for templates in a loop
     *
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function add_dce_background_data_attributes(\Elementor\Element_Base $element)
    {
        // Background Color
        $background_color = $element->get_settings_for_display('background_color');
        if (!empty($background_color)) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-color', $background_color, \true);
        }
        $background_hover_color = $element->get_settings_for_display('background_hover_color');
        if (!empty($background_hover_color)) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-hover-color', $background_hover_color, \true);
        }
        $background_overlay_color = $element->get_settings_for_display('background_overlay_color');
        if (!empty($background_overlay_color)) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-overlay-color', $background_overlay_color, \true);
        }
        $background_overlay_hover_color = $element->get_settings_for_display('background_overlay_hover_color');
        if (!empty($background_overlay_hover_color)) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-overlay-hover-color', $background_overlay_hover_color, \true);
        }
        // Background Image URL
        $background_image = $element->get_settings_for_display('background_image');
        if (!empty($background_image['url'])) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-image-url', $background_image['url'], \true);
        }
        $background_hover_image = $element->get_settings_for_display('background_hover_image');
        if (!empty($background_hover_image['url'])) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-hover-image-url', $background_hover_image['url'], \true);
        }
        $background_overlay_image = $element->get_settings_for_display('background_overlay_image');
        if (!empty($background_overlay_image['url'])) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-overlay-image-url', $background_overlay_image['url'], \true);
        }
        $background_overlay_hover_image = $element->get_settings_for_display('background_overlay_hover_image');
        if (!empty($background_overlay_hover_image['url'])) {
            $element->add_render_attribute('_wrapper', 'data-dce-background-overlay-hover-image-url', $background_overlay_hover_image['url'], \true);
        }
        // Background Color on Advanced
        $advanced_background_color = $element->get_settings_for_display('_background_color');
        if (!empty($advanced_background_color)) {
            $element->add_render_attribute('_wrapper', 'data-dce-advanced-background-color', $advanced_background_color, \true);
        }
        $advanced_background_hover_color = $element->get_settings_for_display('_background_hover_color');
        if (!empty($advanced_background_hover_color)) {
            $element->add_render_attribute('_wrapper', 'data-dce-advanced-background-hover-color', $advanced_background_hover_color, \true);
        }
        // Background Image on Advanced
        $advanced_background_image = $element->get_settings_for_display('_background_image');
        if (!empty($advanced_background_image['url'])) {
            $element->add_render_attribute('_wrapper', 'data-dce-advanced-background-image-url', $advanced_background_image['url'], \true);
        }
        $advanced_background_hover_image = $element->get_settings_for_display('background_hover_image');
        if (!empty($advanced_background_hover_image['url'])) {
            $element->add_render_attribute('_wrapper', 'data-dce-advanced-background-hover-image-url', $advanced_background_hover_image['url'], \true);
        }
    }
    /**
     * CSS Class Fix
     *
     * @param string $content
     * @param int $template_id
     * @return string
     */
    public static function css_class_fix(string $content = '', int $template_id = 0)
    {
        if (empty($content)) {
            return $content;
        }
        $template_html_id = Helper::get_template_id_by_html($content);
        if ($template_id && $template_id !== $template_html_id) {
            $content = \str_replace('class="elementor elementor-' . $template_html_id . ' ', 'class="elementor elementor-' . $template_id . ' ', $content);
        } else {
            $template_id = $template_html_id;
        }
        if ($template_id) {
            $queried_object = get_queried_object();
            $queried_object_id = get_queried_object_id();
            $queried_object_type = Helper::get_queried_object_type();
            if ('post' === $queried_object_type) {
                $queried_object_id = get_the_ID();
            }
            if (Helper::is_acfpro_active()) {
                $row = acf_get_loop('active');
                if ($row) {
                    $queried_object_type = 'row';
                    $queried_object_id = get_row_index();
                }
            }
            $content = \str_replace('class="elementor elementor-' . $template_id . ' ', 'class="elementor elementor-' . $template_id . ' dce-elementor-' . $queried_object_type . '-' . $queried_object_id . ' ', $content);
            $content = \str_replace('class="elementor elementor-' . $template_id . '"', 'class="elementor elementor-' . $template_id . ' dce-elementor-' . $queried_object_type . '-' . $queried_object_id . '"', $content);
            $pieces = \explode('data-elementor-id="', $content, 2);
            foreach ($pieces as $pkey => $apiece) {
                if ($pkey) {
                    list($eid, $more) = \explode('"', $apiece, 2);
                    $new_content .= 'data-elementor-id="' . $eid . '" data-' . $queried_object_type . '-id="' . $queried_object_id . '" data-obj-id="' . $queried_object_id . '"' . $more;
                } else {
                    $new_content = $apiece;
                }
            }
            $content = $new_content;
            $content = \str_replace('data-' . $queried_object_type . '-id="' . $queried_object_id . '" data-' . $queried_object_type . '-id="' . $queried_object_id . '"', 'data-' . $queried_object_type . '-id="' . $queried_object_id . '"', $content);
            $content = \str_replace('data-' . $queried_object_type . '-id="' . $queried_object_id . '" data-' . $queried_object_type . '-id="', 'data-' . $queried_object_type . '-id="', $content);
            $content = \str_replace('data-' . $queried_object_type . '-id="' . $queried_object_id . '" data-obj-id="' . $queried_object_id . '" data-' . $queried_object_type . '-id="' . $queried_object_id . '" data-obj-id="' . $queried_object_id . '"', 'data-' . $queried_object_type . '-id="' . $queried_object_id . '" data-obj-id="' . $queried_object_id . '"', $content);
        }
        return $content;
    }
    /**
     * Fix Style
     *
     * Change Selector to fix background images in a loop
     *
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function fix_style(\Elementor\Element_Base $element)
    {
        $settings = $element->get_settings_for_display();
        if (empty($settings['__dynamic__'])) {
            return;
        }
        $css = '';
        $element_id = $element->get_id();
        $element_controls = $element->get_controls();
        $queried_object_type = Helper::get_queried_object_type();
        $queried_object_id = get_queried_object_id();
        if (Helper::is_acfpro_active()) {
            $row = acf_get_loop('active');
            if ($row) {
                $queried_object_type = 'row';
                $queried_object_id = get_row_index();
            }
        }
        foreach ($settings['__dynamic__'] as $key => $dsetting) {
            $tmp = \explode('_', $key);
            $device_detected = \array_pop($tmp);
            if (\in_array($device_detected, ['tablet', 'mobile'], \true)) {
                $devices = [$device_detected => $key];
            } else {
                $devices = ['desktop' => $key];
            }
            foreach ($devices as $device => $setting_key) {
                $selector = '.dce-fix-background-loop .dce-elementor-' . $queried_object_type . '-' . $queried_object_id;
                if ('desktop' !== $device) {
                    $selector = '[data-elementor-device-mode="' . $device . '"] ' . $selector;
                }
                if (isset($element_controls[$setting_key])) {
                    if (!empty($element_controls[$key]['selectors'])) {
                        foreach ($element_controls[$key]['selectors'] as $skey => $svalue) {
                            $rule_value = \false;
                            $rule_selector = \str_replace('{{WRAPPER}}', $selector . ' .elementor-element.elementor-element-' . $element_id, $skey);
                            if (!empty($settings[$setting_key])) {
                                if (\is_array($settings[$setting_key])) {
                                    if (!empty($settings[$setting_key]['url'])) {
                                        $rule_value = \str_replace('{{URL}}', $settings[$setting_key]['url'], $svalue);
                                    }
                                } else {
                                    $rule_value = \str_replace('{{VALUE}}', $settings[$setting_key], $svalue);
                                }
                            }
                            if ($rule_value) {
                                $css .= $rule_selector . '{' . $rule_value . '}';
                            }
                        }
                    }
                }
            }
        }
        if ($css) {
            $css = '<style>' . $css . '</style>';
            if (!wp_doing_ajax()) {
                $css = \DynamicContentForElementor\Assets::dce_enqueue_style('template-fix-' . $element->get_id() . '-inline', $css);
            }
            echo $css;
        }
    }
    /**
     * Retrieve all Custom Post Types
     *
     * @return void
     */
    public static function get_registered_types()
    {
        $types_registered = get_post_types(array('public' => \true), 'names', 'and');
        $types_excluded = self::$supported_types;
        return \array_diff($types_registered, $types_excluded);
    }
}
