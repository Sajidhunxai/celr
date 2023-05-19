<?php
/**
 * When you are adding new version please follow this sequence for changes: New Module, New Feature, New, Improvement, Fix...
 */
$changelog = [
    [
        'version'  => 'Version 3.7.21',
        'released' => '2023-05-10',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => '[ProductAdvertisement] Added sold individually param to true for advertisement base product when creating it, so that quantity can\'t be changed',
                    'description' => '',
                ],
                [
                    'title'       => 'Added Dokan dummy data link under WordPress Dashboard --> Dokan --> Tools menu',
                    'description' => '',
                ],
                [
                    'title'       => '[DistanceRateShipping] Added a new section to check if Distance Matrix API is enabled for Google Map API Key.',
                    'description' => '',
                ],
                [
                    'title'       => '[ColorSchemeCustomizer] Used color set by Color Scheme Customizer Module instead of hardcoded value for Report Abuse modal header color',
                    'description' => '',
                ],
                [
                    'title'       => '[RFQ] Changed REST base dokan/v1/customers to dokan/v1/request-for-quote/customers since Dokan Mobile App already occupied this REST base',
                    'description' => '',
                ],
                [
                    'title'       => '[RFQ] Changed REST base dokan/v1/dokan-quote-rule to dokan/v1/request-for-quote/dokan-quote-rule',
                    'description' => '',
                ],
                [
                    'title'       => '[RFQ] Changed REST base dokan/v1/dokan-request-quote to dokan/v1/request-for-quote/',
                    'description' => '',
                ],
                [
                    'title'       => '[RFQ] Changed REST base dokan/v1/roles to dokan/v1/request-for-quote/roles',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Added cart notices under vendor subscription list page, previously error messages were displaying only on cart or checkout pages.',
                    'description' => '',
                ],
                [
                    'title'       => 'Remove expected earning calculation from subscription and variable products',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeExpress] Set country field as required if Cross Border Transfer and Onboarding feature is enabled from Payment Gateway setting',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeExpress] Added a button to cancel onboarding process from Vendor Dashboard payment settings page',
                    'description' => '',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'Remove assigned store categories from a user while deleting a user account',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed wrong counts of store categories under Dokan admin page',
                    'description' => '',
                ],
                [
                    'title'       => '[ProductVariationController.php] fix syntax error in get_item_schema() call',
                    'description' => '',
                ],
                [
                    'title'       => '[SellerBadge] fixed a typo in the properties key of the item schema',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Added null check for $product before checking its type',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed badge events are firing even though no badge has been created yet.',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed badge search doesn\'t work after providing an invalid search value under vendor dashboard',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeExpress] Save Card for stripe connect doesn\'t work correctly if the Stripe Express module is enabled',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeExpress] Can not add a new Payment Method using Stripe Express from the My Account page if the cart is not empty (Vendor should be connected using Stripe Express)',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeExpress] Fixed a fatal error while adding a customer payment method (card) from the My Account --> Payment Methods page.',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed after expiration of a subscription, repurchasing vendor subscription publishes all vendor products regardless of product status[draft, pending review, online]',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Fixed calendar doesn\'t show up while creating booking from the Vendor Dashboard --> Booking --> Add Booking menu',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorVerification] Fixed verified badge on the single store page is not appearing in all store headers.',
                    'description' => '',
                ],
                [
                    'title'       => '[DokanStripeExpress] Fixed the vendors are redirected to the first step of the seller setup wizard after they connect their Stripe account in the second step.',
                    'description' => '',
                ],

            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.20',
        'released' => '2023-04-28',
        'changes'  => [

            'Improvement' => [
                [
                    'title'       => '[VendorAnalytics] Completed Google verification for Vendor Analytics App.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.19',
        'released' => '2023-04-17',
        'changes'  => [
            'New'         => [
                [
                    'title'       => '[API] Added new API endpoint for SPMV module, endpoints are:',
                    'description' => 'www.example.com/wp-json/dokan/v1/spmv-product/settings, www.example.com/wp-json/dokan/v1/spmv-product/search,
www.example.com/wp-json/dokan/v1/spmv-product/add-to-cart',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => '[StripeExpress] Moved some CSS code to the Astra theme support folder.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Fixed a design issue under the Social profile link on the vendor dashboard.',
                    'description' => '',
                ],
                [
                    'title'       => '[ProductReviews] Fixed bulk actions are not working on the review list page under vendor dashboard',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed some PHP warning under admin dashboard users profile edit page',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.18',
        'released' => '2023-04-10',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => '[StripeExpress] Admin will be able to disconnect/delete connected seller accounts from the admin dashboard user details page',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] Added support for non-recurring payments for Vendor Subscription products.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[VendorDashboard] Fixed announcement menu is not highlighted for the announcement details page.',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorDashboard] Fixed wrong navigation link on the order details page on product line items lists.',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorDashboard] Fixed duplicating any product bypass the New Product Status feature, now set to draft as default product status',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed some deprecated PHP warnings',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSupport] Fixed Support ticket wasn\'t loading from admin panel if vendor id wasn\'t found on topic meta.',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestForQuote] Fixed unable to add variable products to the cart if the request for quotation module is enabled',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreOpenCloseTime] Fixed time selector of the store open close time settings was broken.',
                    'description' => '',
                ],
                [
                    'title'       => '[Refund] Refund request wasn\'t successful if tax amount rounding precision is greater than 2',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.17',
        'released' => '2023-03-23',
        'changes'  => [
            'New'         => [
                [
                    'title'       => '[StripeExpress] Support for cross-border onboarding and transfer within the boundary of the European Union and SEPA',
                    'description' => '',
                ],
                [
                    'title'       => '[NewVendorDashboard] Added support for Rank Math Module',
                    'description' => '',
                ],
                [
                    'title'       => '[NewVendorDashboard] Added support for Geolocation Module',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeExpress] Added support for purchasing product advertisement via Stripe Express',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] Added support for purchasing product advertisement via Mango Pay',
                    'description' => '',
                ],
                [
                    'title'       => '[FollowStore] Added API endpoint to get a vendor follower list (wp-json/dokan/v1/follow-store/followers?vendor_id=1)',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => '[StripeExpress] Optimized payment request buttons implementation on the cart page',
                    'description' => '',
                ],
                [
                    'title'       => '[SPMV] Removed the capability to clone Grouped Products.',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPalMarketplace] Display a formatted error message if a refund request gets canceled due to insufficient balance under the vendor’s PayPal account.',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPalMarketplace] Set PayPal product type to PPCP only if UCC mode is enabled and supported otherwise product type will be selected as Express.',
                    'description' => '',
                ],
                [
                    'title'       => '[HPOS] Added High Performance Order Storage support for MangoPay and StripeExpress',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[ProductCategoryPopup] Default category is selected by default during bulk edit has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => '[SPMV] Product Advertisement Payment & Reverse Withdrawal Payment product showing on SPMV list',
                    'description' => '',
                ],
                [
                    'title'       => '[FollowStore] Fixed permalink reset issue after activating the module',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreCategories] Uncategorized count increases after adding new users other than seller role',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.16',
        'released' => '2023-03-09',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => '[WithdrawDisbursement] Added unsubscribe option for auto disbursement schedule for vendors.',
                    'description' => 'There is no unsubscribe option for the withdrawal auto-disbursement schedule. Vendors can\'t disable auto-withdraw requests once it is enabled.',
                ],
                [
                    'title'       => '[WithdrawDisbursement] Added announcement support for the withdraw disbursement schedule after admin reset (disable/enable) that particular disbursement option. There\'s no option for a vendor to know whether his/her schedule method is reset or not.',
                    'description' => '',
                ],
                [
                    'title'       => '[Shipping] Added Free Shipping validity check support for individual vendors shipping if set from individual shipping options or coupon',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[GEOLocation] The product is not showing when searching for products using the product location address on the shop page',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorStoreSettings] State option appear while choosing the country with no state',
                    'description' => '',
                ],
                [
                    'title'       => '[Refund] Fixed a fatal error while refunding an order from vendor dashboard if order line item doesn’t contain tax.',
                    'description' => '',
                ],
                [
                    'title'       => '[Refund] The vendor earnings become negative after the admin approves any refund request.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.15',
        'released' => '2023-02-23',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => '[Shortcode] Added user role-based validation for the customer-to-vendor migration shortcode.',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSettings] Added DateRangePicker library instead of DatePicker for vacation mode settings.',
                    'description' => '',
                ],
                [
                    'title'       => '[ShipStation] Dsiplay admin dependency notice to resolve conflict with “WooCommerce – ShipStation Integration” plugin',
                    'description' => '',
                ],
                [
                    'title'       => '[SellerBadge] Removed the placeholder condition part for the badges that do not require a condition and placed a self-explanatory content on that part.',
                    'description' => '',
                ],
                [
                    'title'       => '[SellerBadge] Replaced the Logical select dropdown with the text ‘More Than’ as the dropdown was not required.',
                    'description' => '',
                ],
                [
                    'title'       => '[DistanceRateShipping] Replaced `Second Address` text with `City` under Distance Rate Shipping Vendor Settings page under City text box description.',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime]: Added Delivery/Pickup time in order confirmation email of admin, vendor, and customer.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[Shortcode] The customer-to-vendor migration form throwing a fatal error after submitting has been fixed.',
                    'description' => '',
                ],
                [
                    'title'       => '[Refund] Vendor can\'t refund if the remaining amount is in fraction (e.g. 0.01) for order total/tax/shipping',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSettings] DatePicker was disabling wrong dates after adding a vacation schedule on seller vacation mode settings.',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSettings] Duplicate `Set Vacation Message` textbox was displaying if `Date wise close` mode is selected for vacation mode settings.',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSettings] Unnecessary validation message for `Set Vacation Message` textbox after store settings have been saved.',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestAQuote] Getting a "Your quote is currently empty." message when a customer submitted the quotation request has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Hiding the vendor info from the WP admin dashboard Dokan setting still the vendor information appearing on the single store page has been fixed.',
                    'description' => '',
                ],
                [
                    'title'       => 'Vendor profile picture becomes blurred if the profile picture is uploaded from the admin dashboard.',
                    'description' => '',
                ],
                [
                    'title'       => '[AdminCoupon] Fixed minimum amount of admin coupon applies based on the base product price, excluding tax',
                    'description' => '',
                ],
                [
                    'title'       => '[SellerBadge] Removed animation from badge add/edit page',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] If products are exported without meta value and trying to update them, it gets stuck and returns a fatal error when the Geolocation module is enabled.',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreReview] The markup on the Vendor Dashboard Reviews page is broken if the Vendor Product Review option is disabled in the admin settings.',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeConnect] The `Processing Fee Paid By` tooltip generated by Dokan Stripe Connect is sending the wrong message if the gateway fee was paid by the admin.',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Vendors are getting incorrect links via emails to view the single order and all orders.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.14',
        'released' => '2023-01-10',
        'changes'  => [
            'New Module' => [
                [
                    'title'       => '[Module] Seller Badge',
                    'description' => 'Offer vendors varieties of badges by their performance in your marketplace.',
                ],
                [
                    'title'       => '[API] new endpoint to duplicate product via API: `/dokan/v2/products/:id/duplicate`',
                    'description' => '',
                ],
            ],
            'Fix'        => [
                [
                    'title'       => '[Refund] The search option in the Refund menu is not operational.',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorStaff] Product importer gets stuck when a vendor staff tries to import products.',
                    'description' => '',
                ],
                [
                    'title'       => '[RestAPI]: fatal error for deleting product variation',
                    'description' => '',
                ],
                [
                    'title'       => '[MinMaxQuantities] Min-max rule for products cannot be disabled.',
                    'description' => '',
                ],
                [
                    'title'       => '[DokanShippingStatus] The order note email is sent to the customer when a shipping status is added to an order by the vendor alongside the shipping status email.',
                    'description' => '',
                ],
                [
                    'title'       => '[Dokan Wholesale]: Wholesale customer approval setting is not working correctly.',
                    'description' => '',
                ],
                [
                    'title'       => '[Refund] Using more user-friendly alert for sub-orders refund from the main order and refunded products are not being restocked.',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] The available templates do not show up on the Library modal window.',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorReports] Fixed wrong calculation of net sales on vendor reports',
                    'description' => '',
                ],
                [
                    'title'       => '[AdminReports] Fixed "By Day" & "By Vendor" filter screen keeps loading',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestForQuotation] Customer search of Request for Quotation not working on the plain permalink structure',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestForQuotation]  Error on updating existing quote of Request for Quotation module if plain permalink selected',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestForQuotation]  Error on adding new quote rule of Request for Quotation module if plain permalink selected',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestForQuotation]  Error on updating existing quote rule of Request for Quotation module if plain permalink selected',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.13',
        'released' => '2023-01-26',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Extended REST API support for Dokan',
                    'description' => '-- https://example.com/wp-json/dokan/v1/vendor-dashboard/profile-progressbar
-- https://example.com/wp-json/dokan/v1/rank-math/{id}//store-current-editable-post
-- https://example.com/wp-json/dokan/v1/blocks/product-variation/{id}
-- https://example.com/wp-json/dokan/v1/products/{id}/variations/{id}
-- https://example.com/wp-json/dokan/v1/products/{id}/variations/batch',
                ],
                [
                    'title'       => 'Added a filter hook named `dokan_vendor_biography_form` to control vendor biography form arguments',
                    'description' => '',
                ],
                [
                    'title'       => 'Added a filter named `dokan_paypal_marketplace_product_type` hook to control PayPal marketplace default product type',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Allow vendors to add new values to predefined attributes',
                    'description' => '',
                ],
                [
                    'title'       => 'Added a new section to regenerate variable products author under Dokan → Tools page.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Multiple store category modal wasn’t working for some theme',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreReview]: Reviews page markup broken if Vendor Product Review option disabled from admin settings',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] The vendors are getting an error on the vendor dashboard while adding a person type.',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Enabling two booking products that require confirmation is throwing an email error.',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Added translation support for delivery slot calendar under checkout page.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.12',
        'released' => '2023-01-10',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => '[StripeExpress ] Added support for source transaction in Stripe Express.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[DeliveryTime] Missing delivery time for the newly registered customer at the time of ordering.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.11',
        'released' => '2022-12-26',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => '[VendorSubscription] Vendors subscription pack validity date invalid, it was getting the date without formating from subscription product.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.10',
        'released' => '2022-11-30',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Updated UI/UX of vendor dashboard submenu',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] Enabled support for guest users to purchase vendor subscription pack',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[Stripe Express] Payment error when WooCommerce subscription is disabled',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Product filtering was not working when geolocation was not set in the filters',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] A fatal error occurs when the RMA Requests page is visited from my account page if order doesn\'t exists on database',
                    'description' => '',
                ],
                [
                    'title'       => '[Rank Math SEO] Assets dependency issue with the latest version of Rank Math SEO',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.9',
        'released' => '2022-11-03',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Store category filter was not working in the admin panel',
                    'description' => '',
                ],
                [
                    'title'       => 'SweetAlert library is conflicting with the WooCommerce Conversion Tracking plugin',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed a fatal error after installing the `Disable Rest API` plugin',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Store filtering was not working when geolocation was not set in the filters',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Delivery time box is showing on admin orders, even though the delivery setting is not enabled',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Delivery time box is showing on vendor orders, even though the delivery setting is not enabled',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Fixed some warnings on a fresh installation',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.8',
        'released' => '2022-10-27',
        'changes'  => [
            'New'         => [
                [
                    'title'       => '[DeliveryTime] Added per day multiple delivery time slot support',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription/StripeExpress] Added Stripe Express support for the Vendor Subscription module',
                    'description' => '',
                ],
                [
                    'title'       => '[ProductSubscription/StripeExpress] Added Stripe Express support for the Vendor Product Subscription module',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => '[DeliveryTime] Updated vendor delivery time UI.',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Added delivery type (Home Delivery/Store Pickup) settings for admin',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Added store pickup time section under vendor dashboard order details page, also under the wooCommerce order details page store pickup-related information is displayed.',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Now vendors’ will be able to switch Delivery Time to Store location pickup from the order details page.',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Added `Full Day` support for delivery time for both admin and vendors.',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Added email notification support for customers after modifying order delivery time from the vendor dashboard.',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Added email notification support for customers & vendors after admin updates order delivery time from the wooCommerce admin dashboard order panel.',
                    'description' => '',
                ],
                [
                    'title'       => 'First day of the delivery time widget was set according to the site settings.',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Express] Added support for SEPA Direct Debit payment method to be used in favor of iDEAL during recurring vendor subscription',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Express] Removed Wallet payment methods from Add payment method page as they are not needed there',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Express] Removed Dokan prefix/postfix from customer/vendor end',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Express] Added theme-changing option for Stripe payment element',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] Updated the MangoPay API library to the latest version.',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] Set card payin with 3ds2 as mandatory.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[DeliveryTime] Custom date format isn’t working if the suffix is applied `eg: 1st` now has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => '[VariableProduct] Some fields and options are missing for the variations section under the vendor dashboard product edit page',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestForQuotation] When there are multiple requests for quotation rules, rules priority wasn’t considered and all the applicable rules were merged.',
                    'description' => '',
                ],
                [
                    'title'       => '[Refund] Tax Amount Box takes 3 Decimal Places under vendor dashboard order details page',
                    'description' => '',
                ],
                [
                    'title'       => '[Shipping] Fixed shipping cache wasn\'t removed after a shipping method has been enabled or disabled from the vendor dashboard.',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeConnect] `No such customer found` error after changing API credentials of Stripe modules for Dokan Stripe Connect(non-3ds)',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] Fixes when a customer sends a refund request for multiple products, the amount of products shown in the quantity dropdown does not decrease accordingly',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] The geolocation map\'s pop-up on the shop page and the store listing page is not working fixes',
                    'description' => '',
                ],
                [
                    'title'       => '**fix:** [VendorAnalytics] Tracking number is not being added to the source file when the Add Tracking Code setting is enabled.',
                    'description' => '',
                ],
                [
                    'title'       => '[TableRateShipping/WPML] table rate shipping vendor settings page wasn\'t accessible if the site language wasn\'t set to English.',
                    'description' => '',
                ],
                [
                    'title'       => '[TableRateShipping] Shipping Class column hides after switching to a secondary language.',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Spaces between paragraphs are too large under the store terms and condition page.',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Express] UI conflict of payment request button with Astra theme in the single product page',
                    'description' => '',
                ],
                [
                    'title'       => 'For all payment gateways, announcements and notices to non-connected sellers were showing for inactive withdrawal methods.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.7',
        'released' => '2022-10-11',
        'changes'  => [
            'Update' => [
                [
                    'title'       => '[ColorSchemeCustomizer] Product create popup modal color aligned with the selected theme color.',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreReview] Moved bulk action fields to the top of the store review lists under the vendor dashboard',
                    'description' => '',
                ],
                [
                    'title'       => '[SellerVacation/Auction] Added vacation mode support for Auction products',
                    'description' => '',
                ],
                [
                    'title'       => '[SellerVacation] Added vacation message fields as a required field under the vendor dashboard store settings page.  Previously it was displaying an empty box on the store page if this field was empty.',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Hide Accommodation Addon types checkboxes from the regular products\' edit product page/form',
                    'description' => '',
                ],
            ],
            'Fix'    => [
                [
                    'title'       => '[RMA] Fixed the Store Credit Coupon does not include any products at the time of coupon creation',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Fixed PHP Notice: Undefined index: add_resource_name while creating booking resource with empty resource person',
                    'description' => '',
                ],
                [
                    'title'       => '[TableRateShipping] Fixed Vendor Table Rate Shipping\'s > Method Title does not work correctly',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreReview] Fixed unable to translate Guest string',
                    'description' => '',
                ],
                [
                    'title'       => '[SellerVacation] Fixed product status doesn\'t show on the booking product listing page if the vendor enables vacation mode',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fixed store banner and profile images are not showing on the single store page after updating to Dokan Pro 3.7.6',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fixed a deprecation warning that shows up in Query Monitor on the front end of the site',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreOpenCloseTime] Fixed console error on shop page',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Fixed needed to save permalinks for the auction module to work after the WooCommerce Auction plugin is activated',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestAQuote] Fixed template files NewQuote.php and UpdateQuote.php have the same ID: dokan_request_new_quote',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Fixed the dropdown list of subscription packs on the vendor registration form is not working',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Fixed customer becomes vendor form ( showing all subscription packs) while the Dokan subscription module is enabled',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorShipping] Fixed after changing the shipping cost from the vendor dashboard, it does not update on the cart by reloading the page',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorShipping] Prevent vendors from inputting currency in the "Flat Rate" shipping method\'s cost fields.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.6',
        'released' => '2022-09-27',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => '[SPMV] Fixed magnify popup style broken in SPMV search result page',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed some warning while bulk publishing products',
                    'description' => '',
                ],
                [
                    'title'       => '[Coupons]: Dokan coupon is not applying for the variations if the parent product is selected for that coupon.',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Fixed Map is not loading on some pages',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] Some deprecated warnings while creating renewal order for Vendor Subscription.',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fatal error on elementor update when dokan container is not set',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] Smart payment button was not loading',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Selecting a child category doesn’t work on child category children.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.5',
        'released' => '2022-08-25',
        'changes'  => [
            'New'         => [
                [
                    'title'       => '[Auction] Multistep product category implementation for Auction Module',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Multistep product category implementation for Booking Module',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'No message after clicking the `save changes` button on the vendor dashboard Ship Station settings',
                    'description' => '',
                ],
                [
                    'title'       => 'Display active and inactive module count under the Dokan module page',
                    'description' => '',
                ],
                [
                    'title'       => '[ColorSchemeCustomizer] Added extended supports for Color Scheme Customizer module on frontend',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[StripeConnect] Fixed a deprecated warning `Method WC_Subscriptions::redirect_ajax_add_to_cart is deprecated since version 4.0.0`',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscriptionProduct] fixed a deprecated warning `Function WC_Subscriptions::is_duplicate_site is deprecated since version 4.0.0!`',
                    'description' => '',
                ],
                [
                    'title'       => '[TableRateShipping] Fixed tooltip not working for table rate shipping under vendor dashboard',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Single Store Page templates were not loading on latest version of Elementor',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fixed some deprecated warnings on Dokan Elementor module',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.4',
        'released' => '2022-08-10',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Introduced a new filter hook named `dokan_progressbar_translated_string`',
                    'description' => '',
                ],
                [
                    'title'       => 'Product Inline Edit Support Catalog Mode for Products',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Single store page Featured, Latest, Best-selling, Top-rated products widget for Elementor module',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Added Catalog Mode support for Booking Products',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestAQuote] Added Catalog Mode support',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Implement new category UI in product Quick edit and bulk edit in vendor dashboard.',
                    'description' => '',
                ],
                [
                    'title'       => 'Load asset (css/js) files only on required pages',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Skrill payment gateway shows disconnect button for new vendors even though it is not connected.',
                    'description' => '',
                ],
                [
                    'title'       => 'Delete coupon cache for marketplace coupon after seller account has been deleted.',
                    'description' => '',
                ],
                [
                    'title'       => '[WireCard/Moip] Wirecard payment gateway showed under vendor withdraw options when the module is enabled regardless of the payment method enabled in WooCommerce',
                    'description' => '',
                ],
                [
                    'title'       => 'PHP warning for vendor shipment status email template',
                    'description' => '',
                ],
                [
                    'title'       => 'Updated Pending Product - email was sent for already published products if the setting is enabled for a vendor: Publish Product Directly.',
                    'description' => '',
                ],
                [
                    'title'       => 'Changing withdraw method doesn\'t always show Withdraw Method Changed Modal window for withdraw disbursement feature under Dokan admin settings.',
                    'description' => '',
                ],
                [
                    'title'       => '[Refund] Translation issue on refund required parameters',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] KYC documents were being `Out of Date` in some cases.',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Filter by the vendor wasn\'t working if the Show Filters Before Location Map setting is turned off from the Geolocation admin setting',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Express] Error on adding payment method from My Account -> Add Payment Method page when the gateway setup process wasn\'t completed.',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeConnect] Fixed redirect URL mismatch with stripe oAuth redirect URL',
                    'description' => '',
                ],
                [
                    'title'       => '[StripeConnect] Fixed invalid redirect url issue after vendor connect their account with stripe',
                    'description' => '',
                ],
                [
                    'title'       => '[OrderMinMax] Fixed fatal error while calling filter product API from Request a Quote module',
                    'description' => '',
                ],
                [
                    'title'       => '[ProductAdvertisement] Fixed 3 warnings on the home page if the Product advertisement module is active',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fixed single store page products broken layout of Elementor widgets on Storefront theme',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Categories restriction wasn\'t working when restrict categories was enabled from vendor subscription product.',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] fixed fatal error while purchasing booking product if rma module is active',
                    'description' => '',
                ],
                [
                    'title'       => '[DeliveryTime] Added translation support for Delivery Time Calendar',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestAQuote] change text permanent delete to Delete Permanently',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestAQuote] Search for the product wasn\'t working while creating new Quotes or new Quote rules',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorStaff] Removed unfiltered_html capabilities from vendor_staff role',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.3',
        'released' => '2022-07-26',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => '[RequestForQuotation] Fixed category list wasn’t rendered properly and was missing most of the category items.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.2',
        'released' => '2022-07-15',
        'changes'  => [
            'New'         => [
                [
                    'title'       => '[ColorSchemeCustomizar] Added dashboard navigation active menu color settings.',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Removed default values from withdraw disbursement settings',
                    'description' => '',
                ],
                [
                    'title'       => '[ColorSchemeCustomizar] Added dashboard navigation custom border color settings.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[Stripe] Fixed fatal error on parsing gateway title and a warning after checkout is completed',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed data clear pop not showing after checking the data clearing settings checkbox.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.1',
        'released' => '2022-06-30',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Added disconnect button to the Skrill payment method',
                    'description' => '',
                ],
                [
                    'title'       => 'Redirect the user to the corresponding payment settings page instead of the payment list page after connecting the payment method.',
                    'description' => '',
                ],
                [
                    'title'       => 'Respect the Dokan withdraw enable/disable method during a show of non-connected seller notice.',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Update Elementor Store Open/Close times widget & add hover feature to the Store Open/Close times widget.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Fixed some empty method names in Payment Methods section of Vendor Dashboard > Withdraw.',
                    'description' => '',
                ],
                [
                    'title'       => '[RequestAQuote] Fixed a console warning on WordPress admin panel',
                    'description' => '',
                ],
                [
                    'title'       => 'After completion of 100% again clicking update settings of store/payment/social profile will popup progress bar again',
                    'description' => '',
                ],
                [
                    'title'       => 'Progress bar doesn’t update if a vendor is created by the admin',
                    'description' => '',
                ],
                [
                    'title'       => 'Progress bar doesn’t update if a customer becomes a vendor',
                    'description' => '',
                ],
                [
                    'title'       => 'No progress bar update for PayPal marketplace, Mangopay, razor pay, custom payment method update',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.7.0',
        'released' => '2022-06-14',
        'changes'  => [
            'New'         => [
                [
                    'title'       => '[ColorSchemeCustomizer] Updated admin color picker settings page and included 7 different color pallets for admin to choose from.',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorStaff] An email is sent to the vendor staff if the vendor changes the password.',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Updated Dokan Pro and all modules settings page design according to the new settings page.',
                    'description' => '',
                ],
                [
                    'title'       => 'If ‘Selling Product Types’ is set to ‘I plan to sell only digital products’ then when creating new variations of a variable product the Downloadable and Virtual checkboxes will be checked automatically if the corresponding values are empty. otherwise, the saved values will be placed',
                    'description' => '',
                ],
                [
                    'title'       => '[ProductAdvertisement] delete advertisement product reference after the base product has been moved to the trash',
                    'description' => '',
                ],
                [
                    'title'       => '[ProductAdvertisement] create advertisement base product after saving advertisement settings',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'WPML translated endpoint not working in payment settings page',
                    'description' => '',
                ],
                [
                    'title'       => 'Distance rate shipping doesn\'t show the proper shipping method title under Cart and Checkout page',
                    'description' => '',
                ],
                [
                    'title'       => 'Distance rate shipping method rules order not saved',
                    'description' => '',
                ],
                [
                    'title'       => 'Coupon percentage discount type doesn\'t respect WooCommerce decimal/thousand settings for coupon amount',
                    'description' => '',
                ],
                [
                    'title'       => 'admin shipping method deletion doesn\'t delete vendor shipping methods',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.6.2',
        'released' => '2022-06-03',
        'changes'  => [
            'Fix'         => [
                [
                    'title'       => '[Stripe express] Live mode API wasn\'t working',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => '[Stripe express] Updated some validation to restrict some unnecessary process',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe express] Billing, shipping, and tax data processing for payment request',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Express] Added filter `dokan_stripe_express_payment_method_title` to manipulate payment method title',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.6.1',
        'released' => '2022-05-31',
        'changes'  => [
            'New Module'  => [
                [
                    'title'       => 'Stripe Express',
                    'description' => 'Enable split payments, multi-seller payments, Apple Pay, Google Pay, iDEAL and other marketplace features available in Stripe Express Kindly refer to the <a href="https://wedevs.com/docs/dokan/modules/stripe-express-integration/">documentation</a> for more information.',
                ],
            ],
            'New'         => [
                [
                    'title'       => '[ProductAdvertising] Added reverse withdrawal support',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] Added reverse withdrawal payment purchase support ',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => '[MangoPay] Some UX for MangoPay payment settings in vendor dashboard.',
                    'description' => '',
                ],
                [
                    'title'       => 'Introduce a callback to withdraw methods to determine if a seller if connected to that withdraw method',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Negative vendor earning entry while refunding a multivendor order fully.',
                    'description' => '',
                ],
                [
                    'title'       => 'Marketplace coupon bound to some vendors applies to other vendors\' products',
                    'description' => '',
                ],
                [
                    'title'       => 'Vendor coupon applies to sale items even if its exclude sale item flag is true',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] Empty state error was being thrown for some addresses where state is not required',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe Connect] Payment element\'s designs of other Stripe gateways were breaking.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.6.0',
        'released' => '2022-05-18',
        'changes'  => [
            'New Module'  => [
                [
                    'title'       => 'Request for Quotation',
                    'description' => 'Facilitate wholesale orders between merchants and customers with the option for quoted prices. Kindly refer to the <a href="https://wedevs.com/docs/dokan/modules/dokan-request-for-quotation-module/">documentation</a> for more information.',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Now vendor can choose if a Free shipping rule should be applied before or after deducing the coupon discount amount.',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSupport] Added Store Support unread ticket status icon, and added email settings and new email templates to send support status email to admin.',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Added filters to the auctions products under vendor dashboard',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Added email search in auctions activity page',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'With a manual booking, vendor is being charged instead of the customer',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed a translation issue for automatic withdraw page’s minimum withdraw amount',
                    'description' => '',
                ],
                [
                    'title'       => 'Vendor shipping zone is not selected properly on the cart page due to caching issue',
                    'description' => '',
                ],
                [
                    'title'       => 'Stop the nonce verification failed message after saving store settings',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Fixed pagination under auctions products list page',
                    'description' => '',
                ],
                [
                    'title'       => '[SPMV] min/max price show product not working if cloned products price diff is less than 1.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.5.6',
        'released' => '2022-04-26',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Added withdraw method icon filter hook: dokan_withdraw_method_icon',
                    'description' => '',
                ],
                [
                    'title'       => 'Added withdraw method heading filter hook: dokan_vendor_dashboard_payment_settings_heading',
                    'description' => '',
                ],
                [
                    'title'       => 'Added icons in images directory inside corresponding assets directory of withdraw methods if the icon file is missing',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'The Shipping tax is not calculated on Flat Rate shipping if there is any other method under the Flat Rate shipping method',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] Fixed live API key not working issue',
                    'description' => '',
                ],
                [
                    'title'       => 'Customer does not get the verification link in the email if \'Enable Subscription in registration form\' is enabled in Vendor Subscription',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.5.5',
        'released' => '2022-04-11',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => '[VendorVerification] Added vendor proof of residence upload feature',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreReview] Added email notification for new store reviews',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Fixed Availability Range of the bookable product can not be deleted when the product is checked to be Accommodation Booking type.',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fixed some deprecated warnings and a fatal error while using the latest version of Elementor.',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Fixed Booking not visible in Day view of the calendar if site language is other than English.',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] The product location of the pending review products are automatically changed to same as store on publish has been fixed.',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Vendor are being able to create variations even after restricting using subscription packs has been fixed now.',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPalMarketplace] Fixed invalid parameter value error while creating vendor subscription if price contain more that 2 digits after decimal points.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.5.4',
        'released' => '2022-03-18',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Set delivery time week start date similar to WordPress week start settings',
                    'description' => '',
                ],
                [
                    'title'       => '[MangoPay] Applied some logic to restrict unnecessary implementations for MangoPay',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] Restart Payment flow in case of funding source error on PayPal ie: user doesn’t have enough balance',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] Display user friendly error messages instead of generic message',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] Set Shipping and Tax fee recipient to seller despite of admin settings, previously it was displaying error on checkout page if shipping fee recipient was set to admin',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] Added purchasing capability for not logged in user',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[SPMV] Fatal error under Single Product Multiple Vendor module while trying to clone auction product',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed some translation issue under Vendor Subscription, Vendor Verification, Paypal Marketplace, Mangopay, RazorPay, and Product Advertising modules',
                    'description' => '',
                ],
                [
                    'title'       => '[OrderMinMax] Fixed a warning after clicking Order Again on a completed order',
                    'description' => '',
                ],
                [
                    'title'       => '[Product Addons] Completion of successful add-on creation alert message has wrong css class',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed adding two products from different vendors and one of them is virtual, will receive a warning error on the cart page.',
                    'description' => '',
                ],
                [
                    'title'       => 'JS console error while loading product category & product add new pages has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] Update seller enable for receive payment status if not already updated due to failed web hook event',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Remove validation if no subscription is active under vendor registration form',
                    'description' => '',
                ],
                [
                    'title'       => '[VendorSubscription] Remove validation if no subscription is active under vendor registration form',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] Send refund button is not working under RMA refund request screen',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe] \'Your card number is incomplete\' issue on checkout pay order page',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking/Auction] Fixed product geolocation is not working for Booking and Auction Products',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed follow button not working under \'My Account\' > \'Vendors\' section',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.5.3',
        'released' => '2022-03-08',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Stop loading unnecessary style and script files on every page',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSupport] Added localization support for date time picker library',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Vendor info was set to null if vendor haven’t assigned to any store category',
                    'description' => '',
                ],
                [
                    'title'       => 'Dokan was creating unnecessary rows in the termmeta table, now has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed fatal error while checking dokan_is_store_open(), if admin didn\'t run dokan migrator',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed fatal error on dokan migrator',
                    'description' => '',
                ],
                [
                    'title'       => '[EU Compliance Fields] Fixed a fatal error while saving Germanized trusted product variation fields data',
                    'description' => '',
                ],
                [
                    'title'       => '[EU Compliance Fields] fatal error on wcpdf invoice integration on php version 8.0+',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fixed a warning due to compatibility issue with latest version of Store Support Module',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fixed social profile Elementor icon widget wasn’t working properly due to conflict with latest version of font awesome library',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreReviewe] fixed a fatal error while clicking Sell This Item from spmv module',
                    'description' => '',
                ],
                [
                    'title'       => '[Dokan Stripe] Fixed gateway fee was returning 0 in case of several partial refunds requested for same order',
                    'description' => '',
                ],
                [
                    'title'       => '[Product Enquiry] Fixed loading icon always displaying after product enquiry email is sent',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.5.2',
        'released' => '2022-02-28',
        'changes'  => [
            'New Feature' => [
                [
                    'title'       => '[SPMV] Added product search feature under Add New Product  page if Single Product Multi Vendor module is enabled.',
                    'description' => 'Product search in the Add new product window is added when the SPMV module is activated, <a href="https://wedevs.com/docs/dokan/modules/single-product-multiple-vendor/">Documentation</a>. Currently, we are giving product search functionality under Booking and Auction module also. The Booking or Auction Product search results displays Booking or Auction products only.',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Added seller verification badge under Store listing page, single store page,  and single product page',
                    'description' => '',
                ],
                [
                    'title'       => 'Option to close progress bar if profile completeness score is 100%',
                    'description' => '',
                ],
                [
                    'title'       => '[EU Compliance Fields] Added EU Compliance Customer Fields in Order details Billing and Billing section of Customer profile[EU Compliance Fields] Added EU Compliance Customer Fields in Order details Billing and Billing section of Customer profile',
                    'description' => 'Module page design updates',
                ],
                [
                    'title'       => 'Module page design updates',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[StoreSupport] Activating Store Support from Modules has no reflection on the single store page unless vendor update their settings',
                    'description' => '',
                ],
                [
                    'title'       => 'Tools - Page Installation Pages button does not work appropriately',
                    'description' => '',
                ],
                [
                    'title'       => 'Hide add new coupon button from coupon create page',
                    'description' => '',
                ],
                [
                    'title'       => 'Shipping continent is not being shown under the shipping tab on the single product page',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Disable shipping option when virtual is enabled for bookable products',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Resource available quantity field is empty',
                    'description' => '',
                ],
                [
                    'title'       => 'Added Dokan Upgrader to delivery time schema updates',
                    'description' => '',
                ],
                [
                    'title'       => 'Styles are not being saved If the announcement is drafted or edited after scheduled',
                    'description' => '',
                ],
                [
                    'title'       => 'Showing an extra comma in the Booking resource\'s Parent products when a connected product is deleted',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.5.1',
        'released' => '2022-02-17',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Multiple Store Open Close Widget',
                    'description' => 'Multiple store open close time wasn\'t working for Store Open/Close time Widget',
                ],
                [
                    'title'       => 'Elementor Single Store Page Template',
                    'description' => 'Single Store Page template was missing from Elementor template selection dropdown.',
                ],
                [
                    'title'       => 'Elementor Single Product Page Widgets ',
                    'description' => 'Product Widgets disappeared from Elementor single Product Page template edit panel.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.5.0',
        'released' => '2022-02-03',
        'changes'  => [
            'New Module'  => [
                [
                    'title'       => 'Razorpay Payment Gateway',
                    'description' => 'Accept credit card payments and allow your sellers to get automatic split payment in Dokan via Razorpay. Module is available under <strong>Professional+</strong> plans. Please visit <a href="https://wedevs.com/docs/dokan/modules/dokan-razorpay/" target="_blank">documentation</a> to learn more about this module.',
                ],
                [
                    'title'       => 'MangoPay Payment Gateway',
                    'description' => 'Enable split payments, multi-seller payments, and other marketplace features given by MangoPay. Module is available under <strong>Professional+</strong> plans. Please visit <a href="https://wedevs.com/docs/dokan/modules/dokan-mangopay/" target="_blank">documentation</a> to learn more about this module.',
                ],
                [
                    'title'       => 'Min Max Order Quantities ',
                    'description' => 'Set a minimum or maximum purchase quantity or amount for the products of your marketplace. Module is available under <strong>Professional+</strong> plans. Please visit <a href="https://wedevs.com/docs/dokan/modules/how-to-enable-minimum-maximum-order-amount-for-dokan/" target="_blank">documentation</a> to learn more about this module.',
                ],
                [
                    'title'       => 'Product Advertising ',
                    'description' => 'Admin can earn more by allowing vendors to advertise their products and give them the right exposure. Module is available under <strong>Business+</strong> plans. Please visit <a href="https://wedevs.com/docs/dokan/modules/how-to-enable-minimum-maximum-order-amount-for-dokan/" target="_blank">documentation</a> to learn more about this module.',
                ],
            ],
            'New Feature' => [
                [
                    'title'       => '[Store Support] Added Store Support feature for site admin.',
                    'description' => 'Now Admin will be able to participate in support ticket conversations made via customers right from the admin dashboard. Please visit <a href="https://wedevs.com/docs/dokan/modules/how-to-install-and-use-store-support/support-fot-admin/" target="_blank">documentation</a> to learn more about this feature.',
                ],
                [
                    'title'       => 'Added support for Multiple store open close time for vendor store',
                    'description' => 'Now seller will be able to add multiple open/close time for their store. Please visit <a href="https://wedevs.com/docs/dokan/vendor-guide/how-to-manage-opening-closing-hours-of-vendor-store/" target="_blank">documentation</a> to learn more about this feature.',
                ],
                [
                    'title'       => 'Automatic withdrawal disbursement',
                    'description' => 'Now seller will be able to setup schedule to withdraw their earnings. Please visit <a href="https://wedevs.com/docs/dokan/withdraw/automatic-withdraw-disbursement/" target="_blank">documentation</a> to learn more about this feature.',
                ],
                [
                    'title'       => 'Added custom withdraw method support for admin',
                    'description' => 'Now admin will be able to add custom withdraw method along with existing one. Kindly visit <strong>WordPress Dashboard --> Dokan --> Settings --> Withdraw</strong> page to enable this feature.',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Updated UI for module page',
                    'description' => 'Now you will be able to find your required modules easily with the help of improved UI for the Module page. We have categorized modules into a couple of groups, updated documentation links, included video documentation, and many more. Kindly visit <strong>WordPress Dashboard --> Dokan --> Modules</strong> page to explore the new design.Now you will be able to find your required modules easily with the help of improved UI for the Module page. We have categorized modules into a couple of groups, updated documentation links, included video documentation, and many more. Kindly visit <strong>WordPress Dashboard --> Dokan --> Modules</strong> page to explore the new design.',
                ],
                [
                    'title'       => 'For Store open close time widget, first day of the week will start on according to the WordPress settings',
                    'description' => '',
                ],
                [
                    'title'       => 'Ensured compatibility with latest release of Rank math SEO',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Added support for "=" symbol while creating range and setting up the cost while creating a bookable product. ',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Set the minimum allowed value for \'Minimum booking window ( into the future )\' to zero(0)',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Added a search button under geolocation shortcode  to search store/product via geolocation, also removed auto reload features for this form.',
                    'description' => '',
                ],
                [
                    'title'       => 'Updated Dokan Free Shipping minimum amount calculation based on WooCommerce (compatibility with latest version)',
                    'description' => '',
                ],
                [
                    'title'       => '[Store Support] Design updated on vendor dashboard store support page and customer dashboard support page.',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSupport] Added date range filtering option for vendor support tickets listing ',
                    'description' => '',
                ],
                [
                    'title'       => '[StoreSupport] Added support tickets count under My Account page',
                    'description' => '',
                ],
                [
                    'title'       => '[ProductEnquiry] reCAPTCHA support added to product enquiry form ',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Added Back Navigation button from auction activity list, also fixed a typo',
                    'description' => '',
                ],
                [
                    'title'       => 'Updated some admin notices for better readability',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[PayPal Marketplace] Switching subscription plan doesn\'t work if Paypal Marketplace module is active, now has been fixed.',
                    'description' => '',
                ],
                [
                    'title'       => 'Vendor coupon was not expiring at exact expiry date, now has been fixed.',
                    'description' => '',
                ],
                [
                    'title'       => '[Delivery Time] Delivery date label wasn’t displaying on frontend checkout page, now has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed button width mismatch under vendor dashboard report page',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Starting bidding price was not resetting for Re-listing auction products, now has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => 'Shipping methods are not available when both digital and physical products are in the cart, now has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => '[Product Subscription] Shipping functionality is not working when vendor create subscription product from vendor dashboard, now has been fixed.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.4.4',
        'released' => '2021-12-23',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'SEO section is not appearing while the latest Yoast SEO plugin (17.8) is installed and activated.',
                    'description' => '',
                ],
                [
                    'title'       => 'Refund not working if order has sub order.',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.4.3',
        'released' => '2021-12-15',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => '[Delivery Time]  Now users will not be able to choose time slots that are before the order time.',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] Added sweet alert while canceling a subscription from the vendor dashboard.',
                    'description' => '',
                ],
                [
                    'title'       => 'Redesigned What’s New page design for Dokan Pro changelog',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] Recurring order support added for subscriptions purchased via PayPal Standard Gateway.',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] Recurring order support added for subscriptions purchased via Dokan Stripe Payment Gateway.',
                    'description' => '',
                ],

            ],
            'Refactor'    => [
                [
                    'title'       => 'float typecast refactored to wc_format_decimal() #1448',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[Wholesale] prevent users to buy products at wholesale prices if they are not wholesale customers.',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Location wasn’t updating if geolocation field was added before and then changed the settings to Same as store.',
                    'description' => '',
                ],
                [
                    'title'       => 'Fix loading issue while loading Dokan pages when permalink sets to plain text, Also added a notice to instruct users to change permalink setting.',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Start date and End date" fields from the edit auction product page wasn’t saving when users have not previously provided these fields data.',
                    'description' => '',
                ],
                [
                    'title'       => 'Show all variation products in admin dashboard -> coupon vendor restriction section',
                    'description' => '',
                ],
                [
                    'title'       => '[Rank Math SEO] Compatibility issue with latest version of Rank Math SEO plugin',
                    'description' => '',
                ],
                [
                    'title'       => 'Some string are not translating, now has been fixed',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed Some deprecated warning',
                    'description' => '',
                ],
                [
                    'title'       => 'Category dropdown list in Dokan Live Search was invisible in the Dokani theme. added an id to the element to add proper css in the Dokani theme to make it visible',
                    'description' => '',
                ],
                [
                    'title'       => 'When variable products are edited using the bulk edit feature of the vendor dashboard, it resets the product status and switches product type to Simple. This issue has been fixed now',
                    'description' => '',
                ],
            ],

        ],
    ],
    [
        'version'  => 'Version 3.4.2',
        'released' => '2021-11-30',
        'changes'  => [
            'New'         => [
                [
                    'title'       => '[Booking] Added accommodation booking for Booking module',
                    'description' => '',
                ],
                [
                    'title'       => '[Table Rate Shipping] Added distance rate shipping under table rate shipping module',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] Added downloadable and virtual product support for auction module',
                    'description' => '',
                ],
                [
                    'title'       => '[Store Support] Added searching and filtering for support tickets from vendor dashboard',
                    'description' => '',
                ],
                [
                    'title'       => 'Added manual refund button for both admin and vendors. Admin and seller can use this feature to record manual refund.',
                    'description' => '',
                ],
                [
                    'title'       => 'Added a new order note for payment gateways other than Dokan payment gateways.',
                    'description' => '',
                ],
                [
                    'title'       => 'Added API refund support for payment gateways other than Dokan payment gateways. Based on admin settings, if admin approves a refund request, this will be also processed from corresponding payment gateway.',
                    'description' => '',
                ],
                [
                    'title'       => '[Delivery Time] Made delivery time fields required under checkout page, also added a settings page to make these fields required.',
                    'description' => '',
                ],

            ],
            'Improvement' => [
                [
                    'title'       => 'Caching Enhancement and Fixes',
                    'description' => '',
                ],
                [
                    'title'       => '[Store Support] Display user display name instead of username under Get Support popup form',
                    'description' => '',
                ],
                [
                    'title'       => '[Store Review] Display user display name instead of username under Store Review popup form',
                    'description' => '',
                ],
                [
                    'title'       => 'Added necessary tooltip for various Dokan settings',
                    'description' => '',
                ],
                [
                    'title'       => ' Replaced vendor dashboard dash icons with fontAwesome icons, this was causing conflict with some third party plugins',
                    'description' => '',
                ],

            ],
            'Fix'         => [
                [
                    'title'       => 'Disabled bulk action product edit/delete, inline product edit/delete if vendor is not enabled for selling',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] Fix a conflict with Elementor module and Vendor Analytics module. (Single store page layout was broken)',
                    'description' => '',
                ],
                [
                    'title'       => '[Import/Export] Existing categories wasn’t importing while importing products',
                    'description' => '',
                ],
                [
                    'title'       => '[Store Support] Fixed WPML conflict for various links (some links wasn’t working if site language is other than English)',
                    'description' => '',
                ],
                [
                    'title'       => 'Store category search option was throwing error on console',
                    'description' => '',
                ],
                [
                    'title'       => 'CSV import form is not working when multisite is enabled',
                    'description' => '',
                ],
                [
                    'title'       => 'Saving announcement as draft wasn\'t working',
                    'description' => '',
                ],
                [
                    'title'       => 'Vendor coupon wasn\'t working for variation products',
                    'description' => '',
                ],

            ],

        ],
    ],
    [
        'version'  => 'Version 3.4.1',
        'released' => '2021-11-12',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Added date filter on Dokan —> Reports —> Logs page',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Now Export button will export all logs based on applied filters',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Reset Geolocation fields data after user clears that fields in WooCommerce shop page',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Verification] added four new action hooks after verification submit button in vendor dashboard',
                    'description' => 'Added hooks are: dokan_before_id_verification_submit_button, dokan_before_phone_verification_submit_button, dokan_before_address_verification_submit_button, dokan_before_company_verification_submit_button',
                ],
                [
                    'title'       => '[Vendor Subscription] Added trial text after trial value on vendor subscription list page',
                    'description' => '',
                ],
                [
                    'title'       => '[Auction] some sanitization issue fixed for auction module',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[Vendor Verification] No email sent to vendors after vendor verification status has been changed',
                    'description' => 'Now vendors will get email notification after admin approve or reject a verification request.',
                ],
                [
                    'title'       => '[Product Subscription] Added missing param on woocommerce_admin_order_item_headers hooks',
                    'description' => '',
                ],
                [
                    'title'       => 'Product variation image upload button wasn’t working due to js error',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Geolocation fields asking for user address each time user visit shop page',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed WC mail template overwrite wasn’t working',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] fixed Vendor Subscription category limitation doesn\'t work in the quick edit panel',
                    'description' => '',
                ],
                [
                    'title'       => 'Vendor Dashboard created coupon expired date doesn\'t work correctly',
                    'description' => '',
                ],
                [
                    'title'       => '[Import/Export] Fixed importing products does not get the store geolocation data',
                    'description' => '',
                ],
                [
                    'title'       => '\'Connect With Wirecard\' button in vendor payment settings page was hidden, now it is shown',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.4.0',
        'released' => '2021-10-31',
        'changes'  => [
            'New Module'  => [
                [
                    'title'       => 'Table Rate Shipping',
                    'description' => 'Table rate shipping, multi vendor support to give vendors flexibility on how they set the shipping rates on their products. Set up different rates according to the location, price, weight, shipping class or item count of the shipment.',
                ],
                [
                    'title'       => 'Rank Math SEO Integration',
                    'description' => 'Rank Math is a <a href="https://wordpress.org/plugins/seo-by-rank-math/" target="_blank">Search Engine Optimization plugin for WordPress</a> that makes it easy for anyone to optimize their content with built-in suggestions based on widely-accepted best practices. Easily customize important SEO settings, control which pages are indexable, and how you want your website to appear in search with Structured data. With this integration, vendors will be able to grab features of Rank Math from their dashboard.',
                ],
            ],
            'New Feature' => [
                [
                    'title'       => 'Added Admin coupon support',
                    'description' => 'Now admin can create coupons for vendors. We have introduced four types of coupon amount deduction methods. 1. Default (existing vendor coupons), 2. Deduct form admin earning only 3. Deduct from vendor earning only and  4. Admin and vendor can share the coupon amount.',
                ],
                [
                    'title'       => 'Product Bulk Edit feature for vendors/seller',
                    'description' => 'Now vendors will be able to bulk edit their products from product dashboard just like admin can do from admin dashboard.',
                ],
                [
                    'title'       => '[Vendor Verification] Company Verification Support for vendors',
                    'description' => 'In order to use this feature, you need to enable Germanized module.',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Added integration of sweetalert2 for alert, prompt, confirm, toast notification',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] WC decimal separator support added in RMA module',
                    'description' => '',
                ],
                [
                    'title'       => 'Vendor dashboard shipping class suggestion added. dokan-lite issue id no: #1259',
                    'description' => '',
                ],
                [
                    'title'       => '[Store Support] added dynamic date time format support for Store Support module',
                    'description' => '',
                ],
                [
                    'title'       => '[SMS Verification] Updated Twilio SDK',
                    'description' => 'Now sms verification code can be alphanumeric.',
                ],
                [
                    'title'       => '[WholeSale] Previously vendor and vendor staff does not have the ability to become a wholesale customer, this feature has been added now',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation]  Remove previously added autodetect feature for geolocation module',
                    'description' => '',
                ],
                [
                    'title'       => 'Prevent vendor to create category. Previously vendors were capable of creating categories while importing product from CSV file.',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[Vendor Staff] Fixed No email is triggered when an user is added form the wp-admin panel Users menu',
                    'description' => '',
                ],
                [
                    'title'       => 'Send button collapsed (broken layout) on the RTL version of Dokan —> Announcement —> Add Announcement page',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed tooltips doesn\'t work on the Vendor Dashboard > Orders Edit Page',
                    'description' => '',
                ],
                [
                    'title'       => 'New tag wasn’t creating from vendor dashboard product quick edit section',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.9',
        'released' => '2021-10-13',
        'changes'  => [
            'New Feature' => [
                [
                    'title'       => '[Auction] Added auction activity feature for vendors',
                    'description' => 'An exciting feature added to the module is an auction activity feature for vendors, which lets them see all the bid items and price. This was an option previously only available to admins',
                ],
            ],
            'New'         => [
                [
                    'title'       => 'Added two new filter hooks named dokan_pro_scripts and dokan_load_settings_content_shipping so that some feature can be extended via theme authors',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => '[PayPal Marketplace] Added 60+ new country supports for Dokan PayPal Marketplace module.',
                    'description' => '<a href="https://developer.paypal.com/docs/platforms/seller-onboarding/">Here</a> you’ll be able to find all the supported countries',
                ],
                [
                    'title'       => '[Geolocation] Detect user geo location automatically',
                    'description' => 'Under Product/Store search page, user’s will be automatically asked for their current location and After the user approves the permission request, user geolocation will be automatically filled under the location field. Previously, users needed to manually click the location icon to get the current location.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[PayPal Marketplace] Vendors, previously, could not purchase any product if they are subscribed to a vendor subscription plan, which has now been fixed',
                    'description' => '',
                ],
                [
                    'title'       => '[Delivery Time] Vendor dashboard’s Store Settings form fields were not saving if delivery time module was enabled',
                    'description' => '',
                ],
                [
                    'title'       => '[Geolocation] Fixed search filter URL redirect issue.',
                    'description' => 'Previously, when a user submitted Dokan geolocation filter form, it was redirecting in the current page URL instead of the Store listing page.',
                ],
                [
                    'title'       => '[Product Inquiry] Vendor Contact form didn\'t contain “Reply To” email address',
                    'description' => 'Vendor Contact form didn\'t contain “Reply To” email address when a customer would contact a vendor via the product inquiry form. Issue has been resolved now.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.8',
        'released' => '2021-10-04',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => '[WPML] Multiple issue fixed in WPML integration with Dokan',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.7',
        'released' => '2021-09-30',
        'changes'  => [
            'New Feature' => [
                [
                    'title'       => '[Delivery Time] Store Location Pickup',
                    'description' => 'Customers no longer have to wait for their product\'s delivery but rather collect it at their preferable time. They can choose from vendor-provided single or multiple pickup locations during check out and grab their purchases conveniently.',
                ],
                [
                    'title'       => '[PayPal Marketplace] Vendor Subscription support added for Dokan PayPal Marketplace Payment Gateway',
                    'description' => '',
                ],
            ],
            'New'         => [
                [
                    'title'       => '[Vendor Subscription] filter subscription by package and by stores',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] Sort subscription by start date',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Subscription] Subscription Relation Type column added under WooCommerce order table',
                    'description' => 'support added only for Dokan PayPal Marketplace module',
                ],
                [
                    'title'       => '[Vendor Subscription] Subscription Related Orders meta box added under order details page',
                    'description' => 'support added only for Dokan PayPal Marketplace module',
                ],
                [
                    'title'       => '[Vendor Staff] Added export order permission for staffs, vendors and admins',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Automatically process api refund for orders placed using non dokan payment gateways',
                    'description' => 'Added a new settings under Dokan Selling Options',
                ],
                [
                    'title'       => '[Vendor Analytics] User readable Analytics chart data title added',
                    'description' => '',
                ],
                [
                    'title'       => '[Import/Export] sample file download link added in Vendor product CSV import form',
                    'description' => '',
                ],
                [
                    'title'       => 'Center map on location search in store listing geolocation',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Fixed js deprecated warnings on various pages',
                    'description' => '',
                ],
                [
                    'title'       => '[Elementor] multiple deprecated warning fixed',
                    'description' => '',
                ],
                [
                    'title'       => 'Refund amount and tax over refund check',
                    'description' => '',
                ],
                [
                    'title'       => 'Dokan Pro interference removed from WooCommerce Product Import',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] Fixed multiple warnings.',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] Only display correct/selected refund reason in new RMA request page.',
                    'description' => '',
                ],
                [
                    'title'       => '[RMA] RMA not working for variable product',
                    'description' => '',
                ],
                [
                    'title'       => 'Fixed product attribute value sanitization issue',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Staff] Remove admin login url from vendor staff email',
                    'description' => '',
                ],
                [
                    'title'       => 'Hide dokan shipping setting after WPML activation',
                    'description' => '',
                ],
                [
                    'title'       => 'SKU not importing when ID field is blank',
                    'description' => '',
                ],
                [
                    'title'       => 'Export all button disabled when there is no data in vendor',
                    'description' => '',
                ],
                [
                    'title'       => 'Hide product addon settings when creating a grouped product',
                    'description' => '',
                ],
                [
                    'title'       => 'Post object and type check when change vendor support topic status',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.6',
        'released' => '2021-08-31',
        'changes'  => [
            'New' => [
                [
                    'title'       => '[Booking] Added Manual Booking Support for Vendors',
                    'description' => '[Booking] Added manual booking support feature for vendors, now vendors can manual booking from their dashboard.',
                ],
                [
                    'title'       => 'Order Note for Suborder and Main Order Added When an Refund Request Canceled.',
                    'description' => 'Order note for Suborder and main order added when an refund request gets canceled.',
                ],
                [
                    'title'       => 'Added Net Sale Section Under Vendor Dashboard',
                    'description' => 'Added Net Sale section under vendor dashboard where Total order amount was deducted from the refunded amount.',
                ],
                [
                    'title'       => 'Dokan a New Button to Get Admin Setup Wizard',
                    'description' => 'Dokan a new button to get admin setup wizard on tools page',
                ],
                [
                    'title'       => 'Added Apple Sign in Feature Under Dokan Social Login',
                    'description' => 'Added Apple Sign in feature under Dokan Social Login ( https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-apple/ )',
                ],
                [
                    'title'       => 'Added Refund Request Canceled Notification Email',
                    'description' => 'Added refund request canceled notification email template for vendors.',
                ],
                [
                    'title'       => 'Implemented Sorting on Admin Refund Page',
                    'description' => 'Implemented sorting feature for admin Refund page.',
                ],
            ],
            'Fix' => [
                [
                    'title'       => '[Booking] Fixed Dokan Booking Details Shows Wrong Order Information',
                    'description' => '[Booking] fixed Dokan booking details shows wrong order information after admin creates manual booking from WordPress admin panel.',
                ],
                [
                    'title'       => '[Elementor] Fixed Deprecated Warnings While Customising Store with Elementor',
                    'description' => '[Elementor] Fixed deprecated warning notice while customising store page with Elementor module.',
                ],
                [
                    'title'       => '[Elementor] Fixed WhatsApp Not Get Store Name and URL in Elementor',
                    'description' => '[Elementor] Fixed WhatsApp not get Store Name and URL in Elementor.',
                ],
                [
                    'title'       => 'Fixed Shipping Class Amount Adding with Other Shipping Class Amount',
                    'description' => 'Fixed Shipping class amount adding with other shipping class amount issue.',
                ],
                [
                    'title'       => 'Fixed Inconsistency on Sales Report for Refunded Order Due to Caching',
                    'description' => 'Fixed inconsistency on sales report for refunded order due to caching issue.',
                ],
                [
                    'title'       => '[Booking] Display Fatal Error After Deleting Booking Product',
                    'description' => '[Booking] Display fatal error, after deleting booking product which is associated with any customer.',
                ],
                [
                    'title'       => '[Wholesale] The Wholesale Price Digits Next to the Comma Removes While Saving by Admin',
                    'description' => '[Wholesale] The wholesale price digits next to the comma removes while saving variations from the admin screen.',
                ],
                [
                    'title'       => '[Vendor Subscription] Getting Error While Canceling the Vendor Subscription',
                    'description' => '[Vendor Subscription] Getting error while canceling the Vendor Subscription if subscription order gets deleted.',
                ],
                [
                    'title'       => '[Stripe] Fixed Last Used Card Number was Always Stored on Stripe Non 3ds Mode',
                    'description' => '[Stripe] Fixed last used card number was always stored on stripe non 3ds mode for non-subscription products.',
                ],
                [
                    'title'       => '[Product Addons] Vendor Addon Validation Applies to all Vendors Products',
                    'description' => '[Product Addons] vendor addon validation applies to all vendors products if add to cart url was accessing from browser address bar.',
                ],
                [
                    'title'       => '[Vendor Verification] Fixed WordPress Site Health Shows Critical Issues on the Vendor Verification',
                    'description' => '[Vendor Verification] Fixed WordPress site health shows critical issues when the vendor verification module is enabled (PHP Session).',
                ],
                [
                    'title'       => 'Fixed Social Login Style is Broken on the Checkout Page Login Form',
                    'description' => 'Fixed Social Login style is broken on the checkout page login form.',
                ],
                [
                    'title'       => 'Fixed Social API Logins has Session Deadlock Issues',
                    'description' => 'Fixed Social API Logins has session Deadlock issues by setting session time to 5 minutes',
                ],
                [
                    'title'       => 'Fixed Fatal Error While Changing Order Status',
                    'description' => 'Fixed fatal error while changing order status if product has been deleted.',
                ],
                [
                    'title'       => '[Product Subscription] Fixed Product Subscription Pagination on Vendor Dashboard',
                    'description' => '[Product Subscription] Fixed product subscription pagination problem under vendor dashboard.',
                ],
                [
                    'title'       => '[Vendor Subscription] Fixed Vendors Can Publish Their Pending Products',
                    'description' => '[Vendor Subscription] Fixed vendors can publish their products under review also.',
                ],
                [
                    'title'       => 'Admin Refund Page Search by Store Name was not Loading',
                    'description' => 'Admin Refund page search by store name was not loading refunded list items.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.5',
        'released' => '2021-08-16',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Activating module(s) deactivating other active modules',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.4',
        'released' => '2021-08-10',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Added New Store Support Email Templates.',
                    'description' => 'Added new Store Support email templates, now store support tickets email templates can overwride from theme folder.',
                ],
                [
                    'title'       => 'Coupons Automatic Apply for New Products Settings.',
                    'description' => 'Coupons automatic apply for new products settings on coupon create page in vendor dashboard area.',
                ],
                [
                    'title'       => 'Added translation support for text Back to add-on lists.',
                    'description' => 'Added translation support for text Back to add-on lists under html-global-admin-add.php file',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'Vendor Subscription Pricing Table HTML Broken in German Translation.',
                    'description' => 'Vendor subscription pricing table html broken in german translation issue fixed',
                ],
                [
                    'title'       => 'Administrator User Role Added in User Search for ShipStation Auth Query.',
                    'description' => 'Administrator user role added in user search for ShipStation Auth query added',
                ],
                [
                    'title'       => 'Card is Not Saving While Purchasing WooCommerce Subscription Products [Dokan Stripe].',
                    'description' => '[Dokan Stripe] Card is not saving while purchasing WooCommerce Subscription products (3ds/non3ds)',
                ],
                [
                    'title'       => 'Fixed Pagination Error on Vendor Review Page',
                    'description' => 'Fixed pagination error on Vendor Review page',
                ],
                [
                    'title'       => 'Fixed Couple of Translation Issue for Booking Module.',
                    'description' => 'Fixed couple of translation issue for Booking module.',
                ],
                [
                    'title'       => 'Fixed Fatal error if admin downgrade dokan pro plan.',
                    'description' => 'Fixed Fatal error: Uncaught Error: Class DokanPro\Modules\Subscription\Helper not found.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.3',
        'released' => '2021-08-02',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Pending request validation added in refund request validation.',
                    'description' => '',
                ],
                [
                    'title'       => 'Single validation error message will be displayed during refund request validation failure.',
                    'description' => '',
                ],
                [
                    'title'       => 'Dokan CSV exporter has rewritten to minimize product export errors.',
                    'description' => '',
                ],
                [
                    'title'       => 'Dokan CSV exporter has a new option called variation with variable product export.',
                    'description' => '',
                ],
                [
                    'title'       => 'Dokan CSV Importer has rewritten to minimize product import errors.',
                    'description' => '',
                ],
                [
                    'title'       => 'Dokan Import Export logic will not be imposed during product import export from WooCommerce product export importer.',
                    'description' => '',
                ],
                [
                    'title'       => 'Admin can add new vendor staff from wp-admin users add/edit page',
                    'description' => '',
                ],
                [
                    'title'       => '[Dokan Auction] Validation error feedback for auction product same SKU',
                    'description' => '',
                ],
                [
                    'title'       => '[PayPal Marketplace] added a settings fields to get bn code from admin',
                    'description' => '',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Pending request validation added in refund',
                    'description' => '',
                ],
                [
                    'title'       => '[Vendor Review] Review date time display according to admin selected date time formato',
                    'description' => '',
                ],
                [
                    'title'       => '[Wirecard] Dokan Wirecard module compatibility with WordPress version 5.8',
                    'description' => '',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => '[Delivery Time] JS error fix for fresh installation vendor info',
                    'description' => '',
                ],
                [
                    'title'       => '[Wholesale] Product addon and RMA addon not working with wholesale product fixes',
                    'description' => '',
                ],
                [
                    'title'       => 'New subscription order is being created for profile save is resolved',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.2',
        'released' => '2021-07-15',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => '[PayPal Marketplace]fixed PayPal Marketplace refund conflict with other payment gateway’s refund',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe] fixed deduct gateway fee from vendor balance table after a refund is approved via Stripe 3ds and non-3ds',
                    'description' => '',
                ],
                [
                    'title'       => '[Stripe] fixed Stripe non3ds refund is not working if admin commission is set to zero',
                    'description' => '',
                ],
                [
                    'title'       => 'fixed Order on Cash on delivery deducting money from Vendor balance while processing Refund',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.1',
        'released' => '2021-07-12',
        'changes'  => [
            'New Module' => [
                [
                    'title'       => '[New Module] New Module Named EU Compliance Fields',
                    'description' => 'Added a new module named <strong>EU Compliance Fields</strong>. In this module, you\'l get Custom fields for vendors, Custom fields for customers, <strong>Germanized for WooCommerce</strong> plugin support for vendors and last but not least <strong>Individual PDF invoice numbers</strong> support for vendors.',
                ],
            ],
            'New'        => [
                [
                    'title'       => '[Vendor Subscription] Added Vendor Subscription Information Section',
                    'description' => 'Added  Vendor Subscription information section under single vendor edit page.',
                ],
                [
                    'title'       => '[Vendor Subscription] Hide Create and Add New Button if Only One Product Creation',
                    'description' => 'Hide Create and Add New button if only one product creation is allowed.',
                ],
            ],
            'Fix'        => [
                [
                    'title'       => '[Vendor Subscription] Create and Add New Product button redirect According to Subscription',
                    'description' => 'Fixed create and add new product button redirect according to subscription package allowed product',
                ],
                [
                    'title'       => '[Delivery Time] Fixed Theme Compatibility',
                    'description' => 'Fixed theme compatibility design issues on checkout page.',
                ],
                [
                    'title'       => 'Fixed Rewrite Rules Issues After Dokan Pro Plugin is Activated',
                    'description' => 'Fixed rewrite rules issues after Dokan Pro plugin is activated for Dokan Pro and all Modules',
                ],
                [
                    'title'       => '[Booking] Fixed Booking Calendar Styling Issue',
                    'description' => 'Fixed Booking calendar styling issue for all-day bookings',
                ],
                [
                    'title'       => '[Elementor] Fixed fatal Error on Elementor Store Social Profile',
                    'description' => 'Fixed fatal error on elementor StoreSocialProfile widget',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.0',
        'released' => '2021-07-01',
        'changes'  => [
            'New Module' => [
                [
                    'title'       => 'Introducing a new Payment Gateway Named Dokan PayPal Marketplace',
                    'description' => 'Added a new Payment Gateway named <strong>Dokan PayPal Marketplace</strong>. This module will enable PayPal Commerce Platform (PCP) features including split & Multi-seller payments, multiple disbursement method and <a href="https://wedevs.com/dokan/modules/dokan-paypal-marketplace">more</a>. This new module will be available only on Dokan Pro <strong>Business</strong> and <strong>Enterprise</strong> Plans due to a API restriction from PayPal. We will include this module with all Dokan Pro plans in near future.',
                ],
                [
                    'title'       => 'Introducing a new module named Delivery Time',
                    'description' => 'Added a new module named <strong>Delivery Time: Let customers choose their delivery date & time</strong> with all Dokan Pro Plans. Check out <a href="">module documentation</a> for more details.',
                ],
            ],
            'New'        => [
                [
                    'title'       => '[Elementor] Added product filtering options for Single Store Page',
                    'description' => 'If you are using Dokan Elementor module to design your single store page, now you will be able to add product filtering options in your single store page.',
                ],
                [
                    'title'       => '[Elementor] Added SPMV support for Single Store Page',
                    'description' => 'A new Elementor widget to display SPMV support in Single Store Page',
                ],
                [
                    'title'       => '[Elementor] Added Social widget support for Single Store Page',
                    'description' => 'A new Elementor widget to display Social details in Single Store Page',
                ],
                [
                    'title'       => '[Elementor] Added RMA module support for Singe Store Page Elementor widget',
                    'description' => 'A new Elementor widget to display RMA related fields on single store page',
                ],
                [
                    'title'       => 'Added a new settings to enable/disable Product shipping tab and optimised query for vendor available shipping listing',
                    'description' => '',
                ],
                [
                    'title'       => 'Added a Register button on login popup form',
                    'description' => '',
                ],
            ],
            'Fix'        => [
                [
                    'title'       => 'Removed existing role from an user while user become a vendor',
                    'description' => '',
                ],
                [
                    'title'       => 'Set admin default map address as Geolocation data when a new seller is registered',
                    'description' => '',
                ],
                [
                    'title'       => 'Shipping tax status from vendor shipping methods have no effect',
                    'description' => '',
                ],
                [
                    'title'       => 'Left/Right Map position redirect to the another page issue fixed',
                    'description' => '',
                ],
                [
                    'title'       => 'Subscription pack list broken when use language other than English',
                    'description' => '',
                ],
                [
                    'title'       => 'Unusual number of emails to the vendor staffs on a new order',
                    'description' => '',
                ],
                [
                    'title'       => 'Disabled shipping zone on single product tab if no shipping method is found',
                    'description' => '',
                ],
                [
                    'title'       => 'Become a vendor button not showing when user role is other than customer',
                    'description' => '',
                ],
                [
                    'title'       => 'Wrong direction for shipping status email templates',
                    'description' => '',
                ],
                [
                    'title'       => 'Disabled shop query when geo map turn off from dokan admin settings',
                    'description' => '',
                ],
                [
                    'title'       => '[SPMV] Sell this item not showing when vendors subscription module is enabled, but the subscription is disabled',
                    'description' => '',
                ],
                [
                    'title'       => '[Booking] Cancellation time gets changed from Weeks to Months after saving a Cancellable Booking Product',
                    'description' => '',
                ],
                [
                    'title'       => 'Return Request - Conversations issue for special characters',
                    'description' => '',
                ],
                [
                    'title'       => 'Store dropdown vendor name placeholder changed to Store Name in admin reports page',
                    'description' => '',
                ],
                [
                    'title'       => 'Login Popup css fixed for guest user',
                    'description' => '',
                ],
                [
                    'title'       => 'Email template override directory location corrected',
                    'description' => '',
                ],
                [
                    'title'       => 'RMA policy content format now saves correctly',
                    'description' => '',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.2.5',
        'released' => '2021-05-11',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Products not showing on vendor dashboard product listing page',
                    'description' => 'Fatal error on vendor dashboard product listing page when vacation module is disabled or doesn\'t installed.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.2.4',
        'released' => '2021-05-08',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Shipping Status for Vendor Orders',
                    'description' => 'Shipping Status for vendor orders. Now vendors can manage thir shipments for customers.',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Attach Source to Customer Object First so That Payment Get Processed',
                    'description' => 'Attach source to customer object first so that payment get processed successfully and then remove source if necessary: stripe non3ds.',
                ],
                [
                    'title'       => 'Live Search with Suggestion Set Default',
                    'description' => 'Live search with suggestion set default, also make on dokan live search widgets.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Vendor Variation Product Import Error',
                    'description' => 'Vendor variation product import error fixed.',
                ],
                [
                    'title'       => 'Store Category Not Saving from Setup Widget',
                    'description' => 'Store category not saving from setup widget issue fixed.',
                ],
                [
                    'title'       => 'Updating Product Details Quick Edit Resets Shipping Class',
                    'description' => 'Updating product details using Quick Edit resets the Shipping Class fixed.',
                ],
                [
                    'title'       => 'Does Not Reflect Today\'s Report in Sales by Day',
                    'description' => 'Does not reflect today\'s report in sales by day or overview.',
                ],
                [
                    'title'       => 'Product Doesn\'t Go Offline While Activating Vacation',
                    'description' => 'Product doesn\'t go offline while activating vacation mode issue fixed.',
                ],
                [
                    'title'       => 'All Log Table Filter in Translation',
                    'description' => 'All log table filter in translation for admin reports.',
                ],
                [
                    'title'       => 'Vendor Can Create Tag with Product Import',
                    'description' => 'Vendor can create tag in product import support.',
                ],
                [
                    'title'       => 'Product Live Search Not Work With Android',
                    'description' => 'Android product live search issues fixed.',
                ],
                [
                    'title'       => 'Vendor Store Page Title Replace with Store SEO Title',
                    'description' => 'Vendor store page title replace with store seo title.',
                ],
                [
                    'title'       => 'Store Follow Email Triggering Though Email is Disabled in WC Email',
                    'description' => 'Store follow email triggering though email is disabled in WC email.',
                ],
                [
                    'title'       => 'Update Store Progress When Stripe Connected',
                    'description' => 'Update store progress bar when stripe connected by vendor.',
                ],
                [
                    'title'       => 'Refund Amount and Tax Over Refund Check',
                    'description' => 'Refund amount and tax over refund check.',
                ],
                [
                    'title'       => 'Cannot Charge a Customer That Has no Active Card Error',
                    'description' => 'Cannot charge a customer that has no active card - error if trying to process payment from guest user with non-connected vendors.',
                ],
                [
                    'title'       => 'Set Newly Added Card as Default Payment Source',
                    'description' => 'Set newly added card as default payment source while updating a vendor subscription.',
                ],
                [
                    'title'       => 'Don\'t Save Card If Save Card Checkbox is Not Selected',
                    'description' => 'Don\'t save card if save card checkbox is not selected - Stripe 3DS.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.2.3',
        'released' => '2021-04-30',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Digital and Physical Product Types Vendors',
                    'description' => 'Digital and Physical product types selling option for vendors.',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Verification Clickable Link Added Staff Notify Email',
                    'description' => 'Verification clickable link added on new staff notify email body.',
                ],
                [
                    'title'       => 'IP and Agent Info Removed from Product Enquiry Email',
                    'description' => 'IP and agent info removed from product enquiry email, which send by customer from single product page.',
                ],
                [
                    'title'       => 'Store Support for Customer Order Details Page',
                    'description' => 'Store support for WooCommerce customer my account order details page.',
                ],
                [
                    'title'       => 'Product Shipping Tab Added Continent Countries and States Data',
                    'description' => 'Product shipping tab added continent countries and states data.',
                ],
                [
                    'title'       => 'The Per Class calculation Type Option is Selected Flat Rate Shipping',
                    'description' => 'The Per Class calculation type option is selected by default for flat rate shipping.',
                ],
                [
                    'title'       => 'Add New Filter Hook on Admin Vendor Report Order Status Filters',
                    'description' => 'Add new filter hook on admin vendor report order status filters options.',
                ],
                [
                    'title'       => 'Rearranged Stripe API Credentials Fields on Stripe Connect Payment',
                    'description' => 'Rearranged Stripe API Credentials Fields on Stripe Connect Payment Gateway Setting page.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Vendor Category Box Hide When Outside Click',
                    'description' => 'Vendor category box hide when outside click on store listing page search filter area.',
                ],
                [
                    'title'       => 'Translation Issue Fixed on Store Support Modal',
                    'description' => 'Translation issue fixed on store support modal.',
                ],
                [
                    'title'       => 'Vendor Product Quick Edit Product Status Not Changing Resolved',
                    'description' => 'Vendor product quick edit product status not changing issue fixed.',
                ],
                [
                    'title'       => 'RMA Script Loading Issue Fixed',
                    'description' => 'RMA script loading issue on vendor product edit page.',
                ],
                [
                    'title'       => 'Variation Product Not Working with RMA',
                    'description' => 'Variation product not working with RMA issue fixed.',
                ],
                [
                    'title'       => 'Customer is Seeing the Default Refund Reasons Instead of the Selected Reasons [RMA]',
                    'description' => 'RMA: Customer is seeing the default Refund Reasons instead of the overridden refund reasons set in the edit product form.',
                ],
                [
                    'title'       => 'Store Support for Product Option Fully Disable When Disabled it from Admin',
                    'description' => 'Vendor setting page store support for product option fully disable when disabled it from admin.',
                ],
                [
                    'title'       => 'Wrong Instruction for the Map Zoom Level Dokan Admin Settings',
                    'description' => 'Wrong instruction for the map zoom level in the geolocation settings fixed now.',
                ],
                [
                    'title'       => 'Cannot Charge a Customer That has no Active Card, While Checking Out as Guest [Stripe]',
                    'description' => '[Stripe] Error: Cannot charge a customer that has no active card, while checking out as guest.',
                ],
                [
                    'title'       => 'Fix the dokan-hide Class Placement on the Store Settings',
                    'description' => 'Fix the dokan-hide class placement on the store settings.',
                ],
                [
                    'title'       => 'Germanized for WooCommerce and Email Verification conflict',
                    'description' => 'Germanized for WooCommerce and Email Verification conflict issue fixed.',
                ],
                [
                    'title'       => 'User Subscription Pagination Query',
                    'description' => 'User subscription pagination query issue fixed.',
                ],
                [
                    'title'       => 'Generate Shortcode Button Error',
                    'description' => 'Generate Shortcode Button doing_it_wrong error fixed now.',
                ],
                [
                    'title'       => 'Product Import Updating Another Vendor Product',
                    'description' => 'Product import updating another vendor product issue fixed now.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.2.2',
        'released' => '2021-03-31',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Added 3DS Auth Flow for Changing Payment Method [Stripe]',
                    'description' => 'Added 3DS auth flow for changing payment method from My Account -> Payment Methods page',
                ],
                [
                    'title'       => 'Added Change Payment Method for Subscriptions from My Account [Stripe]',
                    'description' => 'Added Change payment method for subscriptions from My Account -> Subscriptions for Stripe 3ds mode.',
                ],
                [
                    'title'       => 'Added Failed Order Processing Feature [Stripe]',
                    'description' => 'Added failed order processing feature for both Stripe 3ds and non3ds payment method.',
                ],
                [
                    'title'       => 'Added Metadata for Stripe Transactions for 3ds Mode [Stripe]',
                    'description' => 'Added metadata for stripe transactions for 3ds mode, this will help track transfers made on vendors account and the vendors will also be able to track orders made on their account..',
                ],
                [
                    'title'       => 'Added Support for Renewing Subscription Via Modal [Stripe]',
                    'description' => 'Added support for renewing subscription via modal for stripe 3ds mode.',
                ],
                [
                    'title'       => 'Implemented Automatic Refund for Stripe 3ds Mode [Stripe]',
                    'description' => 'Implemented automatic refund for stripe 3ds mode (refund will be processed from admin stripe account, then the transferred amount from vendor account will be automatically reversed to admin account).',
                ],
                [
                    'title'       => 'Added Announcement Notice if Vendors Stripe Account is Not Connected [Stripe]',
                    'description' => 'Added announcement notice if vendors stripe account is not connected with stripe (both 3ds and non-3ds). In 3ds mode, if vendor stripe currency is not similar to site currency they will also receive announcement notice. Added two new admin settings to control this behavior..',
                ],
                [
                    'title'       => 'New Action Hook Added - dokan_auction_before_general_options [Auction]',
                    'description' => 'New action hook added - dokan_auction_before_general_options.',
                ],
                [
                    'title'       => 'Product Image Support Added for New Order Email Vendor Staff [Vendor Staff]',
                    'description' => 'Product image support added for new order email vendor staff. Now can show the product image by using filter hooks which one support WooCommerce.',
                ],
                [
                    'title'       => 'Dokan Shipping Multiple Issues Fixed and Some Enhancements',
                    'description' => 'Dokan shipping multiple issues fixed and some enhancements. Now delete vendor shipping data when main zone delete from admin area, if admin update any zone from admin then it will effect all vendor shipping methods, single product tab shipping info updated.',
                ],
                [
                    'title'       => 'Show Store Name Instead of Selected Vendors if Announcement Sent to a Single Vendor',
                    'description' => 'Show store name instead of selected vendors if announcement sent to a single vendor in announcement listing page..',
                ],
                [
                    'title'       => 'Dokan Tools Page "Install Pages Button" Disabled',
                    'description' => 'Dokan tools page "Install Pages Button" disabled after successful Installation of page',
                ],
                [
                    'title'       => 'Stock Unwanted Management Options Removed',
                    'description' => 'Stock unwanted management options removed now.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Fixes Non3ds Refund-related Issues Settings to Control This Behavior [Stripe]',
                    'description' => 'Fixes non3ds refund-related issues (refund doesn\'t work if a vendor is not connected with stripe.)settings to control this behavior [Stripe].',
                ],
                [
                    'title'       => 'Floating Point Error on Wirecard Integration [Wirecard]',
                    'description' => '[Wirecard] Floating point error on Wirecard integration.',
                ],
                [
                    'title'       => 'Dokan Seller XML File Returns Uncaught Error [Store SEO]',
                    'description' => '[Store SEO] dokan_sellers-sitemap.xml file returns uncaught error.',
                ],
                [
                    'title'       => 'Product Review List, Empty Bulk Action Error',
                    'description' => 'Product review list, empty bulk action error fixed now.',
                ],
                [
                    'title'       => 'Variable Subscription and Variable Product Conflict',
                    'description' => 'Variable subscription and variable product conflict issue fixed now.',
                ],
                [
                    'title'       => 'Sale Price is Not Working with the Variable Product.',
                    'description' => 'Sale Price: Sale price is not working with the variable product.',
                ],
                [
                    'title'       => 'Date Picker is Unavailable for Product Variations',
                    'description' => 'Products: Date Picker is unavailable for product variations.',
                ],
                [
                    'title'       => 'Store Email Sends an Email from the WordPress Email Instead of the Site Admin Email',
                    'description' => 'Store Email: The Store Email sends an email from the WordPress email instead of the site admin email.',
                ],
                [
                    'title'       => 'Booking Shows Order Number When the Booking Status is In Cart ',
                    'description' => 'Booking: Booking shows order number when the booking status is In Cart.',
                ],
                [
                    'title'       => 'Booking Resource Label Does not Display After Save',
                    'description' => 'Booking resource label does not display after save.',
                ],
                [
                    'title'       => 'Store Review Data Display and Pagination',
                    'description' => 'Store review data display and pagination.',
                ],
                [
                    'title'       => 'Loco Translate Strings Can Not be Translated',
                    'description' => 'Loco translate strings can not be translated issue fixed now.',
                ],
                [
                    'title'       => 'Featured Stores Elementor Widgets is Broken Issue Fixed',
                    'description' => 'Featured stores Elementor widget is broken issue fixed #1146.',
                ],
                [
                    'title'       => 'Reply to Custom Email Added on Product Inquiry Email',
                    'description' => 'Reply to custom email added on product inquiry email #1181.',
                ],
                [
                    'title'       => 'Store Support form Conflicting with Elementor',
                    'description' => 'Store support form conflicting with Elementor in the single store page.',
                ],
                [
                    'title'       => 'Fatal error on RMA Details Page Issue Fixed',
                    'description' => 'Fatal error on RMA details page when product somehow got deleted issue fixed.',
                ],
                [
                    'title'       => 'Pagination Not Working on Vendor Return Request Page',
                    'description' => 'Pagination not working on vendor return request page issue fixed.',
                ],
                [
                    'title'       => 'Store Link Added on RMA Request',
                    'description' => 'Store link added on RMA request page on store name.',
                ],
                [
                    'title'       => 'Vendor Search Filter form Widget Not Working Issue Fixed',
                    'description' => 'Vendor search filter form widget not working for vendor search issue fixed.',
                ],
                [
                    'title'       => 'Auto-zoom Set Minimum Zoom Label',
                    'description' => 'Auto-zoom set minimum zoom label check with admin option.',
                ],
                [
                    'title'       => 'The External Product Type Fields Show Permanently',
                    'description' => 'The external product type fields show permanently issue fixed now.',
                ],
                [
                    'title'       => 'Report Export and Filter Date Range in Different Language',
                    'description' => 'Report Export and filter date range in different language does not work fixed now.',
                ],
                [
                    'title'       => 'Germanized Plugin Support for Email Verification',
                    'description' => 'Germanized plugin support for email verification footer placement.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.2.1',
        'released' => '2021-05-03',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'External/Affiliate Product for Vendor [External/Affiliate Product]',
                    'description' => 'External/Affiliate product support added for vendor',
                ],
                [
                    'title'       => 'Added Rest API Support for Follow Store [Follow Store]',
                    'description' => 'Added rest api support for follow store module.',
                ],
                [
                    'title'       => 'Announcements 3 New Options Added for Vendors [Announcements]',
                    'description' => 'Announcements 3 new options added enabled, disabled, featured sellers.',
                ],
                [
                    'title'       => 'Vendor Withdraw Individual Threshold Days Option Added [Store Withdraw]',
                    'description' => 'Admin can set vendor individual threshold days from user edit page in admin area.',
                ],
                [
                    'title'       => 'Disable "Support Button" for Single Product Page [Store Support]',
                    'description' => 'Disable "Support Button" for single product page in vendor settings page when Admin disable support from admin settings.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Fixed PHP 8 Warnings',
                    'description' => 'Fixed some PHP 8 warnings.',
                ],
                [
                    'title'       => 'Vendor Report Date Filter Conflicts Issue Fixed',
                    'description' => 'Vendor report date filter conflicts with non english / local languages set as site language.',
                ],
                [
                    'title'       => 'Apply Product Lot Discount on Order',
                    'description' => 'Apply product lot discount on order issue fixed now.',
                ],
                [
                    'title'       => 'Typos in Edit Product Page and Subscription Page',
                    'description' => 'Typos in edit product page and subscription page fixed now.',
                ],
                [
                    'title'       => 'Whole Price is Not Stored as Decimal Issue Fixed',
                    'description' => 'Whole price is not stored as decimal when decimal separator is comma issue fixed now.',
                ],
                [
                    'title'       => 'Combine Commission Field is Missing on Setup Wizard',
                    'description' => 'Combine commission field is missing on setup wizard issue fixed now.',
                ],
                [
                    'title'       => 'Vendor Analytics Menu not Showing for Administrator',
                    'description' => 'Vendor analytics menu not showing for administrator dokandar issue fixed now.',
                ],
                [
                    'title'       => 'Turn Off Geolocation Auto zoom for Product',
                    'description' => 'Turn off geolocation auto zoom for single product page.',
                ],
                [
                    'title'       => 'Mapbox Zoom Icons Missing',
                    'description' => 'Mapbox zoom icons missing issue fixed now.',
                ],
                [
                    'title'       => 'Elementor Buttons Icon Missing',
                    'description' => 'Elementor buttons icon missing issue resolved.',
                ],
                [
                    'title'       => 'Error Showing in Store Support Ticket',
                    'description' => 'Error showing in store support ticket details if order remove somehow.',
                ],
                [
                    'title'       => 'Dokan Pages Duplicate Issue Fixed',
                    'description' => 'Dokan pages duplicate issue fixed when try to use tools from Dokan admin area.',
                ],
                [
                    'title'       => 'Parent SKU Not Saving on Variation Product',
                    'description' => 'Parent SKU not saving on variation product issue fixed now.',
                ],
                [
                    'title'       => 'Warning Showing Product Listing Page',
                    'description' => 'Warning showing product listing page when imported product on vendor dashboard area.',
                ],
                [
                    'title'       => 'Design Related Problem Fixed All Logs Report',
                    'description' => 'Design related problem in all logs issue report in Dokan admin area.',
                ],
                [
                    'title'       => 'Deprecated Gplus Cleanup',
                    'description' => 'Deprecated Gplus cleanup. Now Google Plus option totally removed from dokan.',
                ],
                [
                    'title'       => 'Booking Details Page Showing Index Error Warning',
                    'description' => 'Fixed an issue where booking details page showing index error warning.',
                ],
                [
                    'title'       => 'Booking SKU Not Saving',
                    'description' => 'Booking SKU not saving, hidden input problem fixed now.',
                ],
                [
                    'title'       => 'Some Filter Was Being Used as Action',
                    'description' => 'Some filter was being used as action, now resolved that issues.',
                ],
                [
                    'title'       => 'Product Discount Price is Not Updating Issue Fixed',
                    'description' => 'Product Discount price is not updating if vendor subscription module is active.',
                ],
                [
                    'title'       => 'Admin Dokandar Staff Module Access Issue',
                    'description' => 'Admin dokandar staff module access issue fixed now.',
                ],
                [
                    'title'       => 'Announcement Page Added and Pagination Issue Fixed',
                    'description' => 'Announcement page added for vendor and pagination issue fixed.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.2.0',
        'released' => '2021-01-29',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Added WhatsApp Provider [Livechat]',
                    'description' => 'Added WhatsApp provider for livechat [Livechat]',
                ],
                [
                    'title'       => 'Added Tawk.to Provider [Livechat] ',
                    'description' => 'Added tawk.to provider for livechat [Livechat] ',
                ],
                [
                    'title'       => 'Added New Settings Where Admin Can Set Whether to Display the Map [Geolocation]',
                    'description' => 'Added new settings where admin can set whether to display the map in shop or store listing page or both page.',
                ],
                [
                    'title'       => 'Added Store Support for Single Product [Store Support]',
                    'description' => 'Added Store support form for single product page.',
                ],
                [
                    'title'       => 'Added Separate Email Subject and Body for Subscription Cancellation [Vendor Subscription]',
                    'description' => 'Added separate email subject and body for subscription cancellation and alert emails.',
                ],
                [
                    'title'       => 'Added Dokan Upgrader to Move Existing Vendor Subscription [Vendor Subscription]',
                    'description' => 'Added Dokan upgrader to move existing vendor subscription data to new keys.',
                ],
                [
                    'title'       => 'Update Billing Cycle Stops Fields [Vendor Subscription]',
                    'description' => 'Update Billing Cycle Stops fields if Billing Cycle Type changes.',
                ],
                [
                    'title'       => 'Changed Product Pack Start Date and End Date Formate [Vendor Subscription]',
                    'description' => 'Changed product_pack_startdate and product_pack_enddate value from date() to current_datetime(), this will fix timezone mismatch.',
                ],
                [
                    'title'       => 'Changed Some Meta Key in Subscription Data [Vendor Subscription]',
                    'description' => 'Changed _subscription_period_interval, _subscription_period, _subscription_length into _dokan_subscription_period_interval, _dokan_subscription_period, _dokan_subscription_length. This was causing conflict with WooCommerce Subscription.',
                ],
                [
                    'title'       => 'Disable Email Verification If Subscription Module is Enabled [Vendor Subscription]',
                    'description' => 'Disable email verification if subscription module is enabled in the registration form.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'All Metadata are Not Exporting [Import Export]',
                    'description' => 'All metadata are not exporting issue fixed.',
                ],
                [
                    'title'       => 'Dokan Auction Product Addons Are Not Saving [Auction]',
                    'description' => 'Dokan auction product addons are not saving issue fixed.',
                ],
                [
                    'title'       => 'Fixed Seller Can Clone Product Without a Subscription [SPMV]',
                    'description' => 'Fixed seller can clone product using sell this item without a subscription.',
                ],
                [
                    'title'       => 'Product Duplicate Button Based on Active Subscription [Vendor Subscription]',
                    'description' => 'Product duplicate button based on active subscription issue fixed.',
                ],
                [
                    'title'       => 'Booking Buffer Period Duration Label Translatable [Booking]',
                    'description' => 'Booking buffer period duration unit label is not translatable now fixed.',
                ],
                [
                    'title'       => 'Email Subscription Ending Soon Email is Never Sent [Vendor Subscription]',
                    'description' => 'Email Subscription Ending Soon email is never sent issue fixed.',
                ],
                [
                    'title'       => 'Recurring Payment is Not Canceling if Admin Assigns Non-recurring Subscription [Vendor Subscription]',
                    'description' => 'Recurring payment is not canceling if admin assigns non-recurring subscription from the admin dashboard.',
                ],
                [
                    'title'       => 'Subscription Purchased by PayPal was Canceled Immediately [Vendor Subscription]',
                    'description' => 'Subscription purchased by PayPal was canceled immediately if subscription pack is not recurring.',
                ],
                [
                    'title'       => 'Added Additional Fee if Commission Type is Combined for Non-dokan Payment [Vendor Subscription]',
                    'description' => 'Added additional fee if commission type is combined for non-dokan payment gateways issue fixed.',
                ],
                [
                    'title'       => 'Multiple Stripe Webhook Was Creating, Moved Webhook [Stripe]',
                    'description' => 'Multiple stripe webhook was creating, moved webhook creation code under activation/deactivation hooks, deactivate and active module to apply these changes.',
                ],
                [
                    'title'       => 'Fixed Fatal Error if the Source String is Empty if Users Try to Change Payment [Stripe]',
                    'description' => 'Fixed fatal error if the source string is empty if users try to change payment method from my account page.',
                ],
                [
                    'title'       => 'Fixed Fatal Error if the Order Value is Less Than or Equal to Zero for Stripe 3DS Mode [Stripe]',
                    'description' => 'Fixed fatal error if the order value is less than or equal to zero for Stripe 3DS mode, this was causing the whole payment to fail.',
                ],
                [
                    'title'       => 'Relist Feature is Unavailable on the Vendor Dashboard [Auction]',
                    'description' => 'Relist feature is unavailable on the vendor dashboard issue fixed.',
                ],
                [
                    'title'       => 'Vendors Can not Add & Save New Tags on Auction Type Products [Auction]',
                    'description' => 'Vendors can not add & save new tags on Auction type products issue fixed.',
                ],
                [
                    'title'       => 'Fixed Elementor Module Causing Issue with Support Ticket Mail [Elementor]',
                    'description' => 'Fixed Elementor module causing issue with support ticket mail issue fixed.',
                ],
                [
                    'title'       => 'Fixed Mapbox Issue with RTL Supported Language [Geolocation]',
                    'description' => 'Fixed Mapbox issue with RTL supported language.',
                ],
                [
                    'title'       => 'Fixed Geolocation Position Settings Left and Right [Geolocation]',
                    'description' => 'Fixed Geolocation position settings left and right area working proper.',
                ],
                [
                    'title'       => 'Geolocation Map Auto zoom When Getting Long Distance [Geolocation]',
                    'description' => 'Geolocation map auto zoom when getting long distance between multiples stores/products locations.',
                ],
                [
                    'title'       => 'Hide Export Button When no Product Found for That Author [Import Export]',
                    'description' => 'Hide export button when no product found for that author.',
                ],
                [
                    'title'       => 'Vendor Analytics Deprecated Warning [Vendor Analytics]',
                    'description' => 'Vendor analytics deprecated warning fixed now.',
                ],
                [
                    'title'       => 'Delete Recurring Subscription Key After a Subscription Has Been Deleted [Subscription]',
                    'description' => 'Delete recurring subscription key after a subscription has been deleted.',
                ],
                [
                    'title'       => 'Fixed Wrong Order Reference URL in Support Tickets [Store Support]',
                    'description' => 'Fixed wrong order reference URL in support tickets in WooCommerce my account and Dokan vendor dashboard area.',
                ],
                [
                    'title'       => 'Product Add pop-up Validation Error Message Style',
                    'description' => 'Product add pop-up validation error message style issue fixed.',
                ],
                [
                    'title'       => 'Fixed dokan_admin JS var Undefined Issue',
                    'description' => 'Fixed dokan_admin js var undefined issue at add/edit product page.',
                ],
                [
                    'title'       => 'Fixed Undefined ID Notice While Creating Products',
                    'description' => 'Fixed undefined ID notice while creating products from vendor dashboard.',
                ],
                [
                    'title'       => 'Downloadable Options Panel Not Showing',
                    'description' => 'Downloadable options panel not showing.',
                ],
                [
                    'title'       => 'Fixed Vendor Setting to Discount on Order Calculation Error',
                    'description' => 'Fixed Vendor Setting to discount on order calculation error fixed now.',
                ],
                [
                    'title'       => 'Fixed WPML Conflict with Menu and Widget Page',
                    'description' => 'Fixed WPML conflict with menu and widget page when users try to switch between language.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.4',
        'released' => '2021-01-11',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Added Disconnect Button and Some Issues Fixed [Vendor Analytics]',
                    'description' => 'Added disconnect button on dokan admin setting page, also fixed some issues.',
                ],
                [
                    'title'       => 'Product Add-on Module Template Override [Product Addon]',
                    'description' => 'Product add-on module template override does not work with theme folder issue fixed.',
                ],
                [
                    'title'       => 'Changed Social Login Sign in URL Change [Vendor Social Login]',
                    'description' => 'Changed social login sign in URL from dokan_reg to vendor_social_reg  on query param.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Import Option Allows Vendors to Create Categories',
                    'description' => 'Import option allows vendors to create categories issue fixed, now vendor not able to create new category when import csv.',
                ],
                [
                    'title'       => 'If Admin Commission for Flat Type Commission is Set to Zero Was Showing Not Set [Vendor Commission]',
                    'description' => 'If admin commission for flat type commission is set to zero it was showing not set.',
                ],
                [
                    'title'       => 'Text-domain Missing on Confirmation Alert',
                    'description' => 'Text-domain missing on confirmation alert. Now it fixed all alert confirmation on vendor dashboard.',
                ],
                [
                    'title'       => 'Default Attribute Was Not Displaying [Vendor Product Update]',
                    'description' => 'Default attribute was not displaying when variation product edit from vendor dashboard issue fixed.',
                ],
                [
                    'title'       => 'Vendor Details Admin Commission Label Changed',
                    'description' => 'Vendor details admin commission label changed to commission rate on the admin area dokan vendor details page.',
                ],
                [
                    'title'       => 'Fixed Vendor Staff Was Not Receiving New Order Email [Vendor Staff]',
                    'description' => 'Fixed vendor staff was not receiving new order email issue fixed now.',
                ],
                [
                    'title'       => 'Fixed Variations Was Not Saving Correctly [Vendor Product]',
                    'description' => 'Fixed Variations was not saving correctly from vendor dashboard when try to use multiples attributes.',
                ],
                [
                    'title'       => 'Fixed Store Support Form Showing Wrong With Elementor [Elementor]',
                    'description' => 'Fixed store support form showing wrong with Elementor if still have logged out users.',
                ],
                [
                    'title'       => 'Replaced WP SEO Deprecated Functions [Product SEO]',
                    'description' => 'Replaced WP SEO deprecated functions, now product seo capable with latest wp seo plugin.',
                ],
                [
                    'title'       => 'Fixed Product Location Mismatch [Geolocation]',
                    'description' => 'Fixed product location mismatch if created from admin and try to reassign a vendor on a product.',
                ],
                [
                    'title'       => 'Auction Product SKU is Not Updating [Auction]',
                    'description' => 'Auction product SKU is not updating or saving now fixed.',
                ],
                [
                    'title'       => 'Single Product Multiple Vendor Redirection [Auction]',
                    'description' => 'Single Product Multiple Vendor redirection for auction and booking type product.',
                ],
                [
                    'title'       => 'Updated Stripe Codebase and Fixed Some Issues [Dokan Stripe]',
                    'description' => 'Updated stripe codebase and fixed some issues with Stripe modules.',
                ],
                [
                    'title'       => 'Responsive Dashboard Product and Order Table',
                    'description' => 'Responsive dashboard product and order table now fixed.',
                ],
                [
                    'title'       => 'Removed Addon Validation for Dokan Subscription [Dokan Subscription]',
                    'description' => 'Removed addon validation for Dokan Subscription product.',
                ],
                [
                    'title'       => 'Vendor Updates Other Vendor Product',
                    'description' => 'Vendor updates other vendor product if SKU/ID is same, instead of creating a new product for requesting vendor.',
                ],
                [
                    'title'       => 'Make Product Status Draft After a Vendor Cancels Their Subscriptions [Dokan Subscriptions]',
                    'description' => 'Make product status draft after a vendor/admin immediately cancels their subscriptions.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.3',
        'released' => '2020-12-17',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Added Tax/Discount for Vendor Subscriptions [WireCard]',
                    'description' => 'Added tax/discount for Vendor Subscriptions, previously only actual product price was sent to API.',
                ],
                [
                    'title'       => 'Added a New Exception if Vendor Account [WireCard]',
                    'description' => 'Added a new exception if vendor account is not linked with wire card, now the user will get proper error messages instead of Something went wrong.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Refund and Announcement Page Loading Problem [Dokan Admin]',
                    'description' => 'Refund and announcement listing loading problem and text-domain issue fixed.',
                ],
                [
                    'title'       => 'Booking Addon Options are Missing [Dokan Booking]',
                    'description' => 'Booking addon options are missing on the Booking type product edit panel.',
                ],
                [
                    'title'       => 'Variable Product Image Upload Issue with Yoast SEO [Vendor Product SEO]',
                    'description' => 'Variable product image upload when yoast seo plugin is active.',
                ],
                [
                    'title'       => 'Added Tax Fields for Vendor Subscription [Vendor Subscription Product]',
                    'description' => 'Added tax fields for vendor subscription type product.',
                ],
                [
                    'title'       => 'Booking Simple Product to Virtual Product [Dokan Booking]',
                    'description' => 'Booking simple product changes to virtual product when create a booking product from vendor area.',
                ],
                [
                    'title'       => 'Stripe Recurring Issue With 3ds [Dokan Stripe]',
                    'description' => 'Fixed Dokan Stripe 3ds recurring issue with vendor subscription products.',
                ],
                [
                    'title'       => 'Dokan Order Discount Mismatch When Recalculate',
                    'description' => 'Dokan order discount mismatch when recalculate from admin panel order details page.',
                ],
                [
                    'title'       => 'Fixed Cart Coupon Option Disabled Multi Vendors',
                    'description' => 'Fixed cart coupon option disabled for multi vendors, it will be work only when single seller mode enabled form dokan settings.',
                ],
                [
                    'title'       => 'Added Some New Exceptions to Display Formatted [WireCard]',
                    'description' => 'Added some new exceptions to display formatted errors to users.',
                ],
                [
                    'title'       => 'Fixed Product Pack End Date for Vendor Subscription [WireCard]',
                    'description' => 'Fixed product pack end date for vendor subscription, previously this was causing subscription to get canceled automatically before subscriptions actual end date.',
                ],
                [
                    'title'       => 'Fixed Decimal Issues on Product Price [WireCard]',
                    'description' => 'Fixed decimal issues on product price, this was causing API error due to mismatch order total.',
                ],
                [
                    'title'       => 'Removed rmccue/requests Library From Vendor Folder [WireCard]',
                    'description' => 'Removed rmccue/requests library from vendor folder, WordPress already has this library preinstalled. This was causing a fatal error on some installations.',
                ],
                [
                    'title'       => 'Fixed Limit Your Zone Selected by Default [Dokan Vendor Shipping]',
                    'description' => 'Limit your zone selected by default when zone created with a country.',
                ],
                [
                    'title'       => 'Vendor Verification Upload Documents Folder Disallow',
                    'description' => 'Disallow direct access vendor verification uploaded documents folder.',
                ],
                [
                    'title'       => 'Fixed Dokan Stripe Resource Missing API',
                    'description' => 'Fixed Dokan Stripe resource missing api error for empty source provided via api call.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.2',
        'released' => '2020-12-01',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Vendor Gets Error With PayPal',
                    'description' => 'Vendor gets error while purchasing products if they purchased a vendor subscription product with PayPal in checkout page.',
                ],
                [
                    'title'       => 'Multi Vendor Product Showing Others Vendor',
                    'description' => 'Single product multi vendor products showing others vendor area issue when SPMV product duplicated.',
                ],
                [
                    'title'       => 'Admin Commission Set 0 by Default',
                    'description' => 'Admin commission set 0 by default when create/update vendor form admin area.',
                ],
                [
                    'title'       => 'Enabling Vacation Mode is Hiding Products',
                    'description' => 'Enabling vacation mode is hiding products from vendor dashboard, vendor is not able to see the products.',
                ],
                [
                    'title'       => 'Vendor Staff Is Not Able To Manage Product',
                    'description' => 'Vendor staff is not able to add/edit any product on vendor dashboard, also fixed capabilities issue.',
                ],
                [
                    'title'       => 'Vendor Shipping Settings Page Console Error',
                    'description' => 'Vendor shipping settings page showing js error issue when try to add/update any shipping zone.',
                ],
                [
                    'title'       => 'Update Vendor Review REST API',
                    'description' => 'Update vendor review REST API and fixed some errors.',
                ],
                [
                    'title'       => 'SMS verification Error Message Translation',
                    'description' => 'SMS verification error message translation was not available.',
                ],
                [
                    'title'       => 'SMS Verification Error Handling',
                    'description' => 'SMS verification error handling for vendors.',
                ],
                [
                    'title'       => 'Booking Product Virtual Option Not Saving',
                    'description' => 'Booking product virtual option not saving while 1st time create form vendor dashboard.',
                ],
                [
                    'title'       => 'Coupon Minimum Amount Not Working',
                    'description' => 'Coupon minimum amount not working with variation products issue fixed.',
                ],
                [
                    'title'       => 'Vendor Product Addon Appears on Other Vendors',
                    'description' => 'Vendor product addon appears in every product in marketplace when that vendor is logged in.',
                ],
                [
                    'title'       => 'Product Wise Commission Issue In Subscription Product',
                    'description' => 'Product wise Commission is not working in subscription product on admin area product edit page.',
                ],
                [
                    'title'       => 'Report CSV Header Mismatch',
                    'description' => 'Report csv header mismatch issue fixed.',
                ],
                [
                    'title'       => 'Stripe Dashboard Tax Issue',
                    'description' => 'Stripe Dashboard does not show the price including the tax for vendors.',
                ],
                [
                    'title'       => 'SKU Data Not importing with CSV',
                    'description' => 'SKU data not importing when CSV import on vendor dashboard.',
                ],
                [
                    'title'       => 'Booking Single Day Data Issue',
                    'description' => 'Booking single day no data showing, responsiveness issue fixes form vendor dashboard booking details page.',
                ],
                [
                    'title'       => 'Product Seo Default Meta Field Issue',
                    'description' => 'Product seo default meta description removed from vendor dashboard product edit page.',
                ],
                [
                    'title'       => 'Variable product gets extra fields of variable subscription product',
                    'description' => 'When a vendor wants to create a variable product, extra field added from the vendor subscription product.',
                ],
                [
                    'title'       => 'Check End Date Before Cancelling Vendor Subscriptions',
                    'description' => 'Check subscription product pack end date matched with stored end date before cancelling vendor subscriptions. If both value does not match, update end date value.',
                ],
                [
                    'title'       => 'Downloads files showing multiple entries when have suborder',
                    'description' => 'Downloads files showing multiple entries when have suborder.',
                ],
                [
                    'title'       => 'Gateway fee paid by admin if empty',
                    'description' => 'If the processing fee is not 0 and if the dokan_gateway_fee_paid_by meta is blank then the processing fee is paid by the admin.',
                ],
                [
                    'title'       => 'Booking by day view which is missing in Booking calendar',
                    'description' => 'Bookable Product: Booking by day view which is missing in Booking calender.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.1',
        'released' => '2020-11-14',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Refactored Stripe Connect Module',
                    'description' => 'Refactored and fixed stripe connect module. Here fixed multiples dokan and vendor product subscription issues.',
                ],
                [
                    'title'       => 'Vendor Subscription Product Cancel Not Showing on Vendor Dashboard',
                    'description' => 'When a customer canceled their subscription then last status not showing vendor dashboard.',
                ],
                [
                    'title'       => 'Wholesale Product Checkbox Not Working',
                    'description' => 'Wholesale product checkbox not working when product status pending in vendor product edit page',
                ],
                [
                    'title'       => 'Product Wise Commission Not Working Comma Decimal',
                    'description' => 'Product wise commission not working when use comma decimal separator issue fixed',
                ],
                [
                    'title'       => 'Dokan Modules Section Active/Inactive Tab Issue',
                    'description' => 'Dokan modules section active/inactive tab section not work correctly',
                ],
                [
                    'title'       => 'Product Addon Select Field Options Issue with Price Field Blank',
                    'description' => 'When a vendor try to add a product addon select field with price field blank then the option not saving',
                ],
                [
                    'title'       => 'Required Minimum PHP Version Set to 7.0.0',
                    'description' => 'PHP 5.6 Compatibility, update required minimum php version is set to 7.0.0 on Dokan',
                ],
                [
                    'title'       => 'Vendor Not Able to Duplicate Product',
                    'description' => 'Duplicate product not working when try any product duplicate from vendor dashboard',
                ],
                [
                    'title'       => 'Fixed translation Issue for Dokan pro',
                    'description' => 'Fixed multiple translation issues for Dokan amdin settings pages',
                ],
                [
                    'title'       => 'Refactored Dokan Admin Modules Page',
                    'description' => 'Modules url changed on title and image in dokan admin modules page',
                ],
                [
                    'title'       => 'Dokan Booking Calendar Issue on Single day',
                    'description' => 'Dokan booking calendar only shows one booking on a single day on vendor dashboard booking details page',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1',
        'released' => '2020-10-20',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Vendor Analytics',
                    'description' => 'Get more insights to vendor data and track store performances. Vendor will now get google analytics for his store and product pages.',
                ],
                [
                    'title'       => 'Live Search',
                    'description' => 'Refine your search results just like Google. Autocomplete will give you a better search experience than ever before.',
                ],

            ],
            'Fix' => [
                [
                    'title'       => 'Refactored Stripe Connect Module',
                    'description' => 'Refactored and fixed stripe connect module. Updated stripe SDK version and stripe connect type.',
                ],
                [
                    'title'       => 'Gateway Fee on Admin Report Logs',
                    'description' => 'Added gateway fee payee indicator in admin report logs. Now the admin will have a proper view of the gateway fee amount and who is paying that.',
                ],
                [
                    'title'       => 'Booking Confirmation from the Booking List',
                    'description' => 'When the vendor tries to confirm booking from the booking list, it was not working properly and was not showing a thank you message.',
                ],
                [
                    'title'       => 'Activate Modules During Plugin Activation',
                    'description' => 'The modules will now be inactive by default after plugin installation, enable the modules you need.',
                ],
                [
                    'title'       => 'Product Grouped Type',
                    'description' => 'We have fixed the issue, product type was not changing to grouped product when trying to change.',
                ],
                [
                    'title'       => 'Branding Issue on Seller Search',
                    'description' => 'You can now have a proper search result of vendors by filtering them with brand on store listing page.',
                ],
                [
                    'title'       => 'Vendor Earning in Order Details',
                    'description' => 'Now your vendors will see accurate vendor earnings in order details after the refund.',
                ],
                [
                    'title'       => 'Vendor Export Report',
                    'description' => 'We have fixed the statement of your vendor\'s balance when they export the statement from their dashboard.',
                ],
                [
                    'title'       => 'Removed External Product Type',
                    'description' => 'Removed external product type from subscription allowed product types for vendor subscription product.',
                ],
                [
                    'title'       => 'Subscription Product Price Not Saving',
                    'description' => 'You can now save the subscription product price when WC auction plugin is active.',
                ],
                [
                    'title'       => 'Featured Seller limit',
                    'description' => 'On your store listing page, the featured sellers number was showing more than the limit. We have fixed that.',
                ],
                [
                    'title'       => 'Product Tags add on Quick Edit Area',
                    'description' => 'Product tags search experience improvement and fixed the issue of not working properly on quick edit area.',
                ],
                [
                    'title'       => 'Text Domain in JS end',
                    'description' => 'Text domain issue when report abuse delete in js end and translate not working properly.',
                ],
                [
                    'title'       => 'JS Console Error on Report Abuse',
                    'description' => 'JS console error fixed on report abuse module from admin area edit product page',
                ],
                [
                    'title'       => 'Subscription Plan Page Design',
                    'description' => 'Subscription plan page design will work properly now when different languages are used.',
                ],
                [
                    'title'       => 'Vendor Product Import',
                    'description' => 'When a vendor imports a product from the dashboard then the default advanced option shows automatically, it\'s not an expected behavior. So we fixed that UI.',
                ],
                [
                    'title'       => 'Dokan Pro Email Template',
                    'description' => 'Dokan Pro core email template locations updated, so now you can override the template file from theme.',
                ],
                [
                    'title'       => 'Store Default Geolocation',
                    'description' => 'When you try to create a new product from the vendor dashboard then store default geolocation was not set in the product.',
                ],
                [
                    'title'       => 'Coupon Product and Exclude Product Field Move',
                    'description' => 'Coupon product and exclude product field move to search select with variations.',
                ],
                [
                    'title'       => 'Product Variation Toggle',
                    'description' => 'Product variation toggle issue, variation downloadable file delete issue.',
                ],
                [
                    'title'       => 'Vendor Can Modify Other Product',
                    'description' => 'There was a permission issue with vendor product edit. ‘Vendors can modify other vendor products’ are now restricted and not possible from this version.',
                ],
                [
                    'title'       => 'Multi Vendor Duplicate SKU',
                    'description' => 'When someone was trying to create a product from another product, then the SKU will not conflict with the existing one.',
                ],
                [
                    'title'       => 'Vendor Confirmation Email',
                    'description' => 'When some purchased a booking and the vendor did not get a booking confirmation email. That issue is fixed now.',
                ],
                [
                    'title'       => 'Quick Update Products',
                    'description' => 'Can not quick update products when product limit reached form vendor dashboard.',
                ],
                [
                    'title'       => 'CSV Import Feature Column',
                    'description' => 'When vendors import CSV from vendor dashboard and feature column make false, here checking CSV import vendor or admin.',
                ],
                [
                    'title'       => 'Export Wholesale Column Missing',
                    'description' => 'The vendor will now see the export wholesale column when you export product from vendor dashboard.',
                ],
                [
                    'title'       => 'Product Add-on Type File not Showed on Order',
                    'description' => 'Product add-on type File upload does not show the file on vendor order.',
                ],
                [
                    'title'       => 'Auction Start End Field',
                    'description' => 'Auction start, end field disable from keyboard.',
                ],
                [
                    'title'       => 'Announcements Week',
                    'description' => 'You will get all the announcements in time regardless of the timezone.',
                ],
                [
                    'title'       => 'Product Discount Scheduled',
                    'description' => 'Your vendor had problems setting schedule discounts for their products in the previous version. Dokan new version has the fix for this issue. Your vendor  can now schedule the discounts to their products.',
                ],
                [
                    'title'       => 'Import Restriction with Subscription ',
                    'description' => 'When someone imports product with category name by using the import tool, now validation for subscription category restricted if found will be applied.',
                ],
                [
                    'title'       => 'Wholesale Customer Registration Email',
                    'description' => 'Wholesale customer registration email to the admin did not contain proper information. This version has the proper template and data.',
                ],
                [
                    'title'       => 'Report Select Date not Working',
                    'description' => 'Report custom date not working for daily sales & statements are fixed now. You can now use a custom date as you want.',
                ],
                [
                    'title'       => 'New Refund Request Email',
                    'description' => 'You can now easily send a refund request email and it will reach the admin.',
                ],
                [
                    'title'       => 'WooCommerce Deprecated Functions',
                    'description' => 'Dokan has updated the list of WooCommerce deprecated functions. Outdated or previous versions templates and functions are not used without proper documentation from this version.',
                ],
                [
                    'title'       => 'Refund Issue with Decimal Number',
                    'description' => 'When the vendor sends a refund request from the order details page then the total and refund amount were not compared correctly.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.8',
        'released' => '2020-09-04',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Shipping data updater',
                    'description' => 'Shipping data updater is not showing some situations',
                ],
                [
                    'title'       => 'Product type allowed in Vendor subscription product',
                    'description' => 'Default subscription type product is not showing in vendor subscription type product module',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.7',
        'released' => '2020-09-01',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Refactor Product SEO',
                    'description' => 'Vendor product SEO refactor codes where improve performance',
                ],
                [
                    'title'       => 'Shipping Continent Issue (Shipping)',
                    'description' => 'When try to add shipping with a continent then it not working properly',
                ],
                [
                    'title'       => 'Global Multiple Zone Conflict (Shipping)',
                    'description' => 'Global multiple zone conflict issue in shipping',
                ],
                [
                    'title'       => 'Paypal Gateway Fee not Showing on All Logs (PayPal)',
                    'description' => 'Paypal gateway fee not showing on all logs when products purchase by multi vendors',
                ],
                [
                    'title'       => 'CSV Import Not Working with WordPress v5.5 (Import/Export Tool)',
                    'description' => 'CSV import not working cause of JS error',
                ],
                [
                    'title'       => 'Product Addon Conflicting with WooCommerce Booking (Product Addon)',
                    'description' => 'Product addon conflicting with WooCommerce booking when try to add new addon fields',
                ],
                [
                    'title'       => 'Tags List Loading Problem',
                    'description' => 'Long tags listing issue fixed on product quick edit area',
                ],
                [
                    'title'       => 'Duplicate Booking Email',
                    'description' => 'Vendor getting duplicate booking email when new customer booking',
                ],
                [
                    'title'       => 'Store Review Author Name (Store Review)',
                    'description' => 'Store review author name show display name if exits',
                ],
                [
                    'title'       => 'Yoast SEO Hooks Changed',
                    'description' => 'Yoast SEO plugin some hooks changed on latest version',
                ],
                [
                    'title'       => 'Update Vendor Analytics Logo and Key (Vendor Analytics)',
                    'description' => 'Update Vendor Analytics module logo and primary metrics key',
                ],
                [
                    'title'       => 'Store Category Resets',
                    'description' => 'Store category resets after updating store Payment details',
                ],
                [
                    'title'       => 'Automatic Save Zone Location Data (Shipping)',
                    'description' => 'Automatic save zone location data during method add, edit and delete',
                ],
                [
                    'title'       => 'Product Type not Saving',
                    'description' => 'Product type not saving when product addon module active with WooCommerce product addon',
                ],
                [
                    'title'       => 'RMA Request Delete by Vendor',
                    'description' => 'RMA request delete by vendor and change text-domain',
                ],
                [
                    'title'       => 'Add Missing Permission Callback in REST Routes',
                    'description' => 'Add missing permission callback in REST routes to make WordPress 5.5 compatible',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.6',
        'released' => '2020-07-23',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Shipping Issue with Same zone Multiple postcode (Shipping)',
                    'description' => 'Full Shipping system revamped our codes structure and make performance improvement where allowing same country multiple zones',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.5',
        'released' => '2020-07-23',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Decimal and Thousand Separator with Comma',
                    'description' => 'Now allowing decimal and thousand separator with comma sign in every where',
                ],
                [
                    'title'       => 'New 3 Columns Added on All Logs (Vendor Subscription Module)',
                    'description' => 'Gateway Fee, Total Shipping and Total Tax 3 new columns added on all logs',
                ],
                [
                    'title'       => 'Gallery Image Restriction (Vendor Subscription Module)',
                    'description' => 'Gallery image restriction count for vendor subscription module',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'Token Issue with Dokan Stripe Module',
                    'description' => 'Stripe token issue come when try to payment with stripe for logged and guest use',
                ],
                [
                    'title'       => 'Shipping Issue with Same Country Multiple Zones (Shipping)',
                    'description' => 'Full Shipping system revamped our codes structure and make performance improvement where allowing same country multiple zones',
                ],
                [
                    'title'       => 'Vendor Subscriptions Product not Allow with Dokan Stripe (Vendor Subscriptions Product)',
                    'description' => 'When try to payment with stripe on Vendor Subscription Product then it not worked',
                ],
                [
                    'title'       => 'After Payment Completed Order Status Not Change (Vendor Subscriptions Product)',
                    'description' => 'Vendor Subscription Products after payment completed order status not changed',
                ],
                [
                    'title'       => 'Gateway Fee Subtract from Admin Commission',
                    'description' => 'Now gateway fee subtract from admin commission value and make it separate column on all logs',
                ],
                [
                    'title'       => 'Products Addon Fields Not Worked for Vendor Staff (Products Addon)',
                    'description' => 'Products Addon fields manage by vendor staff and fields showing on product page',
                ],
                [
                    'title'       => 'Add New Card Not Worked on My Account Page',
                    'description' => 'When try to add new card number in my account page on payment methods tab then it not worked',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.4',
        'released' => '2020-06-19',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Stripe Module add 2 Requires Options (Stripe Connect)',
                    'description' => 'Stripe Module add 2 requires options must need to add stripe credential and SSL',
                ],
                [
                    'title'       => 'Stripe Module Added 2 Notices (Stripe Connect)',
                    'description' => 'Stripe Module added 2 notices for add stripe credentials and another for SSL activation',
                ],
                [
                    'title'       => 'Geolocation Auto Set Same as Store (Geolocation)',
                    'description' => 'Geolocation auto set same as store when product update from admin',
                ],
                [
                    'title'       => 'Add Text Shipping Policies Link on Shipping Setting Page',
                    'description' => 'Add text Shipping Policies link after gear icon on vendor shipping setting page',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.3',
        'released' => '2020-06-11',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Add Facebook Messenger to Dokan live chat (Live Chat)',
                    'description' => 'The Facebook Messenger is new Dokan live chat for vendor single page and product page like as TalkJS',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Stripe Connect Module Revamped (Stripe Connect)',
                    'description' => 'Full Stripe Connect Module revamped our codes structure and make performance improvement',
                ],
                [
                    'title'       => 'Vendor Subscription Module Revamped (Vendor Subscription)',
                    'description' => 'Full Vendor Subscription Module revamped our codes structure and make performance improvement',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Minimum Amount for Discount Coupon',
                    'description' => 'The minimum amount for discount coupon not working on checkout which amount added by vendor',
                ],
                [
                    'title'       => 'Store Review Not Working for Verified Owner',
                    'description' => 'Store review not working if verified owner option is checked (Store Reviews)',
                ],
                [
                    'title'       => 'Sellers Sitemap XML',
                    'description' => 'Dokan Sellers Sitemap XML file showing 404 when visit it from SEO XML file',
                ],
                [
                    'title'       => 'Shipping Tax Calculates',
                    'description' => 'Shipping tax calculates wrong for sub orders',
                ],
                [
                    'title'       => 'Vendor Subscription Product Error with get_current_screen Function',
                    'description' => 'Remove get_current_screen function from vendor subscription product module (Vendor Subscription Product)',
                ],
                [
                    'title'       => 'Vendor Subscription Product Variation Product Price Not Saving',
                    'description' => 'Variation product price not saving when vendor subscription product module enable issue fixed (Vendor Subscription Product)',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.2',
        'released' => '2020-04-22',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Vendor Subscription Product Module',
                    'description' => 'The new Vendor Subscription Product module is a WooCommerce Subscription integration(VSP)',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'JS error in backend report abuse page (Report Abuse)',
                    'description' => 'There was a warning JS error in backend report abuse page, which has been resolved',
                ],
                [
                    'title'       => 'Live chat with elementor issue',
                    'description' => 'Live chat showing fatal error when using with elementor (Elementor)',
                ],
                [
                    'title'       => 'Fatal Error on Booking',
                    'description' => 'Fatal error and calendar issue in frontend booking page (Booking)',
                ],
                [
                    'title'       => 'Vendor Biography Tab Not Showing',
                    'description' => 'Vendor biography tab not showing in store page which is designed with elementor',
                ],
                [
                    'title'       => 'Vendor email issues',
                    'description' => 'Vendor disable email does not work and the vendor enables email is send twice',
                ],
                [
                    'title'       => 'Category Search Issue on Frontpage',
                    'description' => 'When store listing page set as frontpage, category search does not work',
                ],
                [
                    'title'       => 'Unable to create refund from both backend and frontend',
                    'description' => 'Unable to refund order from both backend and frontend if item total is not set',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 3.0.0',
        'released' => '2020-03-25',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Brand Support for Single Product Multi vendor',
                    'description' => 'Brand support for single product multi vendor and normal clone products (SPMV)',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Module Documentation',
                    'description' => 'Added documentation link for modules in admin module page',
                ],
                [
                    'title'       => 'Code Structure and Performance Improvement',
                    'description' => 'We have revamped our code structure and make performance improvement',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Outdated Template Warning on Vendor Migration Page',
                    'description' => 'There was a warning regarding outdated template in vendor migration page, which has been resolved',
                ],
                [
                    'title'       => 'Store Progressbar Issue',
                    'description' => 'Store progressbar wasn\'t updating when vendor save stripe or wirecard payment method (Stripe & Wirecard)',
                ],
                [
                    'title'       => 'Seller Vacation Issue',
                    'description' => 'Customer was able to place order from sellers who are on vacation (Seller Vacation)',
                ],
                [
                    'title'       => 'Vendor Staff Permissions Label',
                    'description' => 'Make vendor staff permissions label translatable (Vendor Staff)',
                ],
                [
                    'title'       => 'Product Review Pagination',
                    'description' => 'Product review pagination is not working correctly',
                ],
                [
                    'title'       => 'Geolocation Map Issue',
                    'description' => 'MAP on the store listing page is not showing if Google API key field is empty but Mapbox (Geolocation)',
                ],
                [
                    'title'       => 'Geolocation Product Update Issue',
                    'description' => 'Modifying the product from the Admin backend reverts the product location to `same as store` (Geolocation)',
                ],
                [
                    'title'       => 'Stripe Refund Issue',
                    'description' => 'If admin has earning from an order, only then refund application fee (Stripe)',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.13',
        'released' => '2019-08-29',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Scheduled Announcement',
                    'description' => 'Add scheduled announcement option for admin.',
                ],
                [
                    'title'       => 'Identity Verification in Live Chat',
                    'description' => 'Add identity verification and unread message count in live chat (Live Chat Module).',
                ],
                [
                    'title'       => 'Admin Defined Default Geolocation',
                    'description' => 'Add admin defined location on Geolocation map to be shown instead of default `Dhaka, Bangladesh` when there is no vendor or product found (Geolocation Module).',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'Stripe Certificate Missing Issue',
                    'description' => 'Add ca-certificate file to allow certificate verification of stripe SSL (Stripe Module).',
                ],
                [
                    'title'       => 'Shipping doesn\'t Work on Variable Product',
                    'description' => 'If variable product is created by admin for a vendor, vendor shipping method doesn\'t work.',
                ],
                [
                    'title'       => 'Payment Fields are Missing in Edit Vendor Page',
                    'description' => 'Set default bank payment object if it\'s not found from the API response.',
                ],
                [
                    'title'       => 'Product Lot Discount on Sub Orders',
                    'description' => 'Product lot discount is getting applied on sub-orders even though discount is disabled.',
                ],
                [
                    'title'       => 'Guest User Checkout',
                    'description' => 'Guest user is unable to checkout with stripe (Stripe Module).',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.12',
        'released' => '2019-08-09',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Stripe 3D Secure and Authentication',
                    'description' => 'Add stripe 3D secure and strong customer authentication (Stripe Connect Module).',
                ],
                [
                    'title'       => 'Subscription Upgrade Downgrade',
                    'description' => 'Add subscription pack upgrade downgrade option for vendors (Subscription Module).',
                ],
                [
                    'title'       => 'Wholesale Options in Backend',
                    'description' => 'Add wholesale options in the admin backend (Wholesale Module).',
                ],
                [
                    'title'       => 'Elementor Vendor Verification Widget',
                    'description' => 'Add support for vendor verification widget (Elementor Module).',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Dokan Admin Settings',
                    'description' => 'Dokan admin settings rearrange and refactor.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Product Discount',
                    'description' => 'Attach product discount in order details.',
                ],
                [
                    'title'       => 'Coupon Type Changes',
                    'description' => 'Coupon discount type changes on coupon edit. This issue has been fixed in this release.',
                ],
                [
                    'title'       => 'Order Refund from Admin Backend',
                    'description' => 'Refund calculation was wrong when it\'s done from the admin backend. It\'s been fixed in this release.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.11',
        'released' => '2019-07-02',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Elementor Module',
                    'description' => 'Add elementor page builder widgets for Dokan.',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Single Product Multi Vendor',
                    'description' => 'Single product multiple vendor hide duplicates based on admin settings.',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Zone Wise Vendor Shipping',
                    'description' => 'Limit your zone location by default was enabled, which is incorrect. It should only be enabled when admin limit the zone.',
                ],
                [
                    'title'       => 'Vendor Biography Tab',
                    'description' => 'Line break and youtube video was not working in vendor biography tab. We have fixed the issue in this update.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.10',
        'released' => '2019-06-19',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Vendor Biography Tab',
                    'description' => 'Add vendor biography tab in dokan store page',
                ],
                [
                    'title'       => 'Filtering and Searching Options',
                    'description' => 'Add filtering and searching option in admin report logs area',
                ],
                [
                    'title'       => 'Vendor Vacation',
                    'description' => 'Add multiple vacation date system for vendor',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Store Progressbar',
                    'description' => 'Store progress serialization and congrats message on 100% profile completeness',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Refund Request Validation',
                    'description' => 'Validate refund request in seller dashboard',
                ],
                [
                    'title'       => 'Coupon Validation',
                    'description' => 'Ensure coupon works on vendors product not the cart',
                ],
                [
                    'title'       => 'Best Selling and Top Rated Widget',
                    'description' => 'Remove subscription product from best selling and top rated product widget',
                ],
                [
                    'title'       => 'Subscription Renew and Cancellation',
                    'description' => 'Subscription renew and cancellation with PayPal',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.9',
        'released' => '2019-05-15',
        'changes'  => [
            'Improvement' => [
                [
                    'title'       => 'Report Abuse Module thumbnail',
                    'description' => 'Add thumbnail and description of report abuse module',
                ],
                [
                    'title'       => 'Social login and vendor verification',
                    'description' => 'Refactor social login and vendor verification module',
                ],
                [
                    'title'       => 'Change Moip brand to wirecard',
                    'description' => 'Rename Moip to Wirecard payment gateway',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Translation issue',
                    'description' => 'Make coupon strings translatable',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.8',
        'released' => '2019-05-07',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Report Abuse',
                    'description' => 'Customer will be able to report against product.',
                ],
                [
                    'title'       => 'Vendor Add Edit',
                    'description' => 'Admin will be able to create new Vendor from the backend',
                ],
                [
                    'title'       => 'Dokan Booking',
                    'description' => 'Add restricted days functionality in dokan booking module',
                ],
                [
                    'title'       => 'Single Product Multi Vendor',
                    'description' => 'Enable SPMV for admins to duplicate products from admin panel',
                ],
                [
                    'title'       => 'Vendor Shipping',
                    'description' => 'Add wildcard and range matching for vendor shipping zone',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Deprecated Functions',
                    'description' => 'Replace get_woocommerce_term_meta with get_term_meta as it was deprecated',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Store Category',
                    'description' => 'Fix store category list table search form',
                ],
                [
                    'title'       => 'Duplicate Subscription Form',
                    'description' => 'Subscription form is rendering twice in registration form',
                ],
                [
                    'title'       => 'Subscription Cancellation',
                    'description' => 'Cancel subscription doesn\'t work for manually assigned subscription',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.7',
        'released' => '2019-03-25',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Store Category',
                    'description' => 'Vendor will be able to register under specefic cateogry. ei(Furniture, Mobile)',
                ],
                [
                    'title'       => 'YITH WC Brand Compatible',
                    'description' => 'Make Dokan YITH WC Brand add-on compatible',
                ],
                [
                    'title'       => 'Date and refund column in admin logs area',
                    'description' => 'Add date and refund column in admin logs area to get more detaild overview.',
                ],
                [
                    'title'       => 'Product Status',
                    'description' => 'Change product status according to subscription status',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Trial Subscription',
                    'description' => 'When a vendor subscribe to a trial subscription, make all other trial to non-trial subscription for that vendor',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Show button for non logged-in user',
                    'description' => 'Show button for non logged-in user',
                ],
                [
                    'title'       => 'Refund Calculation Issue',
                    'description' => 'Send refund admin commission to customer',
                ],
                [
                    'title'       => 'Error on subscription cancellation email',
                    'description' => 'There was an error on subscription cancellation, which has been fixed in this release.',
                ],
                [
                    'title'       => 'Social Login Issue',
                    'description' => 'Update social login and vendor verification API',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.5',
        'released' => '2019-02-18',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Automate order refund process via stripe',
                    'description' => 'Vendor can now send automatic refund to their customer from vendor order dashboard',
                ],
                [
                    'title'       => 'Add trial subscription (Subscription Module)',
                    'description' => 'Admin can now offer trail subscription for vendors',
                ],
                [
                    'title'       => 'Product type & gallery image restriction',
                    'description' => 'Admin can now restrict product type & gallery image upload for vendor subscription',
                ],
                [
                    'title'       => 'Privacy and Policy',
                    'description' => 'Admin can configure privacy policy info for frontend product enquiry form',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'Email notification for store follow',
                    'description' => 'Now vendor can get email notification on store follows and unfollows',
                ],
                [
                    'title'       => 'Unable to select country or state in vendor shipping',
                    'description' => 'Country dropdown not working in shipping and announcement',
                ],
                [
                    'title'       => 'Admin report logs calculation issue is fixed in admin dashboard',
                    'description' => 'Some calculation issue fixed in admin reports',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.4',
        'released' => '2019-01-23',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Wholesale Module(Business, Enterprise Package)',
                    'description' => 'Added new Wholesale module. Vendor can offer wholesale price for his/her products.',
                ],
                [
                    'title'       => 'Return and Warranty Module(Professional, Business, Enterprise Package)',
                    'description' => 'Vendor can offer warranty and return system for their products and customer can take this warranty offers',
                ],
                [
                    'title'       => 'Subscription cancellation email',
                    'description' => 'Now admin can get email if any subscription is cancelled by vendor',
                ],
                [
                    'title'       => 'Subscription Unlimited pack',
                    'description' => 'Admin can offer unlimited package for vendor subscription',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'MOIP Gateway connection issue',
                    'description' => 'Change some gateway api params for connection moip gateway',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.3',
        'released' => '2018-12-18',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'ShipStation Module(Business, Enterprise Package)',
                    'description' => 'Added new ShipStation module',
                ],
                [
                    'title'       => 'Follow Store Module(Professional, Business, Enterprise Package)',
                    'description' => 'Added Follow Store module',
                ],
                [
                    'title'       => 'Product Quick Edit',
                    'description' => 'Added Quick edit option for product in vendor dashboard.',
                ],
                [
                    'title'       => 'Searching Option',
                    'description' => 'Add searching option in dokan vendor and refund page',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Admin Tools & Subscription Page Improvement',
                    'description' => 'Rewrite admin tools & subscription page in vue js',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Filter form & Map in Category Page',
                    'description' => 'Show filter form and map in product category pages (geolocation module)',
                ],
                [
                    'title'       => 'Bookable Product Commission',
                    'description' => 'Add per product commission option for bookable product',
                ],
                [
                    'title'       => 'Refund Calculation Issue',
                    'description' => 'Refund calculation is wrong when shipping fee recipient is set to vendor',
                ],
                [
                    'title'       => 'Bulk Refund is Not Working',
                    'description' => 'Approving batch refund is not working in admin backend',
                ],
                [
                    'title'       => 'Product Stock Issue on Refund',
                    'description' => 'Increase stock amount if the product is refunded',
                ],
                [
                    'title'       => 'Category Restriction Issue',
                    'description' => 'Booking product category restriction for subscription pack is not working',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.2',
        'released' => '2018-11-09',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Geolocation Module',
                    'description' => 'Added zoom level settings in geolocation module.',
                ],
                [
                    'title'       => 'Zone Wise Shipping',
                    'description' => 'Added shipping policy and processing time settings in zone wise shipping.',
                ],
                [
                    'title'       => 'Rest API for Store Reviews',
                    'description' => 'Added rest API support for store review post type.',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'Show Tax on Bookable Product',
                    'description' => 'Show tax on bookable product for vendor',
                ],
                [
                    'title'       => 'Product Importing Issue for Subscribed Vendor',
                    'description' => 'Allow vendor to import only allowed number of products.',
                ],
                [
                    'title'       => 'Product and Order Discount Issue',
                    'description' => 'Product and order discount for vendor is not working.',
                ],
                [
                    'title'       => 'Shipping Class Issue',
                    'description' => 'Shipping class is not saving for bookable product.',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.0',
        'released' => '2018-10-03',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Geolocation Module',
                    'description' => 'Enable this module to let the customers search for a specific product or vendor using any location they want.',
                ],
                [
                    'title'       => 'Moip Payment Gateway',
                    'description' => 'Use one of the most popular payment system known for it\'s efficiency with Dokan.',
                ],
                [
                    'title'       => 'Allow Vendor to crate tags',
                    'description' => 'Your vendors don\'t need to rely on prebuilt tags anymore. Now they can create their own in seconds',
                ],
                [
                    'title'       => 'Responsive Admin Pages',
                    'description' => 'All the admin backend pages is now responsive for all devices',
                ],
                [
                    'title'       => 'Staff email for New Order',
                    'description' => 'Staff will able to get all emails for new order from customer',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.3',
        'released' => '2018-07-19',
        'changes'  => [
            'Fix' => [
                [
                    'title'       => 'Live Chat Module',
                    'description' => 'Right now the chat box is available in customer my account page and also make responsive chat box window',
                ],
                [
                    'title'       => 'Statement and Refund',
                    'description' => 'Change core table structure for refund and statements. Now its easy to understand for vendor to check her statements. Also fixed statement exporting problem',
                ],
                [
                    'title'       => 'Zone wise Shipping',
                    'description' => 'Shipping state rendering issue fixed. If any country have no states then states not showing undefine problem',
                ],
                [
                    'title'       => 'Stripe Module',
                    'description' => 'Card is automatically saved if customer does not want to save his/her card info during checkout',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.2',
        'released' => '2018-06-29',
        'changes'  => [
            'New' => [
                [
                    'title'       => 'Live Chat Module',
                    'description' => 'Vendors will now be able to provide live chat support to visitors and customers through this TalkJS integration. Talk from anywhere in your store, add attachments, get desktop notifications, enable email notifications, and store all your messages safely in Vendor Inbox',
                ],
                [
                    'title'       => 'Added Refund and Announcement REST API',
                    'description' => 'Admins can now modify refund and announcement section of Dokan easily through the Rest API',
                ],
            ],
            'Fix' => [
                [
                    'title'       => 'Local pickup is visible when the cost is set to zero',
                    'description' => 'When local pickup cost in Dokan Zone-wise shipping is set to zero it will show on the cart/checkout page',
                ],
                [
                    'title'       => 'Store Support ticket is visible in customer dashboard support menu',
                    'description' => 'Now customers can view the support tickets they create in My Account> support ticket area',
                ],
                [
                    'title'       => 'Added tax and shipping functionalities in auction product',
                    'description' => 'Now admins can add shipping and tax rates for auction able product',
                ],
                [
                    'title'       => 'Appearance module for admins',
                    'description' => 'Now Admins can view Color Customizer settings in backend without any problem',
                ],
                [
                    'title'       => 'Unable to delete vendor form admin panel',
                    'description' => 'Admin was unable to delete a vendor from admin panel',
                ],
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.0',
        'released' => '2018-05-01',
        'changes'  => [
            'New'         => [
                [
                    'title'       => 'Introduction of REST APIs',
                    'description' => 'We have introduced REST APIs in dokan',
                ],
                [
                    'title'       => 'Zone wise shipping',
                    'description' => 'We have introduced zone wise shipping functionality similar to WooCommerce in dokan.',
                ],
                [
                    'title'       => 'Earning suggestion for variable product',
                    'description' => 'As like simple product, vendor will get to see the earning suggestion for variable product as well',
                ],
                [
                    'title'       => 'Confirmation on subscription cancellation',
                    'description' => 'Cancellation of a subscription pack will ask for confirmation',
                ],
            ],
            'Improvement' => [
                [
                    'title'       => 'Disable back end access for vendor staff',
                    'description' => 'Disable back end access for vendor staff for security purpose',
                ],
                [
                    'title'       => 'Updated deprecated functions',
                    'description' => 'Updated some deprecated functions',
                ],
                [
                    'title'       => 'Statement calculation',
                    'description' => 'Statement calculation',
                ],
                [
                    'title'       => 'Reduction of \'dokan\' text from staff permission',
                    'description' => 'Reduction of \'dokan\' text from staff permission',
                ],
                [
                    'title'       => 'Various UI, UX improvement',
                    'description' => 'Various UI, UX improvement',
                ],
            ],
            'Fix'         => [
                [
                    'title'       => 'Unable to login with social media',
                    'description' => 'Customer, Seller was unable to login with social media',
                ],
                [
                    'title'       => 'CSV earning report exporting',
                    'description' => 'There were an issue with CSV report exporting from back end',
                ],
                [
                    'title'       => 'Unable to delete vendor form admin panel',
                    'description' => 'Admin was unable to delete a vendor from admin panel',
                ],
                [
                    'title'       => 'Seller setup wizard is missing during email verification',
                    'description' => 'Seller setup wizard after a seller is verified by email was missing',
                ],
                [
                    'title'       => 'Subscription Free pack visibility',
                    'description' => 'Hide subscription product type from back end when a seller can access the back end',
                ],
            ],
        ],
    ],
];
