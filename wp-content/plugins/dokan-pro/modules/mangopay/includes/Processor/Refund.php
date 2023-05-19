<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use Exception;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class to process Mangopay refund.
 *
 * @since 3.5.0
 */
class Refund extends Processor {

	/**
	 * Retrieves a refund data.
	 *
	 * @since 3.5.0
	 *
	 * @param int|string $refund_id
	 *
	 * @return object|false
	 */
	public static function get( $refund_id ) {
		try {
			$refund = static::config()->mangopay_api->Refunds->Get( $refund_id );
		} catch( Exception $e ) {
			Helper::log( sprintf( 'Could not fetch data for transaction: %s. Message: %s', $refund_id, $e->getMessage() ), 'Refund' );
			return false;
		}

		return $refund;
	}
}
