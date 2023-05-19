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

/***/ "./modules/store-reviews/assets/src/js/script.js":
/*!*******************************************************!*\
  !*** ./modules/store-reviews/assets/src/js/script.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _vendor_rateyo_rateyo_min_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../vendor/rateyo/rateyo.min.css */ \"./modules/store-reviews/assets/vendor/rateyo/rateyo.min.css\");\n/* harmony import */ var _vendor_rateyo_rateyo_min_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../vendor/rateyo/rateyo.min.js */ \"./modules/store-reviews/assets/vendor/rateyo/rateyo.min.js\");\n\n\n(function ($) {\n  var wrapper = $('.dokan-review-wrapper');\n  var ajax_action = 'dokan_store_rating_ajax_handler';\n  var Dokan_Store_Rating = {\n    init: function init() {\n      wrapper.on('click', 'button.add-review-btn', this.popUp.show);\n      wrapper.on('click', 'button.edit-review-btn', this.popUp.showEdit);\n      $('body').on('submit', '#dokan-add-review-form', this.popUp.submitReview);\n    },\n    popUp: {\n      show: function show(e) {\n        var s_data = {\n          action: ajax_action,\n          data: 'review_form',\n          store_id: $('button.add-review-btn').data('store_id')\n        };\n        $.post(dokan.ajaxurl, s_data, function (resp) {\n          if (resp.success == true) {\n            $.magnificPopup.open({\n              items: {\n                src: '<div class=\"white-popup dokan-seller-rating-add-wrapper\"><div id=\"ds-error-msg\" ></div>' + resp.data + '</div>',\n                type: 'inline'\n              }\n            });\n          } else {\n            alert('failed');\n          }\n        });\n      },\n      showEdit: function showEdit(e) {\n        var s_data = {\n          action: ajax_action,\n          data: 'edit_review_form',\n          store_id: $('button.edit-review-btn').data('store_id'),\n          post_id: $('button.edit-review-btn').data('post_id')\n        };\n        $.post(dokan.ajaxurl, s_data, function (resp) {\n          if (resp.success == true) {\n            $.magnificPopup.open({\n              items: {\n                src: '<div class=\"white-popup dokan-seller-rating-add-wrapper\"><div id=\"ds-error-msg\" ></div>' + resp.data + '</div>',\n                type: 'inline'\n              }\n            });\n          } else {\n            alert('failed');\n          }\n        });\n      },\n      submitReview: function submitReview(e) {\n        e.preventDefault();\n        var self = $(this);\n        var s_data = {\n          action: ajax_action,\n          data: 'submit_review',\n          store_id: $('button.add-review-btn').data('store_id'),\n          rating: $(\"#dokan-seller-rating\").rateYo('rating'),\n          form_data: self.serialize()\n        };\n        var $e_msg = $('#ds-error-msg');\n        $.post(dokan.ajaxurl, s_data, function (resp) {\n          if (resp.success == true) {\n            $.magnificPopup.close();\n            $.magnificPopup.open({\n              items: {\n                src: '<div class=\"white-popup dokan-seller-rating-add-wrapper dokan-alert dokan-alert-success\">' + resp.msg + '</div>',\n                type: 'inline'\n              }\n            });\n            location.reload();\n          } else if (resp.success == false) {\n            $e_msg.removeClass('dokan-hide');\n            $e_msg.html(resp.msg);\n            $e_msg.addClass('dokan-alert dokan-alert-danger');\n          } else {\n            alert('failed');\n          }\n        });\n      }\n    }\n  };\n  $(function () {\n    Dokan_Store_Rating.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/store-reviews/assets/src/js/script.js?");

/***/ }),

/***/ "./modules/store-reviews/assets/vendor/rateyo/rateyo.min.js":
/*!******************************************************************!*\
  !*** ./modules/store-reviews/assets/vendor/rateyo/rateyo.min.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _babel_runtime_helpers_typeof__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/typeof */ \"./node_modules/@babel/runtime/helpers/esm/typeof.js\");\n\n/*rateYo V2.1.1, A simple and flexible star rating plugin\nprashanth pamidi (https://github.com/prrashi)*/\n!function (a) {\n  \"use strict\";\n\n  function b(a, b, c) {\n    return a === b ? a = b : a === c && (a = c), a;\n  }\n  function c(a, b, c) {\n    var d = a >= b && c >= a;\n    if (!d) throw Error(\"Invalid Rating, expected value between \" + b + \" and \" + c);\n    return a;\n  }\n  function d(a) {\n    return \"undefined\" != typeof a;\n  }\n  function e(a, b, c) {\n    var d = (b - a) * (c / 100);\n    return d = Math.round(a + d).toString(16), 1 === d.length && (d = \"0\" + d), d;\n  }\n  function f(a, b, c) {\n    if (!a || !b) return null;\n    c = d(c) ? c : 0, a = p(a), b = p(b);\n    var f = e(a.r, b.r, c),\n      g = e(a.b, b.b, c),\n      h = e(a.g, b.g, c);\n    return \"#\" + f + h + g;\n  }\n  function g(e, h) {\n    function j(a) {\n      d(a) || (a = h.rating), X = a;\n      var b = a / N,\n        c = b * P;\n      b > 1 && (c += (Math.ceil(b) - 1) * R), q(h.ratedFill), V.css(\"width\", c + \"%\");\n    }\n    function k() {\n      S = O * h.numStars + Q * (h.numStars - 1), P = O / S * 100, R = Q / S * 100, e.width(S), j();\n    }\n    function m(a) {\n      var b = h.starWidth = a;\n      return O = window.parseFloat(h.starWidth.replace(\"px\", \"\")), U.find(\"svg\").attr({\n        width: h.starWidth,\n        height: b\n      }), V.find(\"svg\").attr({\n        width: h.starWidth,\n        height: b\n      }), k(), e;\n    }\n    function o(a) {\n      return h.spacing = a, Q = parseFloat(h.spacing.replace(\"px\", \"\")), U.find(\"svg:not(:first-child)\").css({\n        \"margin-left\": a\n      }), V.find(\"svg:not(:first-child)\").css({\n        \"margin-left\": a\n      }), k(), e;\n    }\n    function p(a) {\n      return h.normalFill = a, U.find(\"svg\").attr({\n        fill: h.normalFill\n      }), e;\n    }\n    function q(a) {\n      if (h.multiColor) {\n        var b = X - W,\n          c = b / h.maxValue * 100,\n          d = h.multiColor || {},\n          g = d.startColor || n.startColor,\n          i = d.endColor || n.endColor;\n        a = f(g, i, c);\n      } else Z = a;\n      return h.ratedFill = a, V.find(\"svg\").attr({\n        fill: h.ratedFill\n      }), e;\n    }\n    function r(a) {\n      h.multiColor = a, q(a ? a : Z);\n    }\n    function s(b) {\n      h.numStars = b, N = h.maxValue / h.numStars, U.empty(), V.empty();\n      for (var c = 0; c < h.numStars; c++) {\n        U.append(a(l)), V.append(a(l));\n      }\n      return m(h.starWidth), p(h.normalFill), o(h.spacing), j(), e;\n    }\n    function t(a) {\n      return h.maxValue = a, N = h.maxValue / h.numStars, h.rating > a && A(a), j(), e;\n    }\n    function u(a) {\n      return h.precision = a, A(h.rating), e;\n    }\n    function v(a) {\n      return h.halfStar = a, e;\n    }\n    function w(a) {\n      return h.fullStar = a, e;\n    }\n    function x(a) {\n      var b = a % N,\n        c = N / 2,\n        d = h.halfStar,\n        e = h.fullStar;\n      return e || d ? (e || d && b > c ? a += N - b : (a -= b, b > 0 && (a += c)), a) : a;\n    }\n    function y(a) {\n      var b = U.offset(),\n        c = b.left,\n        d = c + U.width(),\n        e = h.maxValue,\n        f = a.pageX,\n        g = 0;\n      if (c > f) g = W;else if (f > d) g = e;else {\n        var i = (f - c) / (d - c);\n        if (Q > 0) {\n          i *= 100;\n          for (var j = i; j > 0;) {\n            j > P ? (g += N, j -= P + R) : (g += j / P * N, j = 0);\n          }\n        } else g = i * h.maxValue;\n        g = x(g);\n      }\n      return g;\n    }\n    function z(a) {\n      return h.readOnly = a, e.attr(\"readonly\", !0), L(), a || (e.removeAttr(\"readonly\"), K()), e;\n    }\n    function A(a) {\n      var d = a,\n        f = h.maxValue;\n      return \"string\" == typeof d && (\"%\" === d[d.length - 1] && (d = d.substr(0, d.length - 1), f = 100, t(f)), d = parseFloat(d)), c(d, W, f), d = parseFloat(d.toFixed(h.precision)), b(parseFloat(d), W, f), h.rating = d, j(), Y && e.trigger(\"rateyo.set\", {\n        rating: d\n      }), e;\n    }\n    function B(a) {\n      return h.onInit = a, e;\n    }\n    function C(a) {\n      return h.onSet = a, e;\n    }\n    function D(a) {\n      return h.onChange = a, e;\n    }\n    function E(a) {\n      var c = y(a).toFixed(h.precision),\n        d = h.maxValue;\n      c = b(parseFloat(c), W, d), j(c), e.trigger(\"rateyo.change\", {\n        rating: c\n      });\n    }\n    function F() {\n      j(), e.trigger(\"rateyo.change\", {\n        rating: h.rating\n      });\n    }\n    function G(a) {\n      var b = y(a).toFixed(h.precision);\n      b = parseFloat(b), M.rating(b);\n    }\n    function H(a, b) {\n      h.onInit && \"function\" == typeof h.onInit && h.onInit.apply(this, [b.rating, M]);\n    }\n    function I(a, b) {\n      h.onChange && \"function\" == typeof h.onChange && h.onChange.apply(this, [b.rating, M]);\n    }\n    function J(a, b) {\n      h.onSet && \"function\" == typeof h.onSet && h.onSet.apply(this, [b.rating, M]);\n    }\n    function K() {\n      e.on(\"mousemove\", E).on(\"mouseenter\", E).on(\"mouseleave\", F).on(\"click\", G).on(\"rateyo.init\", H).on(\"rateyo.change\", I).on(\"rateyo.set\", J);\n    }\n    function L() {\n      e.off(\"mousemove\", E).off(\"mouseenter\", E).off(\"mouseleave\", F).off(\"click\", G).off(\"rateyo.init\", H).off(\"rateyo.change\", I).off(\"rateyo.set\", J);\n    }\n    this.node = e.get(0);\n    var M = this;\n    e.empty().addClass(\"jq-ry-container\");\n    var N,\n      O,\n      P,\n      Q,\n      R,\n      S,\n      T = a(\"<div/>\").addClass(\"jq-ry-group-wrapper\").appendTo(e),\n      U = a(\"<div/>\").addClass(\"jq-ry-normal-group\").addClass(\"jq-ry-group\").appendTo(T),\n      V = a(\"<div/>\").addClass(\"jq-ry-rated-group\").addClass(\"jq-ry-group\").appendTo(T),\n      W = 0,\n      X = h.rating,\n      Y = !1,\n      Z = h.ratedFill;\n    this.rating = function (a) {\n      return d(a) ? (A(a), e) : h.rating;\n    }, this.destroy = function () {\n      return h.readOnly || L(), g.prototype.collection = i(e.get(0), this.collection), e.removeClass(\"jq-ry-container\").children().remove(), e;\n    }, this.method = function (a) {\n      if (!a) throw Error(\"Method name not specified!\");\n      if (!d(this[a])) throw Error(\"Method \" + a + \" doesn't exist!\");\n      var b = Array.prototype.slice.apply(arguments, []),\n        c = b.slice(1),\n        e = this[a];\n      return e.apply(this, c);\n    }, this.option = function (a, b) {\n      if (!d(a)) return h;\n      var c;\n      switch (a) {\n        case \"starWidth\":\n          c = m;\n          break;\n        case \"numStars\":\n          c = s;\n          break;\n        case \"normalFill\":\n          c = p;\n          break;\n        case \"ratedFill\":\n          c = q;\n          break;\n        case \"multiColor\":\n          c = r;\n          break;\n        case \"maxValue\":\n          c = t;\n          break;\n        case \"precision\":\n          c = u;\n          break;\n        case \"rating\":\n          c = A;\n          break;\n        case \"halfStar\":\n          c = v;\n          break;\n        case \"fullStar\":\n          c = w;\n          break;\n        case \"readOnly\":\n          c = z;\n          break;\n        case \"spacing\":\n          c = o;\n          break;\n        case \"onInit\":\n          c = B;\n          break;\n        case \"onSet\":\n          c = C;\n          break;\n        case \"onChange\":\n          c = D;\n          break;\n        default:\n          throw Error(\"No such option as \" + a);\n      }\n      return d(b) ? c(b) : h[a];\n    }, s(h.numStars), z(h.readOnly), this.collection.push(this), this.rating(h.rating, !0), Y = !0, e.trigger(\"rateyo.init\", {\n      rating: h.rating\n    });\n  }\n  function h(b, c) {\n    var d;\n    return a.each(c, function () {\n      return b === this.node ? (d = this, !1) : void 0;\n    }), d;\n  }\n  function i(b, c) {\n    return a.each(c, function (a) {\n      if (b === this.node) {\n        var d = c.slice(0, a),\n          e = c.slice(a + 1, c.length);\n        return c = d.concat(e), !1;\n      }\n    }), c;\n  }\n  function j(b) {\n    var c = g.prototype.collection,\n      d = a(this);\n    if (0 === d.length) return d;\n    var e = Array.prototype.slice.apply(arguments, []);\n    if (0 === e.length) b = e[0] = {};else {\n      if (1 !== e.length || \"object\" != (0,_babel_runtime_helpers_typeof__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(e[0])) {\n        if (e.length >= 1 && \"string\" == typeof e[0]) {\n          var f = e[0],\n            i = e.slice(1),\n            j = [];\n          return a.each(d, function (a, b) {\n            var d = h(b, c);\n            if (!d) throw Error(\"Trying to set options before even initialization\");\n            var e = d[f];\n            if (!e) throw Error(\"Method \" + f + \" does not exist!\");\n            var g = e.apply(d, i);\n            j.push(g);\n          }), j = 1 === j.length ? j[0] : j;\n        }\n        throw Error(\"Invalid Arguments\");\n      }\n      b = e[0];\n    }\n    return b = a.extend({}, m, b), a.each(d, function () {\n      var d = h(this, c);\n      return d ? void 0 : new g(a(this), a.extend({}, b));\n    });\n  }\n  function k() {\n    return j.apply(this, Array.prototype.slice.apply(arguments, []));\n  }\n  var l = '<?xml version=\"1.0\" encoding=\"utf-8\"?><svg version=\"1.1\"xmlns=\"http://www.w3.org/2000/svg\"viewBox=\"0 12.705 512 486.59\"x=\"0px\" y=\"0px\"xml:space=\"preserve\"><polygon points=\"256.814,12.705 317.205,198.566 512.631,198.566 354.529,313.435 414.918,499.295 256.814,384.427 98.713,499.295 159.102,313.435 1,198.566 196.426,198.566 \"/></svg>',\n    m = {\n      starWidth: \"32px\",\n      normalFill: \"gray\",\n      ratedFill: \"#f39c12\",\n      numStars: 5,\n      maxValue: 5,\n      precision: 1,\n      rating: 0,\n      fullStar: !1,\n      halfStar: !1,\n      readOnly: !1,\n      spacing: \"0px\",\n      multiColor: null,\n      onInit: null,\n      onChange: null,\n      onSet: null\n    },\n    n = {\n      startColor: \"#c0392b\",\n      endColor: \"#f1c40f\"\n    },\n    o = /^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i,\n    p = function p(a) {\n      if (!o.test(a)) return null;\n      var b = o.exec(a),\n        c = parseInt(b[1], 16),\n        d = parseInt(b[2], 16),\n        e = parseInt(b[3], 16);\n      return {\n        r: c,\n        g: d,\n        b: e\n      };\n    };\n  g.prototype.collection = [], window.RateYo = g, a.fn.rateYo = k;\n}(window.jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/store-reviews/assets/vendor/rateyo/rateyo.min.js?");

/***/ }),

/***/ "./modules/store-reviews/assets/vendor/rateyo/rateyo.min.css":
/*!*******************************************************************!*\
  !*** ./modules/store-reviews/assets/vendor/rateyo/rateyo.min.css ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n\n\n//# sourceURL=webpack://dokan-pro/./modules/store-reviews/assets/vendor/rateyo/rateyo.min.css?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/store-reviews/assets/src/js/script.js");
/******/ 	
/******/ })()
;