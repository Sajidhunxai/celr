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

/***/ "./modules/germanized/assets/src/js/script-admin.js":
/*!**********************************************************!*\
  !*** ./modules/germanized/assets/src/js/script-admin.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _admin_components_VendorTaxFields_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../admin/components/VendorTaxFields.vue */ \"./modules/germanized/assets/src/admin/components/VendorTaxFields.vue\");\n\ndokan.addFilterComponent('getVendorAccountFields', 'dokanVendor', _admin_components_VendorTaxFields_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]);\n\n//# sourceURL=webpack://dokan-pro/./modules/germanized/assets/src/js/script-admin.js?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  name: 'VendorTaxFields',\n  props: {\n    vendorInfo: {\n      type: Object\n    },\n    errors: {\n      type: Array,\n      required: false\n    }\n  },\n  created: function created() {\n    this.vendorInfo.company_name_label = dokan.dokan_cf_vendor_labels.company_name;\n    this.vendorInfo.company_id_number_label = dokan.dokan_cf_vendor_labels.company_id_number;\n    this.vendorInfo.vat_number_label = dokan.dokan_cf_vendor_labels.vat_number;\n    this.vendorInfo.bank_name_label = dokan.dokan_cf_vendor_labels.bank_name;\n    this.vendorInfo.bank_iban_label = dokan.dokan_cf_vendor_labels.bank_iban;\n  },\n  data: function data() {\n    return {\n      vendorEnabledCustomFields: dokan.dokan_cf_vendor_fields\n    };\n  },\n  watch: {\n    'vendorInfo.vat_number': function vendorInfoVat_number(newValue) {\n      if (typeof newValue !== 'undefined') {\n        this.vendorInfo.vat_number = newValue.trim().replace(/[^A-Za-z0-9]/g, '');\n      }\n    },\n    'vendorInfo.company_id_number': function vendorInfoCompany_id_number(newValue) {\n      if (typeof newValue !== 'undefined') {\n        this.vendorInfo.company_id_number = newValue.trim().replace(/[^A-Za-z0-9]/g, '');\n      }\n    }\n  }\n});\n\n//# sourceURL=webpack://dokan-pro/./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=template&id=bf65ce12&":
/*!********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=template&id=bf65ce12& ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* binding */ render),\n/* harmony export */   \"staticRenderFns\": () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function render() {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c(\"div\", {\n    staticClass: \"form-group\"\n  }, [_vm.vendorEnabledCustomFields.includes(\"dokan_company_name\") ? _c(\"div\", {\n    staticClass: \"column\",\n    staticStyle: {\n      \"margin-top\": \"10px\"\n    }\n  }, [_c(\"label\", {\n    attrs: {\n      for: \"company-name\"\n    }\n  }, [_vm._v(_vm._s(_vm.vendorInfo.company_name_label))]), _vm._v(\" \"), _c(\"input\", {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.vendorInfo.company_name,\n      expression: \"vendorInfo.company_name\"\n    }],\n    staticClass: \"dokan-form-input\",\n    attrs: {\n      type: \"text\",\n      id: \"company-name\",\n      placeholder: _vm.vendorInfo.company_name_label\n    },\n    domProps: {\n      value: _vm.vendorInfo.company_name\n    },\n    on: {\n      input: function input($event) {\n        if ($event.target.composing) return;\n        _vm.$set(_vm.vendorInfo, \"company_name\", $event.target.value);\n      }\n    }\n  })]) : _vm._e(), _vm._v(\" \"), _vm.vendorEnabledCustomFields.includes(\"dokan_company_id_number\") ? _c(\"div\", {\n    staticClass: \"column\"\n  }, [_c(\"label\", {\n    attrs: {\n      for: \"company-id-number\"\n    }\n  }, [_vm._v(_vm._s(_vm.vendorInfo.company_id_number_label))]), _vm._v(\" \"), _c(\"input\", {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.vendorInfo.company_id_number,\n      expression: \"vendorInfo.company_id_number\"\n    }],\n    staticClass: \"dokan-form-input\",\n    attrs: {\n      type: \"text\",\n      id: \"company-id-number\",\n      placeholder: _vm.vendorInfo.company_id_number_label\n    },\n    domProps: {\n      value: _vm.vendorInfo.company_id_number\n    },\n    on: {\n      input: function input($event) {\n        if ($event.target.composing) return;\n        _vm.$set(_vm.vendorInfo, \"company_id_number\", $event.target.value);\n      }\n    }\n  })]) : _vm._e(), _vm._v(\" \"), _vm.vendorEnabledCustomFields.includes(\"dokan_vat_number\") ? _c(\"div\", {\n    staticClass: \"column\"\n  }, [_c(\"label\", {\n    attrs: {\n      for: \"vat-tax-number\"\n    }\n  }, [_vm._v(_vm._s(_vm.vendorInfo.vat_number_label))]), _vm._v(\" \"), _c(\"input\", {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.vendorInfo.vat_number,\n      expression: \"vendorInfo.vat_number\"\n    }],\n    staticClass: \"dokan-form-input\",\n    attrs: {\n      type: \"text\",\n      id: \"vat-tax-number\",\n      placeholder: _vm.vendorInfo.vat_number_label\n    },\n    domProps: {\n      value: _vm.vendorInfo.vat_number\n    },\n    on: {\n      input: function input($event) {\n        if ($event.target.composing) return;\n        _vm.$set(_vm.vendorInfo, \"vat_number\", $event.target.value);\n      }\n    }\n  })]) : _vm._e(), _vm._v(\" \"), _vm.vendorEnabledCustomFields.includes(\"dokan_bank_name\") ? _c(\"div\", {\n    staticClass: \"column\"\n  }, [_c(\"label\", {\n    attrs: {\n      for: \"dokan-bank-name\"\n    }\n  }, [_vm._v(_vm._s(_vm.vendorInfo.bank_name_label))]), _vm._v(\" \"), _c(\"input\", {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.vendorInfo.bank_name,\n      expression: \"vendorInfo.bank_name\"\n    }],\n    staticClass: \"dokan-form-input\",\n    attrs: {\n      type: \"text\",\n      id: \"dokan-bank-name\",\n      placeholder: _vm.vendorInfo.bank_name_label\n    },\n    domProps: {\n      value: _vm.vendorInfo.bank_name\n    },\n    on: {\n      input: function input($event) {\n        if ($event.target.composing) return;\n        _vm.$set(_vm.vendorInfo, \"bank_name\", $event.target.value);\n      }\n    }\n  })]) : _vm._e(), _vm._v(\" \"), _vm.vendorEnabledCustomFields.includes(\"dokan_bank_iban\") ? _c(\"div\", {\n    staticClass: \"column\"\n  }, [_c(\"label\", {\n    attrs: {\n      for: \"dokan-bank-iban\"\n    }\n  }, [_vm._v(_vm._s(_vm.vendorInfo.bank_iban_label))]), _vm._v(\" \"), _c(\"input\", {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.vendorInfo.bank_iban,\n      expression: \"vendorInfo.bank_iban\"\n    }],\n    staticClass: \"dokan-form-input\",\n    attrs: {\n      type: \"text\",\n      id: \"dokan-bank-iban\",\n      placeholder: _vm.vendorInfo.bank_iban_label\n    },\n    domProps: {\n      value: _vm.vendorInfo.bank_iban\n    },\n    on: {\n      input: function input($event) {\n        if ($event.target.composing) return;\n        _vm.$set(_vm.vendorInfo, \"bank_iban\", $event.target.value);\n      }\n    }\n  })]) : _vm._e()]);\n};\nvar staticRenderFns = [];\nrender._withStripped = true;\n\n\n//# sourceURL=webpack://dokan-pro/./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet%5B1%5D.rules%5B2%5D!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./modules/germanized/assets/src/admin/components/VendorTaxFields.vue":
/*!****************************************************************************!*\
  !*** ./modules/germanized/assets/src/admin/components/VendorTaxFields.vue ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _VendorTaxFields_vue_vue_type_template_id_bf65ce12___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./VendorTaxFields.vue?vue&type=template&id=bf65ce12& */ \"./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=template&id=bf65ce12&\");\n/* harmony import */ var _VendorTaxFields_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./VendorTaxFields.vue?vue&type=script&lang=js& */ \"./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=script&lang=js&\");\n/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n;\nvar component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _VendorTaxFields_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _VendorTaxFields_vue_vue_type_template_id_bf65ce12___WEBPACK_IMPORTED_MODULE_0__.render,\n  _VendorTaxFields_vue_vue_type_template_id_bf65ce12___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"modules/germanized/assets/src/admin/components/VendorTaxFields.vue\"\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);\n\n//# sourceURL=webpack://dokan-pro/./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?");

/***/ }),

/***/ "./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************!*\
  !*** ./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_VendorTaxFields_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./VendorTaxFields.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=script&lang=js&\");\n /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_VendorTaxFields_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://dokan-pro/./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?");

/***/ }),

/***/ "./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=template&id=bf65ce12&":
/*!***********************************************************************************************************!*\
  !*** ./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=template&id=bf65ce12& ***!
  \***********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_VendorTaxFields_vue_vue_type_template_id_bf65ce12___WEBPACK_IMPORTED_MODULE_0__.render),\n/* harmony export */   \"staticRenderFns\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_VendorTaxFields_vue_vue_type_template_id_bf65ce12___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_VendorTaxFields_vue_vue_type_template_id_bf65ce12___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js!../../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./VendorTaxFields.vue?vue&type=template&id=bf65ce12& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?vue&type=template&id=bf65ce12&\");\n\n\n//# sourceURL=webpack://dokan-pro/./modules/germanized/assets/src/admin/components/VendorTaxFields.vue?");

/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ normalizeComponent)\n/* harmony export */ });\n/* globals __VUE_SSR_CONTEXT__ */\n\n// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).\n// This module is a runtime utility for cleaner component module output and will\n// be included in the final webpack user bundle.\n\nfunction normalizeComponent(\n  scriptExports,\n  render,\n  staticRenderFns,\n  functionalTemplate,\n  injectStyles,\n  scopeId,\n  moduleIdentifier /* server only */,\n  shadowMode /* vue-cli only */\n) {\n  // Vue.extend constructor export interop\n  var options =\n    typeof scriptExports === 'function' ? scriptExports.options : scriptExports\n\n  // render functions\n  if (render) {\n    options.render = render\n    options.staticRenderFns = staticRenderFns\n    options._compiled = true\n  }\n\n  // functional template\n  if (functionalTemplate) {\n    options.functional = true\n  }\n\n  // scopedId\n  if (scopeId) {\n    options._scopeId = 'data-v-' + scopeId\n  }\n\n  var hook\n  if (moduleIdentifier) {\n    // server build\n    hook = function (context) {\n      // 2.3 injection\n      context =\n        context || // cached call\n        (this.$vnode && this.$vnode.ssrContext) || // stateful\n        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional\n      // 2.2 with runInNewContext: true\n      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {\n        context = __VUE_SSR_CONTEXT__\n      }\n      // inject component styles\n      if (injectStyles) {\n        injectStyles.call(this, context)\n      }\n      // register component module identifier for async chunk inferrence\n      if (context && context._registeredComponents) {\n        context._registeredComponents.add(moduleIdentifier)\n      }\n    }\n    // used by ssr in case component is cached and beforeCreate\n    // never gets called\n    options._ssrRegister = hook\n  } else if (injectStyles) {\n    hook = shadowMode\n      ? function () {\n          injectStyles.call(\n            this,\n            (options.functional ? this.parent : this).$root.$options.shadowRoot\n          )\n        }\n      : injectStyles\n  }\n\n  if (hook) {\n    if (options.functional) {\n      // for template-only hot-reload because in that case the render fn doesn't\n      // go through the normalizer\n      options._injectStyles = hook\n      // register for functional component in vue file\n      var originalRender = options.render\n      options.render = function renderWithStyleInjection(h, context) {\n        hook.call(context)\n        return originalRender(h, context)\n      }\n    } else {\n      // inject component registration as beforeCreate hook\n      var existing = options.beforeCreate\n      options.beforeCreate = existing ? [].concat(existing, hook) : [hook]\n    }\n  }\n\n  return {\n    exports: scriptExports,\n    options: options\n  }\n}\n\n\n//# sourceURL=webpack://dokan-pro/./node_modules/vue-loader/lib/runtime/componentNormalizer.js?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/germanized/assets/src/js/script-admin.js");
/******/ 	
/******/ })()
;