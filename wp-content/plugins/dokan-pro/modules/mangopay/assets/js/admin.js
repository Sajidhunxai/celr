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

/***/ "./modules/mangopay/assets/src/js/admin-settings.js":
/*!**********************************************************!*\
  !*** ./modules/mangopay/assets/src/js/admin-settings.js ***!
  \**********************************************************/
/***/ (() => {

eval(";\n(function ($) {\n  'use strict';\n\n  var mangopayAdmin = {\n    init: function init() {\n      mangopayAdmin.switchMode($('#woocommerce_dokan_mangopay_sandbox_mode').is(':checked'));\n      mangopayAdmin.toggleDelayPeriodField($('#woocommerce_dokan_mangopay_disburse_mode').val());\n      mangopayAdmin.toggleIntervalField($('#woocommerce_dokan_mangopay_announcement_to_sellers').is(':checked'));\n      $('#woocommerce_dokan_mangopay_sandbox_mode').on('change', function () {\n        mangopayAdmin.switchMode($(this).is(':checked'));\n      });\n      $('#woocommerce_dokan_mangopay_disburse_mode').on('change', function () {\n        mangopayAdmin.toggleDelayPeriodField($(this).val());\n      });\n      $('#woocommerce_dokan_mangopay_announcement_to_sellers').on('change', function () {\n        mangopayAdmin.toggleIntervalField($(this).is(':checked'));\n      });\n      $('#woocommerce_dokan_mangopay_webhook_key').closest('tr').hide();\n    },\n    switchMode: function switchMode(sandbox) {\n      if (sandbox) {\n        $('#woocommerce_dokan_mangopay_client_id').closest('tr').hide();\n        $('#woocommerce_dokan_mangopay_api_key').closest('tr').hide();\n        $('#woocommerce_dokan_mangopay_sandbox_client_id').closest('tr').show();\n        $('#woocommerce_dokan_mangopay_sandbox_api_key').closest('tr').show();\n        $('#woocommerce_dokan_mangopay_enable_3DS2').closest('tr').show();\n      } else {\n        $('#woocommerce_dokan_mangopay_client_id').closest('tr').show();\n        $('#woocommerce_dokan_mangopay_api_key').closest('tr').show();\n        $('#woocommerce_dokan_mangopay_sandbox_client_id').closest('tr').hide();\n        $('#woocommerce_dokan_mangopay_sandbox_api_key').closest('tr').hide();\n        $('#woocommerce_dokan_mangopay_enable_3DS2').closest('tr').hide();\n      }\n    },\n    toggleDelayPeriodField: function toggleDelayPeriodField(disburseMode) {\n      if (disburseMode === 'DELAYED') {\n        $('#woocommerce_dokan_mangopay_disbursement_delay_period').closest('tr').show();\n      } else {\n        $('#woocommerce_dokan_mangopay_disbursement_delay_period').closest('tr').hide();\n      }\n    },\n    toggleIntervalField: function toggleIntervalField(noticeEnabled) {\n      if (noticeEnabled) {\n        $('#woocommerce_dokan_mangopay_notice_interval').closest('tr').show();\n      } else {\n        $('#woocommerce_dokan_mangopay_notice_interval').closest('tr').hide();\n      }\n    }\n  };\n  $(document).ready(function () {\n    mangopayAdmin.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/mangopay/assets/src/js/admin-settings.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/mangopay/assets/src/js/admin-settings.js"]();
/******/ 	
/******/ })()
;