jQuery( function( $ ) {
    'use strict';

    try {
        var stripe = Stripe( dokan_stripe_connect_params.key );
        var elements = stripe.elements();
        var card     = elements.create( 'card', { hidePostalCode: true } );
        var iban     = elements.create( 'iban' );
    } catch( error ) {
        console.log( error );
        return;
    }

    var dokan_stripe_form = {
        init: function() {
            // Initialize tokenization script if on change payment method page and pay for order page.
            if ( 'yes' === dokan_stripe_connect_params.is_change_payment_page || 'yes' === dokan_stripe_connect_params.is_pay_for_order_page ) {
                $( document.body ).trigger( 'wc-credit-card-form-init' );
            }

            // checkout page
            if ( $( 'form.woocommerce-checkout' ).length ) {
                this.form = $( 'form.woocommerce-checkout' );
            }

            // pay order page
            if ( $( 'form#order_review' ).length ) {
                this.form = $( 'form#order_review' );
            }

            // add payment method page
            if ( $( 'form#add_payment_method' ).length ) {
                this.form = $( 'form#add_payment_method' );
            }

            $( document ).on( 'stripeError', this.onError );
            $( document ).on( 'checkout_error', this.reset );

            $( 'form#order_review, form#add_payment_method' ).on( 'submit', this.onSubmit );
            $( 'form.woocommerce-checkout' ).on( 'change', this.reset );
            $( 'form.woocommerce-checkout' ).on( 'checkout_place_order_dokan-stripe-connect', this.onSubmit );

            // Subscription early renewals modal.
            $( '#early_renewal_modal_submit' ).on( 'click', dokan_stripe_form.onEarlyRenewalSubmit );

            dokan_stripe_form.createElements();

            // Listen for hash changes in order to handle payment intents
            window.addEventListener( 'hashchange', dokan_stripe_form.onHashChange );
            dokan_stripe_form.maybeConfirmIntent();
        },

        createElements: function() {
            /**
             * Only in checkout page we need to delay the mounting of the
             * card as some AJAX process needs to happen before we do.
             */
            if ( 'yes' === dokan_stripe_connect_params.is_checkout ) {
                $( document.body ).on( 'updated_checkout', function() {
                    // Don't re-mount if already mounted in DOM.
                    if ( $( '#dokan-stripe-card-element' ).children().length ) {
                        return;
                    }

                    dokan_stripe_form.mountElements();
                } );
            } else if ( $( 'form#add_payment_method' ).length || $( 'form#order_review' ).length ) {
                dokan_stripe_form.mountElements();
            }
        },

        mountElements: function() {
            if ( ! $( '#dokan-stripe-card-element' ).length ) {
                return;
            }

            card.mount( '#dokan-stripe-card-element' );
        },

        onSubmit: function( event ) {
            if ( ! dokan_stripe_form.isStripeChosen() ) {
                return true;
            }
            // If a source is already in place, submit the form as usual.
            if ( dokan_stripe_form.isStripeSaveCardChosen() || dokan_stripe_form.hasSource() ) {
                return true;
            }

            dokan_stripe_form.block();
            dokan_stripe_form.createSource();

            return false;
        },

        createSource: function() {
            const details = dokan_stripe_form.getOwnerDetails();
            return stripe.createSource( card, details )
                .then( dokan_stripe_form.sourceResponse );
        },

        getOwnerDetails: function() {
            var first_name = $( '#billing_first_name' ).length ? $( '#billing_first_name' ).val() : dokan_stripe_connect_params.billing_first_name,
                last_name  = $( '#billing_last_name' ).length ? $( '#billing_last_name' ).val() : dokan_stripe_connect_params.billing_last_name,
                owner      = { name: '', address: {}, email: '', phone: '' };

            owner.name = first_name;

            if ( first_name && last_name ) {
                owner.name = first_name + ' ' + last_name;
            } else {
                owner.name = $( '#dokan-stripe-payment-data' ).data( 'full-name' );
            }

            owner.email = $( '#billing_email' ).val();
            owner.phone = $( '#billing_phone' ).val();

            /* Stripe does not like empty string values so
             * we need to remove the parameter if we're not
             * passing any value.
             */
            if ( typeof owner.phone === 'undefined' || 0 >= owner.phone.length ) {
                delete owner.phone;
            }

            if ( typeof owner.email === 'undefined' || 0 >= owner.email.length ) {
                if ( $( '#dokan-stripe-payment-data' ).data( 'email' ).length ) {
                    owner.email = $( '#dokan-stripe-payment-data' ).data( 'email' );
                } else {
                    delete owner.email;
                }
            }

            if ( typeof owner.name === 'undefined' || 0 >= owner.name.length ) {
                delete owner.name;
            }

            owner.address.line1       = $( '#billing_address_1' ).val() || dokan_stripe_connect_params.billing_address_1;
            owner.address.line2       = $( '#billing_address_2' ).val() || dokan_stripe_connect_params.billing_address_2;
            owner.address.state       = $( '#billing_state' ).val()     || dokan_stripe_connect_params.billing_state;
            owner.address.city        = $( '#billing_city' ).val()      || dokan_stripe_connect_params.billing_city;
            owner.address.postal_code = $( '#billing_postcode' ).val()  || dokan_stripe_connect_params.billing_postcode;
            owner.address.country     = $( '#billing_country' ).val()   || dokan_stripe_connect_params.billing_country;

            return {
                owner: owner,
            };
        },


        /**
         * Displays stripe-related errors.
         *
         * @param {Event}  e      The jQuery event.
         * @param {Object} result The result of Stripe call.
         */
        onError: function( e, result ) {
            let message = result.error.message;
            let errorContainer = dokan_stripe_form.form.closest( '.woocommerce' ).find( '.woocommerce-notices-wrapper' );

            // there maybe multiple `woocommerce-notices-wrapper`. So get the first one
            if ( errorContainer.length ) {
                errorContainer = errorContainer.get( 0 );
            }

            // Notify users that the email is invalid.
            if ( 'email_invalid' === result.error.code ) {
                message = dokan_stripe_connect_params.email_invalid;
            } else if (
                /*
                 * Customers do not need to know the specifics of the below type of errors
                 * therefore return a generic localizable error message.
                 */
                'invalid_request_error' === result.error.type ||
                'api_connection_error'  === result.error.type ||
                'api_error'             === result.error.type ||
                'authentication_error'  === result.error.type ||
                'rate_limit_error'      === result.error.type
            ) {
                message = dokan_stripe_connect_params.invalid_request_error;
            }

            dokan_stripe_form.reset();

            $( '.woocommerce-NoticeGroup-checkout' ).remove();
            console.log( result.error.message ); // Leave for troubleshooting.
            $( errorContainer ).html( '<ul class="woocommerce_error woocommerce-error dokan-stripe-error"><li /></ul>' );
            $( errorContainer ).find( 'li' ).text( message ); // Prevent XSS

            if ( $( '.dokan-stripe-error' ).length ) {
                $( 'html, body' ).animate({
                    scrollTop: ( $( '.dokan-stripe-error' ).offset().top - 200 )
                }, 200 );
            }

            dokan_stripe_form.unblock();
        },

        /**
         * Checks if a source ID is present as a hidden input.
         * Only used when SEPA Direct Debit is chosen.
         *
         * @return {boolean}
         */
        hasSource: function() {
            return 0 < $( 'input.stripe-source' ).length;
        },

        /**
         * Check to see if Stripe in general is being used for checkout.
         *
         * @return {boolean}
         */
        isStripeChosen: function() {
            return $( '#payment_method_dokan-stripe-connect' ).is( ':checked' )
                || ( $( '#payment_method_dokan-stripe-connect' ).is( ':checked' ) && 'new' === $( 'input[name="wc-dokan-stripe-connect-payment-token"]:checked' ).val() );
        },

        /**
         * Check to see if Stripe in general is being used for checkout.
         *
         * @return {boolean}
         */
        isStripeSaveCardChosen: function() {
            return (
                $( '#payment_method_dokan-stripe-connect' ).is( ':checked' )
                && $( 'input[name="wc-dokan-stripe-connect-payment-token"]' ).is( ':checked' )
                && 'new' !== $( 'input[name="wc-dokan-stripe-connect-payment-token"]:checked' ).val()
            );
        },

        /**
         * Handles responses, based on source object.
         *
         * @param {Object} response The `stripe.createSource` response.
         */
        sourceResponse: function( response ) {
            if ( response.error ) {
                return $( document.body ).trigger( 'stripeError', response );
            }

            var subscriptionProductId = $( '#dokan-subscription-product-id' ).val();

            if ( subscriptionProductId && subscriptionProductId.length ) {
                return dokan_stripe_form.handleSource( response.source, subscriptionProductId );
            }

            dokan_stripe_form.reset();

            dokan_stripe_form.form.append(
                $( '<input type="hidden" />' )
                    .addClass( 'stripe-source' )
                    .attr( 'name', 'stripe_source' )
                    .val( response.source.id )
            );

            if ( $( 'form#add_payment_method' ).length || $( '#wc-stripe-change-payment-method' ).length ) {
                dokan_stripe_form.sourceSetup( response );
                return;
            }

            dokan_stripe_form.form.trigger( 'submit' );
        },

        handleSource( source, subscriptionProductId ) {
            $.ajax( {
                url: dokan.ajaxurl,
                method: 'POST',
                data: {
                    action: 'dokan_send_source',
                    stripe_source: source.id,
                    nonce: dokan.nonce,
                    product_id: subscriptionProductId
                }
            } )
            .done( function( response ) {
                if ( typeof response !== 'undefined'
                    && response.data
                    && response.data.code
                    && 'subscription_not_created' === response.data.code
                    ) {
                    let errorContainer = dokan_stripe_form.form.closest( '.woocommerce' ).find( '.woocommerce-notices-wrapper' );

                    // there maybe multiple `woocommerce-notices-wrapper`. So get the first one
                    if ( errorContainer.length ) {
                        errorContainer = errorContainer.get( 0 );
                    }

                    dokan_stripe_form.reset();

                    $( '.woocommerce-NoticeGroup-checkout' ).remove();
                    console.log( response ); // Leave for troubleshooting.
                    $( errorContainer ).html( '<ul class="woocommerce_error woocommerce-error dokan-stripe-error"><li /></ul>' );
                    $( errorContainer ).find( 'li' ).text( response.data.message ); // Prevent XSS

                    if ( $( '.dokan-stripe-error' ).length ) {
                        $( 'html, body' ).animate({
                            scrollTop: ( $( '.dokan-stripe-error' ).offset().top - 200 )
                        }, 200 );
                    }

                    dokan_stripe_form.unblock();
                    return;
                }

                if ( typeof response !== 'undefined'
                    && response.status
                    && 'trialing' === response.status
                    ) {

                    return dokan_stripe_form.resetAndSubmit( source );
                }

                if ( typeof response !== 'undefined'
                    && response.status
                    && 'active' === response.status
                    ) {
                    return dokan_stripe_form.resetAndSubmit( source );
                }

                if ( typeof response !== 'undefined'
                    && response.latest_invoice
                    && response.latest_invoice.payment_intent
                    && response.latest_invoice.payment_intent.client_secret
                    ) {
                    var clientSecret = response.latest_invoice.payment_intent.client_secret;
                    return dokan_stripe_form.confirmCardPayment( clientSecret, source );
                }
            } );
        },

        confirmCardPayment: function( clientSecret, source ) {
            stripe.confirmCardPayment( clientSecret )
            .then( function( response ) {
                if ( response.paymentIntent && 'succeeded' === response.paymentIntent.status ) {
                    dokan_stripe_form.resetAndSubmit( source );
                }
            } );
        },

        resetAndSubmit: function( source ) {
            dokan_stripe_form.reset();
            dokan_stripe_form.form.append(
                $( '<input type="hidden" />' )
                    .addClass( 'stripe-source' )
                    .attr( 'name', 'stripe_source' )
                    .val( source.id )
            );

            dokan_stripe_form.form.submit();
        },

        /**
         * Removes all Stripe errors and hidden fields with IDs from the form.
         */
        reset: function() {
            $( '.dokan-stripe-error, .stripe-source' ).remove();
        },

        /**
         * Check whether a mobile device is being used.
         *
         * @return {boolean}
         */
        isMobile: function() {
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test( navigator.userAgent ) ) {
                return true;
            }

            return false;
        },

        /**
         * Blocks payment forms with an overlay while being submitted.
         */
        block: function() {
            if ( ! dokan_stripe_form.isMobile() ) {
                dokan_stripe_form.form.block( {
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                } );
            }
        },

        /**
         * Removes overlays from payment forms.
         */
        unblock: function() {
            dokan_stripe_form.form && dokan_stripe_form.form.unblock();
        },

        /**
         * Handles changes in the hash in order to show a modal for PaymentIntent/SetupIntent confirmations.
         *
         * Listens for `hashchange` events and checks for a hash in the following format:
         * #confirm-pi-<intentClientSecret>:<successRedirectURL>
         *
         * If such a hash appears, the partials will be used to call `stripe.handleCardPayment`
         * in order to allow customers to confirm an 3DS/SCA authorization, or stripe.handleCardSetup if
         * what needs to be confirmed is a SetupIntent.
         *
         * Those redirects/hashes are generated in `WC_Gateway_Stripe::process_payment`.
         */
        onHashChange: function() {
            var partials = window.location.hash.match( /^#?confirm-(pi|si)-([^:]+):(.+)$/ );

            if ( ! partials || 4 > partials.length ) {
                return;
            }

            var type               = partials[1];
            var intentClientSecret = partials[2];
            var redirectURL        = decodeURIComponent( partials[3] );

            // Cleanup the URL
            window.location.hash = '';

            dokan_stripe_form.openIntentModal( intentClientSecret, redirectURL, false );
        },

        maybeConfirmIntent: function() {
            if ( ! $( '#dokan-stripe-intent-id' ).length || ! $( '#dokan-stripe-intent-return' ).length ) {
                return;
            }

            var intentSecret = $( '#dokan-stripe-intent-id' ).val();
            var returnURL    = $( '#dokan-stripe-intent-return' ).val();

            dokan_stripe_form.openIntentModal( intentSecret, returnURL, true );
        },

        /**
         * Opens the modal for PaymentIntent authorizations.
         *
         * @param {string}  intentClientSecret The client secret of the intent.
         * @param {string}  redirectURL        The URL to ping on fail or redirect to on success.
         * @param {boolean} alwaysRedirect     If set to true, an immediate redirect will happen no matter the result.
         *                                     If not, an error will be displayed on failure.
         */
        openIntentModal: function( intentClientSecret, redirectURL, alwaysRedirect ) {
            stripe.handleCardPayment( intentClientSecret )
                .then( function( response ) {
                    if ( response.error ) {
                        throw response.error;
                    }

                    var intent = response[ 'paymentIntent' ];
                    if ( 'succeeded' !== intent.status ) {
                        return;
                    }

                    window.location = redirectURL;
                } )
                .catch( function( error ) {
                    if ( alwaysRedirect ) {
                        return window.location = redirectURL;
                    }

                    $( document.body ).trigger( 'stripeError', { error: error } );
                    dokan_stripe_form.form && dokan_stripe_form.form.removeClass( 'processing' );

                    // Report back to the server.
                    $.get( redirectURL + '&is_ajax' );
                } );
        },

        /**
         * Prevents the standard behavior of the "Renew Now" button in the
         * early renewals modal by using AJAX instead of a simple redirect.
         *
         * @param {Event} e The event that occured.
         */
        onEarlyRenewalSubmit: function( e ) {
            e.preventDefault();

            $.ajax( {
                url: $( '#early_renewal_modal_submit' ).attr( 'href' ),
                method: 'get',
                success: function( html ) {
                    var response = JSON.parse( html );
                    console.log( response );

                    if ( response.stripe_sca_required ) {
                        dokan_stripe_form.openIntentModal( response.intent_secret, response.redirect_url, true );
                    } else {
                        window.location = response.redirect_url;
                    }
                },
            } );

            return false;
        },

        /**
         * Authenticate Source if necessary by creating and confirming a SetupIntent.
         *
         * @param {Object} response The `stripe.createSource` response.
         */
        sourceSetup: function( response ) {
            var apiError = {
                error: {
                    type: 'api_connection_error'
                }
            };

            $.ajax( {
                url: dokan_stripe_form.getAjaxURL( 'create_setup_intent'),
                dataType: 'json',
                method: 'POST',
                data: {
                    stripe_source_id: response.source.id,
                    nonce: dokan_stripe_connect_params.add_card_nonce,
                },
                error: function() {
                    console.log( 'here111' );
                    $( document.body ).trigger( 'stripeError', apiError );
                }
            } ).done( function( serverResponse ) {
                if ( 'success' === serverResponse.status ) {
                    if ( $( 'form#add_payment_method' ).length ) {
                        $( dokan_stripe_form.form ).off( 'submit', dokan_stripe_form.form.onSubmit );
                    }
                    dokan_stripe_form.form.trigger( 'submit' );
                    return;
                } else if ( 'requires_action' !== serverResponse.status ) {
                    $( document.body ).trigger( 'stripeError', serverResponse );
                    return;
                }

                stripe.confirmCardSetup( serverResponse.client_secret, { payment_method: response.source.id } )
                    .then( function( result ) {
                        if ( result.error ) {
                            $( document.body ).trigger( 'stripeError', result );
                            return;
                        }

                        if ( $( 'form#add_payment_method' ).length ) {
                            $( dokan_stripe_form.form ).off( 'submit', dokan_stripe_form.form.onSubmit );
                        }
                        dokan_stripe_form.form.trigger( 'submit' );
                    } )
                    .catch( function( err ) {
                        console.log( err );
                        $( document.body ).trigger( 'stripeError', { error: err } );
                    } );
            } );
        },

        getAjaxURL: function( endpoint ) {
            return dokan_stripe_connect_params.ajaxurl
                .toString()
                .replace( '%%endpoint%%', 'dokan_stripe_' + endpoint );
        },

    };

    dokan_stripe_form.init();
});
