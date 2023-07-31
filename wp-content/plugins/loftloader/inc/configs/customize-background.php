<?php
/**
* Load loftloader lite bakcground related functions
*
* @since version 2.0.0
*/
add_action( 'customize_register', 'loftloader_customize_background', 30 );
function loftloader_customize_background( $wp_customize ) {
	global $loftloader_default_settings;

	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_bg_color', array(
		'default'   		=> $loftloader_default_settings['loftloader_bg_color'],
		'transport' 		=> 'postMessage',
		'type' 				=> 'option',
		'sanitize_callback' => 'sanitize_hex_color'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_bg_opacity', array(
		'default'   		=> $loftloader_default_settings['loftloader_bg_opacity'],
		'transport' 		=> 'postMessage',
		'type' 				=> 'option',
		'sanitize_callback' => 'absint'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_bg_animation', array(
		'default'   		=> $loftloader_default_settings['loftloader_bg_animation'],
		'transport'			=> 'refresh',
		'type' 				=> 'option',
		'sanitize_callback' => 'loftloader_sanitize_choice'
	) ) );

	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_background', array(
		'title'       => esc_html__( 'Background', 'loftloader' ),
		'description' => '',
		'priority'    => 40
	) ) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'loftloader_bg_color', array(
		'label'    => esc_html__( 'Pick Color', 'loftloader' ),
		'section'  => 'loftloader_background',
		'settings' => 'loftloader_bg_color'
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Slider_Control( $wp_customize, 'loftloader_bg_opacity', array(
		'type'     		=> 'slider',
		'label'   	 	=> esc_html__( 'Opacity', 'loftloader' ),
		'input_attrs' 	=> array(
			'data-default' => 100,
			'data-min'     => 0,
			'data-max'     => 100,
			'data-step'    => 5
		),
		'input_class' 	=> 'loaderbgopacity',
		'section'  		=> 'loftloader_background',
		'settings' 		=> 'loftloader_bg_opacity'
	) ) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'loftloader_bg_animation', array(
		'type' 			=> 'select',
		'label' 		=> esc_html__( 'Ending Animation', 'loftloader' ),
		'description' 	=> esc_html__( 'Hover on preview area to see the result.', 'loftloader' ),
		'choices' 		=> array(
			'fade' 			=> esc_html__( 'Fade', 'loftloader' ),
			'split-h' 		=> esc_html__( 'Slide Left & Right', 'loftloader' ),
			'up' 			=> esc_html__( 'Slide Up', 'loftloader' ),
			'split-v' 		=> esc_html__( 'Slide Up & Down', 'loftloader' ),
			'no-animation' 	=> esc_html__( 'No Animation', 'loftloader' )
		),
		'section' 	=> 'loftloader_background',
		'settings' 	=> 'loftloader_bg_animation'
	) ) );
}
