<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Models;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

class BadgeEvent {

    /**
     * Badge properties
     *
     * @since 3.7.14
     *
     * @var array
     */
    protected $data = [
        'id'                  => '',
        'title'               => '',
        'description'         => '',
        'condition_text'      => [],
        'responsible_class'   => '',
        'responsible_hooks'   => [],
        'hover_text'          => '',
        'group'               => [],
        'has_multiple_levels' => false,
        'badge_logo'          => '',
        'badge_logo_raw'      => '',
        'input_group_icon'    => [
            'condition' => '',
            'data'      => '',
        ],
        'status'              => 'draft',
        'created'             => false,
    ];

    /**
     * Class constructor
     *
     * @since 3.7.14
     *
     * @param string $event_id
     * @param array  $data
     */
    public function __construct( $event_id = '', $data = [] ) {
        $data = wp_parse_args( $data, $this->data );

        // set default data
        $this
            ->set_event_id( $event_id )
            ->set_title( $data['title'] )
            ->set_description( $data['description'] )
            ->set_conditional_text( $data['condition_text'] )
            ->set_class( $data['responsible_class'] )
            ->set_hooks( $data['responsible_hooks'] )
            ->set_hover_text( $data['hover_text'] )
            ->set_group( $data['group'] )
            ->set_has_multiple_levels( $data['has_multiple_levels'] )
            ->set_badge_logo( $data['badge_logo'] )
            ->set_badge_logo_raw( ! empty( $data['badge_logo_raw'] ) ?? $data['badge_logo'] )
            ->set_icon( $data['input_group_icon'] )
            ->set_status( $data['status'] )
            ->set_created( $data['created'] );
    }

    /**
     * Get object data as json string
     *
     * @since 3.7.14
     *
     * @return false|string
     */
    public function __toString() {
        return wp_json_encode( $this->get_data() );
    }

    /**
     * Get badge event id
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_event_id() {
        return $this->data['id'];
    }

    /**
     * Get badge title
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_title() {
        return $this->data['title'];
    }

    /**
     * Get badge description
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_description() {
        return $this->data['description'];
    }

    /**
     * Get badge conditional help text
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_conditional_text() {
        return $this->data['condition_text'];
    }

    /**
     * Get responsible badge class name
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_class() {
        return $this->data['responsible_class'];
    }

    /**
     * Get responsible badge hooks
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_hooks() {
        return (array) $this->data['responsible_hooks'];
    }

    /**
     * Get badge hover text
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_hover_text() {
        return $this->data['hover_text'];
    }

    /**
     * Get badge group
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_group() {
        return $this->data['group'];
    }

    /**
     * Get badge url
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_formatted_badge_logo() {
        if ( is_numeric( $this->data['badge_logo'] ) ) {
            $badge_logo = wp_get_attachment_image_url( $this->data['badge_logo'], 'dokan-seller-badge' );;
        } else {
            // this is a default logo, so add site prefix to it,
            // this will prevent asset not found issue if site domain is changed since we are storing this to database
            $badge_logo = sprintf( '%s/images/badges/%s', DOKAN_SELLER_BADGE_ASSETS, $this->data['badge_logo'] );
        }

        return $badge_logo;
    }

    /**
     * Get badge url
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_badge_logo() {
        return $this->data['badge_logo'];
    }

    /**
     * Get badge icon
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_icon() {
        return $this->data['input_group_icon'];
    }

    /**
     * Get badge created value
     *
     * @since 3.7.14
     *
     * @return bool
     */
    public function get_badge_created() {
        return wc_string_to_bool( $this->data['created'] );
    }

    /**
     * Get formatted hover text for a badge
     *
     * @since 3.7.14
     *
     * @param object $db_item
     *
     * @return string
     */
    public function get_formatted_hover_text( $db_item ) {
        if ( ! isset( $db_item->acquired_data ) ) {
            return '';
        }

        $search_data = [
            '{badge_name}',
            '{badge_data}',
            '{badge_data_price}',
        ];

        $replaceable_data = [
            sanitize_text_field( $db_item->badge_name ),
            intval( $db_item->acquired_data ),
            sprintf( '%s %s', get_woocommerce_currency_symbol(), wc_format_localized_price( wc_format_decimal( $db_item->acquired_data, wc_get_price_decimals() ) ) ),
        ];

        return str_replace( $search_data, $replaceable_data, $this->get_hover_text() );
    }

    /**
     * Get event data
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_data() {
        $data               = $this->data;
        $data['badge_logo'] = $this->get_formatted_badge_logo();
        // unset sensitive data, since we are sending this via rest api
        unset( $data['responsible_class'], $data['responsible_hooks'] );

        return $data;
    }

    /**
     * Set badge event id
     *
     * @param $event_id
     *
     * @return $this
     */
    public function set_event_id( $event_id ) {
        $this->data['id'] = sanitize_text_field( $event_id );

        return $this;
    }

    /**
     * Set badge title
     *
     * @since 3.7.14
     *
     * @param string $title
     *
     * @return $this
     */
    public function set_title( $title ) {
        $this->data['title'] = sanitize_text_field( $title );

        return $this;
    }

    /**
     * Set badge description
     *
     * @since 3.7.14
     *
     * @param string $description
     *
     * @return $this
     */
    public function set_description( $description ) {
        $this->data['description'] = wp_kses_post( $description );

        return $this;
    }

    /**
     * Set badge conditional help text
     *
     * @since 3.7.14
     *
     * @param array $conditional_text
     *
     * @return $this
     */
    public function set_conditional_text( $conditional_text ) {
        $this->data['condition_text'] = [
            'prefix' => sanitize_text_field( $conditional_text['prefix'] ),
            'suffix' => sanitize_text_field( $conditional_text['suffix'] ),
            'type'   => ! empty( $conditional_text['type'] ) ? sanitize_text_field( $conditional_text['type'] ) : '',
        ];

        return $this;
    }

    /**
     * Set responsible badge class name
     *
     * @since 3.7.14
     *
     * @param string $class_name
     *
     * @return $this
     */
    public function set_class( $class_name ) {
        $this->data['responsible_class'] = $class_name;

        return $this;
    }

    /**
     * Set responsible hooks for this badge to work
     *
     * @since 3.7.14
     *
     * @param array|string $responsible_hooks
     *
     * @return $this
     */
    public function set_hooks( $responsible_hooks ) {
        $this->data['responsible_hooks'] = array_map( 'sanitize_text_field', (array) $responsible_hooks );

        return $this;
    }

    /**
     * Set badge hover help text
     *
     * @since 3.7.14
     *
     * @param string $hover_text
     *
     * @return $this
     */
    public function set_hover_text( $hover_text ) {
        $this->data['hover_text'] = wp_kses_post( $hover_text );

        return $this;
    }

    /**
     * Set badge group
     *
     * @since 3.7.14
     *
     * @param array $group_name
     *
     * @return $this
     */
    public function set_group( $group_name ) {
        $this->data['group'] = array_map( 'sanitize_text_field', $group_name );

        return $this;
    }

    /**
     * Set is badge event has multiple levels
     *
     * @since 3.7.14
     *
     * @param bool $has_multiple_level
     *
     * @return $this
     */
    public function set_has_multiple_levels( $has_multiple_level = false ) {
        $this->data['has_multiple_levels'] = wc_string_to_bool( $has_multiple_level );

        return $this;
    }

    /**
     * Set badge logo
     *
     * @since 3.7.14
     *
     * @param string $badge_logo
     *
     * @return $this
     */
    public function set_badge_logo( $badge_logo ) {
        if ( ! empty( $badge_logo ) ) {
            $this->data['badge_logo'] = $badge_logo;
        }

        return $this;
    }

    /**
     * Set badge logo
     *
     * @since 3.7.14
     *
     * @param string $badge_logo_raw
     *
     * @return $this
     */
    public function set_badge_logo_raw( $badge_logo_raw ) {
        if ( ! empty( $badge_logo_raw ) ) {
            $this->data['badge_logo_raw'] = $badge_logo_raw;
        }

        return $this;
    }

    /**
     * Set badge icon css class name
     *
     * @since 3.7.14
     *
     * @param array $icon_class_name
     *
     * @return $this
     */
    public function set_icon( $icon_class_name ) {
        $this->data['input_group_icon']['condition'] = ! empty( $icon_class_name['condition'] ) ? sanitize_text_field( $icon_class_name['condition'] ) : 'icon-compare';
        $this->data['input_group_icon']['data']      = ! empty( $icon_class_name['data'] ) ? sanitize_text_field( $icon_class_name['data'] ) : 'icon-count';

        return $this;
    }

    /**
     * Set badge created param
     *
     * @since 3.7.14
     *
     * @param string $status
     *
     * @return $this
     */
    public function set_status( $status ) {
        $this->data['status'] = in_array( $status, [ 'draft', 'published' ], true ) ? sanitize_text_field( $status ) : 'draft';

        return $this;
    }

    /**
     * Set badge created param
     *
     * @since 3.7.14
     *
     * @param bool $badge_created
     *
     * @return $this
     */
    public function set_created( $badge_created ) {
        $this->data['created'] = wc_string_to_bool( $badge_created );

        return $this;
    }

    /**
     * Check if badge event has multiple levels
     *
     * @since 3.7.14
     *
     * @return bool
     */
    public function has_multiple_levels() {
        return wc_string_to_bool( $this->data['has_multiple_levels'] );
    }

    /**
     * Check if event class exists
     *
     * @since 3.7.14
     *
     * @return bool
     */
    public function is_event_class_exists() {
        return class_exists( $this->get_class() );
    }

    /**
     * Check if badge is created on database
     *
     * @since 3.7.14
     *
     * @return bool
     */
    public function is_event_created() {
        return $this->get_badge_created();
    }
}
