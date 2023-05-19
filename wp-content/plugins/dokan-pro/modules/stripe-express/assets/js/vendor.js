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

/***/ "./modules/stripe-express/assets/src/js/vendor-dashboard.js":
/*!******************************************************************!*\
  !*** ./modules/stripe-express/assets/src/js/vendor-dashboard.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ \"./node_modules/@babel/runtime/helpers/esm/defineProperty.js\");\n/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ \"./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js\");\n/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/regenerator */ \"./node_modules/@babel/runtime/regenerator/index.js\");\n/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2__);\n\n\n\nfunction ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }\nfunction _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__[\"default\"])(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }\n;\n(function ($) {\n  'use strict';\n\n  var dokanStripeExpressVendor = {\n    init: function init() {\n      var self = this;\n      $('#dokan-stripe-express-account-connect').on('click', self.signUp);\n      $('#dokan-stripe-express-dashboard-login').on('click', self.expressLogin);\n      $('#dokan-stripe-express-account-disconnect').on('click', self.disconnect);\n      $('#dokan-stripe-express-account-cancel').on('click', self.cancelAccount);\n    },\n    signUp: function signUp(e) {\n      e.preventDefault();\n      dokanStripeExpressVendor.hideMessage();\n      dokanStripeExpressVendor.showProcessing();\n      var selected_country = $('#dokan_stripe_express_vendor_country').length ? $('#dokan_stripe_express_vendor_country').val().trim() : undefined;\n      if ($('#dokan_stripe_express_country_select_error').length) {\n        $('#dokan_stripe_express_country_select_error').remove();\n      }\n      if (undefined !== selected_country && !selected_country.length && $('#dokan_stripe_express_vendor_country').length) {\n        $(\"<p class=\\\"error\\\" id=\\\"dokan_stripe_express_country_select_error\\\">\".concat(dokanStripeExpressData.i18n.country_select_error, \"</p>\")).insertAfter('#dokan_stripe_express_vendor_country');\n        dokanStripeExpressVendor.hideProcessing();\n        return;\n      }\n      $.post(dokanStripeExpressData.ajaxurl, {\n        country: undefined !== selected_country ? selected_country : '',\n        action: 'dokan_stripe_express_vendor_signup',\n        url_args: window.location.search,\n        _wpnonce: dokanStripeExpressData.nonce\n      }, function (response) {\n        if (response.success) {\n          window.location.replace(response.data.url);\n        } else {\n          dokanStripeExpressVendor.hideProcessing();\n          dokanStripeExpressVendor.showMessage(response.data, true);\n        }\n      });\n    },\n    expressLogin: function expressLogin(e) {\n      e.preventDefault();\n      dokanStripeExpressVendor.hideMessage();\n      dokanStripeExpressVendor.showProcessing();\n      $.post(dokanStripeExpressData.ajaxurl, {\n        action: 'dokan_stripe_express_get_login_url',\n        _wpnonce: dokanStripeExpressData.nonce\n      }, function (response) {\n        if (response.success) {\n          window.open(response.data.url, '_blank');\n        } else {\n          dokanStripeExpressVendor.showMessage(response.data, true);\n        }\n        dokanStripeExpressVendor.hideProcessing();\n      });\n    },\n    disconnect: function disconnect(e) {\n      e.preventDefault();\n      dokanStripeExpressVendor.hideMessage();\n      dokanStripeExpressVendor.showProcessing();\n      $.post(dokanStripeExpressData.ajaxurl, {\n        action: 'dokan_stripe_express_vendor_disconnect',\n        _wpnonce: dokanStripeExpressData.nonce\n      }, function (response) {\n        if (response.success) {\n          dokanStripeExpressVendor.showMessage(response.data);\n          window.location.reload(true);\n        } else {\n          dokanStripeExpressVendor.showMessage(response.data, true);\n        }\n        dokanStripeExpressVendor.hideProcessing();\n      });\n    },\n    cancelAccount: function () {\n      var _cancelAccount = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__[\"default\"])( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee3(e) {\n        var _dokanStripeExpressDa, _dokanStripeExpressDa2, _dokanStripeExpressDa3, _dokanStripeExpressDa4, _dokanStripeExpressDa5, _dokanStripeExpressDa6, _dokanStripeExpressDa7, _dokanStripeExpressDa8, _dokanStripeExpressDa9, _dokanStripeExpressDa10, _dokanStripeExpressDa11, _dokanStripeExpressDa12, _dokanStripeExpressDa13, _dokanStripeExpressDa14;\n        var self, swal_options, data;\n        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function _callee3$(_context3) {\n          while (1) {\n            switch (_context3.prev = _context3.next) {\n              case 0:\n                e.preventDefault();\n                dokanStripeExpressVendor.hideMessage();\n                dokanStripeExpressVendor.showProcessing();\n                self = this;\n                swal_options = {\n                  title: (_dokanStripeExpressDa = (_dokanStripeExpressDa2 = dokanStripeExpressData.i18n) === null || _dokanStripeExpressDa2 === void 0 ? void 0 : (_dokanStripeExpressDa3 = _dokanStripeExpressDa2.cancel_onboarding) === null || _dokanStripeExpressDa3 === void 0 ? void 0 : _dokanStripeExpressDa3.title) !== null && _dokanStripeExpressDa !== void 0 ? _dokanStripeExpressDa : 'Cancel Onboarding',\n                  text: (_dokanStripeExpressDa4 = (_dokanStripeExpressDa5 = dokanStripeExpressData.i18n) === null || _dokanStripeExpressDa5 === void 0 ? void 0 : (_dokanStripeExpressDa6 = _dokanStripeExpressDa5.cancel_onboarding) === null || _dokanStripeExpressDa6 === void 0 ? void 0 : _dokanStripeExpressDa6.text) !== null && _dokanStripeExpressDa4 !== void 0 ? _dokanStripeExpressDa4 : 'Are you sure you want to cancel current onboarding process? Note that, you can\\'t undo this action.',\n                  icon: 'info',\n                  width: 600,\n                  showCloseButton: false,\n                  showCancelButton: true,\n                  cancelButtonText: (_dokanStripeExpressDa7 = (_dokanStripeExpressDa8 = dokanStripeExpressData.i18n) === null || _dokanStripeExpressDa8 === void 0 ? void 0 : (_dokanStripeExpressDa9 = _dokanStripeExpressDa8.cancel_onboarding) === null || _dokanStripeExpressDa9 === void 0 ? void 0 : _dokanStripeExpressDa9.cancelButtonText) !== null && _dokanStripeExpressDa7 !== void 0 ? _dokanStripeExpressDa7 : 'No',\n                  confirmButtonText: (_dokanStripeExpressDa10 = (_dokanStripeExpressDa11 = dokanStripeExpressData.i18n) === null || _dokanStripeExpressDa11 === void 0 ? void 0 : (_dokanStripeExpressDa12 = _dokanStripeExpressDa11.cancel_onboarding) === null || _dokanStripeExpressDa12 === void 0 ? void 0 : _dokanStripeExpressDa12.confirmButtonText) !== null && _dokanStripeExpressDa10 !== void 0 ? _dokanStripeExpressDa10 : 'Yes, Cancel Onboarding',\n                  confirmButtonColor: '#1A9ED4',\n                  showLoaderOnConfirm: true,\n                  allowOutsideClick: false\n                };\n                if ((_dokanStripeExpressDa13 = dokanStripeExpressData.i18n) !== null && _dokanStripeExpressDa13 !== void 0 && (_dokanStripeExpressDa14 = _dokanStripeExpressDa13.cancel_onboarding) !== null && _dokanStripeExpressDa14 !== void 0 && _dokanStripeExpressDa14.is_setup_wizard) {\n                  delete swal_options.icon;\n                }\n                data = {\n                  action: 'dokan_stripe_express_cancel_onboarding',\n                  _wpnonce: dokanStripeExpressData.nonce\n                };\n                _context3.next = 9;\n                return Swal.fire(_objectSpread(_objectSpread({}, swal_options), {}, {\n                  preConfirm: function preConfirm() {\n                    return $.post(dokanStripeExpressData.ajaxurl, data).done(function (response) {\n                      return response;\n                    }).always(function () {\n                      dokanStripeExpressVendor.hideProcessing();\n                    }).fail(function (jqXHR) {\n                      var message = dokan_handle_ajax_error(jqXHR);\n                      if (message) {\n                        var _dokanStripeExpressDa15, _dokanStripeExpressDa16, _dokanStripeExpressDa17;\n                        Swal.fire((_dokanStripeExpressDa15 = (_dokanStripeExpressDa16 = dokanStripeExpressData.i18n) === null || _dokanStripeExpressDa16 === void 0 ? void 0 : (_dokanStripeExpressDa17 = _dokanStripeExpressDa16.cancel_onboarding) === null || _dokanStripeExpressDa17 === void 0 ? void 0 : _dokanStripeExpressDa17.errorMessage) !== null && _dokanStripeExpressDa15 !== void 0 ? _dokanStripeExpressDa15 : 'Something went wrong!', message, 'error');\n                      }\n                      return false;\n                    });\n                  },\n                  allowOutsideClick: function allowOutsideClick() {\n                    return !Swal.isLoading();\n                  },\n                  backdrop: true\n                })).then( /*#__PURE__*/function () {\n                  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__[\"default\"])( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee2(result) {\n                    var _dokanStripeExpressDa18, _dokanStripeExpressDa19, _dokanStripeExpressDa20, _dokanStripeExpressDa21, _dokanStripeExpressDa22, _dokanStripeExpressDa23;\n                    return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function _callee2$(_context2) {\n                      while (1) {\n                        switch (_context2.prev = _context2.next) {\n                          case 0:\n                            dokanStripeExpressVendor.hideProcessing();\n                            // show success message\n                            if (!result.isConfirmed) {\n                              _context2.next = 4;\n                              break;\n                            }\n                            _context2.next = 4;\n                            return Swal.fire({\n                              icon: 'success',\n                              title: (_dokanStripeExpressDa18 = (_dokanStripeExpressDa19 = dokanStripeExpressData.i18n) === null || _dokanStripeExpressDa19 === void 0 ? void 0 : (_dokanStripeExpressDa20 = _dokanStripeExpressDa19.cancel_onboarding) === null || _dokanStripeExpressDa20 === void 0 ? void 0 : _dokanStripeExpressDa20.successTitle) !== null && _dokanStripeExpressDa18 !== void 0 ? _dokanStripeExpressDa18 : 'Success',\n                              width: 600,\n                              text: (_dokanStripeExpressDa21 = (_dokanStripeExpressDa22 = dokanStripeExpressData.i18n) === null || _dokanStripeExpressDa22 === void 0 ? void 0 : (_dokanStripeExpressDa23 = _dokanStripeExpressDa22.cancel_onboarding) === null || _dokanStripeExpressDa23 === void 0 ? void 0 : _dokanStripeExpressDa23.successMessage) !== null && _dokanStripeExpressDa21 !== void 0 ? _dokanStripeExpressDa21 : 'Onboarding Cancelled'\n                            }).then( /*#__PURE__*/function () {\n                              var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_1__[\"default\"])( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().mark(function _callee(res) {\n                                return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_2___default().wrap(function _callee$(_context) {\n                                  while (1) {\n                                    switch (_context.prev = _context.next) {\n                                      case 0:\n                                        window.location.reload(true);\n                                      case 1:\n                                      case \"end\":\n                                        return _context.stop();\n                                    }\n                                  }\n                                }, _callee);\n                              }));\n                              return function (_x3) {\n                                return _ref2.apply(this, arguments);\n                              };\n                            }());\n                          case 4:\n                          case \"end\":\n                            return _context2.stop();\n                        }\n                      }\n                    }, _callee2);\n                  }));\n                  return function (_x2) {\n                    return _ref.apply(this, arguments);\n                  };\n                }());\n              case 9:\n              case \"end\":\n                return _context3.stop();\n            }\n          }\n        }, _callee3, this);\n      }));\n      function cancelAccount(_x) {\n        return _cancelAccount.apply(this, arguments);\n      }\n      return cancelAccount;\n    }(),\n    showProcessing: function showProcessing() {\n      $('#dokan-stripe-express-payment').block({\n        message: null,\n        overlayCSS: {\n          background: '#fff',\n          opacity: 0.6\n        }\n      });\n    },\n    hideProcessing: function hideProcessing() {\n      $('#dokan-stripe-express-payment').unblock();\n    },\n    showMessage: function showMessage(message, error) {\n      var $element = error ? $('#dokan-stripe-express-signup-error') : $('#dokan-stripe-express-signup-message');\n      $element.html(message);\n      $element.show();\n    },\n    hideMessage: function hideMessage() {\n      $('#dokan-stripe-express-payment .signup-message').each(function () {\n        $(this).html('');\n        $(this).hide();\n      });\n    }\n  };\n  $(document).ready(function () {\n    dokanStripeExpressVendor.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/stripe-express/assets/src/js/vendor-dashboard.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/regeneratorRuntime.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/regeneratorRuntime.js ***!
  \*******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var _typeof = (__webpack_require__(/*! ./typeof.js */ \"./node_modules/@babel/runtime/helpers/typeof.js\")[\"default\"]);\nfunction _regeneratorRuntime() {\n  \"use strict\";\n\n  /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */\n  module.exports = _regeneratorRuntime = function _regeneratorRuntime() {\n    return exports;\n  }, module.exports.__esModule = true, module.exports[\"default\"] = module.exports;\n  var exports = {},\n    Op = Object.prototype,\n    hasOwn = Op.hasOwnProperty,\n    $Symbol = \"function\" == typeof Symbol ? Symbol : {},\n    iteratorSymbol = $Symbol.iterator || \"@@iterator\",\n    asyncIteratorSymbol = $Symbol.asyncIterator || \"@@asyncIterator\",\n    toStringTagSymbol = $Symbol.toStringTag || \"@@toStringTag\";\n  function define(obj, key, value) {\n    return Object.defineProperty(obj, key, {\n      value: value,\n      enumerable: !0,\n      configurable: !0,\n      writable: !0\n    }), obj[key];\n  }\n  try {\n    define({}, \"\");\n  } catch (err) {\n    define = function define(obj, key, value) {\n      return obj[key] = value;\n    };\n  }\n  function wrap(innerFn, outerFn, self, tryLocsList) {\n    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator,\n      generator = Object.create(protoGenerator.prototype),\n      context = new Context(tryLocsList || []);\n    return generator._invoke = function (innerFn, self, context) {\n      var state = \"suspendedStart\";\n      return function (method, arg) {\n        if (\"executing\" === state) throw new Error(\"Generator is already running\");\n        if (\"completed\" === state) {\n          if (\"throw\" === method) throw arg;\n          return doneResult();\n        }\n        for (context.method = method, context.arg = arg;;) {\n          var delegate = context.delegate;\n          if (delegate) {\n            var delegateResult = maybeInvokeDelegate(delegate, context);\n            if (delegateResult) {\n              if (delegateResult === ContinueSentinel) continue;\n              return delegateResult;\n            }\n          }\n          if (\"next\" === context.method) context.sent = context._sent = context.arg;else if (\"throw\" === context.method) {\n            if (\"suspendedStart\" === state) throw state = \"completed\", context.arg;\n            context.dispatchException(context.arg);\n          } else \"return\" === context.method && context.abrupt(\"return\", context.arg);\n          state = \"executing\";\n          var record = tryCatch(innerFn, self, context);\n          if (\"normal\" === record.type) {\n            if (state = context.done ? \"completed\" : \"suspendedYield\", record.arg === ContinueSentinel) continue;\n            return {\n              value: record.arg,\n              done: context.done\n            };\n          }\n          \"throw\" === record.type && (state = \"completed\", context.method = \"throw\", context.arg = record.arg);\n        }\n      };\n    }(innerFn, self, context), generator;\n  }\n  function tryCatch(fn, obj, arg) {\n    try {\n      return {\n        type: \"normal\",\n        arg: fn.call(obj, arg)\n      };\n    } catch (err) {\n      return {\n        type: \"throw\",\n        arg: err\n      };\n    }\n  }\n  exports.wrap = wrap;\n  var ContinueSentinel = {};\n  function Generator() {}\n  function GeneratorFunction() {}\n  function GeneratorFunctionPrototype() {}\n  var IteratorPrototype = {};\n  define(IteratorPrototype, iteratorSymbol, function () {\n    return this;\n  });\n  var getProto = Object.getPrototypeOf,\n    NativeIteratorPrototype = getProto && getProto(getProto(values([])));\n  NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype);\n  var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype);\n  function defineIteratorMethods(prototype) {\n    [\"next\", \"throw\", \"return\"].forEach(function (method) {\n      define(prototype, method, function (arg) {\n        return this._invoke(method, arg);\n      });\n    });\n  }\n  function AsyncIterator(generator, PromiseImpl) {\n    function invoke(method, arg, resolve, reject) {\n      var record = tryCatch(generator[method], generator, arg);\n      if (\"throw\" !== record.type) {\n        var result = record.arg,\n          value = result.value;\n        return value && \"object\" == _typeof(value) && hasOwn.call(value, \"__await\") ? PromiseImpl.resolve(value.__await).then(function (value) {\n          invoke(\"next\", value, resolve, reject);\n        }, function (err) {\n          invoke(\"throw\", err, resolve, reject);\n        }) : PromiseImpl.resolve(value).then(function (unwrapped) {\n          result.value = unwrapped, resolve(result);\n        }, function (error) {\n          return invoke(\"throw\", error, resolve, reject);\n        });\n      }\n      reject(record.arg);\n    }\n    var previousPromise;\n    this._invoke = function (method, arg) {\n      function callInvokeWithMethodAndArg() {\n        return new PromiseImpl(function (resolve, reject) {\n          invoke(method, arg, resolve, reject);\n        });\n      }\n      return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg();\n    };\n  }\n  function maybeInvokeDelegate(delegate, context) {\n    var method = delegate.iterator[context.method];\n    if (undefined === method) {\n      if (context.delegate = null, \"throw\" === context.method) {\n        if (delegate.iterator[\"return\"] && (context.method = \"return\", context.arg = undefined, maybeInvokeDelegate(delegate, context), \"throw\" === context.method)) return ContinueSentinel;\n        context.method = \"throw\", context.arg = new TypeError(\"The iterator does not provide a 'throw' method\");\n      }\n      return ContinueSentinel;\n    }\n    var record = tryCatch(method, delegate.iterator, context.arg);\n    if (\"throw\" === record.type) return context.method = \"throw\", context.arg = record.arg, context.delegate = null, ContinueSentinel;\n    var info = record.arg;\n    return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, \"return\" !== context.method && (context.method = \"next\", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = \"throw\", context.arg = new TypeError(\"iterator result is not an object\"), context.delegate = null, ContinueSentinel);\n  }\n  function pushTryEntry(locs) {\n    var entry = {\n      tryLoc: locs[0]\n    };\n    1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry);\n  }\n  function resetTryEntry(entry) {\n    var record = entry.completion || {};\n    record.type = \"normal\", delete record.arg, entry.completion = record;\n  }\n  function Context(tryLocsList) {\n    this.tryEntries = [{\n      tryLoc: \"root\"\n    }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0);\n  }\n  function values(iterable) {\n    if (iterable) {\n      var iteratorMethod = iterable[iteratorSymbol];\n      if (iteratorMethod) return iteratorMethod.call(iterable);\n      if (\"function\" == typeof iterable.next) return iterable;\n      if (!isNaN(iterable.length)) {\n        var i = -1,\n          next = function next() {\n            for (; ++i < iterable.length;) {\n              if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next;\n            }\n            return next.value = undefined, next.done = !0, next;\n          };\n        return next.next = next;\n      }\n    }\n    return {\n      next: doneResult\n    };\n  }\n  function doneResult() {\n    return {\n      value: undefined,\n      done: !0\n    };\n  }\n  return GeneratorFunction.prototype = GeneratorFunctionPrototype, define(Gp, \"constructor\", GeneratorFunctionPrototype), define(GeneratorFunctionPrototype, \"constructor\", GeneratorFunction), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, \"GeneratorFunction\"), exports.isGeneratorFunction = function (genFun) {\n    var ctor = \"function\" == typeof genFun && genFun.constructor;\n    return !!ctor && (ctor === GeneratorFunction || \"GeneratorFunction\" === (ctor.displayName || ctor.name));\n  }, exports.mark = function (genFun) {\n    return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, \"GeneratorFunction\")), genFun.prototype = Object.create(Gp), genFun;\n  }, exports.awrap = function (arg) {\n    return {\n      __await: arg\n    };\n  }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () {\n    return this;\n  }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) {\n    void 0 === PromiseImpl && (PromiseImpl = Promise);\n    var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl);\n    return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) {\n      return result.done ? result.value : iter.next();\n    });\n  }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, \"Generator\"), define(Gp, iteratorSymbol, function () {\n    return this;\n  }), define(Gp, \"toString\", function () {\n    return \"[object Generator]\";\n  }), exports.keys = function (object) {\n    var keys = [];\n    for (var key in object) {\n      keys.push(key);\n    }\n    return keys.reverse(), function next() {\n      for (; keys.length;) {\n        var key = keys.pop();\n        if (key in object) return next.value = key, next.done = !1, next;\n      }\n      return next.done = !0, next;\n    };\n  }, exports.values = values, Context.prototype = {\n    constructor: Context,\n    reset: function reset(skipTempReset) {\n      if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = \"next\", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) {\n        \"t\" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined);\n      }\n    },\n    stop: function stop() {\n      this.done = !0;\n      var rootRecord = this.tryEntries[0].completion;\n      if (\"throw\" === rootRecord.type) throw rootRecord.arg;\n      return this.rval;\n    },\n    dispatchException: function dispatchException(exception) {\n      if (this.done) throw exception;\n      var context = this;\n      function handle(loc, caught) {\n        return record.type = \"throw\", record.arg = exception, context.next = loc, caught && (context.method = \"next\", context.arg = undefined), !!caught;\n      }\n      for (var i = this.tryEntries.length - 1; i >= 0; --i) {\n        var entry = this.tryEntries[i],\n          record = entry.completion;\n        if (\"root\" === entry.tryLoc) return handle(\"end\");\n        if (entry.tryLoc <= this.prev) {\n          var hasCatch = hasOwn.call(entry, \"catchLoc\"),\n            hasFinally = hasOwn.call(entry, \"finallyLoc\");\n          if (hasCatch && hasFinally) {\n            if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0);\n            if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc);\n          } else if (hasCatch) {\n            if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0);\n          } else {\n            if (!hasFinally) throw new Error(\"try statement without catch or finally\");\n            if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc);\n          }\n        }\n      }\n    },\n    abrupt: function abrupt(type, arg) {\n      for (var i = this.tryEntries.length - 1; i >= 0; --i) {\n        var entry = this.tryEntries[i];\n        if (entry.tryLoc <= this.prev && hasOwn.call(entry, \"finallyLoc\") && this.prev < entry.finallyLoc) {\n          var finallyEntry = entry;\n          break;\n        }\n      }\n      finallyEntry && (\"break\" === type || \"continue\" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null);\n      var record = finallyEntry ? finallyEntry.completion : {};\n      return record.type = type, record.arg = arg, finallyEntry ? (this.method = \"next\", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record);\n    },\n    complete: function complete(record, afterLoc) {\n      if (\"throw\" === record.type) throw record.arg;\n      return \"break\" === record.type || \"continue\" === record.type ? this.next = record.arg : \"return\" === record.type ? (this.rval = this.arg = record.arg, this.method = \"return\", this.next = \"end\") : \"normal\" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel;\n    },\n    finish: function finish(finallyLoc) {\n      for (var i = this.tryEntries.length - 1; i >= 0; --i) {\n        var entry = this.tryEntries[i];\n        if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel;\n      }\n    },\n    \"catch\": function _catch(tryLoc) {\n      for (var i = this.tryEntries.length - 1; i >= 0; --i) {\n        var entry = this.tryEntries[i];\n        if (entry.tryLoc === tryLoc) {\n          var record = entry.completion;\n          if (\"throw\" === record.type) {\n            var thrown = record.arg;\n            resetTryEntry(entry);\n          }\n          return thrown;\n        }\n      }\n      throw new Error(\"illegal catch attempt\");\n    },\n    delegateYield: function delegateYield(iterable, resultName, nextLoc) {\n      return this.delegate = {\n        iterator: values(iterable),\n        resultName: resultName,\n        nextLoc: nextLoc\n      }, \"next\" === this.method && (this.arg = undefined), ContinueSentinel;\n    }\n  }, exports;\n}\nmodule.exports = _regeneratorRuntime, module.exports.__esModule = true, module.exports[\"default\"] = module.exports;\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/regeneratorRuntime.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/typeof.js":
/*!*******************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/typeof.js ***!
  \*******************************************************/
/***/ ((module) => {

eval("function _typeof(obj) {\n  \"@babel/helpers - typeof\";\n\n  return (module.exports = _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (obj) {\n    return typeof obj;\n  } : function (obj) {\n    return obj && \"function\" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? \"symbol\" : typeof obj;\n  }, module.exports.__esModule = true, module.exports[\"default\"] = module.exports), _typeof(obj);\n}\nmodule.exports = _typeof, module.exports.__esModule = true, module.exports[\"default\"] = module.exports;\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/typeof.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/regenerator/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/@babel/runtime/regenerator/index.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("// TODO(Babel 8): Remove this file.\n\nvar runtime = __webpack_require__(/*! ../helpers/regeneratorRuntime */ \"./node_modules/@babel/runtime/helpers/regeneratorRuntime.js\")();\nmodule.exports = runtime;\n\n// Copied from https://github.com/facebook/regenerator/blob/main/packages/runtime/runtime.js#L736=\ntry {\n  regeneratorRuntime = runtime;\n} catch (accidentalStrictMode) {\n  if (typeof globalThis === \"object\") {\n    globalThis.regeneratorRuntime = runtime;\n  } else {\n    Function(\"r\", \"regeneratorRuntime = r\")(runtime);\n  }\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/regenerator/index.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _asyncToGenerator)\n/* harmony export */ });\nfunction asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {\n  try {\n    var info = gen[key](arg);\n    var value = info.value;\n  } catch (error) {\n    reject(error);\n    return;\n  }\n  if (info.done) {\n    resolve(value);\n  } else {\n    Promise.resolve(value).then(_next, _throw);\n  }\n}\nfunction _asyncToGenerator(fn) {\n  return function () {\n    var self = this,\n      args = arguments;\n    return new Promise(function (resolve, reject) {\n      var gen = fn.apply(self, args);\n      function _next(value) {\n        asyncGeneratorStep(gen, resolve, reject, _next, _throw, \"next\", value);\n      }\n      function _throw(err) {\n        asyncGeneratorStep(gen, resolve, reject, _next, _throw, \"throw\", err);\n      }\n      _next(undefined);\n    });\n  };\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js?");

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/defineProperty.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ _defineProperty)\n/* harmony export */ });\nfunction _defineProperty(obj, key, value) {\n  if (key in obj) {\n    Object.defineProperty(obj, key, {\n      value: value,\n      enumerable: true,\n      configurable: true,\n      writable: true\n    });\n  } else {\n    obj[key] = value;\n  }\n  return obj;\n}\n\n//# sourceURL=webpack://dokan-pro/./node_modules/@babel/runtime/helpers/esm/defineProperty.js?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/stripe-express/assets/src/js/vendor-dashboard.js");
/******/ 	
/******/ })()
;