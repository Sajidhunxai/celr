(function( $ ) {
	'use strict';

	var initVariationsButton = $('#init-variations');
	var variationsCount = parseInt( $('#init-variations-total').text() );

	var initVariationsUpdated = '#init-variations-updated';
	var initVariationsAlreadyUpdated = '#init-variations-already-updated';

	var initVariationsStatistic = $('#init-variations-statistic');
	initVariationsStatistic.hide();

	var updated = 0;
	var already_updated = 0;

	initVariationsButton.on('click', function(e) {
		e.preventDefault();

		initVariationsStatistic.fadeIn();

		if(!variationsCount) {
			alert('No Variations found / All initiated already');
		}

		var loops = Math.ceil(variationsCount / 50);
		
	    var p = $.Deferred().resolve().promise(); 

	    var i = 0;
	    for (var n = 0; n < loops; ++n) {
	        p = p.then( function() {        
	            return $.ajax( {
					type: "POST",
					url: woocommerce_single_variations_options.ajax_url, 
					dataType: "json",
					data: {
						'action' : 'init_single_variations',
						'loop' : i,
					}, 
					success: function( response ) {

						if ( ! response ) {
							alert('No response! Strange error, contact us.')
							return;
						}

						updated = parseInt( $(initVariationsUpdated).text() ) + response.updated;
						if(updated > variationsCount) {
							updated = variationsCount;
						}

						already_updated = parseInt( $(initVariationsAlreadyUpdated).text() ) + response.already_updated;
						if(already_updated > variationsCount) {
							already_updated = variationsCount;
						}

						$(initVariationsUpdated).text( updated);
						$(initVariationsAlreadyUpdated).text( already_updated );

						++i;
						console.log(i);
					}
				}).then( 
	                function() {

	                }, 
	                function() { 
	                    return $.Deferred().resolve();
	                } 
	            );
	        } );

	        p.then( function() {
	            
	        } );
	    }

	});

	var resetVariationsButton = $('#reset-variations');
	var variationsCount = parseInt( $('#reset-variations-total').text() );

	var resetVariationsUpdated = '#reset-variations-updated';
	var resetVariationsAlreadyUpdated = '#reset-variations-already-updated';

	var resetVariationsStatistic = $('#reset-variations-statistic');
	resetVariationsStatistic.hide();

	resetVariationsButton.on('click', function(e) {
		e.preventDefault();

		resetVariationsStatistic.fadeIn();

		if(!variationsCount) {
			alert('No Variations found / All reseted already');
		}

		var loops = Math.ceil(variationsCount / 50);
		
	    var p = $.Deferred().resolve().promise(); 

	    var i = 0;
	    for (var n = 0; n < loops; ++n) {
	        p = p.then( function() {        
	            return $.ajax( {
					type: "POST",
					url: woocommerce_single_variations_options.ajax_url, 
					dataType: "json",
					data: {
						'action' : 'reset_single_variations',
						'loop' : i,
					}, 
					success: function( response ) {

						if ( ! response ) {
							alert('No response! Strange error, contact us.')
							return;
						}

						updated = parseInt( $(resetVariationsUpdated).text() ) + response.updated;
						if(updated > variationsCount) {
							updated = variationsCount;
						}

						already_updated = parseInt( $(resetVariationsAlreadyUpdated).text() ) + response.already_updated;
						if(already_updated > variationsCount) {
							already_updated = variationsCount;
						}

						$(resetVariationsUpdated).text( updated);
						$(resetVariationsAlreadyUpdated).text( already_updated );

						++i;
						console.log(i);
					}
				}).then( 
	                function() {

	                }, 
	                function() { 
	                    return $.Deferred().resolve();
	                } 
	            );
	        } );

	        p.then( function() {
	            
	        } );
	    }

	});


})( jQuery );