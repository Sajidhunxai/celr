;(function($) {
    /**
     * Store support tickets searching panel
     */
    var Dokan_search_store_support_tickets = {
        init : function () {
            // Support tickets customer names searching handler
            $('#dokan-search-support-customers').select2({
                ajax: {
                    url: dokan.ajaxurl,
                    dataType: 'json',
                    delay: 250, // wait 250 milliseconds before triggering the request
                    data: function ( params ) {
                        return {
                            search: params.term,
                            action: 'dokan_search_support_customers',
                            support_listing_nonce: $('#dokan-support-listing-search-nonce').val()
                        };
                    },
                    processResults: function( response ) {
                        if ( response.success ) {
                            var options = [];
                            if ( response.data ) {
                                $.each( response.data, function( index, text ) {
                                    options.push( { id: text.ID, text: text.display_name  } );
                                });
                            }
                            return {
                                results: options,
                            };
                        }
                    },
                    cache: true
                },

                language: {
                    errorLoading: function() {
                        return dokan.i18n_searching;
                    },
                    inputTooLong: function( args ) {
                        var overChars = args.input.length - args.maximum;

                        if ( 1 === overChars ) {
                            return dokan.i18n_input_too_long_1;
                        }

                        return dokan.i18n_input_too_long_n.replace( '%qty%', overChars );
                    },
                    inputTooShort: function( args ) {
                        var remainingChars = args.minimum - args.input.length;

                        if ( 1 === remainingChars ) {
                            return dokan.i18n_input_too_short_1;
                        }

                        return dokan.i18n_input_too_short_n.replace( '%qty%', remainingChars );
                    },
                    loadingMore: function() {
                        return dokan.i18n_load_more;
                    },
                    maximumSelected: function( args ) {
                        if ( args.maximum === 1 ) {
                            return dokan.i18n_selection_too_long_1;
                        }

                        return dokan.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
                    },
                    noResults: function() {
                        return dokan.i18n_no_matches;
                    },
                    searching: function() {
                        return dokan.i18n_searching;
                    }
                },
            });

            // Set date range data.
            let localeData = {
                format      : dokan_get_daterange_picker_format(),
                ...dokan_helper.locale
            };

            // Support tickets date range picker handler.
            $("#support_ticket_date_filter").daterangepicker({
                autoUpdateInput : false,
                locale          : localeData,
            }, function(start, end, label) {
                // Set the value for date range field to show frontend.
                $( '#support_ticket_date_filter' ).on( 'apply.daterangepicker', function( ev, picker ) {
                    $( this ).val( picker.startDate.format( localeData.format ) + ' - ' + picker.endDate.format( localeData.format ) );
                });

                // Set the value for date range fields to send backend
                $("#support_ticket_start_date_filter_alt").val(start.format('YYYY-MM-DD'));
                $("#support_ticket_end_date_filter_alt").val(end.format('YYYY-MM-DD'));
            });

            // Support tickets date range picker clear button handler
            $('#support_ticket_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                // Clear date range input fields value on clicking clear button
                $(this).val('');
                $("#support_ticket_start_date_filter_alt").val('');
                $("#support_ticket_end_date_filter_alt").val('');
            });
        }
    }

    // Let's invoke the init method
    Dokan_search_store_support_tickets.init();

})(jQuery);
