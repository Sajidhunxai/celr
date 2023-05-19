/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./modules/single-product-multiple-vendor/assets/src/js/dokan-spmv-products-admin.js":
/*!*******************************************************************************************!*\
  !*** ./modules/single-product-multiple-vendor/assets/src/js/dokan-spmv-products-admin.js ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _less_dokan_spmv_products_admin_less__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../less/dokan-spmv-products-admin.less */ \"./modules/single-product-multiple-vendor/assets/src/less/dokan-spmv-products-admin.less\");\n\n(function ($) {\n  $('#dokan-spmv-products-admin-assign-vendors').selectWoo({\n    minimumInputLength: 3,\n    closeOnSelect: false,\n    ajax: {\n      url: dokan_admin.ajaxurl,\n      dataType: 'json',\n      delay: 250,\n      data: function data(params) {\n        return {\n          action: 'dokan_spmv_products_admin_search_vendors',\n          _wpnonce: dokan_admin.nonce,\n          s: params.term,\n          product_id: dokan_admin.dokanSPMVAdmin.product_id\n        };\n      },\n      processResults: function processResults(data, params) {\n        params.page = params.page || 1;\n        return {\n          results: data.data.vendors,\n          pagination: {\n            more: false // (params.page * 30) < data.total_count\n          }\n        };\n      },\n\n      cache: true\n    },\n    language: {\n      errorLoading: function errorLoading() {\n        return dokan_admin.dokanSPMVAdmin.i18n.error_loading;\n      },\n      searching: function searching() {\n        return dokan_admin.dokanSPMVAdmin.i18n.searching + '...';\n      },\n      inputTooShort: function inputTooShort() {\n        return dokan_admin.dokanSPMVAdmin.i18n.input_too_short + '...';\n      }\n    },\n    escapeMarkup: function escapeMarkup(markup) {\n      return markup;\n    },\n    templateResult: function templateResult(vendor) {\n      if (vendor.loading) {\n        return vendor.text;\n      }\n      var markup = \"<div class='dokan-spmv-vendor-dropdown-results clearfix'>\" + \"<div class='dokan-spmv-vendor-dropdown-results__avatar'><img src='\" + vendor.avatar + \"' /></div>\" + \"<div class='dokan-spmv-vendor-dropdown-results__title'>\" + vendor.name + \"</div></div>\";\n      return markup;\n    },\n    templateSelection: function templateSelection(vendor) {\n      return vendor.name;\n    }\n  });\n  $('#dokan-spmv-products-admin-assign-vendors-btn').on('click', function (e) {\n    e.preventDefault();\n    var button = $(this);\n    var select = $('#dokan-spmv-products-admin-assign-vendors');\n    var vendors = select.selectWoo('val');\n    if (vendors && vendors.length) {\n      button.prop('disabled', true);\n      select.prop('disabled', true);\n      $.ajax({\n        url: dokan_admin.ajaxurl,\n        method: 'post',\n        dataType: 'json',\n        data: {\n          action: 'dokan_spmv_products_admin_assign_vendors',\n          _wpnonce: dokan_admin.nonce,\n          product_id: dokan_admin.dokanSPMVAdmin.product_id,\n          vendors: vendors\n        }\n      }).done(function (response) {\n        window.location.href = window.location.href;\n      }).always(function () {\n        button.prop('disabled', true);\n        select.prop('disabled', true);\n      });\n    }\n  });\n  $('#dokan-spmv-products-admin .delete-product').on('click', function (e) {\n    e.preventDefault();\n    if (confirm(dokan_admin.dokanSPMVAdmin.i18n.confirm_delete)) {\n      var product_id = $(this).data('product-id');\n      $.ajax({\n        url: dokan_admin.ajaxurl,\n        method: 'post',\n        dataType: 'json',\n        data: {\n          action: 'dokan_spmv_products_admin_delete_clone_product',\n          _wpnonce: dokan_admin.nonce,\n          product_id: product_id\n        }\n      }).done(function (response) {\n        window.location.href = window.location.href;\n      });\n    }\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/single-product-multiple-vendor/assets/src/js/dokan-spmv-products-admin.js?");

/***/ }),

/***/ "./modules/single-product-multiple-vendor/assets/src/less/dokan-spmv-products-admin.less":
/*!***********************************************************************************************!*\
  !*** ./modules/single-product-multiple-vendor/assets/src/less/dokan-spmv-products-admin.less ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://dokan-pro/./modules/single-product-multiple-vendor/assets/src/less/dokan-spmv-products-admin.less?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/single-product-multiple-vendor/assets/src/js/dokan-spmv-products-admin.js");
/******/ 	
/******/ })()
;