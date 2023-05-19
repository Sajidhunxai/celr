<?php
/**
 *  Dokan Add Store Location Template
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper;


$vendor_id               = isset( $vendor_id ) ? $vendor_id : 0;
$store_locations         = Helper::get_vendor_store_pickup_locations( $vendor_id );
$profile_info            = isset( $profile_info ) ? $profile_info : [];
$multiple_store_location = isset( $profile_info['vendor_store_location_pickup']['multiple_store_location'] ) ? $profile_info['vendor_store_location_pickup']['multiple_store_location'] : '';
$default_location_name   = isset( $profile_info['vendor_store_location_pickup']['default_location_name'] ) ? $profile_info['vendor_store_location_pickup']['default_location_name'] : __( 'Default', 'dokan' );
$is_address_verified     = isset( $is_address_verified ) ? $is_address_verified : false;

$default_location = isset( $store_locations[0] ) ? $store_locations[0] : [];

wp_add_inline_script( 'dokan-store-location-pickup-script', 'let dokan_vendor_default_address =' . wp_json_encode( $default_location ), 'before' );
?>

<fieldset id="dokan-store-pickup-location">
    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="enable-store-location-pickup"><?php esc_html_e( 'Multiple location', 'dokan' ); ?></label>
        <div class="dokan-w9">
            <div class="checkbox dokan-text-left">
                <label>
                    <input type="hidden" name="multiple-store-location" value="no">
                    <input type="checkbox" name="multiple-store-location" id="multiple-store-location" value="yes" <?php checked( 'yes', $multiple_store_location ); ?>> <?php esc_html_e( 'Store has multiple locations', 'dokan' ); ?>
                </label>
            </div>
        </div>
    </div>

    <div id="dokan-add-store-location-section">
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="store-location-name-input"><?php esc_html_e( 'Location Name', 'dokan' ); ?> <span>*</span></label>
            <div class="dokan-w5 dokan-text-left">
                <input id="store-location-name-input" value="<?php echo esc_attr( $default_location_name ); ?>" name="store_location_name" placeholder="<?php esc_attr_e( 'Location name', 'dokan' ); ?>" class="dokan-form-control input-md valid" type="text">
            </div>
        </div>

        <?php
        /**
         * Rendering seller address fields
         */
        dokan_seller_address_fields();
        ?>

        <input type="hidden" id="store-location-edit-index" value="">

        <div id="dokan-store-location-edit-section">
            <div class="dokan-form-group">
                <div class="dokan-w4 dokan-text-left" style="margin-left:24%;">
                    <button id="cancel-store-location-section-btn" class="dokan-btn dokan-btn-default dokan-btn-sm"><?php esc_html_e( 'Cancel', 'dokan' ); ?></button>
                    <button id="dokan-save-store-location-btn" class="dokan-btn dokan-btn-default dokan-btn-sm"><?php esc_html_e( 'Save Location', 'dokan' ); ?></button>
                </div>
            </div>
        </div>

    </div>

    <div class="dokan-text-left">
        <div class="dokan-form-group">
            <div class="dokan-w3"><label for="show-add-store-location-section-btn"></label></div>
            <div class="dokan-w9">
                <button id="show-add-store-location-section-btn" class="dokan-btn dokan-btn-default dokan-btn-sm"><?php esc_html_e( 'Add Location', 'dokan' ); ?></button>
            </div>
        </div>
    </div>

    <div class="dokan-text-left dokan-store-location-main-section">
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Store Locations', 'dokan' ); ?></label>

            <div class="dokan-w9">
                <table class="dokan-table dokan-table-striped" id="store-pickup-location-list-table">
                    <thead>
                        <tr>
                            <th colspan="2" class="dokan-store-location-name"><?php esc_html_e( 'Name', 'dokan' ); ?></th>
                            <th colspan="5" class="dokan-store-location-address"><?php esc_html_e( 'Address', 'dokan' ); ?></th>
                            <th colspan="2" class="dokan-store-location-action"><?php esc_html_e( 'Action', 'dokan' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ( count( $store_locations ) > 0 ) : ?>
                        <?php foreach ( $store_locations as $key => $location ) : ?>
                            <tr>
                                <td colspan="2"><?php echo esc_html( $location['location_name'] ); ?></td>
                                <td colspan="5"><?php echo wp_kses_post( Helper::get_formatted_vendor_store_pickup_location( $location, ' ' ) ); ?></td>
                                <td colspan="2" class="store-pickup-location-action-wrapper" style="display: flex;">
                                    <?php if ( ( 0 === $key && ! $is_address_verified ) || 0 !== $key ) : ?>
                                        <button class="dokan-btn dokan-btn-default dokan-btn-sm store-pickup-location-edit-btn" data-location="<?php echo htmlspecialchars( wp_json_encode( $location ) ); ?>" data-location-index="<?php echo esc_attr( $key ); ?>"><span class="fas fa-pencil-alt"></span></button>
                                    <?php endif; ?>
                                    <?php if ( 0 !== $key ) : ?>
                                        <button class="dokan-btn dokan-btn-default dokan-btn-sm store-pickup-location-delete-btn" data-location-index="<?php echo esc_attr( $key ); ?>"><span class="fas fa-trash"></span></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td><?php esc_html_e( 'No store locations found!', 'dokan' ); ?></td>
                        </tr>
                    </tbody>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</fieldset>
