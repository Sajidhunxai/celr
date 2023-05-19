<?php
/* Template name: vendor */
if ( ! woodmart_is_woo_ajax() ) {
	get_header();
} else {
	woodmart_page_top_part();
}

$product_id = get_the_ID(); // Get the product ID
echo do_shortcode("[vendor_form product_id=334]");
?>
