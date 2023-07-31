<?php
/**
* Load loftloader lite more section
*
* @since version 2.1.3
*/
add_action( 'customize_register', 'loftloader_customize_more', 45 );
function loftloader_customize_more( $wp_customize ) {
	global $loftloader_default_settings;

	$wp_customize->add_panel( new WP_Customize_Panel ( $wp_customize, 'loftloader_panel_more', array(
		'title'       => esc_html__( 'More', 'loftloader' ),
		'priority'    => 52
	) ) );

	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_section_max_load_time', array(
		'title' => esc_html__( 'Maximum Load Time', 'loftloader' ),
		'panel'	=> 'loftloader_panel_more'
	) ) );
	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_section_close_button', array(
		'title' => esc_html__( 'Close Button', 'loftloader' ),
		'panel'	=> 'loftloader_panel_more'
	) ) );
	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_section_remove_settings', array(
		'title' => esc_html__( 'Plugin Data', 'loftloader' ),
		'panel'	=> 'loftloader_panel_more'
	) ) );

	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_max_load_time', array(
		'default'   		=> $loftloader_default_settings['loftloader_max_load_time'],
		'transport' 		=> 'postMessage',
		'type' 				=> 'option',
		'sanitize_callback' => 'loftloader_sanitize_number'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_show_close_timer', array(
		'default'   		=> $loftloader_default_settings['loftloader_show_close_timer'],
		'transport' 		=> 'postMessage',
		'type' 				=> 'option',
		'sanitize_callback' => 'absint'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_show_close_tip', array(
		'default'   		=> $loftloader_default_settings['loftloader_show_close_tip'],
		'transport' 		=> 'postMessage',
		'type' 				=> 'option',
		'sanitize_callback' => 'sanitize_text_field'
	) ) );

	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_remove_settings', array(
		'default'   		=> $loftloader_default_settings['loftloader_remove_settings'],
		'transport' 		=> 'postMessage',
		'type'				=> 'option',
		'sanitize_callback' => 'loftloader_sanitize_checkbox'
	) ) );

	$wp_customize->add_control( new LoftLoader_Customize_Control( $wp_customize, 'loftloader_max_load_time', array(
		'type' 			=> 'number',
		'label'			=> esc_html__( 'Maximum Load Time', 'loftloader' ),
		'note_below'	=> esc_html__( 'Please enter any number greater than 0 to enable this feature.', 'loftloader' ),
		'section' 		=> 'loftloader_section_max_load_time',
		'input_attrs' 	=> array( 'min' => '0' ),
		'text'			=> esc_html__( ' second(s)', 'loftloader' ),
		'settings' 		=> 'loftloader_max_load_time'
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Slider_Control( $wp_customize, 'loftloader_show_close_timer', array(
		'type'    		=> 'slider',
		'label'    		=> esc_html__( 'Show Close Button after', 'loftloader' ),
		'after_text' 	=> 'second(s)',
		'input_attrs' 	=> array(
			'data-default' 	=> '15',
			'data-min' 		=> '5',
			'data-max' 		=> '20',
			'data-step' 	=> '1'
		),
		'input_class' 	=> 'loftloader-show-close-timer',
		'section'  		=> 'loftloader_section_close_button',
		'settings' 		=> 'loftloader_show_close_timer'
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Control( $wp_customize, 'loftloader_show_close_tip', array(
		'type' 			=> 'text',
		'label'			=> esc_html__( 'Description for Close Button', 'loftloader' ),
		'section' 		=> 'loftloader_section_close_button',
		'settings' 		=> 'loftloader_show_close_tip'
	) ) );

	$wp_customize->add_control( new LoftLoader_Customize_Control( $wp_customize, 'loftloader_remove_settings', array(
		'type' 			=> 'check',
		'label' 		=> esc_html__( 'Remove Plugin Data after Deactivating Plugin', 'loftloader' ),
		'description' 	=> esc_html__( 'If checked, all settings will be removed after deactivating this plugin.', 'loftocean' ),
		'choices'		=> array( 'on' => '' ),
		'section' 		=> 'loftloader_section_remove_settings',
		'settings' 		=> 'loftloader_remove_settings'
	) ) );
}
