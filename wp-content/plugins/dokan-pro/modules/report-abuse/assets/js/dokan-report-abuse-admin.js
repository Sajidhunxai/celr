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

/***/ "./modules/report-abuse/assets/src/js/admin/main.js":
/*!**********************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/admin/main.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _pages_AbuseReports_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pages/AbuseReports.vue */ \"./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue\");\n\ndokan_add_route(_pages_AbuseReports_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]);\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/admin/main.js?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=script&lang=js&":
/*!******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=script&lang=js& ***!
  \******************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ \"jquery\");\n/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _components_AbuseReasonsDropdown_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../components/AbuseReasonsDropdown.vue */ \"./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue\");\n\n\nvar ListTable = dokan_get_lib('ListTable');\nvar Modal = dokan_get_lib('Modal');\nvar Multiselect = dokan_get_lib('Multiselect');\nvar AdminNotice = dokan_get_lib('AdminNotice');\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  name: 'AbuseReports',\n  components: {\n    AbuseReasonsDropdown: _components_AbuseReasonsDropdown_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n    ListTable: ListTable,\n    Modal: Modal,\n    Multiselect: Multiselect,\n    AdminNotice: AdminNotice\n  },\n  data: function data() {\n    return {\n      columns: {\n        reason: {\n          label: this.__('Reason', 'dokan')\n        },\n        product: {\n          label: this.__('Product', 'dokan')\n        },\n        vendor: {\n          label: this.__('Vendor', 'dokan')\n        },\n        reported_by: {\n          label: this.__('Reported by', 'dokan')\n        },\n        reported_at: {\n          label: this.__('Reported at', 'dokan')\n        }\n      },\n      loading: false,\n      reports: [],\n      actions: [],\n      bulkActions: [{\n        key: 'delete',\n        label: this.__('Delete', 'dokan')\n      }],\n      totalItems: 0,\n      totalPages: 1,\n      perPage: 10,\n      showModal: false,\n      report: {},\n      query: {},\n      filter: {\n        reason: '',\n        vendor_id: 0,\n        product_id: 0\n      }\n    };\n  },\n  computed: {\n    currentPage: function currentPage() {\n      var page = this.$route.query.page || 1;\n      return parseInt(page);\n    },\n    queryFilterReason: function queryFilterReason() {\n      return this.$route.query.reason || '';\n    }\n  },\n  created: function created() {\n    if (this.queryFilterReason) {\n      this.filter.reason = this.queryFilterReason;\n      this.query.reason = this.queryFilterReason;\n    }\n\n    // @todo: Filter by product and vendor on page load\n\n    this.fetchReports();\n  },\n  mounted: function mounted() {\n    var self = this;\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#filter-products').selectWoo({\n      ajax: {\n        url: \"\".concat(dokan.rest.root, \"wc/v3/products\"),\n        dataType: 'json',\n        headers: {\n          \"X-WP-Nonce\": dokan.rest.nonce\n        },\n        data: function data(params) {\n          return {\n            search: params.term\n          };\n        },\n        processResults: function processResults(data) {\n          return {\n            results: data.map(function (product) {\n              return {\n                id: product.id,\n                text: product.name\n              };\n            })\n          };\n        }\n      }\n    });\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#filter-products').on('select2:select', function (e) {\n      self.filter.product_id = e.params.data.id;\n    });\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#filter-vendors').selectWoo({\n      ajax: {\n        url: \"\".concat(dokan.rest.root, \"dokan/v1/stores\"),\n        dataType: 'json',\n        headers: {\n          \"X-WP-Nonce\": dokan.rest.nonce\n        },\n        data: function data(params) {\n          return {\n            search: params.term\n          };\n        },\n        processResults: function processResults(data) {\n          return {\n            results: data.map(function (store) {\n              return {\n                id: store.id,\n                text: store.store_name\n              };\n            })\n          };\n        }\n      }\n    });\n    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#filter-vendors').on('select2:select', function (e) {\n      self.filter.vendor_id = e.params.data.id;\n    });\n  },\n  watch: {\n    '$route.query.page': function $routeQueryPage() {\n      this.fetchReports();\n    },\n    '$route.query.reason': function $routeQueryReason() {\n      this.fetchReports();\n    },\n    '$route.query.product_id': function $routeQueryProduct_id() {\n      this.fetchReports();\n    },\n    '$route.query.vendor_id': function $routeQueryVendor_id() {\n      this.fetchReports();\n    },\n    'filter.reason': function filterReason(reason) {\n      this.query = {};\n      if (reason) {\n        this.query = {\n          reason: reason\n        };\n      }\n      this.goTo(this.query);\n    },\n    'filter.product_id': function filterProduct_id(product_id) {\n      if (product_id) {\n        this.query.product_id = product_id;\n      } else if (this.query.product_id) {\n        delete this.query.product_id;\n        this.clearSelection('#filter-products');\n      }\n      this.goTo(this.query);\n    },\n    'filter.vendor_id': function filterVendor_id(vendor_id) {\n      if (vendor_id) {\n        this.query.vendor_id = vendor_id;\n      } else if (this.query.vendor_id) {\n        delete this.query.vendor_id;\n        this.clearSelection('#filter-vendors');\n      }\n      this.goTo(this.query);\n    }\n  },\n  methods: {\n    fetchReports: function fetchReports() {\n      var self = this;\n      self.loading = true;\n      if (self.currentPage > 1) {\n        self.query.page = self.currentPage;\n      }\n      dokan.api.get('/abuse-reports', self.query).done(function (response, status, xhr) {\n        self.reports = response;\n        self.loading = false;\n        self.updatePagination(xhr);\n      });\n    },\n    updatePagination: function updatePagination(xhr) {\n      this.totalPages = parseInt(xhr.getResponseHeader('X-Dokan-AbuseReports-TotalPages'));\n      this.totalItems = parseInt(xhr.getResponseHeader('X-Dokan-AbuseReports-Total'));\n    },\n    moment: function (_moment) {\n      function moment(_x) {\n        return _moment.apply(this, arguments);\n      }\n      moment.toString = function () {\n        return _moment.toString();\n      };\n      return moment;\n    }(function (date) {\n      return moment(date);\n    }),\n    goToPage: function goToPage(page) {\n      this.query.page = page;\n      this.goTo(this.query);\n    },\n    goTo: function goTo(query) {\n      this.$router.push({\n        name: 'AbuseReports',\n        query: query\n      });\n    },\n    showReport: function showReport(report) {\n      this.report = report;\n      this.showModal = true;\n    },\n    hideReport: function hideReport() {\n      this.report = {};\n      this.showModal = false;\n    },\n    clearSelection: function clearSelection(element) {\n      jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).val(null).trigger('change');\n    },\n    onBulkAction: function onBulkAction(action, items) {\n      var self = this;\n      if (!confirm(this.__('Are you sure you want to delete this report', 'dokan'))) {\n        return;\n      }\n      dokan.api.delete('/abuse-reports/batch', {\n        items: items\n      }).done(function (response) {\n        self.fetchReports();\n      });\n    }\n  }\n});\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  name: 'AbuseReasonsDropdown',\n  props: {\n    value: {\n      type: String,\n      required: true\n    },\n    placeholder: {\n      type: String,\n      required: false,\n      default: ''\n    }\n  },\n  data: function data() {\n    return {\n      abuseReasons: []\n    };\n  },\n  computed: {\n    selectedReason: {\n      get: function get() {\n        var _this = this;\n        var reason = this.abuseReasons.filter(function (reason) {\n          return _this.value === reason.value;\n        });\n        if (reason.length) {\n          return reason[0].value;\n        }\n        return '';\n      },\n      set: function set(reason) {\n        this.$emit('input', reason || '');\n      }\n    },\n    noneText: function noneText() {\n      return this.placeholder || this.__('Select a reason', 'dokan');\n    }\n  },\n  created: function created() {\n    this.fetchAbuseReasons();\n  },\n  methods: {\n    fetchAbuseReasons: function fetchAbuseReasons() {\n      var self = this;\n      dokan.api.get('/abuse-reports/abuse-reasons').done(function (response) {\n        self.abuseReasons = response;\n      });\n    }\n  }\n});\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=template&id=27b6196f&":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=template&id=27b6196f& ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* binding */ render),\n/* harmony export */   \"staticRenderFns\": () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function render() {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c(\"div\", [_c(\"h1\", {\n    staticClass: \"wp-heading-inline\"\n  }, [_vm._v(_vm._s(_vm.__(\"Abuse Reports\", \"dokan\")))]), _vm._v(\" \"), _c(\"AdminNotice\"), _vm._v(\" \"), _c(\"hr\", {\n    staticClass: \"wp-header-end\"\n  }), _vm._v(\" \"), _c(\"list-table\", {\n    attrs: {\n      columns: _vm.columns,\n      loading: _vm.loading,\n      rows: _vm.reports,\n      actions: _vm.actions,\n      \"bulk-actions\": _vm.bulkActions,\n      \"total-items\": _vm.totalItems,\n      \"total-pages\": _vm.totalPages,\n      \"per-page\": _vm.perPage,\n      \"current-page\": _vm.currentPage,\n      text: _vm.$root.listTableTexts()\n    },\n    on: {\n      pagination: _vm.goToPage,\n      \"bulk:click\": _vm.onBulkAction\n    },\n    scopedSlots: _vm._u([{\n      key: \"reason\",\n      fn: function fn(_ref) {\n        var row = _ref.row;\n        return [_c(\"strong\", [_c(\"a\", {\n          attrs: {\n            href: \"#view-report\"\n          },\n          on: {\n            click: function click($event) {\n              $event.preventDefault();\n              return _vm.showReport(row);\n            }\n          }\n        }, [_vm._v(_vm._s(row.reason))])])];\n      }\n    }, {\n      key: \"product\",\n      fn: function fn(_ref2) {\n        var row = _ref2.row;\n        return [_c(\"a\", {\n          attrs: {\n            href: row.product.admin_url\n          }\n        }, [_vm._v(_vm._s(row.product.title))])];\n      }\n    }, {\n      key: \"vendor\",\n      fn: function fn(_ref3) {\n        var row = _ref3.row;\n        return [_c(\"router-link\", {\n          attrs: {\n            to: \"/vendors/\" + row.vendor.id\n          }\n        }, [_vm._v(\"\\n                \" + _vm._s(row.vendor.name ? row.vendor.name : _vm.__(\"(no name)\", \"dokan\")) + \"\\n            \")])];\n      }\n    }, {\n      key: \"reported_by\",\n      fn: function fn(_ref4) {\n        var row = _ref4.row;\n        return [row.reported_by.admin_url ? _c(\"a\", {\n          attrs: {\n            href: row.reported_by.admin_url,\n            target: \"_blank\"\n          },\n          domProps: {\n            textContent: _vm._s(row.reported_by.name)\n          }\n        }) : [_vm._v(\"\\n                \" + _vm._s(row.reported_by.name) + \" <\" + _vm._s(row.reported_by.email) + \">\\n            \")]];\n      }\n    }, {\n      key: \"reported_at\",\n      fn: function fn(_ref5) {\n        var row = _ref5.row;\n        return [_vm._v(\"\\n            \" + _vm._s(_vm.moment(row.reported_at).format(\"MMM D, YYYY h:mm:ss a\")) + \"\\n        \")];\n      }\n    }])\n  }, [_vm._v(\" \"), _vm._v(\" \"), _vm._v(\" \"), _vm._v(\" \"), _vm._v(\" \"), _c(\"template\", {\n    slot: \"filters\"\n  }, [_c(\"abuse-reasons-dropdown\", {\n    attrs: {\n      placeholder: _vm.__(\"Filter by abuse reason\", \"dokan\")\n    },\n    model: {\n      value: _vm.filter.reason,\n      callback: function callback($$v) {\n        _vm.$set(_vm.filter, \"reason\", $$v);\n      },\n      expression: \"filter.reason\"\n    }\n  }), _vm._v(\" \"), _vm.filter.reason ? _c(\"button\", {\n    staticClass: \"button\",\n    attrs: {\n      type: \"button\"\n    },\n    on: {\n      click: function click($event) {\n        _vm.filter.reason = \"\";\n      }\n    }\n  }, [_vm._v(\"×\")]) : _vm._e(), _vm._v(\" \"), _c(\"select\", {\n    staticStyle: {\n      width: \"190px\"\n    },\n    attrs: {\n      id: \"filter-products\",\n      \"data-placeholder\": _vm.__(\"Filter by product\", \"dokan\")\n    }\n  }), _vm._v(\" \"), _vm.filter.product_id ? _c(\"button\", {\n    staticClass: \"button\",\n    attrs: {\n      type: \"button\"\n    },\n    on: {\n      click: function click($event) {\n        _vm.filter.product_id = 0;\n      }\n    }\n  }, [_vm._v(\"×\")]) : _vm._e(), _vm._v(\" \"), _c(\"select\", {\n    staticStyle: {\n      width: \"190px\"\n    },\n    attrs: {\n      id: \"filter-vendors\",\n      \"data-placeholder\": _vm.__(\"Filter by vendor\", \"dokan\")\n    }\n  }), _vm._v(\" \"), _vm.filter.vendor_id ? _c(\"button\", {\n    staticClass: \"button\",\n    attrs: {\n      type: \"button\"\n    },\n    on: {\n      click: function click($event) {\n        _vm.filter.vendor_id = 0;\n      }\n    }\n  }, [_vm._v(\"×\")]) : _vm._e()], 1)], 2), _vm._v(\" \"), _vm.showModal ? _c(\"modal\", {\n    attrs: {\n      title: _vm.__(\"Product Abuse Report\", \"dokan\"),\n      footer: false\n    },\n    on: {\n      close: _vm.hideReport\n    }\n  }, [_c(\"template\", {\n    slot: \"body\"\n  }, [_c(\"p\", {\n    staticStyle: {\n      \"margin-top\": \"0\"\n    }\n  }, [_c(\"strong\", [_vm._v(_vm._s(_vm.__(\"Reported Product\", \"dokan\")) + \":\")]), _vm._v(\" \"), _c(\"a\", {\n    attrs: {\n      href: _vm.report.product.admin_url\n    }\n  }, [_vm._v(_vm._s(_vm.report.product.title))])]), _vm._v(\" \"), _c(\"p\", [_c(\"strong\", [_vm._v(_vm._s(_vm.__(\"Reason\", \"dokan\")) + \":\")]), _vm._v(\" \" + _vm._s(_vm.report.reason))]), _vm._v(\" \"), _c(\"p\", [_c(\"strong\", [_vm._v(_vm._s(_vm.__(\"Description\", \"dokan\")) + \":\")]), _vm._v(\" \" + _vm._s(_vm.report.description || \"―\"))]), _vm._v(\" \"), _c(\"p\", [_c(\"strong\", [_vm._v(_vm._s(_vm.__(\"Reported by\", \"dokan\")) + \":\")]), _vm._v(\" \"), _vm.report.reported_by.admin_url ? _c(\"a\", {\n    attrs: {\n      href: _vm.report.reported_by.admin_url,\n      target: \"_blank\"\n    },\n    domProps: {\n      textContent: _vm._s(_vm.report.reported_by.name)\n    }\n  }) : [_vm._v(\"\\n                    \" + _vm._s(_vm.report.reported_by.name) + \" <\" + _vm._s(_vm.report.reported_by.email) + \">\\n                \")]], 2), _vm._v(\" \"), _c(\"p\", [_c(\"strong\", [_vm._v(_vm._s(_vm.__(\"Reported At\", \"dokan\")) + \":\")]), _vm._v(\" \" + _vm._s(_vm.moment(_vm.report.reported_at).format(\"MMM D, YYYY h:mm:ss a\")))]), _vm._v(\" \"), _c(\"p\", [_c(\"strong\", [_vm._v(_vm._s(_vm.__(\"Product Vendor\", \"dokan\")) + \":\")]), _vm._v(\" \"), _vm.report.vendor.admin_url ? _c(\"a\", {\n    attrs: {\n      href: _vm.report.vendor.admin_url\n    },\n    domProps: {\n      textContent: _vm._s(_vm.report.vendor.name)\n    }\n  }) : [_vm._v(\"\\n                    \" + _vm._s(_vm.report.reported_by.name) + \" <\" + _vm._s(_vm.report.reported_by.email) + \">\\n                \")]], 2)])], 2) : _vm._e()], 1);\n};\nvar staticRenderFns = [];\nrender._withStripped = true;\n\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet%5B1%5D.rules%5B2%5D!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8&":
/*!************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8& ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* binding */ render),\n/* harmony export */   \"staticRenderFns\": () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function render() {\n  var _vm = this,\n    _c = _vm._self._c;\n  return _c(\"select\", {\n    directives: [{\n      name: \"model\",\n      rawName: \"v-model\",\n      value: _vm.selectedReason,\n      expression: \"selectedReason\"\n    }],\n    on: {\n      change: function change($event) {\n        var $$selectedVal = Array.prototype.filter.call($event.target.options, function (o) {\n          return o.selected;\n        }).map(function (o) {\n          var val = \"_value\" in o ? o._value : o.value;\n          return val;\n        });\n        _vm.selectedReason = $event.target.multiple ? $$selectedVal : $$selectedVal[0];\n      }\n    }\n  }, [_c(\"option\", {\n    attrs: {\n      value: \"\"\n    }\n  }, [_vm._v(_vm._s(_vm.noneText))]), _vm._v(\" \"), _vm._l(_vm.abuseReasons, function (reason) {\n    return _c(\"option\", {\n      key: reason.id,\n      domProps: {\n        textContent: _vm._s(reason.value)\n      }\n    });\n  })], 2);\n};\nvar staticRenderFns = [];\nrender._withStripped = true;\n\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet%5B1%5D.rules%5B2%5D!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue":
/*!*************************************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _AbuseReports_vue_vue_type_template_id_27b6196f___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AbuseReports.vue?vue&type=template&id=27b6196f& */ \"./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=template&id=27b6196f&\");\n/* harmony import */ var _AbuseReports_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AbuseReports.vue?vue&type=script&lang=js& */ \"./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=script&lang=js&\");\n/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n;\nvar component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _AbuseReports_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _AbuseReports_vue_vue_type_template_id_27b6196f___WEBPACK_IMPORTED_MODULE_0__.render,\n  _AbuseReports_vue_vue_type_template_id_27b6196f___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue\"\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?");

/***/ }),

/***/ "./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue":
/*!********************************************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _AbuseReasonsDropdown_vue_vue_type_template_id_5146c7f8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8& */ \"./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8&\");\n/* harmony import */ var _AbuseReasonsDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AbuseReasonsDropdown.vue?vue&type=script&lang=js& */ \"./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=script&lang=js&\");\n/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n;\nvar component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _AbuseReasonsDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _AbuseReasonsDropdown_vue_vue_type_template_id_5146c7f8___WEBPACK_IMPORTED_MODULE_0__.render,\n  _AbuseReasonsDropdown_vue_vue_type_template_id_5146c7f8___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue\"\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?");

/***/ }),

/***/ "./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReports_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js!../../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./AbuseReports.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=script&lang=js&\");\n /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReports_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?");

/***/ }),

/***/ "./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReasonsDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./AbuseReasonsDropdown.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=script&lang=js&\");\n /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReasonsDropdown_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?");

/***/ }),

/***/ "./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=template&id=27b6196f&":
/*!********************************************************************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=template&id=27b6196f& ***!
  \********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReports_vue_vue_type_template_id_27b6196f___WEBPACK_IMPORTED_MODULE_0__.render),\n/* harmony export */   \"staticRenderFns\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReports_vue_vue_type_template_id_27b6196f___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReports_vue_vue_type_template_id_27b6196f___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../../node_modules/babel-loader/lib/index.js!../../../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./AbuseReports.vue?vue&type=template&id=27b6196f& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?vue&type=template&id=27b6196f&\");\n\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/admin/pages/AbuseReports.vue?");

/***/ }),

/***/ "./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8&":
/*!***************************************************************************************************************!*\
  !*** ./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8& ***!
  \***************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReasonsDropdown_vue_vue_type_template_id_5146c7f8___WEBPACK_IMPORTED_MODULE_0__.render),\n/* harmony export */   \"staticRenderFns\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReasonsDropdown_vue_vue_type_template_id_5146c7f8___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AbuseReasonsDropdown_vue_vue_type_template_id_5146c7f8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js!../../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?vue&type=template&id=5146c7f8&\");\n\n\n//# sourceURL=webpack://dokan-pro/./modules/report-abuse/assets/src/js/components/AbuseReasonsDropdown.vue?");

/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* binding */ normalizeComponent)\n/* harmony export */ });\n/* globals __VUE_SSR_CONTEXT__ */\n\n// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).\n// This module is a runtime utility for cleaner component module output and will\n// be included in the final webpack user bundle.\n\nfunction normalizeComponent(\n  scriptExports,\n  render,\n  staticRenderFns,\n  functionalTemplate,\n  injectStyles,\n  scopeId,\n  moduleIdentifier /* server only */,\n  shadowMode /* vue-cli only */\n) {\n  // Vue.extend constructor export interop\n  var options =\n    typeof scriptExports === 'function' ? scriptExports.options : scriptExports\n\n  // render functions\n  if (render) {\n    options.render = render\n    options.staticRenderFns = staticRenderFns\n    options._compiled = true\n  }\n\n  // functional template\n  if (functionalTemplate) {\n    options.functional = true\n  }\n\n  // scopedId\n  if (scopeId) {\n    options._scopeId = 'data-v-' + scopeId\n  }\n\n  var hook\n  if (moduleIdentifier) {\n    // server build\n    hook = function (context) {\n      // 2.3 injection\n      context =\n        context || // cached call\n        (this.$vnode && this.$vnode.ssrContext) || // stateful\n        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional\n      // 2.2 with runInNewContext: true\n      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {\n        context = __VUE_SSR_CONTEXT__\n      }\n      // inject component styles\n      if (injectStyles) {\n        injectStyles.call(this, context)\n      }\n      // register component module identifier for async chunk inferrence\n      if (context && context._registeredComponents) {\n        context._registeredComponents.add(moduleIdentifier)\n      }\n    }\n    // used by ssr in case component is cached and beforeCreate\n    // never gets called\n    options._ssrRegister = hook\n  } else if (injectStyles) {\n    hook = shadowMode\n      ? function () {\n          injectStyles.call(\n            this,\n            (options.functional ? this.parent : this).$root.$options.shadowRoot\n          )\n        }\n      : injectStyles\n  }\n\n  if (hook) {\n    if (options.functional) {\n      // for template-only hot-reload because in that case the render fn doesn't\n      // go through the normalizer\n      options._injectStyles = hook\n      // register for functional component in vue file\n      var originalRender = options.render\n      options.render = function renderWithStyleInjection(h, context) {\n        hook.call(context)\n        return originalRender(h, context)\n      }\n    } else {\n      // inject component registration as beforeCreate hook\n      var existing = options.beforeCreate\n      options.beforeCreate = existing ? [].concat(existing, hook) : [hook]\n    }\n  }\n\n  return {\n    exports: scriptExports,\n    options: options\n  }\n}\n\n\n//# sourceURL=webpack://dokan-pro/./node_modules/vue-loader/lib/runtime/componentNormalizer.js?");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./modules/report-abuse/assets/src/js/admin/main.js");
/******/ 	
/******/ })()
;