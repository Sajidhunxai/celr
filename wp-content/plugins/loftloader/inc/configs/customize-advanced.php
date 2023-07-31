<?php
/**
* Load loftloader lite Any page related functions
*
* @since version 2.0.0
*/
add_action( 'customize_register', 'loftloader_customize_advanced', 50 );
function loftloader_customize_advanced( $wp_customize ) {
	global $loftloader_default_settings;

	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_inline_js', array(
		'default'   		=> $loftloader_default_settings['loftloader_inline_js'],
		'transport' 		=> 'refresh',
		'type'				=> 'option',
		'sanitize_callback' => 'loftloader_sanitize_choice'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_enable_any_page', array(
		'default'   		=> $loftloader_default_settings['loftloader_enable_any_page'],
		'transport' 		=> 'refresh',
		'type'				=> 'option',
		'sanitize_callback' => 'loftloader_sanitize_checkbox'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_any_page_generation', array(
		'default'  		 	=> esc_html__( 'Generate', 'loftloader' ),
		'transport' 		=> 'postMessage',
		'type' 				=> 'option',
		'sanitize_callback' => 'sanitize_text_field'
	) ) );

	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_customize_advanced', array(
		'title'       => esc_html__( 'Advanced', 'loftloader' ),
		'description' => '',
		'priority'    => 55
	) ) );

	$wp_customize->add_control( new LoftLoader_Customize_Control( $wp_customize, 'loftloader_inline_js', array(
		'type' 			=> 'radio',
		'label' 		=> esc_html__( 'How to Load LoftLoader\'s JavaScript', 'loftloader' ),
		'description' 	=> '',
		'choices'		=> array(
			'' => esc_html__( 'External JavaScript', 'loftloader' ),
			'inline' => esc_html__( 'Inline JavaScript', 'loftloader' )
		),
		'section' 		=> 'loftloader_customize_advanced',
		'settings' 		=> 'loftloader_inline_js'
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Control( $wp_customize, 'loftloader_enable_any_page', array(
		'type' 			=> 'check',
		'label' 		=> esc_html__( 'Check to enable Any Page Extension', 'loftloader' ),
		'description' 	=> '',
		'choices'		=> array( 'on' => '' ),
		'section' 		=> 'loftloader_customize_advanced',
		'settings' 		=> 'loftloader_enable_any_page'
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Control( $wp_customize, 'loftloader_any_page_generation', array(
		'type' 				=> 'loftloader-any-page',
		'label' 			=> esc_html__( 'Generate LoftLoader Shortcode', 'loftloader' ),
		'description' 		=> '',
		'section' 			=> 'loftloader_customize_advanced',
		'settings' 			=> 'loftloader_any_page_generation',
		'filter' 			=> true,
		'parent_setting_id' => 'loftloader_enable_any_page',
		'show_filter' 		=> array( 'on' )
	) ) );
}
