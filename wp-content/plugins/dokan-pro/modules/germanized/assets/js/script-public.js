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

/***/ "./modules/germanized/assets/src/js/script-public.js":
/*!***********************************************************!*\
  !*** ./modules/germanized/assets/src/js/script-public.js ***!
  \***********************************************************/
/***/ (() => {

eval("(function ($) {\n  'use strict';\n\n  var Germanized_Actions = {\n    init: function init() {\n      this.show_or_hide_unit_variation();\n      $(document).on('change', 'input#_unit_product, input#_unit_base, select#_unit', this.show_or_hide_unit_variation);\n      $(document).on('click', '.dokan-product-variation-itmes', this.show_or_hide_unit_variation);\n    },\n    show_or_hide_unit_variation: function show_or_hide_unit_variation() {\n      var fields = ['unit', 'unit_base', 'unit_product'];\n      var variations = $('.dokan-product-variation-itmes');\n      $.each(fields, function (index, id) {\n        var parent_val = $('#_' + id).val();\n        variations.each(function () {\n          $('.wc-gzd-parent-' + id).val(parent_val);\n        });\n      });\n      if ($('select#product_type').val() === 'variable' && $('select#_unit').val() !== '0' && $('input#_unit_base').val().length !== 0) {\n        $('.variable_unit_price_notice').hide();\n        $('.variable_unit_price_section').show();\n      } else {\n        $('.variable_unit_price_notice').show();\n        $('.variable_unit_price_section').hide();\n      }\n    }\n  };\n  $(document).ready(function () {\n    Germanized_Actions.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/germanized/assets/src/js/script-public.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/germanized/assets/src/js/script-public.js"]();
/******/ 	
/******/ })()
;