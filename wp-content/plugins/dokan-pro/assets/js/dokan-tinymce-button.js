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

/***/ "./assets/src/js/dokan-tinymce-button.js":
/*!***********************************************!*\
  !*** ./assets/src/js/dokan-tinymce-button.js ***!
  \***********************************************/
/***/ (() => {

eval("jQuery(document).ready(function ($) {\n  tinymce.create('tinymce.plugins.dokan_button', {\n    init: function init(editor, url) {\n      var menuItem = [];\n      var ds_img = dokan_assets_url + '/images/D.png';\n      $.each(dokan_shortcodes, function (i, val) {\n        var tempObj = {\n          text: val.title,\n          onclick: function onclick() {\n            editor.insertContent(val.content);\n          }\n        };\n        menuItem.push(tempObj);\n      });\n      // Register buttons - trigger above command when clickeditor\n      editor.addButton('dokan_button', {\n        title: 'Dokan shortcodes',\n        classes: 'dokan-ss',\n        type: 'menubutton',\n        //                    text  : 'Dokan',\n        //                    image : dokan_assets_url +'/images/D.png',\n        menu: menuItem,\n        style: ' background-size : 18px; background-repeat : no-repeat; background-image: url( ' + ds_img + ' );'\n      });\n    }\n  });\n\n  // Register our TinyMCE plugin\n\n  tinymce.PluginManager.add('dokan_button', tinymce.plugins.dokan_button);\n});\n\n//# sourceURL=webpack://dokan-pro/./assets/src/js/dokan-tinymce-button.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./assets/src/js/dokan-tinymce-button.js"]();
/******/ 	
/******/ })()
;