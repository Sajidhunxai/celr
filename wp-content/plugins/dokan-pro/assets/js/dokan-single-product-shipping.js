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

/***/ "./assets/src/js/dokan-single-product-shipping.js":
/*!********************************************************!*\
  !*** ./assets/src/js/dokan-single-product-shipping.js ***!
  \********************************************************/
/***/ (() => {

eval("// For single page shipping calculation scripts;\n(function ($) {\n  $(document).ready(function () {\n    $('.dokan-shipping-calculate-wrapper').on('change', 'select#dokan-shipping-country', function (e) {\n      e.preventDefault();\n      var self = $(this),\n        data = {\n          'action': 'dokan_shipping_country_select',\n          'country_id': self.val(),\n          'author_id': self.data('author_id')\n        };\n      if (self.val() != '') {\n        $.post(dokan.ajaxurl, data, function (resp) {\n          if (resp.success) {\n            self.closest('.dokan-shipping-calculate-wrapper').find('.dokan-shipping-state-wrapper').html(resp.data);\n            self.closest('.dokan-shipping-calculate-wrapper').find('.dokan-shipping-price-wrapper').html('');\n          }\n        });\n      } else {\n        self.closest('.dokan-shipping-calculate-wrapper').find('.dokan-shipping-price-wrapper').html('');\n        self.closest('.dokan-shipping-calculate-wrapper').find('.dokan-shipping-state-wrapper').html('');\n      }\n    });\n    $('.dokan-shipping-calculate-wrapper').on('keydown', '#dokan-shipping-qty', function (e) {\n      // Allow: backspace, delete, tab, escape, enter and .\n      if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||\n      // Allow: Ctrl+A\n      e.keyCode == 65 && e.ctrlKey === true ||\n      // Allow: home, end, left, right\n      e.keyCode >= 35 && e.keyCode <= 39) {\n        // let it happen, don't do anything\n        return;\n      }\n\n      // Ensure that it is a number and stop the keypress\n      if ((e.shiftKey || e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {\n        e.preventDefault();\n      }\n    });\n    $('.dokan-shipping-calculate-wrapper').on('click', 'button.dokan-shipping-calculator', function (e) {\n      e.preventDefault();\n      var self = $(this),\n        data = {\n          'action': 'dokan_shipping_calculator',\n          'country_id': self.closest('.dokan-shipping-calculate-wrapper').find('select.dokan-shipping-country').val(),\n          'product_id': self.closest('.dokan-shipping-calculate-wrapper').find('select.dokan-shipping-country').data('product_id'),\n          'author_id': self.closest('.dokan-shipping-calculate-wrapper').find('select.dokan-shipping-country').data('author_id'),\n          'quantity': self.closest('.dokan-shipping-calculate-wrapper').find('input.dokan-shipping-qty').val(),\n          'state': self.closest('.dokan-shipping-calculate-wrapper').find('select.dokan-shipping-state').val()\n        };\n      $.post(dokan.ajaxurl, data, function (resp) {\n        if (resp.success) {\n          self.closest('.dokan-shipping-calculate-wrapper').find('.dokan-shipping-price-wrapper').html(resp.data);\n        }\n      });\n    });\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./assets/src/js/dokan-single-product-shipping.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./assets/src/js/dokan-single-product-shipping.js"]();
/******/ 	
/******/ })()
;