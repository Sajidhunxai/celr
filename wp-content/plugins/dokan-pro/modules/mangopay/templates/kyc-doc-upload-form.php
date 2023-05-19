<div id="dokan-mangopay-kyc" class="dokan-mangopay-container hidden">
    <div class="dokan-mangopay-kyc-form-block">
        <h4>
            <?php esc_html_e( 'Upload a new document', 'dokan' ); ?>
        </h4>
        <div id="dokan-mangopay-kyc-notice" class="dokan-alert"></div>

        <div class="kyc_select_type_div">
            <label for="dokan_kyc_file_type">
                <?php esc_html_e( 'Document type', 'dokan' ); ?>
            </label>

            <select name="dokan_kyc_file_type" id="dokan-kyc-file-type" class="kyc_select_type">
            <?php foreach ( $list_to_show as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>">
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>

        <div class="dokan-kyc-input-file-type">
            <p>
                <input type="file" name="dokan_kyc_file[]" id="dokan-kyc-file" multiple class="kyc_input_file">
            </p>
            <p>
                <?php esc_html_e( 'Supported types: .pdf, .jpeg, .jpg and .png only. Minimum file size is 1KB (32KB for Identity Proof), and Maximum file size is 10MB. Multiple files are accepted upon necessary', 'dokan' ); ?><br>
            </p>
        </div>

        <div id="dokan-mangopay-kyc-submit-container">
            <div class="upload-progress-status">
                <div class="progress-wrap">
                    <div class="progress-bar"></div>
                    <div class="status"><?php esc_html_e( '0', 'dokan' ); ?>%</div></p>
                </div>
                <div id="dokan-kyc-inputs"></div>
            </div>
            <p>
                <input type="submit" value="<?php esc_attr_e( 'Submit', 'dokan' ); ?>" class="kyc_submit dokan-mp-btn-submit" id="dokan-mangopay-submit-kyc">
                <input type="submit" value="<?php esc_attr_e( 'Submitting', 'dokan' ); ?>..." class="kyc_submit dokan-mp-btn-submit" id="dokan-mangopay-submit-kyc-disabled" disabled style="display: none;">
            </p>
        </div>
    </div>

    <?php if ( $ubo_applicable ) : ?>
    <div class="dokan-mangopay-ubo-block">
        <h3 clas="ubo_title_h">
            <?php esc_html_e( 'UBO Declaration', 'dokan' ); ?>
        </h3>

        <div class="ubo_description_hint_div">
            <?php esc_html_e( 'UBOs (Ultimate Beneficial Owners) are the individuals possessing more than 25% of the shares or voting rights of a company. This declaration replaces the old shareholder declaration.', 'dokan' ); ?>
        </div>

        <div id="ubo_data" data-mpid="<?php echo esc_attr( $existing_account_id ); ?>">
            <?php esc_html_e( 'Loading', 'dokan' ); ?>...
        </div>
    </div>
    <?php endif; ?>
</div>
