<?php

namespace WeDevs\DokanPro\Modules\Stripe\Interfaces;

interface WebhookHandleable {

    /**
     * Handle the event
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function handle();
}