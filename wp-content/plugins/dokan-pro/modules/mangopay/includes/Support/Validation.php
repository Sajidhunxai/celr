<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Support;

/**
 * Class to handle validation.
 *
 * @since 3.5.0
 */
class Validation{

    /**
     * Check if a valid company number is provided
     *
     * @since 3.5.0
     *
     * @param string $company_number
     *
     * @return boolean
     */
    public static function check_company_number_pattern( $company_number ) {
        $patterns = array(
            '^([a-z]{2})([0-9]{6})([a-z]{1})$', //LL XXXXXX L
            '^([0-9]{6})$', //XXXXXXXXX
            '^([0-9]{8})$', //XXXXXXXXX
            '^([0-9]{9})$', //XXXXXXXXX
            '^([0-9]{10})$', //XXXX.XXX.XXX
            '^([0-9]{11})$', //XXXXXXXXXXX
            '^([0-9]{12})$', //XXXXXXXXXXXX
            '^([0-9]{14})$', //XXXXXXXXXXXXXX
            '^([a-z]{1})([0-9]{5})$', //L XXXXX
            '^([a-z]{1})([0-9]{6})$', //L XXXXXX
            '^([a-z]{1})([0-9]{8})$', //L XXXXXXXX
            '^([a-z]{2})([0-9]{6})$', //HEXXXXXX
            '^([a-z]{2})([0-9]{10})$', //LLXX-XX-XXXXXX
            '^([a-z]{3})([0-9]{6})$', //LLXXXXXX
            '^([a-z]{3})([0-9]{9})$', //LLXX-XX-XXXXXX
        );

        $company_number = str_replace( array( ' ', '.', '_', '-' ), '', $company_number );
        foreach ( $patterns as $pattern ) {
            if ( preg_match( '#' . $pattern . '#i', $company_number ) ) {
                return true;
            }
        }

        return false;
    }
}
?>
