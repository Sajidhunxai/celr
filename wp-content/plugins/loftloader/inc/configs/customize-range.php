<?php
/**
* Load loftloader lite display range related functions
*
* @since version 2.0.0
*/
add_action( 'customize_register', 'loftloader_customize_range', 20 );
function loftloader_customize_range( $wp_customize ) {
	global $loftloader_default_settings;

	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_show_range', array(
		'default'   		=> $loftloader_default_settings['loftloader_show_range'],
		'transport' 		=> 'refresh',
		'type' 				=> 'option',
		'sanitize_callback' => 'loftloader_sanitize_choice'
	) ) );

	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_range', array(
		'title'       => esc_html__( 'Display on', 'loftloader' ),
		'description' => '',
		'priority'    => 20
	) ) );

	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'loftloader_show_range', array(
		'type' 		=> 'radio',
		'label' 	=> '',
		'choices' 	=> array(
			'sitewide' => esc_html__( 'Sitewide', 'loftloader' ),
			'homepage' => esc_html__( 'Homepage only', 'loftloader' )
		),
		'section' 	=> 'loftloader_range',
		'settings'	=> 'loftloader_show_range'
	) ) );
}