<?php
/**
* Load loftloader lite loader related functions
*
* @since version 2.0.0
*/
add_action( 'customize_register', 'loftloader_customize_loader', 40 );
function loftloader_customize_loader( $wp_customize ) {
	global $loftloader_default_settings;

	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_loader_type', array(
		'default'   		=> $loftloader_default_settings['loftloader_loader_type'],
		'transport' 		=> 'refresh',
		'type' 				=> 'option',
		'sanitize_callback' => 'loftloader_sanitize_choice'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_loader_color', array(
		'default'   		=> $loftloader_default_settings['loftloader_loader_color'],
		'transport' 		=> 'refresh',
		'type' 				=> 'option',
		'sanitize_callback' => 'sanitize_hex_color'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_custom_img', array(
		'default'   		=> $loftloader_default_settings['loftloader_custom_img'],
		'transport' 		=> 'refresh',
		'type' 				=> 'option',
		'sanitize_callback' => 'esc_url_raw'
	) ) );
	$wp_customize->add_setting( new WP_Customize_Setting( $wp_customize, 'loftloader_img_width', array(
		'default'   		=> $loftloader_default_settings['loftloader_img_width'],
		'transport' 		=> 'refresh',
		'type' 				=> 'option',
		'sanitize_callback' => 'absint'
	) ) );

	$wp_customize->add_section( new LoftLoader_Customize_Section( $wp_customize, 'loftloader_loader', array(
		'title'       => esc_html__( 'Loader', 'loftloader' ),
		'description' => '',
		'priority'    => 50
	) ) );

	// Controls for section loader
	$wp_customize->add_control( new LoftLoader_Customize_Animation_Types_Control( $wp_customize, 'loftloader_loader_type', array(
		'type' 			=> 'radio',
		'label' 		=> esc_html__( 'Loader Animation', 'loftloader' ),
		'description' 	=> sprintf( esc_html__( 'Some support custom image.', 'loftloader' ), '<strong>', '</strong>'),
		'choices' 		=> array(
			'sun' 			=> array( 'label' => esc_html__( 'Spinning Sun', 'loftloader' ) ),
			'circles' 		=> array( 'label' => esc_html__( 'Luminous Circles', 'loftloader' ) ),
			'wave' 			=> array( 'label' => esc_html__( 'Wave', 'loftloader' ) ),
			'square' 		=> array( 'label' => esc_html__( 'Spinning Square', 'loftloader' ) ),
			'frame' 		=> array( 'label' => esc_html__( 'Drawing Frame', 'loftloader' ) ),
			'imgloading' 	=> array( 'label' => esc_html__( 'Custom Image Loading', 'loftloader' ) ),
			'beating' 		=> array( 'label' => esc_html__( 'Beating', 'loftloader' ) )
		),
		'section' 	=> 'loftloader_loader',
		'settings' 	=> 'loftloader_loader_type'
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Color_Control( $wp_customize, 'loftloader_loader_color', array(
		'label'   			=> esc_html__( 'Pick Color', 'loftloader' ),
		'section' 	 		=> 'loftloader_loader',
		'settings' 			=> 'loftloader_loader_color',
		'filter' 			=> true,
		'parent_setting_id' => 'loftloader_loader_type',
		'show_filter' 		=> array( 'sun', 'circles', 'wave', 'square', 'frame', 'beating' )
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Image_Control( $wp_customize, 'loftloader_custom_img', array(
		'type' 				=> 'image',
		'label' 			=> esc_html__( 'Upload Image', 'loftloader' ),
		'description' 		=> '',
		'section' 			=> 'loftloader_loader',
		'settings' 			=> 'loftloader_custom_img',
		'filter' 			=> true,
		'parent_setting_id' => 'loftloader_loader_type',
		'show_filter' 		=> array( 'frame', 'imgloading' )
	) ) );
	$wp_customize->add_control( new LoftLoader_Customize_Number_Text_Control( $wp_customize, 'loftloader_img_width', array(
		'type' 				=> 'number',
		'label' 			=> esc_html__( 'Image Width', 'loftloader' ),
		'section' 			=> 'loftloader_loader',
		'settings' 			=> 'loftloader_img_width',
		'after_text' 		=> 'px',
		'input_class' 		=> 'loaderimgwidth',
		'input_wrap_class' 	=> 'imgwidth',
		'filter' 			=> true,
		'parent_setting_id' => 'loftloader_loader_type',
		'show_filter' 		=> array( 'imgloading' )
	) ) );
}