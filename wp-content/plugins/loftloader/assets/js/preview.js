/**
* Copyright (c) Loft.Ocean
* http://www.loftocean.com
*/

(function(api, $, preview, parentAPI){
	if(typeof parentAPI.settings.settings.loftloader_main_switch !== 'undefined'){
		var $loaderWrapper = $( '#loftloader-wrapper' );
		// Helper functions
		/***** Update style element by id inside <head>, if not exist, create new *****/
		function loftloader_update_style(id, style){
			var $style = $('head').find('#' + id);
			$style = $style.length ? $style : $('<style>').attr('id', id).html('').appendTo($('head'));
			$style.html(style);
		}
		// Change loader background opacity instantly
		api('loftloader_bg_opacity', function( value ) {
			value.bind( function( to ) {
				loftloader_update_style('loftloader-lite-custom-bg-opacity', '#loftloader-wrapper .loader-section { opacity: ' + (to / 100) + '; }');
			});
		});
		// Change loader background color
		api('loftloader_bg_color', function(value){
			value.bind(function(to){
				loftloader_update_style('loftloader-lite-custom-bg-color', '#loftloader-wrapper .loader-section { background-color: ' + to + '; }');
			});
		});
		$('body').hover(function(e){
			$(this).addClass('loaded');
		}, function(e){
			$(this).removeClass('loaded');
		});
	}
	else if($('#loftloader-wrapper').length){
		$(window).load(function(){ $('body').addClass('loaded'); });
	}
})(wp.customize, jQuery, parent.document, parent.wp.customize);
