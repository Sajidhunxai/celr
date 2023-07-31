<?php
if ( ! class_exists( 'LoftLoader_Upgrade' ) ) {
	class LoftLoader_Upgrade {
		private $version ='2.4';
		function __construct(){
			$old_version = get_option('loftloader_lite_version', '1.0');
			if(version_compare($old_version, $this->version, '<')){
				if(version_compare($old_version, '2.0', '<')){
					$this->upgrade20();
				}
				$this->update_version();
			}
		}
		private function upgrade20(){
			$default_img = LOFTLOADER_URI . 'assets/img/loftloader-logo.png';
			$defaults = array(
				'enable' => 'on',
				'homepage' => '',
				'settings'         => array(
					'background'   => array(
						'effect'   => 'fade',
						'color'    => '#000000',
						'opacity'  => '95%'
					),
					'animation' => array(
						'type'  => 'pl-sun',
						'color' => '#248acc',
						'image' => array(
							'url' => $default_img,
							'id'  => ''
						),
						'width' => '76'
					)
				)
			);
			$saved = get_option('loftloader-custom-settings', array());
			$options = array_merge($defaults, $saved);
			$effect = array('fade'=> 'fade', 'slide-up'=> 'up', 'slide-left-right'=> 'split-h', 'slide-up-down'=> 'split-v');

			if($options['settings']['animation']['image']['url'] == LOFTLOADER_URI . 'img/loftloader-logo.png'){
				$options['settings']['animation']['image']['url'] = $default_img;
			}

			update_option('loftloader_main_switch', ($options['enable'] == 'on' ? 'on' : 'off'));
			update_option('loftloader_show_range', ($options['homepage'] == 'on' ? 'homepage': 'sitewide'));

			update_option('loftloader_bg_color', $options['settings']['background']['color']);
			update_option('loftloader_bg_opacity', intval($options['settings']['background']['opacity']));
			update_option('loftloader_bg_animation', $effect[$options['settings']['background']['effect']]);

			update_option('loftloader_loader_type', substr($options['settings']['animation']['type'], 3));
			update_option('loftloader_loader_color', $options['settings']['animation']['color']);
			update_option('loftloader_custom_img', $options['settings']['animation']['image']['url']);
			update_option('loftloader_img_width', $options['settings']['animation']['width']);
		}
		private function update_version(){
			update_option('loftloader_lite_version', $this->version);
		}
	}
	new LoftLoader_Upgrade();
}
