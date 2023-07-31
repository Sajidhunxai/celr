# Changelog
======
1.3.19
======
- NEW:	Product ID no longer needed for AJAX call
- NEW:	Recoded the init variations function to support WP All Import
		https://www.welaunch.io/en/knowledge-base/faq/init-variations-after-wp-all-import/
- FIX:	Set enabled / Disabled in edit product backend variations not saving
- FIX:	Backend save performance

======
1.3.18
======
- NEW:	Yoast Premium WooCommerce SEO Plugin support

======
1.3.17
======
- NEW:	Implemented caching for shortcode also
- FIX:	Filter by variable products in admin not possible

======
1.3.16
======
- NEW:	Show variations only when filtered
		https://imgur.com/a/0o85WmI
- NEW:	Option to Cache Categories Separately
		https://imgur.com/a/BfSIRym
- FIX:	Related products showed wrong product title when attribute was set in GET parameter URL
- FIX:	Hide variation on shop page shortcode support

======
1.3.15
======
- NEW:	New FAQs available:
		https://www.welaunch.io/en/knowledge-base/faq/woocommerce-single-variation-sorting-does-not-work/
		https://www.welaunch.io/en/knowledge-base/faq/product-variation-doesnt-autoselect/
		https://www.welaunch.io/en/knowledge-base/faq/single-product-variation-images-are-missing/
- FIX:	non UTF8 chars stopped attributes assignments for variations
- FIX:	Hide on shop page option will ignore query parameters now

======
1.3.14
======
- NEW:	Draft variations will be set to private to follow WooCommerce intention on this level
		-> This will support "Draft variable product" creation
		-> Setting published products on draft without loosing variations
- FIX:	Sale product shortcode did returned excluded variations
- FIX:	Reset variation uses post type any to also reset deleted or draft variations
- FIX:	Get name in porto theme
- FIX:	Error in caching

======
1.3.13
======
- NEW:	Enable / Disable variation Menu Order
		Enable this to inherit the menu order of single variations from the main variable product. 
		This ensures the listing in product categories works as normal variable products. Deactivate 
		when you want to sort single variations when you edit a product 
		(be aware this will break category listing order!).
		https://imgur.com/a/gQdxuyL
- FIX:	Layered nav filter respects current category ID

======
1.3.12
======
- FIX:	Variation title removed attributes values
		E.g. Size S => Super T-Shirt => uper T-hirt
- FIX:	PHP notice

======
1.3.11
======
- NEW:	Variation title option enabled by default now
- NEW:	Option to hide variations on Shop main page:
		https://imgur.com/a/hu6QPOB
- FIX:	Sorting / ordering variations is just a pain -> from now it will always use parent product order menu
- FIX:	Enabled / disabled not working on product page
- FIX:	Using found_variation now for getting variation title (AJAX support)
- FIX:	PHP notice

======
1.3.10
======
- NEW:	Related products variation support
		https://imgur.com/a/bcdeDMM
- FIX:	Woo 4.9 compatibility

======
1.3.9.2
======
- FIX:	auto draft post type was syned to variations (stopped variation from beeing published)

======
1.3.9.1
======
- FIX:	Hot fix

======
1.3.9
======
- NEW:	Added shortcode support to show single variations
		Demo: https://demos.welaunch.io/woocommerce-single-variations/variations-shortcode/
		Docs: https://www.welaunch.io/en/knowledge-base/faq/woocommerce-single-variations-shortcode/

======
1.3.8
======
- FIX:	Moved updater into weLaunch framework

======
1.3.7
======
- NEW:	Dropped Redux Framework support and added our own framework 
		Read more here: https://www.welaunch.io/en/2021/01/switching-from-redux-to-our-own-framework
		This ensure auto updates & removes all gutenberg stuff
		You can delete Redux (if not used somewhere else) afterwards
		https://www.welaunch.io/updates/welaunch-framework.zip
		https://imgur.com/a/BIBz6kz

======
1.3.6
======
- FIX:	Reset variations AJAX used 5 instead of 50 variations
- FIX:	Added another check for JS loading custom variation title

======
1.3.5
======
- FIX:	AJAX Get variation title now runs async (delays title change, but better for shopping UX)
- FIX:	Product not found

======
1.3.4
======
- NEW:	Reset variations now also runs via AJAX
- NEW:	Variation post status is now taken over from parent product (e.g. draft status) 
- FIX:	Pagination issue (reset & init variation again after update)

======
1.3.3
======
- NEW:	Reset transients Caching within plugin settings
- NEW:	WooCommerce Shortcodes now no longer show parent variable products
- FIX:	Category transients not deleting
- FIX:	Better cache handling

======
1.3.2
======
- NEW:	Added excluded attributes filter: apply_filters('woocommerce_single_variations_excluded_attributes', $excludedAttributes);
- FIX:	Excluded attributes when filtering active showing duplicate results
- FIX:	removed var dump

======
1.3.1
======
- NEW: 	Add an option to disable excluded attributes when filter are active
		https://imgur.com/a/YX4xINr

======
1.3.0
======
- NEW:	Change variation title when another variation is selected (AJAX)
		https://imgur.com/a/oNfqEFW
- NEW:	SEO section with canonical support
		https://imgur.com/a/2fpnSxt
- NEW:	SEO Variations in Sitemap
		https://imgur.com/a/Fio1FT9
- NEW:	SEO Change Meta Title
		https://imgur.com/a/nZQrxC8
- NEW:	SEO Change Meta Description
		https://imgur.com/a/efHY7hy
- NEW:	Support for 3rd party plugins who uses custom post types like subscription or gift cards
- FIX: 	PHP Notice

======
1.2.4
======
- NEW:	Show ratings for single variations in shop loop
		https://imgur.com/a/oN1RNOD
		
======
1.2.3
======
- NEW:	Option to add support for 2nd gallery images in listings 
		Requires our new plugin "WooCommerce Gallery Images" (soon available)
		https://www.welaunch.io/en/product/woocommerce-gallery-images/
- NEW:	Filtered pages now also respect excluded attributes
- NEW:	Option to specify excluded attribute cache expiration
		https://imgur.com/a/44IG4AJ
- NEW:	Option to hide variations when filters are active
- NEW:	Option to include product variations in search results

======
1.2.2
======
- NEW:	Improved performance by using nested tax queries
- FIX:	switched return with continue statement

======
1.2.1
======
- NEW: 	Rewritten the exclude attributes functionality
- FIX:  Products with one attribute, that was excluded only shows once in frontend now
		e.g. a Variable product with just size variations shows once now only
- FIX:	Ordering now gets correctly reset on "init variations"

======
1.2.0
======
- NEW:	Performance increase when saving variations in backend
- NEW:	Performance increase (change query relation in plugin settings to AND)
- NEW:	Optional transient caching
- NEW:  Sorting works now on variation level
- FIX:	New published products not appearing
- FIX:	Default query relation set to "AND" for better performance
- FIX:	Removed tansient caching

======
1.1.23
======
- FIX:	New things sometimes break old things ... this will fix duplicate single variation products

======
1.1.22
======
- NEW:	Keep One Attribute Products
		For example when you have color + size products, but also you have a product with just size. Then size will still show.
- NEW:	Only Keep in Stock Products
		It will keep the first variation only when it is also on stock.
		https://imgur.com/a/lm7M7jl
		You need to reset + init variations after you checkd one
- FIX:	Excluded attributes filter count wrong

======
1.1.21
======
- NEW:	Init variations NOW uses ajax and shows statistics about initating
- FIX:	PHP notice in public.php line 290

======
1.1.20
======
- NEW:	Performance increase in admin panel through AJAX loading
		!! MAKE SURE YOU ARE ON LATEST VERSION OF REDUX FRAMEWORK !!

- FIX:	Updated Docs

======
1.1.19
======
- NEW:	Added support for custom attributes (even though we recommend not to use them)
		https://imgur.com/a/F2cYero
- FIX:	Error when no get_menu_order

======
1.1.18
======
- NEW:	Added an option to disable "adjust count" for performance
		https://imgur.com/a/SON4C8o
- FIX:	Custom variation title not shown on single product page
- FIX:	Count now substracts vairable if main product hidden choosen

======
1.1.17
======
- NEW:	Added support for date_created on variations
- FIX:	Category & Layer nav count wrong
- FIX:	PHP notice attribute not defined

======
1.1.16
======
- NEW:	Added support for non utf8 attributes (cryillic signs)
- FIX:	When enabled to hide main products, the filter / category count substracts
		variable products now

======
1.1.15
======
- NEW:	Caching for excluded attribute functionality for pagespeed
- FIX:	Filtering now still shows all variations
- FIX:	Issue with out of stock products not excluded

======
1.1.14
======
- NEW:	Added support for 3 or more variations to be excluded when used in variable products

======
1.1.13
======
- NEW:	Reset variations performance
- FIX:	Reset variations now deletes caching meta key so you rerun init afterwards

======
1.1.12
======
- FIX:	Menu Ordering removed the single variations ordering (breaking main loop)
- FIX:	When attribute e.g. "S" was in the title it got removed in product title
- FIX: 	Not enabled variations showed in catalog

======
1.1.11
======
- NEW:	Added updated check to avoid memory outtakes for first init
- FIX:	PHP notice

======
1.1.10
======
- NEW:	Added support for Price Filter widget when attributes excluded

======
1.1.9
======
- NEW:	Excluded attribute will now also be removed from permalink if enabled
		Demo: https://imgur.com/a/HwIkP25
- FIX:	Parent product check when initing variations


======
1.1.8
======
- FIX:	Exclude attributes query respects in stock query now

======
1.1.7
======
- NEW:	Keep first variation when excluding attributes
		This allow you to skip creating "any" product variations
		DEMO: https://imgur.com/a/YIjOLCY
		FAQ Updated: https://welaunch.io/plugins/woocommerce-single-variations/faq/exclude-attributes-variations/
- NEW:	Option to only show variations in shortcode

======
1.1.6
======
- NEW:	Variation Sorting
		When sorting variable (parent product) it is the first index e.g. 1
		When sortin variations inside the variable product it wil be second index
		For example: 11, 12, 31, 32
		1 & 3 are products

======
1.1.5
======
- NEW:	Added an option to set the excluded attribute relation query
		See: https://imgur.com/a/H3x5aYw

======
1.1.4
======
- FIX:	PHP Notice public 390

======
1.1.3
======
- NEW:	Option to exclude attributes from variation title (e.g. size)
- NEW:	Option to "Always show these Variation Products" in exclusions
		This can be used to show a variation, that is excluded by size but the only one available
		E.g. a shirt available in color grey and size 40 (that would normally get excluded by size attribute)
- FIX:	Reset variations showed taxonomies

======
1.1.2
======
- NEW:	Reset Variations (this will remove all variation <-> category, tag, attribute connections)
- FIX:	Adding a new variation in backend showed wp-admin panel

======
1.1.1
======
- NEW:	Variation title is now also changing on single product pages
		Example: https://welaunch.io/plugins/woocommerce-single-variations/product/variable-t-shirt/?attribute_pa_color=grey&attribute_pa_size=20

======
1.1.0
======
- NEW:	Added an option to exclude attributes
		So you can now show only color without size
		How to: https://welaunch.io/plugins/woocommerce-single-variations/faq/exclude-attributes-variations/
		Credits to our client Alexandros Koritsoglou

======
1.0.6
======
- FIX: Simple products not showing in shortcode

======
1.0.5
======
- NEW:	init variations on product publishing
- FIX: 	Product check issue
- FIX:	Parent product showing in shortcode

======
1.0.4
======
- FIX: 	Product check issue

======
1.0.3
======
- FIX:	Added a check if a product really exists

======
1.0.2
======
- NEW:	Added a filter for custom taxonomies "woocommerce_single_variations_taxonomies"
		See: https://welaunch.io/plugins/woocommerce-single-variations/faq/add-custom-taxonomies-filter/

======
1.0.1
======
- NEW:	Added support for WooCommerce Attribute Filtering 

======
1.0.0
======
- Inital release