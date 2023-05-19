<?php

namespace WeDevs\DokanPro\Admin;

defined( 'ABSPATH' ) || exit;

use WeDevs\Dokan\Abstracts\DokanBackgroundProcesses;

if ( ! class_exists( 'WC_Email', false ) ) {
    include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php';
}

class AnnouncementBackgroundProcess extends DokanBackgroundProcesses {

    /**
     * @var string
     */
    protected $action = 'dokan_announcement_emails';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    public function task( $payload ) {
        $seller_id = $payload['sender_id'];
        $post_id   = $payload['post_id'];

        $announcement_email = new \WeDevs\DokanPro\Emails\Announcement();

        if ( ! empty( $seller_id ) ) {
            $announcement_email->trigger( $seller_id, $post_id );
            dokan_log( sprintf( 'Mail send to %d', $seller_id ) );
        }

        return false;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    public function complete() {
        dokan_log( 'Sending process completed' );
    }

}
