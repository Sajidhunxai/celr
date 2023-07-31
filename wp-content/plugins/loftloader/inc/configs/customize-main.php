<?php
/**
* Load loftloader lite main switcher related functions
*
* @since version 2.0.0
*/
add_action( 'customize_register', 'loftloader_customize_main', 10 );
function loftloader_customize_main( $wp_customize ) { 
	global $loftloader_default_settings;

	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_main_switch', array(
		'default'   => $loftloader_default_settings['loftloader_main_switch'],
		'transport' => 'refresh',
		'type' => 'option',
		'sanitize_callback' => 'loftloader_sanitize_checkbox'
	) ) );

	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_switch', array(
		'title' => esc_html__('Enable LoftLoader', 'loftloader'),
		'type' => 'loftloader_switch',
		'priority' => 10,
	) ) );

	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'loftloader_main_switch', array(
		'type' => 'checkbox',
		'label' => esc_html__( 'Enable LoftLoader', 'loftloader' ),
		'choices' => array('on' => ''),
		'section' => 'loftloader_switch',
		'settings' => 'loftloader_main_switch'
	) ) );
}