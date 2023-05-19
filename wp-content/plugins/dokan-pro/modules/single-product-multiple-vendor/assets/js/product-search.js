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

/***/ "./modules/single-product-multiple-vendor/assets/src/js/product-search.js":
/*!********************************************************************************!*\
  !*** ./modules/single-product-multiple-vendor/assets/src/js/product-search.js ***!
  \********************************************************************************/
/***/ (() => {

eval(";\n(function ($) {\n  var Dokan_SPMV_Product_Search = {\n    init: function init() {\n      $('#dokan-spmv-area-toggle-button').on('click', Dokan_SPMV_Product_Search.toggleBoxContent);\n      $('.dokan-spmv-add-new-product-search-box-area.section-closed .info-section').on('click', function () {\n        if ($('.dokan-spmv-add-new-product-search-box-area').hasClass('section-closed')) {\n          Dokan_SPMV_Product_Search.toggleBoxContent();\n        }\n      });\n      $('button.dokan-spmv-clone-product').on('click', Dokan_SPMV_Product_Search.processProductCloning);\n    },\n    toggleBoxContent: function toggleBoxContent() {\n      $('.dokan-spmv-add-new-product-search-box-area').toggleClass('section-closed');\n    },\n    processProductCloning: function processProductCloning(e) {\n      e.preventDefault();\n      var tableArea = $('#dokan-spmv-product-list-table');\n      var productId = $(e.target).data('product');\n      var nonce = tableArea.data('security');\n      tableArea.block({\n        message: null,\n        overlayCSS: {\n          background: '#fff',\n          opacity: 0.6\n        }\n      });\n      $.post(dokan.ajaxurl, {\n        action: 'dokan_spmv_handle_product_clone_request',\n        nonce: nonce,\n        product_id: productId\n      }, function (response) {\n        if (response.success) {\n          dokan_sweetalert(response.data.message, {\n            position: 'bottom-end',\n            toast: true,\n            icon: 'success',\n            showConfirmButton: false,\n            timer: 2000,\n            timerProgressBar: true\n          });\n          tableArea.unblock();\n          window.location.replace(response.data.url);\n        } else {\n          dokan_sweetalert(response.data, {\n            position: 'bottom-end',\n            toast: true,\n            icon: 'error',\n            showConfirmButton: false,\n            timer: 2000,\n            timerProgressBar: true\n          });\n          tableArea.unblock();\n        }\n      });\n    }\n  };\n  $(window).on('load', function () {\n    Dokan_SPMV_Product_Search.init();\n  });\n\n  // listener for create new product popup open.\n  $('body').on('dokan-product-editor-popup-opened', function () {\n    Dokan_SPMV_Product_Search.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/single-product-multiple-vendor/assets/src/js/product-search.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/single-product-multiple-vendor/assets/src/js/product-search.js"]();
/******/ 	
/******/ })()
;