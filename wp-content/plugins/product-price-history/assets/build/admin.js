(()=>{var t={450:()=>{jQuery(document).ready((function(){const t=jQuery,{ranges:e,nonce:n,text:a}=devnet_pph_script;t("tr.info").map(((e,n)=>{const a=t(n),o=a.find("label").text(),i=a.find(".info-description").html();a.html(`\n\t\t<td colspan="2">\n\t\t\t<span class="info-title">${o}</span>\n\t\t\t<span class="info-description">${i}</p>\n\t\t</td>\n\t\t`)})),t(".info-description input").on("click",(function(){t(this).select()}));const o=t('[name="devnet_pph_general[multilingual]"]'),i=t('input[type="text"][name^="devnet_pph_"], textarea[name^="devnet_pph_"]');o.is(":checked")?i.addClass("disabled multilingual"):i.removeClass("disabled multilingual"),i.each((function(){t(this).hasClass("disabled multilingual")&&t(this).after('<div class="devnet-visible-tooltip">Multilingual option is active!</div>')})),t('[name="devnet_pph_general[delete_old_data]"]').append(`\n\t<option value="30_days">${a["30_days"]}</option>\n\t<option value="3_months">${a["3_months"]}</option>\n\t<option value="6_months">${a["6_months"]}</option>\n\t<option value="12_months">${a["12_months"]}</option>\n\t`),t(document).on("change",'[name="devnet_pph_general[delete_old_data]"]',(function(e){const n=t(this).val();if(t("#pph-delete-old-data, .pph-response").remove(),!n)return!1;t(this).after(`<button id="pph-delete-old-data" class="devnet-btn devnet-danger-btn" value="${n}">Delete Now</button>`)})),t(document).on("click","#pph-delete-old-data",(async function(e){e.preventDefault();const o=t(this).val();if(!confirm(a.delete_confirm))return!1;t(this).addClass("disabled");const i=
/* </fs_premium_only> */await async function(){let e,a=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",o=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"get";if(!a)return!1;try{e=await t.ajax({type:i,url:ajaxurl,data:{action:a,args:o,nonce:n}})}catch(t){console.error("ERROR: ",t)}return e}("pph_delete_old_data",{older_than:o},"post");let s=a.delete_none,l="";i>0&&(s=a.delete_success.replace("%s",i),l="color:green"),t(this).replaceWith(`<span class="pph-response" style="${l}">${s}</span>`)})),
/* <fs_premium_only> */
"function"==typeof t.fn.select2&&t('[id="devnet_pph_chart[range_selector]"]').select2({data:e})}))}},e={};function n(a){var o=e[a];if(void 0!==o)return o.exports;var i=e[a]={exports:{}};return t[a](i,i.exports,n),i.exports}n.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return n.d(e,{a:e}),e},n.d=(t,e)=>{for(var a in e)n.o(e,a)&&!n.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:e[a]})},n.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),(()=>{"use strict";n(450);jQuery(document).ready((function(){const t=jQuery,{ajaxurl:e}=devnet_pph_script;t(document).on("change",".pph-table input",(async function(n){const a=t(this).closest("tr").data("id");if("pph_hidden"===n.target.name){const n=t(this).is(":checked")?1:0;await async function(){let n,a=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",o=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"get";if(!a)return!1;try{n=await t.ajax({type:i,url:e,data:{action:a,args:o}})}catch(t){console.error("ERROR: ",t)}return n}("update_pph_db_row",{id:a,hidden:n},"post")}}))}))})()})();