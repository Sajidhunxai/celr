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

/***/ "./modules/subscription/assets/src/js/script.js":
/*!******************************************************!*\
  !*** ./modules/subscription/assets/src/js/script.js ***!
  \******************************************************/
/***/ (() => {

eval(";\n(function ($) {\n  $('.pack_content_wrapper').on('click', '.buy_product_pack', function (evt) {\n    url = $(this).attr('href');\n  });\n  var wrapper = $('.dps-pack-wrappper');\n  var Dokan_Subscription_details = {\n    init: function init() {\n      wrapper.on('change', 'select#dokan-subscription-pack', this.show_details);\n      this.show_details();\n      this.cancel();\n      this.activate();\n    },\n    show_details: function show_details() {\n      var id = $('select#dokan-subscription-pack').val();\n      $('.dps-pack').hide();\n      $('.dps-pack-' + id).show();\n    },\n    cancel: function cancel() {\n      $('.seller_subs_info input[name=\"dps_submit\"]').on('click', function (e) {\n        e.preventDefault();\n        if (!$('input[name=\"dps_cancel_subscription\"]').val()) {\n          return;\n        }\n        dokan_sweetalert(dokanSubscription.cancel_string, {\n          action: 'confirm',\n          icon: 'warning'\n        }).then(function (res) {\n          if (res.isConfirmed) {\n            $(\"#dps_submit_form\").submit();\n          }\n        });\n      });\n    },\n    activate: function activate() {\n      $('.seller_subs_info input[name=\"dps_submit\"]').on('click', function (e) {\n        e.preventDefault();\n        if ($('input[name=\"dps_cancel_subscription\"]').val()) {\n          return;\n        }\n        dokan_sweetalert(dokanSubscription.activate_string, {\n          action: 'confirm',\n          icon: 'warning'\n        }).then(function (res) {\n          if (res.isConfirmed) {\n            $(\"#dps_submit_form\").submit();\n          }\n        });\n      });\n    }\n  };\n  $(function () {\n    Dokan_Subscription_details.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/subscription/assets/src/js/script.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/subscription/assets/src/js/script.js"]();
/******/ 	
/******/ })()
;