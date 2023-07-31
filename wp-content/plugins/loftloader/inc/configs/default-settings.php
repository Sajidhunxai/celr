<?php
/**
* Default settings for loftloader
*
* @since version 2.0.0
*/

global $loftloader_default_settings;

$loftloader_default_settings = array(
	'loftloader_main_switch' 		=> 'on',
	'loftloader_show_range' 		=> 'sitewide',

	'loftloader_bg_color' 			=> '#000000',
	'loftloader_bg_opacity' 		=> 95,
	'loftloader_bg_animation' 		=> 'fade',

	'loftloader_loader_type' 		=> 'sun',
	'loftloader_loader_color' 		=> '#248acc',
	'loftloader_custom_img' 		=> LOFTLOADER_URI . 'assets/img/loftloader-logo.png',
	'loftloader_img_width' 			=> 76,

	'loftloader_show_close_timer' 	=> 15,
	'loftloader_show_close_tip'		=> '',

	'loftloader_max_load_time'		=> 0,

	'loftloader_inline_js'			=> '',
	'loftloader_enable_any_page' 	=> '',

	'loftloader_remove_settings'	=> ''
);
