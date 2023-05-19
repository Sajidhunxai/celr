<?php

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dokan_Live_Search_Widget class
 *
 * @extends WP_Widget
 * @class Dokan_Live_Search_Widget The class that registered a new widget
 * entire Dokan_Live_Search plugin
 */
class Dokan_Live_Search_Widget extends WP_Widget {

    /**
     * Constructor for the Dokan_Live_Search_Widget class
     *
     * @uses is_admin()
     */
    public function __construct() {
        parent::__construct(
            'dokna_product_search',
            __( 'Dokan Live Search', 'dokan' ),
            array( 'description' => __( 'Search products live', 'dokan' ) )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        if ( $args && is_array( $args ) ) {
            extract( $args, EXTR_SKIP ); // phpcs:ignore
        }

        $title              = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';
        $live_search_option = dokan_get_option( 'live_search_option', 'dokan_live_search_setting', 'default' );

        if ( 'old_live_search' === $live_search_option ) {
            $live_search_option_class = '';
        } else {
            $live_search_option_class = 'dokan-ajax-search-suggestion';
        }

        wp_enqueue_style( 'dokan-ls-custom-style' );

        echo isset( $before_widget ) ? $before_widget : '';

        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        ?>
        <div class="dokan-product-search">
            <form role="search" method="get" class="ajaxsearchform ajaxsearchform-dokan" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <div class="input-group">
                    <input type="text" autocomplete="off" class="form-control dokan-ajax-search-textfield <?php echo $live_search_option_class; ?>" value="<?php echo get_search_query(); ?>" name="s" placeholder="<?php echo __( 'Just type ...', 'dokan' ); ?>" />
                    <span class="input-group-addon" id="dokan-ls-ajax-cat-dropdown">
                        <?php
                        wp_dropdown_categories(
                            array(
                                'taxonomy' => 'product_cat',
                                'show_option_all' => __( 'All', 'dokan' ),
                                'hierarchical' => true,
                                'hide_empty' => false,
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'class' => 'orderby dokan-ajax-search-category',
                                'walker' => new Dokan_LS_Walker_CategoryDropdown(),
                            )
                        );
                        ?>
                    </span>
                    <input type="hidden" name="dokan-live-search-option" value="<?php echo $live_search_option; ?>" class="dokan-live-search-option" id="dokan-live-search-option">
                </div>
                <div id="dokan-ajax-search-suggestion-result" class="dokan-ajax-search-result">
                </div>
            </form>
        </div>
        <?php
        echo isset( $after_widget ) ? $after_widget : '';

        wp_enqueue_script( 'dokan-ls-custom-js' );

    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance['title'] ) ) {
            $title = $instance['title'];
        } else {
            $title = __( 'Live Search', 'dokan' );
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'dokan' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }

} // class Dokan_Live_Search_Widget

/**
 * Create HTML dropdown list of Categories.
 *
 * @uses Walker
 */
class Dokan_LS_Walker_CategoryDropdown extends Walker {
    /**
     * @see Walker::$tree_type
     * @var string
     */
    public $tree_type = 'category';

    /**
     * @see Walker::$db_fields
     * @var array
     */
    public $db_fields = array(
        'parent' => 'parent',
        'id'     => 'term_id',
    );

    /**
     * Start the element output.
     *
     * @see Walker::start_el()
     *
     * @param string $output   Passed by reference. Used to append additional content.
     * @param object $category Category data object.
     * @param int    $depth    Depth of category. Used for padding.
     * @param array  $args     Uses 'selected' and 'show_count' keys, if they exist. @see wp_dropdown_categories()
     */
    public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $pad = str_repeat( '&nbsp;', $depth * 3 );

        $cat_name = apply_filters( 'list_cats', $category->name, $category );
        $output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $category->slug ) . '"';

        $output .= '>';
        $output .= $pad . $cat_name;
        if ( $args['show_count'] ) {
            $output .= '&nbsp;&nbsp;(' . $category->count . ')';
        }
        $output .= "</option>\n";
    }
}
