(function( $ ) {
	'use strict';

	// Create the defaults once
	var pluginName = "WooCommerceSingleVariations",
		defaults = {
			'modalHeightAuto' : '1',
		};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this.trans = this.settings.trans;
		this._name = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend( Plugin.prototype, {
		init: function() {
			this.window = $(window);
			this.documentHeight = $( document ).height();
			this.windowHeight = this.window.height();
			this.product = {};
			this.elements = {};


			this.getVariationTitle();
		},
		getVariationTitle: function() {

			var that = this;

			if($('.variations_form').length < 1) {
				return;
			}

			if(!$('body').hasClass('single-product')) {
				return;
			}

			$('.variations_form').on('found_variation', function(e, variation) {
				
				if(!variation) {
					return;
				}

				var $this = $('input[name="variation_id"]');
				var variation_id = variation.variation_id;

				if(variation_id == "") {
					return;
				}
				jQuery.ajax({
					url: that.settings.ajax_url,
					type: 'post',
					async: true,
					dataType: 'JSON',
					data: {
						action: 'woocommerce_get_variation_title',
						variation_id: variation_id,
					},
					success : function( response ) {

						if(!response.status) {
							return;
						}
						$(that.settings.titleSelector).html(response.title);

					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
					}
				});
			});
		},
	} );

	// Constructor wrapper
	$.fn[ pluginName ] = function( options ) {
		return this.each( function() {
			if ( !$.data( this, "plugin_" + pluginName ) ) {
				$.data( this, "plugin_" +
					pluginName, new Plugin( this, options ) );
			}
		} );
	};

	$(document).ready(function() {

		$( "body" ).WooCommerceSingleVariations( 
			woocommerce_single_variations_options
		);

	} );

})( jQuery );