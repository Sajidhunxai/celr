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

/***/ "./assets/src/js/dokan-pro-admin.js":
/*!******************************************!*\
  !*** ./assets/src/js/dokan-pro-admin.js ***!
  \******************************************/
/***/ (() => {

eval("/* global woocommerce_admin_meta_boxes_variations */\n\n;\n(function ($) {\n  var Dokan_Admin = {\n    init: function init() {\n      $('.dokan-modules').on('change', 'input.dokan-toggle-module', this.toggleModule);\n      $('body').on('click', '.shipment-item-details-tab-toggle', function () {\n        var shipment_id = $(this).data('shipment_id');\n        $('.shipment_body_' + shipment_id).toggle();\n        $('.shipment_footer_' + shipment_id).toggle();\n        $('.shipment_notes_area_' + shipment_id).toggle();\n        $(this).find('span').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');\n        return false;\n      });\n      $('body').on('click', '.shipment-notes-details-tab-toggle', function (e) {\n        e.preventDefault();\n        var shipment_id = $(this).data('shipment_id');\n        $(\".shipment-list-notes-inner-area\" + shipment_id).toggle();\n        $(this).find('span').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');\n      });\n    },\n    toggleModule: function toggleModule(e) {\n      var self = $(this);\n      if (self.is(':checked')) {\n        // Enabled\n        var mesg = dokan_admin.activating,\n          data = {\n            action: 'dokan-toggle-module',\n            type: 'activate',\n            module: self.closest('li').data('module'),\n            nonce: dokan_admin.nonce\n          };\n      } else {\n        // Disbaled\n        var mesg = dokan_admin.deactivating,\n          data = {\n            action: 'dokan-toggle-module',\n            type: 'deactivate',\n            module: self.closest('li').data('module'),\n            nonce: dokan_admin.nonce\n          };\n      }\n      self.closest('.plugin-card').block({\n        message: mesg,\n        overlayCSS: {\n          background: '#222',\n          opacity: 0.7\n        },\n        css: {\n          fontSize: '19px',\n          color: '#fff',\n          border: 'none',\n          backgroundColor: 'none',\n          cursor: 'wait'\n        }\n      });\n      wp.ajax.send('dokan-toggle-module', {\n        data: data,\n        success: function success(response) {},\n        error: function error(_error) {\n          if (_error.error === 'plugin-exists') {\n            wp.ajax.send('dokan-toggle-module', {\n              data: data\n            });\n          }\n        },\n        complete: function complete(resp) {\n          $('.blockMsg').text(resp.data);\n          setTimeout(function () {\n            self.closest('.plugin-card').unblock();\n          }, 1000);\n        }\n      });\n    }\n  };\n  $(document).ready(function () {\n    Dokan_Admin.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./assets/src/js/dokan-pro-admin.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./assets/src/js/dokan-pro-admin.js"]();
/******/ 	
/******/ })()
;