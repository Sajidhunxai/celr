<?php

use WeDevs\DokanPro\Modules\Razorpay\Helper;

$existing_razorpay_id = get_user_meta( get_current_user_id(), Helper::get_seller_account_id_key_trashed(), true );
?>

<script type="text/html" id="tmpl-dokan-razorpay-vendor-account-register">
    <div class="dokan-popup-content white-popup dokan-razorpay-account-popup-wrapper" id="dokan-razorpay-account-popup" style="width: 600px;">
        <h2 class="dokan-popup-title dokan-razorpay-account-title">
            <?php esc_html_e( 'Connect Razorpay Account', 'dokan' ); ?>
        </h2>

        <form action="<?php echo esc_url( $ajax_url ); ?>" method="POST" id="dokan-razorpay-vendor-register-form">
            <div class="vendor-register-form-container">
                <div class="dokan-razorpay-already-registered">
                    <label for="dokan_razorpay_existing_user_chekbox">
                        <input type="checkbox" name="razorpay_existing_user" id="dokan_razorpay_existing_user_chekbox">
                        <?php esc_html_e( 'I\'ve already an account', 'dokan' ); ?>
                    </label>

                    <?php if ( ! empty( $existing_razorpay_id ) ) : ?>
                        <div class="dokan-existing-account-info dokan-hide">
                            <?php esc_html_e( 'Existing Razorpay Account: ', 'dokan' ); ?>
                            <strong><?php echo esc_html( $existing_razorpay_id ); ?></strong>
                            <span class="account-hint">&nbsp;( <?php esc_html_e( 'To use this account, just write it below or give any razorpay account ID.', 'dokan' ); ?> )</span>
                        </div>
                    <?php endif; ?>

                    <input
                        name="razorpay_account_id"
                        class="dokan-form-control dokan-hide"
                        id="dokan_razorpay_account_id"
                        placeholder="<?php esc_html_e( 'Razorpay Account ID; eg: acc_', 'dokan' ); ?>"
                        type="text"
                    >
                </div>

                <div id="dokan-razorpay-new-connect">
                    <div class="dokan-form-group dokan-clearfix">
                        <div class="content-half-part">
                            <label class="dokan-control-label" for="razorpay_account_name">
                                <?php esc_html_e( 'Account Name', 'dokan' ); ?>
                                <span class="dokan-text-required">*</span>
                            </label>
                            <input
                                name="razorpay_account_name"
                                class="dokan-form-control"
                                id="razorpay_account_name"
                                placeholder="<?php esc_html_e( 'Your Razorpay Account Name', 'dokan' ); ?>"
                                type="text"
                                required
                            >
                        </div>

                        <div class="content-half-part">
                            <label class="dokan-control-label" for="razorpay_account_email">
                                <?php esc_html_e( 'Account Email', 'dokan' ); ?>
                                <span class="dokan-text-required">*</span>
                            </label>
                            <input
                                name="razorpay_account_email"
                                class="dokan-form-control"
                                id="razorpay_account_email"
                                placeholder="<?php esc_html_e( 'Your Razorpay Account Email', 'dokan' ); ?>"
                                type="email"
                                required
                            >
                        </div>
                    </div>

                    <div class="dokan-razorpay-form-group">
                        <p><b><?php esc_html_e( 'Business Information', 'dokan' ); ?></b></p>
                        <div class="dokan-form-group dokan-clearfix">
                            <div class="content-half-part">
                                <label for="razorpay_business_name" class="dokan-control-label">
                                    <?php esc_html_e( 'Your Company name', 'dokan' ); ?>
                                    <span class="dokan-text-required">*</span>
                                </label>
                                <input
                                    name="razorpay_business_name"
                                    class="dokan-form-control"
                                    id="razorpay_business_name"
                                    placeholder="<?php esc_html_e( 'Your Company Name', 'dokan' ); ?>"
                                    type="text"
                                    required
                                >
                            </div>

                            <div class="content-half-part">
                                <label for="razorpay_business_type" class="dokan-control-label">
                                    <?php esc_html_e( 'Your company type', 'dokan' ); ?>
                                    <span class="dokan-text-required">*</span>
                                </label>
                                <select name="razorpay_business_type" id="razorpay_business_type" required>
                                    <?php foreach ( $razorpay_business_types as $key => $business_type ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>">
                                            <?php echo esc_attr( $business_type ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dokan-clearfix"></div>
                        </div>
                    </div>

                    <div class="dokan-razorpay-form-group">
                        <p><b><?php esc_html_e( 'Bank Information', 'dokan' ); ?></b></p>
                        <div class="dokan-form-group dokan-clearfix">
                            <div>
                                <div class="content-half-part">
                                    <label for="razorpay_beneficiary_name" class="dokan-control-label">
                                        <?php esc_html_e( 'Bank Account Name', 'dokan' ); ?>
                                        <span class="dokan-text-required">*</span>
                                    </label>
                                    <input
                                        name="razorpay_beneficiary_name"
                                        class="dokan-form-control"
                                        id="razorpay_beneficiary_name"
                                        placeholder="<?php esc_html_e( 'Your Bank Account Name', 'dokan' ); ?>"
                                        type="text"
                                        required
                                    >
                                </div>

                                <div class="content-half-part">
                                    <label for="razorpay_account_number" class="dokan-control-label">
                                        <?php esc_html_e( 'Bank Account Number', 'dokan' ); ?>
                                        <span class="dokan-text-required">*</span>
                                    </label>
                                    <input
                                        name="razorpay_account_number"
                                        class="dokan-form-control"
                                        id="razorpay_account_number"
                                        placeholder="<?php esc_html_e( 'Your Bank Account Number', 'dokan' ); ?>"
                                        type="text"
                                        required
                                    >
                                </div>

                                <div>
                                    <div class="content-half-part">
                                        <label for="razorpay_ifsc_code" class="dokan-control-label">
                                            <?php esc_html_e( 'Bank IFSC Code', 'dokan' ); ?>
                                            <span class="dokan-text-required">*</span>
                                        </label>
                                        <input
                                            name="razorpay_ifsc_code"
                                            class="dokan-form-control"
                                            id="razorpay_ifsc_code"
                                            placeholder="<?php esc_html_e( 'Your Bank IFSC Code', 'dokan' ); ?>"
                                            type="text"
                                            required
                                        >
                                    </div>

                                    <div class="content-half-part">
                                        <label for="razorpay_account_type" class="dokan-control-label">
                                            <?php esc_html_e( 'Bank Account type', 'dokan' ); ?>
                                        </label>
                                        <select name="razorpay_account_type" id="razorpay_account_type">
                                            <?php foreach ( $bank_account_types as $key => $account_type ) : ?>
                                                <option value="<?php echo esc_attr( $key ); ?>">
                                                    <?php echo esc_attr( $account_type ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="dokan-clearfix"></div>
                        </div>
                    </div>

                    <div>
                        <p class="account-note">
                            <b><?php esc_html_e( 'Note:', 'dokan' ); ?></b>
                            <?php esc_html_e( 'Please make sure that you have entered all the details correctly. The information can not be changed.', 'dokan' ); ?>
                        </p>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <div class="dokan-w12">
                        <?php wp_nonce_field( 'dokan_razorpay_connect' ); ?>
                        <input type="hidden" name="action" value="dokan_razorpay_connect">
                        <span class="dokan-spinner dokan-razorpay-connect-spinner dokan-hide"></span>
                        <button type="button" class="button button-primary" id="dokan_razorpay_vendor_register_button"> <?php esc_html_e( 'Connect Account', 'dokan' ); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
