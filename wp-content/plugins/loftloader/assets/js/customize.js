/**
* Copyright (c) Loft.Ocean
* http://www.loftocean.com
*/

(function(api, $){
	$('head').append($('<style>', {'id': 'loftloader-hide-site-title', 'text': '.site-title { opacity:  0; }'}));
	// Main Switch section
	api.LoftLoaderSwitchSection = api.Section.extend({
		initialize: function () {
			return api.Section.prototype.initialize.apply( this, arguments );
		},
		ready: function(){
			var checked = this.container.find('input[name=loftloader-main-switch]').is(':checked');
			checked ? '' : $( '#customize-theme-controls' ).addClass( 'loftloader-settings-disabled' );
			$('#customize-theme-controls').addClass('loftloader-controls-wrapper');
		},
		attachEvents: function () {
			var container = this.container;
			container.on('change', 'input[name=loftloader-main-switch]', function(e){
				var checked  = $(this).is(':checked'), controls_wrap = $('#customize-theme-controls');

				api( 'loftloader_main_switch' )( checked );
				checked ? controls_wrap.removeClass('loftloader-settings-disabled') : controls_wrap.addClass('loftloader-settings-disabled');
			});
		}
	});
	$.extend(api.sectionConstructor, {loftloader_switch: api.LoftLoaderSwitchSection});

	// Slider control
	api.controlConstructor.slider = api.Control.extend({
		ready: function(){
			var elem = this.container.find('.loader-ui-slider'),
				input = this.container.find('input[data-customize-setting-link]');
			elem.slider({
				'range': 'min',
				'min': elem.data('min'),
				'max': elem.data('max'),
				'value': elem.data('value'),
				'step': elem.data('step'),
				'slide': function(event, ui){
					input.val(ui.value).trigger('change');
				}
			});
		}
	});

	// Register event handler for hide controls/description
	api.bind('ready', function(e){
		// Change the site title in string "You are customizing ..."
		loftloader_lite_i18n ? $('.site-title').text(loftloader_lite_i18n.name) : '';
		$('#loftloader-hide-site-title').remove();

		var current_url = document.createElement( 'a' ), current_search;
		current_url.href = api.previewer.previewUrl();
		current_search = api.utils.parseQueryString( current_url.search.substr( 1 ) );
		api.previewer.unbind( 'url' ).bind( 'url', function( url ) {
			var previewer = this, onUrlChange, urlChanged = false, urlParser;
			urlParser = document.createElement( 'a' );
			urlParser.href = url;
			urlParser.search = $.param( { 'plugin': 'loftloader-lite' } );
			url = urlParser.href;
			previewer.scroll = 0;
			onUrlChange = function() {
				urlChanged = true;
			};
			previewer.previewUrl.bind( onUrlChange );
			previewer.previewUrl.set( url );
			previewer.previewUrl.unbind( onUrlChange );
			if ( ! urlChanged ) {
				previewer.refresh();
			}
		} );
		if ( ! current_search['plugin'] ) {
			current_search['plugin'] = 'loftloader-lite';
			current_url.search = $.param( current_search );
			api.previewer.previewUrl.set( current_url.href );
		}


		// Init for loader 2.0 customizer, when sync from lower version
		var settings = api.get(),
			$custom_img = $('#customize-control-loftloader_custom_img');
		if($custom_img.length && !$custom_img.find('.attachment-thumb').length && settings && settings['loftloader_custom_img']){
			var image = settings['loftloader_custom_img'];
			if(image){
				var $container = $custom_img.find('.attachment-media-view').addClass('attachment-media-view-image'),
					$image = $('<div>', {'class': "thumbnail thumbnail-image"}).append($('<img>', {'class': "attachment-thumb", 'src': image}));

				$container.children('.placeholder').css('display', 'none').after($image).remove();
			}
		}

		$('body')
		.on('change', 'input[type=number]', function(e){
			var val = parseInt($(this).val()),
				min = $(this).attr('min') ? parseInt($(this).attr('min')) : 1;
			(val < min) ? $(this).val(min).trigger('change') : '';
		})
		.on('change', 'input.loftlader-checkbox', function(e){
			var $element = $(this).siblings('input');
			if ( $element.length && $element.attr( 'data-customize-setting-link' ) ) {
				api( $element.attr( 'data-customize-setting-link' ) )( $(this).is( ':checked' ) );
			}
		})
		.on('click', '.customize-more-toggle', function(e){
			e.preventDefault();
			var self = $(this),
				description = $(this).siblings('.customize-control-description');

			if(description.length){
				self.hasClass('expanded') ? description.slideUp('slow') : description.slideDown('slow', function(){ $(this).css('display', 'block'); });
				self.toggleClass('expanded');
			}
		})
		.on('click', '.loftloader-any-page-generate', function(e){
			e.preventDefault();
			var shortcode = api.loftloader_generate_parameters();
			$(this).siblings('.loftloader-any-page-shortcode').val('[loftloader ' + shortcode + ']').select();
		});
	});

	/**
	* Convert to string 'on' if current value is boolean true (not other value equals to true)
	* @param value mix, the value to check
	* @return mix, string 'on' if current value equals to boolean true, otherwise return the original value.
	*/
	function loftloader_check_boolean(value){
		return (value === true) ? 'on' : value;
	}
	/**
	* Get customize setting value
	* @param id string, the setting id
	* @return mix, return dirty value/setting value or false (the id not exists)
	**/
	function loftloader_get_setting_value(id){
		var settings = api.get(), //settings.settings, dirty_values = api.dirtyValues(),
			value = settings[id] ? settings[id] : false; //dirty_values[id] ? dirty_values[id] : (settings[id] ? settings[id]['value'] : false);
		if(typeof value === 'string'){
			value = value.trim();
		}
		return loftloader_check_boolean(value);
	}

	// Generate loftloader parameters
	api.loftloader_generate_parameters = function(){
		var dependency = {
				'loftloader_bg_color': {},
				'loftloader_bg_opacity': {},
				'loftloader_bg_animation': {},
				'loftloader_loader_type': {
					'sun': [
						'loftloader_loader_color'
					],
					'circles': [
						'loftloader_loader_color'
					],
					'wave': [
						'loftloader_loader_color'
					],
					'square': [
						'loftloader_loader_color'
					],
					'beating': [
						'loftloader_loader_color'
					],
					'frame': [
						'loftloader_loader_color',
						'loftloader_custom_img'
					],
					'imgloading': [
						'loftloader_custom_img',
						'loftloader_img_width'
					]
				},
				'loftloader_show_close_timer': {},
				'loftloader_show_close_tip': {},
				'loftloader_max_load_time': {},
				'loftloader_inline_js': {}
			};
		var loftloader = type_value = loop = '';
		if(loftloader_get_setting_value('loftloader_main_switch') === 'on'){
			loftloader = 'loftloader_main_switch=on loftloader_show_range=sitewide';
			for(var id in dependency){
				type_value = loftloader_get_setting_value(id);
				switch(id){
					case 'loftloader_show_close_tip':
						type_value = type_value ? ( '"' + btoa( unescape( encodeURIComponent( type_value ) ) ) + '"' ) : '""';
					case 'loftloader_bg_color':
					case 'loftloader_bg_opacity':
					case 'loftloader_bg_animation':
						loop = [];
						break;
					case 'loftloader_max_load_time':
						if ( ! type_value ) {
							type_value = 0;
						}
						break;
					default:
						loop = dependency[id][type_value] ? dependency[id][type_value] : [];
				}
				loftloader += ' ' + id + '=' + type_value;
				if(loop){
					for(var j in loop){
						loftloader += ' ' + loop[j] + '=' + loftloader_get_setting_value(loop[j]);
					}
				}
			}
		}
		else{
			loftloader = 'loftloader_main_switch=false';
		}
		return loftloader;
	}
})(wp.customize, jQuery);
