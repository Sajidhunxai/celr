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

/***/ "./modules/report-abuse/assets/src/js/frontend/main.js":
/*!*************************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/frontend/main.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ \"jquery\");\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);\n\ndokan.reportAbuse = {\n  button: null,\n  form_html: '',\n  form_title: '',\n  flashMessage: '',\n  init: function init() {\n    var self = this;\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('.dokan-report-abuse-button').on('click', function (e) {\n      e.preventDefault();\n      self.button = this;\n      if (dokanReportAbuse.reported_by_logged_in_users_only === 'on' && !dokanReportAbuse.is_user_logged_in) {\n        return jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').trigger('dokan:login_form_popup:show');\n      }\n      self.getForm();\n    });\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').on('dokan:login_form_popup:fetching_form', function () {\n      self.showLoadingAnim();\n    });\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').on('dokan:login_form_popup:fetched_form', function () {\n      self.stopLoadingAnim();\n    });\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').on('dokan:login_form_popup:logged_in', function (e, response) {\n      dokanReportAbuse.is_user_logged_in = true;\n      dokanReportAbuse.nonce = response.data.dokan_report_abuse_nonce;\n      self.getForm();\n    });\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').on('submit', '#dokan-report-abuse-form-popup form', function (e) {\n      e.preventDefault();\n      self.submitForm(this);\n    });\n  },\n  showLoadingAnim: function showLoadingAnim() {\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this.button).addClass('working').children('i').removeClass('fa-flag').addClass('fa-spin fa-refresh');\n  },\n  stopLoadingAnim: function stopLoadingAnim() {\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this.button).removeClass('working').children('i').removeClass('fa-spin fa-refresh').addClass('fa-flag');\n  },\n  submittingForm: function submittingForm() {\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-report-abuse-form-popup fieldset').prop('disabled', true);\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-report-abuse-form-submit-btn').addClass('dokan-hide');\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-report-abuse-form-working-btn').removeClass('dokan-hide');\n  },\n  submittedForm: function submittedForm() {\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-report-abuse-form-popup fieldset').prop('disabled', false);\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-report-abuse-form-submit-btn').removeClass('dokan-hide');\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-report-abuse-form-working-btn').addClass('dokan-hide');\n  },\n  getForm: function getForm() {\n    var self = this;\n    if (self.form_html) {\n      self.showPopup();\n      return;\n    }\n    self.showLoadingAnim();\n    jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({\n      url: dokan.ajaxurl,\n      method: 'get',\n      dataType: 'json',\n      data: {\n        _wpnonce: dokanReportAbuse.nonce,\n        action: 'dokan_report_abuse_get_form'\n      }\n    }).done(function (response) {\n      self.form_html = response.data.html;\n      self.form_title = response.data.title;\n      self.showPopup();\n    }).always(function () {\n      self.stopLoadingAnim();\n    });\n  },\n  showPopup: function showPopup() {\n    var self = this;\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('body').append('<div id=\"dokan-modal-report-abuse-popup\"></div>');\n    var modal = jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-modal-report-abuse-popup').iziModal({\n      headerColor: dokan.modal_header_color,\n      width: 690,\n      onClosed: function onClosed() {\n        self.afterPopupClose();\n      }\n    });\n    modal.iziModal('setTitle', self.form_title);\n    modal.iziModal('setContent', self.form_html);\n    modal.iziModal('open');\n  },\n  afterPopupClose: function afterPopupClose() {\n    if (this.flashMessage) {\n      dokan_sweetalert(this.flashMessage, {\n        icon: 'success'\n      });\n      this.flashMessage = '';\n    }\n  },\n  submitForm: function submitForm(form) {\n    var self = this;\n    var form_data = jquery__WEBPACK_IMPORTED_MODULE_0___default()(form).serialize();\n    var error_section = jquery__WEBPACK_IMPORTED_MODULE_0___default()('.dokan-popup-error', '#dokan-report-abuse-form-popup');\n    error_section.removeClass('has-error').text('');\n    self.submittingForm();\n    jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({\n      url: dokan.ajaxurl,\n      method: 'post',\n      dataType: 'json',\n      data: {\n        _wpnonce: dokanReportAbuse.nonce,\n        action: 'dokan_report_abuse_submit_form',\n        form_data: {\n          reason: jquery__WEBPACK_IMPORTED_MODULE_0___default()(form).find('[name=\"reason\"]:checked').val(),\n          product_id: dokanReportAbuse.product_id,\n          customer_name: jquery__WEBPACK_IMPORTED_MODULE_0___default()(form).find('[name=\"customer_name\"]').val(),\n          customer_email: jquery__WEBPACK_IMPORTED_MODULE_0___default()(form).find('[name=\"customer_email\"]').val(),\n          description: jquery__WEBPACK_IMPORTED_MODULE_0___default()(form).find('[name=\"description\"]').val()\n        }\n      }\n    }).done(function (response) {\n      self.flashMessage = response.data.message;\n      jquery__WEBPACK_IMPORTED_MODULE_0___default()('#dokan-modal-report-abuse-popup').iziModal('close');\n    }).always(function () {\n      self.submittedForm();\n    }).fail(function (jqXHR) {\n      if (jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message) {\n        error_section.addClass('has-error').text(jqXHR.responseJSON.data.message);\n      }\n    });\n  }\n};\ndokan.reportAbuse.init();\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/frontend/main.js?");

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ ((module) => {

module.exports = jQuery;

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
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/report-abuse/assets/src/js/frontend/main.js");
/******/ 	
/******/ })()
;