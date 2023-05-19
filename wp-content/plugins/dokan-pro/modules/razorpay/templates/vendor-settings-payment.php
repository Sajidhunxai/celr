<?php
use WeDevs\DokanPro\Modules\Razorpay\Helper;
?>

<div class="dokan-razorpay-container">
    <?php if ( $is_seller_enabled ) : ?>
        <p class="dokan-text-left">
            <a class="dokan-btn dokan-btn-danger dokan-btn-theme"
                href="<?php echo esc_url_raw( $disconnect_url ); ?>"
            ><?php esc_html_e( 'Disconnect', 'dokan' ); ?></a>
        </p>
        <div class="dokan-alert dokan-alert-success dokan-text-left">
            <?php esc_html_e( 'Merchant ID: ', 'dokan' ); ?>
            <?php echo '<strong>' . $merchant_id . '</strong>'; ?>
        </div>
        <div class="dokan-alert dokan-alert-success dokan-text-left">
            <?php esc_html_e( 'Your account is connected with Razorpay. You can disconnect your account using the above button.', 'dokan' ); ?>
        </div>
    <?php else : ?>
        <div class="dokan-form-group">
            <div class="dokan-w12">
                <div class="dokan-alert dokan-alert-warning dokan-text-left">
                    <p><?php esc_html_e( 'Your account is not connected with Razorpay. Please click Sign Up button to connect your Razorpay account.', 'dokan' ); ?></p>

                    <div class="dokan-form-horizontal">
                        <br>
                        <a href="#" class="button button-primary vendor_razorpay_connect">
                            <?php esc_html_e( 'Sign Up', 'dokan' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="razorpay_connect_button"></div>

        <!-- Load Razorpay Vendor Register template -->
        <?php
            dokan_get_template_part(
                'vendor-settings-account-register',
                false,
                [
                    'is_razorpay'             => true,
                    'ajax_url'                => $ajax_url,
                    'bank_account_types'      => Helper::get_bank_account_types(),
                    'razorpay_business_types' => Helper::get_razorpay_business_types(),
                ]
            );
        ?>

    <?php endif; ?>
</div>

<script type="text/javascript">
    ;(function($, document) {
        const dokan_razorpay_connect = {
            clicked: false,

            /**
             * Show razorpay linked account register popup.
             */
            showPopup() {
                const self                 = this,
                razorpayVendorRegisterForm = $("#tmpl-dokan-razorpay-vendor-account-register");

                $.magnificPopup.open({
                    items: {
                        src: razorpayVendorRegisterForm.html(),
                        type: 'inline'
                    }
                });
            },

            /**
             * Sign up razorpay linked account for this vendor.
             */
            signUpVendorLinkedAccount: function (e) {
                e.preventDefault();

                const self = $(this),
                    form   = self.closest('form#dokan-razorpay-vendor-register-form');

                form.find( 'span.dokan-razorpay-connect-spinner' ).css( 'display', 'inline-block' );
                self.attr( 'disabled', 'disabled' );

                $.post( wp.ajax.settings.url, form.serialize(), function( resp ) {
                    form.find( 'span.dokan-razorpay-connect-spinner' ).css( 'display', 'none' );

                    if ( resp.success ) {
                        self.removeAttr( 'disabled' );
                        $.magnificPopup.close();
                        dokan_sweetalert( resp.data.message, { icon: 'success', } );
                        window.location.href = resp.data.url;
                    } else {
                        dokan_sweetalert( resp.data.message, { icon: 'error', } );
                        self.removeAttr( 'disabled' );
                        $( 'span.dokan-show-add-product-error' ).html( resp.data );
                    }
                }).catch(error => {
                    form.find( 'span.dokan-razorpay-connect-spinner' ).css( 'display', 'none' );
                    self.removeAttr( 'disabled' );
                });
            },

            /**
             * Existing razorpay linked account connect for this vendor.
             */
            enableExistsingUserFields: function() {
                $( '#dokan_razorpay_account_id' ).toggleClass( 'dokan-hide' );
                $( '#dokan-razorpay-new-connect' ).toggleClass( 'dokan-hide' );
                $( '.dokan-existing-account-info' ).toggleClass( 'dokan-hide' );
            },

            init: function () {
                const self = this;

                // Open Razorpay Linked Account Popup
                $( '.vendor_razorpay_connect' ).on( 'click', function( e ) {
                    e.preventDefault();
                    self.showPopup();
                });

                $( 'body' ).on( 'click', '#dokan_razorpay_vendor_register_button', self.signUpVendorLinkedAccount );
                $( 'body' ).on( 'click', '#dokan_razorpay_existing_user_chekbox', self.enableExistsingUserFields );
            }
        };

        dokan_razorpay_connect.init();
    })(jQuery, document);
</script>
