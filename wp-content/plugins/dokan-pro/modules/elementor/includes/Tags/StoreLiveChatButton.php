<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreLiveChatButton extends TagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-live-chat-button-tag';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Live Chat Button', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render() {
        // check if module active
        if ( ! dokan_pro()->module->is_active( 'live_chat' ) ) {
            return;
        }

        if ( dokan_get_option( 'chat_button_seller_page', 'dokan_live_chat' ) !== 'on' ) {
            return;
        }

        $online_indicator = '';
        if ( dokan_is_store_page() ) {
            $chatter = dokan_pro()->module->live_chat->chat->provider;

            if ( ! is_null( $chatter ) && 'talkjs' === $chatter->get_name() && $chatter->dokan_is_seller_online() ) {
                $online_indicator = '<i class="fas fa-circle" aria-hidden="true"></i>';
            }
        }

        printf(
            '%s%s',
            $online_indicator,
            __( 'Chat Now', 'dokan' )
        );
    }
}
