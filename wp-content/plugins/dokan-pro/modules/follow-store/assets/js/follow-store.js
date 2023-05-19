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

/***/ "./modules/follow-store/assets/src/js/follow-store.js":
/*!************************************************************!*\
  !*** ./modules/follow-store/assets/src/js/follow-store.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _less_follow_store_less__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../less/follow-store.less */ \"./modules/follow-store/assets/src/less/follow-store.less\");\n\n(function ($) {\n  function follow_store(button, vendor_id, _wpnonce) {\n    button.toggleClass('dokan-follow-store-button-working');\n    $.ajax({\n      url: dokan.ajaxurl,\n      method: 'post',\n      dataType: 'json',\n      data: {\n        action: 'dokan_follow_store_toggle_status',\n        _nonce: _wpnonce || dokanFollowStore._nonce,\n        vendor_id: vendor_id\n      }\n    }).fail(function (e) {\n      var response = e.responseJSON.data.pop();\n      dokan_sweetalert(response.message, {\n        icon: 'error'\n      });\n    }).always(function () {\n      button.toggleClass('dokan-follow-store-button-working');\n    }).done(function (response) {\n      if (response.data && response.data.status) {\n        if (response.data.status === 'following') {\n          button.attr('data-status', 'following').children('.dokan-follow-store-button-label-current').html(dokanFollowStore.button_labels.following);\n        } else {\n          button.attr('data-status', '').children('.dokan-follow-store-button-label-current').html(dokanFollowStore.button_labels.follow);\n        }\n      }\n      $('body').trigger('dokan:follow_store:changed_follow_status', {\n        vendor_id: vendor_id,\n        button: button,\n        status: response.data.status\n      });\n    });\n  }\n  function get_current_status(vendor_id) {\n    $.ajax({\n      url: dokan.ajaxurl,\n      method: 'get',\n      dataType: 'json',\n      data: {\n        action: 'dokan_follow_store_get_current_status',\n        vendor_id: vendor_id\n      }\n    }).done(function (response) {\n      $('body').trigger('dokan:follow_store:current_status', {\n        vendor_id: vendor_id,\n        is_following: response.data.is_following,\n        nonce: response.data.nonce\n      });\n    });\n  }\n  $('.dokan-follow-store-button', 'body').on('click', function (e) {\n    e.preventDefault();\n    var button = $(this),\n      vendor_id = parseInt(button.data('vendor-id')),\n      is_logged_in = parseInt(button.data('is-logged-in'));\n    if (!is_logged_in) {\n      $('body').on('dokan:login_form_popup:fetching_form dokan:login_form_popup:fetched_form', function () {\n        button.toggleClass('dokan-follow-store-button-working');\n      });\n      $('body').on('dokan:login_form_popup:logged_in', function () {\n        get_current_status(vendor_id);\n      });\n      $('body').on('dokan:follow_store:current_status', function (e, data) {\n        if (!data.is_following) {\n          follow_store(button, vendor_id, data.nonce);\n        } else {\n          window.location.href = window.location.href;\n        }\n      });\n      $('body').on('dokan:follow_store:changed_follow_status', function () {\n        window.location.href = window.location.href;\n      });\n      $('body').trigger('dokan:login_form_popup:show');\n      return;\n    }\n    follow_store(button, vendor_id);\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/follow-store/assets/src/js/follow-store.js?");

/***/ }),

/***/ "./modules/follow-store/assets/src/less/follow-store.less":
/*!****************************************************************!*\
  !*** ./modules/follow-store/assets/src/less/follow-store.less ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://dokan-pro/./modules/follow-store/assets/src/less/follow-store.less?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/follow-store/assets/src/js/follow-store.js");
/******/ 	
/******/ })()
;