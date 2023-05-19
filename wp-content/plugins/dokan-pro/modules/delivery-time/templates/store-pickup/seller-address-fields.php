<?php
/**
 *  Dokan seller address fields Template
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

$verified = false;
if ( isset( $profile_info['dokan_verification']['info']['store_address']['v_status'] ) ) {
    if ( 'approved' === $profile_info['dokan_verification']['info']['store_address']['v_status'] ) {
        $verified = true;
    }
}

dokan_seller_address_fields( $verified );

