<?php
namespace WeDevs\DokanPro\Modules\StoreReviews\Emails;

/**
 * Dokan email handler class
 *
 * @package Dokan
 */
class Manager {

    /**
     * Load automatically when class initiate
     */
    public function __construct() {
        //Dokan Email filters for WC Email
        add_filter( 'woocommerce_email_classes', [ $this, 'load_dokan_emails' ], 35 );
    }

    /**
     * Add Dokan Store Review Email classes in WC Email
     *
     * @since 3.5.5
     *
     * @param array $wc_emails
     *
     * @return array $wc_emails
     */
    public function load_dokan_emails( $wc_emails ) {
        require_once DOKAN_SELLER_RATINGS_DIR . '/classes/Emails/NewStoreReview.php';
        $wc_emails['Dokan_Email_New_Store_Review'] = new NewStoreReview();

        return $wc_emails;
    }
}
