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

/***/ "./modules/delivery-time/assets/src/js/main.js":
/*!*****************************************************!*\
  !*** ./modules/delivery-time/assets/src/js/main.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ \"./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js\");\n\n;\n(function ($) {\n  var Dokan_Handle_Delivery_Time_Slot_Picker = {\n    init: function init() {\n      $('.delivery-time-slot-picker').hide();\n      $('.store-pickup-select-options .store-address').hide();\n      $(\".delivery-time-date-picker\").on('change', Dokan_Handle_Delivery_Time_Slot_Picker.delivery_type_selector_handler);\n      Dokan_Handle_Delivery_Time_Slot_Picker.init_calendar();\n    },\n    delivery_type_selector_handler: function delivery_type_selector_handler(e) {\n      e.preventDefault();\n      var self = $(e.target);\n      var vendor_id = self.data('vendor_id');\n      var nonce = self.data('nonce');\n      var selected_date = self.attr('value');\n      var args = {\n        vendor_id: vendor_id,\n        nonce: nonce,\n        selected_date: selected_date\n      };\n      Dokan_Handle_Delivery_Time_Slot_Picker.get_delivery_time_slots(args);\n      Dokan_Handle_Delivery_Time_Slot_Picker.get_store_pickup_locations(args);\n      var selected_type = $(\"#dokan-selected-delivery-type-\".concat(vendor_id)).val();\n      if ('delivery' === selected_type) {\n        $(\"#delivery-store-location-picker-\".concat(vendor_id)).hide();\n      }\n    },\n    get_store_pickup_locations: function get_store_pickup_locations(args) {\n      var data = {\n        action: 'dokan_store_location_get_items',\n        vendor_id: args.vendor_id,\n        nonce: args.nonce\n      };\n      if (args.selected_date) {\n        $(\"#delivery-store-location-picker-\".concat(args.vendor_id)).fadeIn(400);\n        $(\"#dokan-spinner-overlay\").fadeIn(100);\n        jQuery.post(dokan.ajaxurl, data, function (response) {\n          if (response.success) {\n            $(\"#dokan-spinner-overlay\").fadeOut(400);\n            $(\"#delivery-store-location-picker-\".concat(data.vendor_id, \" option:gt(0)\")).remove();\n            $.each(response.data.vendor_store_locations, function (key, value) {\n              var location_name = Dokan_Store_Location_Pickup_Frontend.get_store_location_info_from_formatted_string(value).name;\n              $(\"#delivery-store-location-picker-\".concat(data.vendor_id)).append($(\"<option></option>\").attr(\"value\", value).text(location_name));\n            });\n          }\n        });\n      } else {\n        $(\"#delivery-store-location-picker-\".concat(args.vendor_id)).fadeOut(400);\n      }\n    },\n    get_delivery_time_slots: function get_delivery_time_slots(args) {\n      if (args.selected_date) {\n        $(\"#delivery-time-slot-picker-\".concat(args.vendor_id)).fadeIn(400);\n      } else {\n        $(\"#delivery-time-slot-picker-\".concat(args.vendor_id)).fadeOut(400);\n      }\n      $(\"#delivery-time-slot-picker-\".concat(args.vendor_id)).val($(\"#delivery-time-slot-picker-\".concat(args.vendor_id, \" option:first\")).val());\n      var data = {\n        action: 'dokan_get_delivery_time_slot',\n        vendor_id: args.vendor_id,\n        nonce: args.nonce,\n        date: args.selected_date\n      };\n      if (data.date) {\n        Dokan_Handle_Delivery_Time_Slot_Picker.get_delivery_time_slots_data(data);\n      }\n    },\n    get_delivery_time_slots_data: function get_delivery_time_slots_data(data) {\n      $(\"#dokan-spinner-overlay\").fadeIn(100);\n      jQuery.post(dokan.ajaxurl, data, function (response) {\n        if (response.success) {\n          $(\"#dokan-spinner-overlay\").fadeOut(400);\n          $(\"#delivery-time-slot-picker-\".concat(data.vendor_id, \" option:gt(0)\")).remove();\n          $.each(response.data.vendor_delivery_slots, function (key, value) {\n            var formatted_start = dokan_get_formatted_time(value.start, dokan_get_i18n_time_format(), 'hh:mm a'),\n              formatted_end = dokan_get_formatted_time(value.end, dokan_get_i18n_time_format(), 'hh:mm a');\n            $(\"#delivery-time-slot-picker-\".concat(data.vendor_id)).append($(\"<option></option>\").attr(\"value\", key).text(\"\".concat(formatted_start, \" - \").concat(formatted_end)));\n          });\n        }\n      });\n    },\n    init_calendar: function init_calendar() {\n      if (typeof dokan_delivery_time_infos === 'undefined') {\n        return;\n      }\n\n      // Collect delivery infos & admin delivery days.\n      var infos = dokan_delivery_time_infos;\n      var config = {\n        disable: [],\n        minDate: 'today',\n        altInput: true,\n        altFormat: dokan_helper.i18n_date_format.replace('jS', 'J'),\n        dateFormat: 'Y-m-d',\n        locale: dokan_flatpickr_i18n\n      };\n      var deliveryDays = '';\n      // Collect vendor ids.\n      var allDeliveryDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],\n        delivery_time_picker_keys = Object.keys(infos.vendor_data);\n      delivery_time_picker_keys.forEach(function (delivery_time_picker_key) {\n        var _infos$vendor_data$de;\n        var vendorDeliveryDayObj = (_infos$vendor_data$de = infos.vendor_data[delivery_time_picker_key].vendor_delivery_options) === null || _infos$vendor_data$de === void 0 ? void 0 : _infos$vendor_data$de.delivery_day,\n          vendorDeliveryDays = typeof vendorDeliveryDayObj !== 'undefined' ? Object.entries(vendorDeliveryDayObj).map(function (dayArray) {\n            return parseInt(dayArray[1]) || dayArray[0] === dayArray[1] ? dayArray[0] : '';\n          }).filter(function (day) {\n            return day !== null ? day : '';\n          }) : [];\n\n        // If vendor delivery days not exists.\n        if (!vendorDeliveryDays.length) {\n          return;\n        }\n        deliveryDays = vendorDeliveryDays;\n        var deliveryBlockedDaysIndex = [];\n        allDeliveryDays.forEach(function (day) {\n          if (!deliveryDays.includes(day)) {\n            deliveryBlockedDaysIndex.push(allDeliveryDays.indexOf(day));\n          }\n        });\n        config.disableDays = [function (date) {\n          // Return true if disabled.\n          return deliveryBlockedDaysIndex.includes(date.getDay());\n        }];\n        config.disable = (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(config.disableDays);\n        var vendorVacationDays = infos.vendor_data[delivery_time_picker_key].vendor_vacation_days,\n          preOrderBlockedDates = infos.vendor_data[delivery_time_picker_key].vendor_preorder_blocked_dates;\n        config.disable = [].concat((0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(config.disableDays), (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(vendorVacationDays), (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(preOrderBlockedDates));\n        delete config.disableDays;\n        flatpickr('#delivery-time-date-picker-' + delivery_time_picker_key, config);\n      });\n    }\n  };\n  var Dokan_Store_Location_Pickup_Frontend = {\n    init: function init() {\n      if (!$('.dokan-store-location-selector').length) {\n        return;\n      }\n      this.trigger_store_location_pickup();\n    },\n    trigger_store_location_pickup: function trigger_store_location_pickup() {\n      $(\".dokan-store-location-selector .selector-wrapper .selector\").on('click', function (event) {\n        var selector = $(this).data('selector');\n        var vendor_id = $(this).data('vendor_id');\n        var delivery_selector = $(\"#dokan-delivery-selector-\".concat(vendor_id));\n        var store_pickup_selector = $(\"#dokan-store-pickup-selector-\".concat(vendor_id));\n        var delivery_type = $(\"#dokan-selected-delivery-type-\".concat(vendor_id));\n        $(\"#dokan-spinner-overlay\").fadeIn(100);\n        store_pickup_selector.removeClass('active-selector');\n        delivery_selector.removeClass('active-selector');\n        if (!$(this).hasClass('active-selector')) {\n          $(this).addClass('active-selector');\n          $(\"#dokan-spinner-overlay\").fadeOut(400);\n        }\n        delivery_type.val(selector);\n        if ('delivery' === selector) {\n          $(\"#delivery-store-location-picker-\".concat(vendor_id)).fadeOut();\n          $(\"#delivery-store-location-address-\".concat(vendor_id)).parent().fadeOut();\n        } else if ('store-pickup' === selector) {\n          $(\"#delivery-store-location-picker-\".concat(vendor_id)).fadeIn();\n          if ($(\"#delivery-store-location-picker-\".concat(vendor_id)).val()) {\n            $(\"#delivery-store-location-address-\".concat(vendor_id)).parent().fadeIn();\n          }\n        }\n      });\n      $(\".delivery-store-location-picker\").on('change', function (event) {\n        var vendor_id = $(this).data('vendor_id');\n        var address_span = $(\"#delivery-store-location-address-\".concat(vendor_id));\n        var location_address = Dokan_Store_Location_Pickup_Frontend.get_store_location_info_from_formatted_string(this.value).address;\n        address_span.parent().fadeIn();\n        address_span.html(location_address);\n      });\n    },\n    get_store_location_info_from_formatted_string: function get_store_location_info_from_formatted_string(address) {\n      if (!address) {\n        return {\n          name: '',\n          address: ''\n        };\n      }\n      var location_name = address.split('(')[0];\n      var location_address = address.match(/\\((.*?)\\)/)[1];\n      return {\n        name: location_name,\n        address: location_address\n      };\n    }\n  };\n  jQuery(document).ready(function ($) {\n    Dokan_Store_Location_Pickup_Frontend.init();\n    Dokan_Handle_Delivery_Time_Slot_Picker.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/delivery-time/assets/src/js/main.js?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/delivery-time/assets/src/js/main.js");
/******/ 	
/******/ })()
;