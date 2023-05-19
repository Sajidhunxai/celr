<?php
$vendor_proof       = ! empty( $address['proof'] ) ? $address['proof'] : '';
$seller_proof_field = ! empty( $seller_address_fields['proof'] ) ? $seller_address_fields['proof'] : '';
?>

<?php if ( $seller_proof_field ) : ?>
    <div id="dokan-seller-address-proof" class="dokan-form-group">
        <label class="control-label" for="dokan_address[proof]">
            <?php echo esc_html( $label ); ?>
            <?php echo ( ! empty( $seller_proof_field['required'] ) ? '<span class="required">*</span>' : '' ); ?>
        </label>
        <br/>
        <div class="proof-button-area" style="display: <?php echo $vendor_proof ? 'none' : 'block'; ?>;">
            <a href="#" id="vendor-proof" class="dokan-btn dokan-btn-default">
                <i class="fas fa-cloud-upload-alt"></i>
                <?php echo esc_html( $btn_text ); ?>
            </a>
            <input type="hidden" id="vendor-proof-url" name="dokan_address[proof]" value="<?php echo esc_attr( $vendor_proof ); ?>"/>
            <div class="dokan-vendor-proof-alert dokan-hide">
                <?php echo esc_html( $required_text ); ?>
            </div>
        </div>
        <div class="vendor_img_container">
            <?php if ( $vendor_proof ) : ?>
                <img src="<?php echo esc_url( $vendor_proof ); ?>"/>
                <a class="dokan-close dokan-remove-proof-image">×</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
