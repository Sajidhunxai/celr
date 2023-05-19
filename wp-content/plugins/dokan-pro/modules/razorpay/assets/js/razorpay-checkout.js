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

/***/ "./modules/razorpay/assets/src/js/razorpay-checkout.js":
/*!*************************************************************!*\
  !*** ./modules/razorpay/assets/src/js/razorpay-checkout.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _babel_runtime_helpers_typeof__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/typeof */ \"./node_modules/@babel/runtime/helpers/esm/typeof.js\");\n\n;\n(function ($, document) {\n  'use strict';\n\n  var data = dokan_razorpay_checkout_data,\n    razorpayForm = $('#dokan_razorpay_form'),\n    razorpayCheckout = null;\n\n  // razorpay checkout process\n  var dokan_razorpay_checkout = {\n    init: function init() {\n      data.modal = dokan_razorpay_checkout.modal;\n      data.handler = dokan_razorpay_checkout.handler;\n      razorpayCheckout = new Razorpay(data);\n    },\n    // Toggle disabling of Pay Now button\n    setDisable: function setDisable(id, state) {\n      if (undefined === (0,_babel_runtime_helpers_typeof__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(state)) {\n        state = true;\n      }\n      if (false === state) {\n        $(id).removeAttr('disabled');\n      } else {\n        $(id).attr('disabled', state);\n      }\n    },\n    // Payment was closed without handler getting called\n    modal: {\n      ondismiss: function ondismiss() {\n        dokan_razorpay_checkout.setDisable('#dokan-razorpay-btn', false);\n      }\n    },\n    // After Closing the modal set the form, loader and submit it.\n    handler: function handler(payment) {\n      dokan_razorpay_checkout.setDisable('#dokan-razorpay-btn-cancel');\n      $('.dokan-razorpay-success').removeClass('dokan-hide');\n      $('.dokan-razorpay-pay-buttons, .dokan-razorpay-thank-you').addClass('dokan-hide');\n\n      // Update action with payment id and signature to validate with razorpay\n      razorpayForm.attr('action', \"\".concat(razorpayForm.attr('action'), \"&razorpay_payment_id=\").concat(payment.razorpay_payment_id, \"&razorpay_signature=\").concat(payment.razorpay_signature));\n      razorpayForm.submit();\n    },\n    // Open checkout modal\n    openCheckout: function openCheckout() {\n      dokan_razorpay_checkout.setDisable('#dokan-razorpay-btn');\n      razorpayCheckout.open();\n    },\n    addEvent: function addEvent(element, event, func) {\n      if (element.attachEvent) {\n        return element.attachEvent('on' + event, func);\n      } else {\n        return element.addEventListener(event, func, false);\n      }\n    },\n    // DOM Listener\n    handleCheckoutModal: function handleCheckoutModal() {\n      if ('complete' === document.readyState) {\n        dokan_razorpay_checkout.addEvent($('#dokan-razorpay-btn')[0], 'click', dokan_razorpay_checkout.openCheckout);\n        dokan_razorpay_checkout.openCheckout();\n      } else {\n        document.addEventListener('DOMContentLoaded', function () {\n          dokan_razorpay_checkout.addEvent($('#dokan-razorpay-btn')[0], 'click', dokan_razorpay_checkout.openCheckout);\n          dokan_razorpay_checkout.openCheckout();\n        });\n      }\n    }\n  };\n\n  // Init checkout process\n  dokan_razorpay_checkout.init();\n  dokan_razorpay_checkout.handleCheckoutModal();\n})(jQuery, window, document);\n\n//# sourceURL=webpack://dokan-pro/./modules/razorpay/assets/src/js/razorpay-checkout.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/typeof.js":
/*!***********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/typeof.js ***!
  \***********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _typeof)\n/* harmony export */ });\nfunction _typeof(obj) {\n  \"@babel/helpers - typeof\";\n\n  return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (obj) {\n    return typeof obj;\n  } : function (obj) {\n    return obj && \"function\" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? \"symbol\" : typeof obj;\n  }, _typeof(obj);\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/typeof.js?");

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
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/razorpay/assets/src/js/razorpay-checkout.js");
/******/ 	
/******/ })()
;