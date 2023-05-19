<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreVacationMessage extends TagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-vacation-message';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Vacation Message', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render() {
        if ( ! dokan_pro()->module->is_active( 'seller_vacation' ) ) {
            return;
        }

        if ( ! class_exists( \WeDevs\DokanPro\Modules\SellerVacation\Module::class ) ) {
            return;
        }

        if ( dokan_is_store_page() ) {
            $seller_vacation = dokan_pro()->module->seller_vacation;
            $store           = dokan()->vendor->get( get_query_var( 'author' ) );
            $shop_info       = $store->get_shop_info();

            $seller_vacation->show_vacation_message( $store->data, $shop_info, true );
        } else {
            esc_html_e( 'Store vacation message set in vendor dashboard will show here.', 'dokan' );
        }
    }
}
