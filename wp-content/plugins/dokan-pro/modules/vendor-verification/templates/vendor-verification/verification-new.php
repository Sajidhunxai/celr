<?php

$dokan_Seller_Verification = dokan_pro()->module->vendor_verification; // phpcs:ignore

// phpcs:ignore
$current_user   = get_current_user_id();
$seller_profile = dokan_get_store_info( $current_user );

$gravatar       = isset( $seller_profile['dokan_verification']['info']['photo_id'] ) ? $seller_profile['dokan_verification']['info']['photo_id'] : 0;
$phone          = isset( $seller_profile['dokan_verification']['info']['phone'] ) ? $seller_profile['dokan_verification']['info']['phone'] : '';
$address        = isset( $seller_profile['dokan_verification']['info']['address'] ) ? $seller_profile['dokan_verification']['info']['address'] : '';
$id_type        = isset( $seller_profile['dokan_verification']['info']['dokan_v_id_type'] ) ? $seller_profile['dokan_verification']['info']['dokan_v_id_type'] : 'passport';
$id_type        = ( $id_type !== '' ) ? $id_type : 'passport';
$id_status      = isset( $seller_profile['dokan_verification']['info']['dokan_v_id_status'] ) ? $seller_profile['dokan_verification']['info']['dokan_v_id_status'] : '';
$address_status = isset( $seller_profile['dokan_verification']['info']['store_address']['v_status'] ) ? $seller_profile['dokan_verification']['info']['store_address']['v_status'] : '';

$phone_status   = isset( $seller_profile['dokan_verification']['info']['phone_status'] ) ? $seller_profile['dokan_verification']['info']['phone_status'] : '';
$phone_no       = isset( $seller_profile['dokan_verification']['info']['phone_no'] ) ? $seller_profile['dokan_verification']['info']['phone_no'] : '';
?>


<div class="dokan-verification-content">

    <!-- =================================================== -->
    <!-- ID Verification Content Start -->
    <!-- =================================================== -->
    <?php if ( $dokan_Seller_Verification->e_msg ) { ?>
        <div class="dokan-alert dokan-alert-danger"><?php echo $dokan_Seller_Verification->e_msg; ?></div>
        <?php $dokan_Seller_Verification->e_msg = false;
    } ?>
    <div id="feedback" class=""></div>

    <div class="dokan-panel dokan-panel-default dokan_v_id" id="dokan_v_id">
            <div class="dokan-panel-heading">
                <strong><?php esc_html_e( 'ID Verification', 'dokan' ); ?></strong>
            </div>

            <div class="dokan-panel-body">
                <?php
                if ( $id_status !== '' ) {
                    $alert_class = 'dokan-alert-info';
                    $cancel_btn_class = '';

                    switch ( $id_status ) {
                        case 'approved':
                            $alert_class = 'dokan-alert-success';
                            $cancel_btn_class = 'dokan-hide';
                            break;

                        case 'rejected':
                            $alert_class = 'dokan-alert-danger';
                            $cancel_btn_class = 'dokan-hide';
                            break;

                        case 'pending':
                            $alert_class = 'dokan-alert-warning';
                            break;
                    }
                    ?>
                    <div class="dokan-alert <?php echo $alert_class; ?>" id="dokan_v_id_feedback">
                        <?php /* translators: %s: id_status */ ?>
                        <?php echo sprintf( __( 'Your ID verification request is %s', 'dokan' ), \WeDevs\DokanPro\Modules\VendorVerification\Module::get_translated_status( $id_status ) ); ?>
                    </div>
                <?php } ?>

                <?php
                    // phpcs:ignore
                    if ( $id_status == 'rejected' || $id_status == '' )
                        $id_btn_class = '';
                    else
                        $id_btn_class = 'dokan-hide';
                ?>

                <button class="dokan-btn dokan-btn-theme dokan-v-start-btn <?php echo esc_attr( $id_btn_class ); ?>" id="dokan_v_id_click"><?php esc_html_e( 'Start Verification', 'dokan' ); ?></button>


                <div class="dokan_v_id_info_box dokan-hide">
                        <form method="post" id="dokan-verify-id-form"  action="" class="dokan-form-horizontal">
                            <h4><?php esc_html_e( 'Select ID type :', 'dokan' ); ?></h4>

                            <div class="document-box">
                                <label class="radio">
                                    <input type="radio" name="dokan_v_id_type" value="passport" <?php checked( $id_type, 'passport' ); ?> >
                                    <?php esc_html_e( 'Passport', 'dokan' ); ?>
                                </label>

                                <label class="radio">
                                    <input type="radio" name="dokan_v_id_type" value="national_id" <?php checked( $id_type, 'national_id' ); ?>>
                                    <?php esc_html_e( 'National ID Card', 'dokan' ); ?>
                                </label>

                                <label class="radio">
                                    <input type="radio" name="dokan_v_id_type" value="driving_license" <?php checked( $id_type, 'driving_license' ); ?>>
                                    <?php esc_html_e( 'Driving License', 'dokan' ); ?>
                                </label>

                                <div class="dokan-form-group dokan-verify-photo-id">
                                    <div class="dokan-w5 dokan-gravatar">
                                        <div class="gravatar-wrap<?php echo $gravatar ? '' : ' dokan-hide'; ?>">
                                            <?php $gravatar_url = $gravatar ? wp_get_attachment_url( $gravatar ) : ''; ?>
                                            <input type="hidden" class="dokan-file-field" value="<?php echo $gravatar; ?>" name="dokan_gravatar">
                                            <img class="dokan-gravatar-img" src="<?php echo esc_url( $gravatar_url ); ?>">
                                            <a class="dokan-close dokan-remove-gravatar-image">&times;</a>
                                        </div>

                                        <div class="gravatar-button-area<?php echo $gravatar ? ' dokan-hide' : ''; ?>">
                                            <a href="#" class="dokan-gravatar-drag dokan-btn dokan-btn-default"><i class="fas fa-cloud-upload-alt"></i> <?php esc_html_e( 'Upload Photo', 'dokan' ); ?></a>
                                        </div>

                                        <?php do_action( 'dokan_before_id_verification_submit_button', $seller_profile ); ?>
                                        <?php wp_nonce_field( 'dokan_verify_action', 'dokan_verify_action_nonce' ); ?>
                                        <input type="submit" id='dokan_v_id_submit' class="dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Submit', 'dokan' ); ?>">
                                        <input type="button" id='dokan_v_id_cancel_form' class="dokan-btn dokan-btn-theme dokan-v-cancel-btn" value="<?php esc_attr_e( 'Cancel', 'dokan' ); ?>">

                                    </div>
                                </div>
                            </div>
                        </form>
                </div>
                <?php
                if ( $id_status !== 'pending' ) {
                    $cancel_btn_class = 'dokan-hide';
                }
                ?>
                <button class="dokan-btn dokan-btn-theme <?php echo esc_attr( $cancel_btn_class ); ?>" id="dokan_v_id_cancel"><?php esc_html_e( 'Cancel Request', 'dokan' ); ?></button>
            </div>
    </div>
    <!-- =================================================== -->
    <!-- ID Verification Content End -->
    <!-- =================================================== -->

    <!-- =================================================== -->
    <!-- Phone Verification Content Start -->
    <!-- =================================================== -->
    <?php
    $active_gateway     = dokan_get_option( 'active_gateway', 'dokan_verification_sms_gateways' );
    $active_gw_username = trim( dokan_get_option( $active_gateway . '_username', 'dokan_verification_sms_gateways' ) );
    $active_gw_pass     = trim( dokan_get_option( $active_gateway . '_pass', 'dokan_verification_sms_gateways' ) );

    if ( ! empty( $active_gw_username ) || ! empty( $active_gw_pass ) ) {
        ?>
        <div class="dokan-panel dokan-panel-default dokan_v_phone">
            <div class="dokan-panel-heading">
                <strong><?php esc_attr_e( 'Phone Verification', 'dokan' ); ?></strong>
            </div>

            <div class="dokan-panel-body">
                <div class="" id="d_v_phone_feedback"></div>

                <?php if ( $phone_status !== 'verified' ) { ?>

                    <div class="dokan_v_phone_box">
                        <form method="post" id="dokan-verify-phone-form"  action="" class="dokan-form-horizontal">
                            <?php wp_nonce_field( 'dokan_verify_action', 'dokan_verify_action_nonce' ); ?>
                            <div class="dokan-form-group">
                                <label class="dokan-w3 dokan-control-label" for="phone"><?php esc_attr_e( 'Phone No', 'dokan' ); ?></label>
                                <div class="dokan-w5">
                                    <input id="phone" value="<?php echo $phone; ?>" name="phone" placeholder="+123456.." class="dokan-form-control input-md" type="text"><br>
                                    <?php if ( 'nexmo' === $active_gateway ) { ?>
                                    <div class="dokan-alert dokan-alert-warning">
                                        <?php _e( 'When entering US phone numbers, exclude the plus sign from the phone number.', 'dokan' ); ?>
                                    </div>
                                    <?php } ?>
                                    <?php do_action( 'dokan_before_phone_verification_submit_button', $seller_profile ); ?>
                                    <div class="dokan-form-group">
                                        <input type="submit" id='dokan_v_phone_submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Submit', 'dokan' ); ?>">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="dokan_v_phone_code_box dokan-hide">
                        <form method="post" id="dokan-v-phone-code-form"  action="" class="dokan-form-horizontal">
                            <?php wp_nonce_field( 'dokan_verify_action', 'dokan_verify_action_nonce' ); ?>

                            <div class="dokan-form-group">
                                <label class="dokan-w3 dokan-control-label" for="sms_code"><?php esc_attr_e( 'SMS code', 'dokan' ); ?></label>
                                <div class="dokan-w5">
                                    <input id="sms_code" value="" name="sms_code" placeholder="" class="dokan-form-control input-md" type="text">
                                </div>
                            </div>

                            <div class="dokan-form-group">
                                <input type="submit" id='dokan_v_code_submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Submit', 'dokan' ); ?>">
                            </div>
                        </form>
                    </div>

                <?php } elseif ( 'verified' === $phone_status ) { ?>

                    <div class="dokan-alert dokan-alert-success">
                        <p><?php esc_attr_e( 'Your Verified Phone number is : ', 'dokan' ); echo '<b>' . $phone_no . '</b>'; ?></p>
                    </div>

                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <!-- =================================================== -->
    <!-- Phone Verification Content End -->
    <!-- =================================================== -->

    <!-- =================================================== -->
    <!-- Address Verification Content Start -->
    <!-- =================================================== -->
    <?php
    // Extract Variables
    if ( ! isset( $seller_profile['store_address'] ) ) {
        $seller_profile['store_address']['street_1']      = '';
        $seller_profile['store_address']['street_2']      = '';
        $seller_profile['store_address']['city']    = '';
        $seller_profile['store_address']['zip']     = '';
        $seller_profile['store_address']['country'] = '';
        $seller_profile['store_address']['state']   = '';
        $seller_profile['store_address']['dokan-v-state']   = array(
            '' => array( '' ),
        );
    }

    if ( isset( $seller_profile['dokan_verification']['info']['store_address'] ) ) {
        $seller_profile['store_address'] = $seller_profile['dokan_verification']['info']['store_address'];
    }

    extract( $seller_profile['store_address'] );

    $dv_street_1 = isset( $street_1 ) ? $street_1 : '';
    $dv_street_2 = isset( $street_2 ) ? $street_2 : '';
    $dv_city     = isset( $store_city ) ? $store_city : '';
    $dv_zip      = isset( $store_zip ) ? $store_zip : '';
    $country_id  = isset( $store_country ) ? $store_country : '';

    $state_id = isset( $dokan_v_state[ $country_id ][0] ) ? isset( $dokan_v_state[ $country_id ][0] ) : '';

    ?>

    <div class="dokan-panel dokan-panel-default">
        <div class="dokan-panel-heading">
            <strong><?php esc_attr_e( 'Address Verification', 'dokan' ); ?></strong>
        </div>

        <div class="dokan-panel-body">
            <?php
            $alert_class = 'dokan-hide';
            $cancel_btn_class = '';
            if ( $address_status !== '' ) {
                $alert_class = 'dokan-alert-info';

                switch ( $address_status ) {
                    case 'approved':
                        $alert_class = 'dokan-alert-success';
                        $cancel_btn_class = 'dokan-hide';
                        break;
                    case 'rejected':
                        $alert_class = 'dokan-alert-danger';
                        $cancel_btn_class = 'dokan-hide';
                        break;
                    case 'pending':
                        $alert_class = 'dokan-alert-warning';
                        break;
                }
            } ?>


            <div class="dokan-alert <?php echo $alert_class; ?>" id="d_v_address_feedback">
                <?php /* translators: %s: address status */ ?>
                <?php echo sprintf( __( 'Your Address verification request is %s', 'dokan' ), \WeDevs\DokanPro\Modules\VendorVerification\Module::get_translated_status( $address_status ) ); ?>
            </div>

            <?php
            $addrs_btn_class = 'dokan-hide';

            if ( 'rejected' === $address_status || '' === $address_status ) {
                $addrs_btn_class = '';
            }
            ?>
            <?php
            $btn_name = __( 'Start Verification', 'dokan' );
            if ( 'approved' === $address_status ) {

                $btn_name = __( 'Change Address', 'dokan' );
                $addrs_btn_class = '';

            } ?>
            <button class="dokan-btn dokan-btn-theme dokan-v-start-btn <?php echo esc_attr( $addrs_btn_class ); ?>" id="dokan_v_address_click"><?php echo $btn_name; ?></button>

                <div class="dokan_v_address_box dokan-hide">
                    <form method="post" id="dokan-verify-address-form"  action="" class="dokan-form-horizontal">
                        <?php dokan_seller_address_fields( false, true ); ?>
                        <?php do_action( 'dokan_before_address_verification_submit_button', $seller_profile ); ?>
                        <div class="dokan-form-group">
                            <input type="submit" id='dokan_v_address_submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Submit', 'dokan' ); ?>">
                            <input type="button" id='dokan_v_address_cancel' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Cancel', 'dokan' ); ?>">
                            <input type="hidden" name="action" value="dokan_update_verify_info_insert_address" />
                            <?php wp_nonce_field( 'dokan_verify_action_address_form', 'dokan_verify_action_address_form_nonce' ); ?>
                        </div>
                    </form>
                </div>
            <?php

            if ( $address_status !== 'pending' ) {
                $cancel_btn_class = 'dokan-hide';
            }
            ?>
            <button class="dokan-btn dokan-btn-theme <?php echo esc_attr( $cancel_btn_class ); ?>" id="dokan_v_address_cancel"><?php esc_attr_e( 'Cancel Request', 'dokan' ); ?></button>

        </div>
    </div>
    <!-- =================================================== -->
    <!-- Address Verification Content End -->
    <!-- =================================================== -->

    <!-- =================================================== -->
    <!-- Social Profiles Verification Content Start -->
    <!-- =================================================== -->
    <div class="dokan-panel dokan-panel-default">
        <div class="dokan-panel-heading clickable">
            <strong><?php esc_attr_e( 'Social Profiles', 'dokan' ); ?></strong>
        </div>

        <div class="dokan-panel-body">
            <div class="dokan-verify-links">
                <?php

                $configured_providers = array();

                //facebook config from admin
                $fb_id     = dokan_get_option( 'fb_app_id', 'dokan_verification' );
                $fb_secret = dokan_get_option( 'fb_app_secret', 'dokan_verification' );
                if ( $fb_id !== '' && $fb_secret !== '' ) {
                    $configured_providers [] = 'facebook';
                }
                //google config from admin
                $g_id     = dokan_get_option( 'google_app_id', 'dokan_verification' );
                $g_secret = dokan_get_option( 'google_app_secret', 'dokan_verification' );
                if ( $g_id !== '' && $g_secret !== '' ) {
                    $configured_providers [] = 'google';
                }
                //linkedin config from admin
                $l_id     = dokan_get_option( 'linkedin_app_id', 'dokan_verification' );
                $l_secret = dokan_get_option( 'linkedin_app_secret', 'dokan_verification' );
                if ( $l_id !== '' && $l_secret !== '' ) {
                    $configured_providers [] = 'linkedin';
                }
                //Twitter config from admin
                $twitter_id     = dokan_get_option( 'twitter_app_id', 'dokan_verification' );
                $twitter_secret = dokan_get_option( 'twitter_app_secret', 'dokan_verification' );
                if ( $twitter_id !== '' && $twitter_secret !== '' ) {
                    $configured_providers [] = 'twitter';
                }


                /**
                 * Filter the list of Providers connect links to display
                 *
                 * @since 1.0.0
                 *
                 * @param array $providers
                 */
                $providers = apply_filters( 'dokan_verify_provider_list', $configured_providers );
                $provider  = '';
                if ( ! empty( $providers ) ) {
                    foreach ( $providers as $provider ) {
                        $provider_info = '';

                        if ( isset( $seller_profile['dokan_verification'][ $provider ] ) ) {
                            $provider_info = $seller_profile['dokan_verification'][ $provider ];
                        }
                        ?>
                        <?php if ( ! isset( $provider_info ) || '' === $provider_info ) { ?>
                            <a href="<?php echo add_query_arg( array( 'dokan_auth' => $provider ), dokan_get_navigation_url( 'settings/verification' ) ); ?>">
                                <button class="dokan-btn dokan-verify-connect-btn">
                                    <?php
                                    esc_html_e( 'Connect ', 'dokan' );
                                    echo ucwords( $provider );
                                    ?>
                                </button>
                            </a>
                        <?php } else { ?>
                            <div class="dokan-va-row">
                                <div class="dokan-w12">
                                    <div class=""><h2><u><?php echo ucwords( $provider ); ?></u></h2></div>
                                    <div class="">
                                        <div class="dokan-w4"><img src="<?php echo $provider_info['photoURL']; ?>"/></div>
                                        <div class="dokan-w5"><a target="_blank" href="<?php echo $provider_info['profileURL']; ?>"><?php echo $provider_info['displayName']; ?></a></div>
                                        <div class="dokan-w5"><?php echo $provider_info['email']; ?></div>
                                    </div>

                                    <div class="dokan_verify_dc_button">
                                        <a href="<?php echo add_query_arg( array( 'dokan_auth_dc' => $provider ), dokan_get_navigation_url( 'settings/verification' ) ); ?>">
                                            <button class="dokan-btn dokan-btn-block">
                                                <?php
                                                esc_html_e( 'Disconnect ', 'dokan' );
                                                echo ucwords( $provider );
                                                ?>
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                <?php } else { ?>
                    <div class="dokan-alert dokan-alert-info">
                            <?php echo __( 'No Social App is configured by website Admin', 'dokan' ); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- =================================================== -->
    <!-- Social Profiles Verification Content End -->
    <!-- =================================================== -->

    <!-- =================================================== -->
    <!-- Company Verification Content Start -->
    <!-- =================================================== -->
    <?php
        $seller_profile          = dokan_get_store_info( $current_user );

        $company_v_status = isset( $seller_profile['dokan_verification']['info']['company_v_status'] ) ? $seller_profile['dokan_verification']['info']['company_v_status'] : '';

    ?>
    <div class="dokan-panel dokan-panel-default">
        <div class="dokan-panel-heading">
            <strong><?php esc_html_e( 'Company Verification', 'dokan' ); ?></strong>
        </div>

        <div class="dokan-panel-body">
            <?php
            $alert_class = 'dokan-hide';
            $cancel_btn_class = '';
            if ( '' !== $company_v_status ) {
                $alert_class = 'dokan-alert-info';

                switch ( $company_v_status ) {
                    case 'approved':
                        $alert_class = 'dokan-alert-success';
                        $cancel_btn_class = 'dokan-hide';
                        break;
                    case 'rejected':
                        $alert_class = 'dokan-alert-danger';
                        $cancel_btn_class = 'dokan-hide';
                        break;
                    case 'pending':
                        $alert_class = 'dokan-alert-warning';
                        break;
                }
            } ?>


            <div class="dokan-alert <?php echo $alert_class; ?>" id="d_v_company_feedback">
                <?php /* translators: %s: company_v_status */ ?>
                <?php echo sprintf( __( 'Your company verification request is %s', 'dokan' ), $company_v_status ); ?>
            </div>

            <?php
            $company_btn_class = 'dokan-hide';

            if ( 'rejected' === $company_v_status || '' === $company_v_status ) {
                $company_btn_class = '';
            }
            ?>
            <?php
            $btn_name = __( 'Start Verification', 'dokan' );
            if ( 'approved' === $company_v_status ) {

                $btn_name = __( 'Change company details', 'dokan' );
                $company_btn_class = '';

            } ?>
            <button class="dokan-btn dokan-btn-theme dokan-v-start-btn <?php echo esc_attr( $company_btn_class ); ?>" id="dokan_v_company_click"><?php echo $btn_name; ?></button>

                <div class="dokan_v_company_box dokan-hide">
                    <form method="post" id="dokan-verify-company-form"  action="" class="dokan-form-horizontal">

                        <div class="dokan-form-group">
                            <label class="dokan-w3 dokan-control-label" for="setting_company_files"><?php esc_html_e( 'Files', 'dokan' ); ?></label>
                            <div class="dokan-w5 dokan-text-left">
                                <div class="dokan-form-control">
                                    <div class="dokan-vendor-company-files">
                                        <?php
                                            if ( isset( $seller_profile['company_verification_files'] ) && is_array( $seller_profile['company_verification_files'] ) ) :
                                                foreach ( $seller_profile['company_verification_files'] as $key => $file ) :
                                                    $customId = 'dokan-vendor-company-file-' . absint( $file );
                                        ?>
                                            <div class="dokan-vendor-company-file-item" id="<?php echo $customId; ?>">
                                                <a href="<?php echo wp_get_attachment_url( $file ) ?>" target="_blank" ><?php echo get_the_title( $file ) ?></a>
                                                <a href="#" onclick="companyVerificationRemoveList(event)" data-attachment_id="<?php echo $customId; ?>" class="dokan-btn disconnect dokan-btn-danger"><i class="fas fa-times" data-attachment_id="<?php echo $customId; ?>"></i></a>
                                                <input type="hidden" name="vendor_verification_files_ids[]" value="<?php echo esc_attr( $file ); ?>" />
                                            </div>
                                        <?php
                                                endforeach;
                                            endif;
                                        ?>
                                    </div>
                                    <a style="width: 100%;" href="#" class="dokan-files-drag dokan-btn dokan-btn-default"><i class="fas fa-cloud-upload-alt"></i> <?php esc_html_e( 'Upload Files', 'dokan' ); ?></a>
                                </div>
                            </div>
                        </div>
                        <?php do_action( 'dokan_before_company_verification_submit_button', $seller_profile ); ?>
                        <div class="dokan-form-group">
                            <label class="dokan-w3 dokan-control-label" for="setting_bank_iban">&nbsp;</label>
                            <div class="dokan-w5 dokan-text-left">
                            <input type="submit" id='dokan_v_company_submit' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Submit', 'dokan' ); ?>">
                            <input type="button" id='dokan_v_company_cancel' class="dokan-left dokan-btn dokan-btn-theme" value="<?php esc_attr_e( 'Cancel', 'dokan' ); ?>">
                            <input type="hidden" name="action" value="dokan_update_verify_info_insert_company" />
                            <?php wp_nonce_field( 'dokan_verify_action_company_form', 'dokan_verify_action_company_form_nonce' ); ?>
                            </div>
                        </div>
                    </form>
                </div>
            <?php
                if ( 'pending' !== $company_v_status ) {
                    $cancel_btn_class = 'dokan-hide';
                }
            ?>
            <button class="dokan-btn dokan-btn-theme <?php echo esc_attr( $cancel_btn_class ); ?>" id="dokan_v_company_cancel"><?php esc_html_e( 'Cancel Request', 'dokan' ); ?></button>

        </div>
    </div>
    <!-- =================================================== -->
    <!-- Company Verification Content End -->
    <!-- =================================================== -->
</div>
