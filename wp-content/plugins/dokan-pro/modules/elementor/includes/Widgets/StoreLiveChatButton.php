<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanButton;
use Elementor\Controls_Manager;

class StoreLiveChatButton extends DokanButton {

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-live-chat-button';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Live Chat Button', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-comments';
    }

    /**
     * Widget keywords
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'button', 'support', 'live', 'chat', 'message' ];
    }

    /**
     * Register widget controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function register_controls() {
        parent::register_controls();

        $this->update_control(
            'text',
            [
                'dynamic'   => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-live-chat-button-tag' ),
                    'active'  => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-button-wrapper > .dokan-store-live-chat-btn' => 'width: auto; margin: 0;',
                ],
            ]
        );

        $this->update_control(
            'link',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );
    }

    /**
     * Button wrapper class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_wrapper_class() {
        return parent::get_button_wrapper_class() . ' dokan-store-live-chat-btn-wrap';
    }
    /**
     * Button class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_class() {
        $classes = 'dokan-store-live-chat-btn ';

        $classes .= is_user_logged_in() ? 'dokan-live-chat' : 'dokan-live-chat-login';

        return $classes;
    }

    /**
     * Render button
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        if ( ! dokan_is_store_page() ) {
            return;
        }

        // check if module is active
        if ( ! dokan_pro()->module->is_active( 'live_chat' ) ) {
            return;
        }

        // check if admin settings is enabled
        if ( dokan_get_option( 'chat_button_seller_page', 'dokan_live_chat' ) !== 'on' ) {
            return;
        }

        $id = dokan_elementor()->get_store_data( 'id' );
        if ( ! $id ) {
            return;
        }

        // check if admin enabled live chat for this store
        $store = dokan()->vendor->get( $id )->get_shop_info();
        if ( ! isset( $store['live_chat'] ) || $store['live_chat'] !== 'yes' ) {
            return;
        }

        $chatter = dokan_pro()->module->live_chat->chat->provider;
        if ( is_null( $chatter ) ) {
            return;
        }

        parent::render();

        if ( ! is_user_logged_in() && 'talkjs' === $chatter->get_name() ) {
            return $chatter->login_to_chat();
        }

        switch ( $chatter->get_name() ) {
            case 'talkjs':
                echo do_shortcode( '[dokan-live-chat]' );
                break;
            case 'messenger':
                $page_id = ! empty( $store['fb_page_id'] ) ? $store['fb_page_id'] : '';
                echo do_shortcode( sprintf( '[dokan-live-chat-messenger page_id="%s"]', $page_id ) );
                break;
            case 'tawkto':
                $tawk_property_id = ! empty( $store['tawkto_property_id'] ) ? $store['tawkto_property_id'] : '';
                $tawk_widget_id   = ! empty( $store['tawkto_widget_id'] ) ? $store['tawkto_widget_id'] : '';

                echo do_shortcode( sprintf( '[dokan-live-chat-tawkto property_id="%s" widget_id="%s"]', $tawk_property_id, $tawk_widget_id ) );
                break;
            case 'whatsapp':
                $whatsapp_number = ! empty( $store['whatsapp_number'] ) ? $store['whatsapp_number'] : '';

                echo do_shortcode( sprintf( '[dokan-live-chat-whatsapp number="%s"]', $whatsapp_number ) );
                break;
        }
    }
}
