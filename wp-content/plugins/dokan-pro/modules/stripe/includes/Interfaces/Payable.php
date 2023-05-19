<?php

namespace WeDevs\DokanPro\Modules\Stripe\Interfaces;

interface Payable {

    /**
     * Make the payment
     *
     * @since 3.0.3
     *
     * @return array
     */
    public function pay();
}