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

/***/ "./modules/stripe-express/assets/src/js/admin-settings.js":
/*!****************************************************************!*\
  !*** ./modules/stripe-express/assets/src/js/admin-settings.js ***!
  \****************************************************************/
/***/ (() => {

eval(";\n(function ($) {\n  'use strict';\n\n  var dokanStripeExpressAdmin = {\n    init: function init() {\n      var self = dokanStripeExpressAdmin;\n\n      // Initially toggle as neccessary.\n      self.toggleMode();\n      self.toggleDelayPeriodField();\n      self.togglePaymentRequestFields();\n      self.toggleCrossBorderTransferFields();\n      self.toggleIntervalField();\n\n      // Events for toggling on state change.\n      self.element('testmode').change(self.toggleMode);\n      self.element('disburse_mode').change(self.toggleDelayPeriodField);\n      self.element('payment_request').change(self.togglePaymentRequestFields);\n      self.element('cross_border_transfer').change(self.toggleCrossBorderTransferFields);\n      self.element('announcement_to_sellers').change(self.toggleIntervalField);\n    },\n    toggleMode: function toggleMode() {\n      ['test_publishable_key', 'test_secret_key', 'test_webhook_key'].forEach(function (key) {\n        if (dokanStripeExpressAdmin.element('testmode').is(':checked')) {\n          dokanStripeExpressAdmin.element(key).closest('tr').show();\n          dokanStripeExpressAdmin.element('webhook_key').closest('tr').hide();\n        } else {\n          dokanStripeExpressAdmin.element(key).closest('tr').hide();\n          dokanStripeExpressAdmin.element('webhook_key').closest('tr').show();\n        }\n      });\n    },\n    togglePaymentRequestFields: function togglePaymentRequestFields() {\n      ['type', 'theme', 'size', 'locations'].forEach(function (key) {\n        if (dokanStripeExpressAdmin.element('payment_request').is(':checked')) {\n          dokanStripeExpressAdmin.element(key, 'payment_request_button').closest('tr').show();\n        } else {\n          dokanStripeExpressAdmin.element(key, 'payment_request_button').closest('tr').hide();\n        }\n      });\n    },\n    toggleCrossBorderTransferFields: function toggleCrossBorderTransferFields() {\n      ['restricted_countries'].forEach(function (key) {\n        if (dokanStripeExpressAdmin.element('cross_border_transfer').is(':checked')) {\n          dokanStripeExpressAdmin.element(key).closest('tr').show();\n        } else {\n          dokanStripeExpressAdmin.element(key).closest('tr').hide();\n        }\n      });\n    },\n    toggleDelayPeriodField: function toggleDelayPeriodField() {\n      if (dokanStripeExpressAdmin.element('disburse_mode').val() === 'DELAYED') {\n        dokanStripeExpressAdmin.element('disbursement_delay_period').closest('tr').show();\n      } else {\n        dokanStripeExpressAdmin.element('disbursement_delay_period').closest('tr').hide();\n      }\n    },\n    toggleIntervalField: function toggleIntervalField() {\n      if (dokanStripeExpressAdmin.element('announcement_to_sellers').is(':checked')) {\n        dokanStripeExpressAdmin.element('notice_interval').closest('tr').show();\n      } else {\n        dokanStripeExpressAdmin.element('notice_interval').closest('tr').hide();\n      }\n    },\n    element: function element(key) {\n      var section = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;\n      if (section && section.length) {\n        key = \"\".concat(section, \"_\").concat(key);\n      }\n      return $(\"#woocommerce_dokan_stripe_express_\".concat(key));\n    }\n  };\n  $(document).ready(dokanStripeExpressAdmin.init);\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/stripe-express/assets/src/js/admin-settings.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/stripe-express/assets/src/js/admin-settings.js"]();
/******/ 	
/******/ })()
;