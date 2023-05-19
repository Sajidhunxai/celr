/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./modules/subscription/assets/src/js/admin-script.js":
/*!************************************************************!*\
  !*** ./modules/subscription/assets/src/js/admin-script.js ***!
  \************************************************************/
/***/ (() => {

eval(";\n(function ($) {\n  var pricingPane = $('#woocommerce-product-data');\n  if (pricingPane.length) {\n    pricingPane.find('.pricing').addClass('show_if_product_pack').end().find('.inventory_tab').addClass('hide_if_product_pack').end().find('.shipping_tab').addClass('hide_if_product_pack').end().find('.linked_product_tab').addClass('hide_if_product_pack').end().find('.attributes_tab').addClass('hide_if_product_pack').end().find('._no_of_product_field').hide().end().find('._pack_validity_field').hide().end().find('#_tax_status').parent().parent().addClass('show_if_product_pack').end();\n  }\n  $('body').on('woocommerce-product-type-change', function (event, select_val) {\n    $('._no_of_product_field').hide();\n    $('._pack_validity_field').hide();\n    $('._enable_recurring_payment_field').hide();\n    $('.dokan_subscription_pricing').hide();\n    $('._sale_price_field').show();\n    $('.dokan_subscription_trial_period').hide();\n    if (select_val == 'product_pack') {\n      $('._no_of_product_field').show();\n      $('._pack_validity_field').show();\n      $('._enable_recurring_payment_field').show();\n      $('._sale_price_field').hide();\n      if ($('#dokan_subscription_enable_trial').is(':checked')) {\n        $('.dokan_subscription_trial_period').show();\n      }\n      if ($('#_enable_recurring_payment').is(\":checked\")) {\n        $('.dokan_subscription_pricing').show();\n        $('._pack_validity_field').hide();\n      }\n    }\n  });\n  $('.woocommerce_options_panel').on('change', '#dokan_subscription_enable_trial', function () {\n    $('.dokan_subscription_trial_period').hide();\n    if ($(this).is(':checked')) {\n      $('.dokan_subscription_trial_period').fadeIn();\n    }\n  });\n  $('.woocommerce_options_panel').on('change', '#_enable_recurring_payment', function () {\n    $('.dokan_subscription_pricing').hide();\n    $('._pack_validity_field').show();\n    if ($(this).is(':checked')) {\n      $('.dokan_subscription_pricing').fadeIn();\n      $('._pack_validity_field').hide();\n    }\n  });\n\n  // Update subscription ranges when subscription period or interval is changed\n  $('#woocommerce-product-data').on('change', '[name^=\"_dokan_subscription_period\"], [name^=\"_dokan_subscription_period_interval\"]', function () {\n    setDokanSubscriptionLengths();\n  });\n  function setDokanSubscriptionLengths() {\n    $('[name^=\"_dokan_subscription_length\"]').each(function () {\n      var $lengthElement = $(this),\n        selectedLength = $lengthElement.val(),\n        hasSelectedLength = false,\n        periodSelector;\n      periodSelector = '#_dokan_subscription_period';\n      billingInterval = parseInt($('#_dokan_subscription_period_interval').val());\n      $lengthElement.empty();\n      $.each(dokanSubscription.subscriptionLengths[$(periodSelector).val()], function (length, description) {\n        if (parseInt(length) == 0 || 0 == parseInt(length) % billingInterval) {\n          $lengthElement.append($('<option></option>').attr('value', length).text(description));\n        }\n      });\n      $lengthElement.children('option').each(function () {\n        if (this.value == selectedLength) {\n          hasSelectedLength = true;\n          return false;\n        }\n      });\n      if (hasSelectedLength) {\n        $lengthElement.val(selectedLength);\n      } else {\n        $lengthElement.val(0);\n      }\n    });\n  }\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/subscription/assets/src/js/admin-script.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/subscription/assets/src/js/admin-script.js"]();
/******/ 	
/******/ })()
;