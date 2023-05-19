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

/***/ "./modules/delivery-time/assets/src/js/vendor.js":
/*!*******************************************************!*\
  !*** ./modules/delivery-time/assets/src/js/vendor.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ \"./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js\");\n\n;\n(function ($) {\n  var Dokan_Handle_Vendor_Delivery_Time = {\n    init: function init() {\n      $(\"#vendor-delivery-time-date-picker\").on('change', Dokan_Handle_Vendor_Delivery_Time.set_delivery_time_date_picker);\n      $('#vendor-delivery-time-slot-picker').on('change', Dokan_Handle_Vendor_Delivery_Time.check_delivery_time_slot);\n      $(\"#dokan-store-pickup-location\").on('change', Dokan_Handle_Vendor_Delivery_Time.set_selected_store_location).trigger('change');\n      $(\".delivery-type-delivery\").on('click', Dokan_Handle_Vendor_Delivery_Time.set_delivery_order_type);\n      $(\".delivery-type-pickup\").on('click', Dokan_Handle_Vendor_Delivery_Time.set_pickup_order_type);\n    },\n    init_dashboard_calendar: function init_dashboard_calendar() {\n      if (typeof FullCalendar === 'undefined') {\n        return;\n      }\n      var local = ''; // default local is en\n      var local_length = FullCalendar.globalLocales.length;\n      for (var i = 0; i < local_length; i++) {\n        // get week start day from local\n        if (0 !== dokan_helper.week_starts_day) {\n          FullCalendar.globalLocales[i]['week']['dow'] = dokan_helper.week_starts_day;\n        }\n        // check if local data exists\n        if (FullCalendar.globalLocales[i]['code'] === dokan_full_calendar_i18n.code) {\n          local = dokan_full_calendar_i18n.code;\n          break;\n        } else if (FullCalendar.globalLocales[i]['code'] === dokan_full_calendar_i18n.code_1) {\n          local = dokan_full_calendar_i18n.code_1;\n          break;\n        }\n      }\n      var calendarEl = document.getElementById(\"delivery-time-calendar\");\n      var calendar = new FullCalendar.Calendar(calendarEl, {\n        locale: local,\n        initialView: 'dayGridMonth',\n        initialDate: new Date(),\n        headerToolbar: {\n          start: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',\n          center: 'title',\n          end: 'today prev,next'\n        },\n        events: function events(fetchInfo, successCallback, failureCallback) {\n          var data = {\n            action: 'dokan_get_dashboard_calendar_event',\n            start_date: moment(fetchInfo.start).format('YYYY-MM-DD'),\n            end_date: moment(fetchInfo.end).format('YYYY-MM-DD'),\n            nonce: dokan_delivery_time_calendar_nonce\n          };\n          var filter = Dokan_Handle_Vendor_Delivery_Time.get_filter_query_param('delivery_type_filter');\n          if (filter) {\n            data.type_filter = filter;\n          }\n          jQuery.post(dokan.ajaxurl, data, function (response) {\n            if (response.success) {\n              if (response.data.calendar_events) {\n                successCallback(Array.prototype.slice.call(response.data.calendar_events));\n              }\n            }\n          });\n        },\n        eventDidMount: function eventDidMount(info) {\n          $(info.el).tooltip({\n            title: info.event.extendedProps.info.body,\n            placement: 'top',\n            trigger: 'hover',\n            container: 'body',\n            animation: true,\n            html: true\n          });\n        }\n      });\n      calendar.render();\n    },\n    set_delivery_time_date_picker: function set_delivery_time_date_picker() {\n      var self = $(\"#vendor-delivery-time-date-picker\");\n      var vendor_id = self.data('vendor_id');\n      var nonce = self.data('nonce');\n      var selected_date = self.attr('value');\n      if (selected_date) {\n        $(\"#vendor-delivery-time-date-picker\").fadeIn(400);\n      } else {\n        $(\"#vendor-delivery-time-date-picker\").fadeOut(400);\n      }\n      var data = {\n        action: 'dokan_get_delivery_time_slot',\n        vendor_id: vendor_id,\n        nonce: nonce,\n        date: selected_date\n      };\n      if (data.date) {\n        $('#dokan_update_delivery_time').prop('disabled', true);\n        Dokan_Handle_Vendor_Delivery_Time.get_delivery_time_slots(data);\n      }\n    },\n    check_delivery_time_slot: function check_delivery_time_slot() {\n      var time_slot = $('#vendor-delivery-time-slot-picker').val();\n      if (!time_slot) {\n        return;\n      }\n      var order_type = $('#vendor-delivery-type #selected-delivery-type').val();\n      if (order_type === 'delivery') {\n        $('#dokan_update_delivery_time').prop('disabled', false);\n        return;\n      }\n      var pickup_location = $(\"#dokan-store-pickup-location\").val();\n      if (pickup_location) {\n        $('#dokan_update_delivery_time').prop('disabled', false);\n        return;\n      }\n    },\n    set_order_details_delivery_calendar_config: function set_order_details_delivery_calendar_config() {\n      if (typeof vendorInfo === 'undefined') {\n        return;\n      }\n      var info = vendorInfo;\n      var config = {\n        disable: [],\n        minDate: 'today',\n        altInput: true,\n        altFormat: dokan_helper.i18n_date_format.replace('jS', 'J'),\n        dateFormat: 'Y-m-d'\n      };\n      var allDeliveryDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];\n      var deliveryDays = Object.entries(info.vendor_delivery_options.delivery_day).map(function (dayArray) {\n        return parseInt(dayArray[1]) || dayArray[0] === dayArray[1] ? dayArray[0] : '';\n      }).filter(function (day) {\n        return day !== null ? day : '';\n      });\n      var deliveryBlockedDaysIndex = [];\n      allDeliveryDays.forEach(function (day) {\n        if (!deliveryDays.includes(day)) {\n          deliveryBlockedDaysIndex.push(allDeliveryDays.indexOf(day));\n        }\n      });\n      var vendorVacationDays = info.vendor_vacation_days;\n      var preOrderBlockedDates = info.vendor_preorder_blocked_dates;\n      config.disable = [function (date) {\n        // return true to disable\n        return deliveryBlockedDaysIndex.includes(date.getDay());\n      }];\n      config.disable = [].concat((0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(config.disable), (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(vendorVacationDays), (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(preOrderBlockedDates));\n      flatpickr('#vendor-delivery-time-date-picker', config);\n    },\n    get_delivery_time_slots: function get_delivery_time_slots(data) {\n      var vendor_delivery_time_box = $('.dokan-vendor-panel');\n      vendor_delivery_time_box.block({\n        message: null,\n        overlayCSS: {\n          background: '#fff',\n          opacity: 0.6\n        }\n      });\n      $(\"#vendor-delivery-time-slot-picker\").prop(\"disabled\", true);\n      jQuery.post(dokan.ajaxurl, data, function (response) {\n        vendor_delivery_time_box.unblock();\n        if (response.success) {\n          $(\"#vendor-delivery-time-slot-picker option:gt(0)\").remove();\n          $.each(response.data.vendor_delivery_slots, function (key, value) {\n            var formatted_start = dokan_get_formatted_time(value.start, dokan_get_i18n_time_format(), 'hh:mm a'),\n              formatted_end = dokan_get_formatted_time(value.end, dokan_get_i18n_time_format(), 'hh:mm a');\n            $(\"#vendor-delivery-time-slot-picker\").append($(\"<option></option>\").attr(\"value\", key).text(\"\".concat(formatted_start, \" - \").concat(formatted_end)));\n          });\n          $(\"#vendor-delivery-time-slot-picker\").prop(\"disabled\", false);\n        }\n      });\n    },\n    get_filter_query_param: function get_filter_query_param(name) {\n      var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.search);\n      return results !== null ? results[1] || 0 : false;\n    },\n    set_selected_store_location: function set_selected_store_location() {\n      var self = $(this),\n        store_location = self.val(),\n        selected_val = self.children('option:selected').data('value'),\n        time_slot = $('.delivery-time-slot-picker').val(),\n        order_type = $('#vendor-delivery-type #selected-delivery-type').val();\n      if (selected_val) {\n        if (time_slot) {\n          $('#dokan_update_delivery_time').prop('disabled', false);\n        }\n        $('.store-address').slideDown(300, function () {\n          $('#delivery-store-location-address').text(selected_val);\n        });\n      } else {\n        $('.store-address').slideUp(300);\n      }\n      if (order_type !== 'delivery' && !store_location) {\n        $('#dokan_update_delivery_time').prop('disabled', true);\n      }\n    },\n    set_delivery_order_type: function set_delivery_order_type() {\n      var checked = $('input.delivery-type-delivery')[0].hasAttribute('checked');\n      if (!checked) {\n        $('input.delivery-type-delivery').attr('checked', 'checked');\n        $('input.delivery-type-pickup').removeAttr('checked');\n      }\n      Dokan_Handle_Vendor_Delivery_Time.update_vendor_delivery_content('slideUp', 'delivery');\n    },\n    set_pickup_order_type: function set_pickup_order_type() {\n      var checked = $('input.delivery-type-pickup')[0].hasAttribute('checked');\n      if (!checked) {\n        $('input.delivery-type-pickup').attr('checked', 'checked');\n        $('input.delivery-type-delivery').removeAttr('checked');\n      }\n      Dokan_Handle_Vendor_Delivery_Time.update_vendor_delivery_content('slideDown', 'pickup');\n    },\n    update_vendor_delivery_content: function update_vendor_delivery_content(animationType, typeName) {\n      // Set pickup or delivery variable here.\n      var date = Vendor_Delivery_Data[typeName + '_date'],\n        heading = Vendor_Delivery_Data[typeName + '_time_heading'],\n        placeholder = Vendor_Delivery_Data[typeName + '_placeholder'];\n      Dokan_Handle_Vendor_Delivery_Time.check_delivery_time_slot();\n      $('#vendor-delivery-type #selected-delivery-type').val(typeName);\n\n      // Update pickup or delivery content here.\n      $('.delivery-time-box-heading').text(heading);\n      $('.delivery-type-date-info span').text(date);\n      $('.delivery-time-date-picker').attr('placeholder', placeholder);\n\n      // Added slideUp or slideDown animation for delivery type change.\n      $('.store-pickup-select-options')[animationType](300);\n      $('#dokan-store-pickup-location')[animationType](300);\n    }\n  };\n  jQuery(document).ready(function ($) {\n    Dokan_Handle_Vendor_Delivery_Time.init();\n    Dokan_Handle_Vendor_Delivery_Time.set_order_details_delivery_calendar_config();\n    Dokan_Handle_Vendor_Delivery_Time.init_dashboard_calendar();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/delivery-time/assets/src/js/vendor.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _arrayLikeToArray)\n/* harmony export */ });\nfunction _arrayLikeToArray(arr, len) {\n  if (len == null || len > arr.length) len = arr.length;\n  for (var i = 0, arr2 = new Array(len); i < len; i++) {\n    arr2[i] = arr[i];\n  }\n  return arr2;\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _arrayWithoutHoles)\n/* harmony export */ });\n/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ \"./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js\");\n\nfunction _arrayWithoutHoles(arr) {\n  if (Array.isArray(arr)) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(arr);\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/iterableToArray.js":
/*!********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/iterableToArray.js ***!
  \********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _iterableToArray)\n/* harmony export */ });\nfunction _iterableToArray(iter) {\n  if (typeof Symbol !== \"undefined\" && iter[Symbol.iterator] != null || iter[\"@@iterator\"] != null) return Array.from(iter);\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/iterableToArray.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _nonIterableSpread)\n/* harmony export */ });\nfunction _nonIterableSpread() {\n  throw new TypeError(\"Invalid attempt to spread non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\");\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _toConsumableArray)\n/* harmony export */ });\n/* harmony import */ var _arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayWithoutHoles.js */ \"./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js\");\n/* harmony import */ var _iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./iterableToArray.js */ \"./node_modules/@babel/runtime/helpers/esm/iterableToArray.js\");\n/* harmony import */ var _unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./unsupportedIterableToArray.js */ \"./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js\");\n/* harmony import */ var _nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./nonIterableSpread.js */ \"./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js\");\n\n\n\n\nfunction _toConsumableArray(arr) {\n  return (0,_arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(arr) || (0,_iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(arr) || (0,_unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(arr) || (0,_nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])();\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js":
/*!*******************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _unsupportedIterableToArray)\n/* harmony export */ });\n/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ \"./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js\");\n\nfunction _unsupportedIterableToArray(o, minLen) {\n  if (!o) return;\n  if (typeof o === \"string\") return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(o, minLen);\n  var n = Object.prototype.toString.call(o).slice(8, -1);\n  if (n === \"Object\" && o.constructor) n = o.constructor.name;\n  if (n === \"Map\" || n === \"Set\") return Array.from(o);\n  if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(o, minLen);\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/delivery-time/assets/src/js/vendor.js");
/******/ 	
/******/ })()
;