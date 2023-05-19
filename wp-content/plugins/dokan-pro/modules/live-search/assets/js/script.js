(function($){

    $(document).ready(function($){

        var xhr ,timeout;

        $('body').addClass('woocommerce');

        $('.ajaxsearchform').on('submit',function(e){
            e.preventDefault();
        });

        $('body').on( 'click', function(evt){
            if(!$(evt.target).is('div#dokan-ajax-search-suggestion-result li')) {
                $("#dokan-ajax-search-suggestion-result").html('');
            }
        });

        function get_div_id() {
            var div_id = dokanLiveSearch.themeTags[dokanLiveSearch.currentTheme];

            if ( div_id === undefined ) {
                return '#content';
            }

            return div_id;
        }

        function debounce_delay(callback, ms) {
            var timer   = 0;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                  callback.apply(context, args);
                }, ms || 0);
            };
        }

        $('body').on('keyup', '.dokan-ajax-search-textfield', debounce_delay( function(evt){
            evt.preventDefault();

            var self            = $(this);
            var nurl            = self.closest('form').attr('action');
            var textfield       = self.val();
            var selectfield     = self.closest('.ajaxsearchform').find('.dokan-ajax-search-category').val();
            var search_option   = self.closest('.ajaxsearchform').find('.dokan-live-search-option').val();

            var ordershort = $('.woocommerce-ordering .orderby').val();

            for_onkeyup_onchange(evt,self, nurl, textfield, selectfield, ordershort, search_option);

        } ,500 ) );

        $('body').on('change', '.dokan-ajax-search-category', function(e) {
            e.preventDefault();

            var self            = $(this);
            var nurl            = self.closest('form').attr('action');
            var textfield       = self.closest('.ajaxsearchform').find('.dokan-ajax-search-textfield').val();
            var search_option   = self.closest('.ajaxsearchform').find('.dokan-live-search-option').val();
            var selectfield     = self.val();
            var ordershort      = $('.woocommerce-ordering .orderby').val();

            for_onkeyup_onchange(e, self, nurl, textfield, selectfield, ordershort, search_option );
        });

        function for_onkeyup_onchange( evt, self, nurl, textfield, selectfield, ordershort, search_option ) {

            if ( ! ordershort ){
                ordershort = '';
            }

            if(selectfield == 'All' && evt.type == 'change' && ordershort == 'menu_order'){

                var url = nurl +'?s='+ textfield.replace(/\s/g,"+")+'&post_type=product';
                loading_get_request( url, textfield, selectfield, search_option );

            } else if(selectfield == 'All' && ordershort == 'menu_order') {

                var url = nurl +'?s='+ textfield.replace(/\s/g,"+")+'&post_type=product';
                loading_get_request( url, textfield, selectfield, search_option );

            } else if(selectfield == 'All' && ordershort != 'menu_order') {

                var url = nurl +'?s='+ textfield.replace(/\s/g,"+")+'&post_type=product&orderby='+ordershort;
                loading_get_request( url, textfield, selectfield, search_option );

            }else if(selectfield != 'All' && ordershort == 'menu_order'){

                var url = nurl +'?s='+ textfield.replace(/\s/g,"+")+'&post_type=product&product_cat='+ selectfield;
                loading_get_request( url, textfield, selectfield, search_option );

            } else {

                var url = nurl +'?s='+ textfield.replace(/\s/g,"+")+'&post_type=product&product_cat='+ selectfield + '&orderby=' + ordershort;
                loading_get_request( url, textfield, selectfield, search_option );

            }
        }

        function loading_get_request( url, textfield, selectfield, search_option ){

            if(search_option == 'default'){
                var div_id = get_div_id();

                $(div_id).append('<div id="loading"><img src="' + dokanLiveSearch.loading_img + '" atr="Loding..."/></div>');
                $(div_id).css({'opacity':0.3,'position':'relative'});
                $('#loading').show();

                clearTimeout(timeout);

                if(xhr) {
                xhr.abort();
                }

                timeout = setTimeout(function(){
                 xhr = get_ajax_request( url, textfield, selectfield );
                },150);
            } else {
                $('.ajaxsearchform-dokan .dokan-ajax-search-suggestion').addClass('dokan-ajax-search-loader');
                $('#dokan-ajax-search-suggestion-result').hide();
                $("#dokan-ajax-search-suggestion-result").html('');

                jQuery.ajax({
                    type : "post",
                    dataType : "json",
                    url : dokanLiveSearch.ajaxurl,
                    data: {
                        textfield: textfield,
                        selectfield: selectfield,
                        _wpnonce: dokanLiveSearch.dokan_search_nonce,
                        action: dokanLiveSearch.dokan_search_action
                    },
                    success: function(response) {
                        $('.ajaxsearchform-dokan .dokan-ajax-search-suggestion').removeClass('dokan-ajax-search-loader');
                        if ( response.type == 'success' ){
                            $("#dokan-ajax-search-suggestion-result").show('');
                            $("#dokan-ajax-search-suggestion-result").html('<ul>'+response.data_list+'</ul>');
                        }
                    }
                });

            }

        }

        function get_ajax_request( url, textfield, selectfield ) {

            xhr = $.get(url, function(resp, status) {

                var dom = $(resp).find(get_div_id()).html();

                $('.dokan-ajax-search-textfield').val( textfield );
                $('.dokan-ajax-search-category').val( selectfield );
                $(get_div_id()).html(dom);

                $('#loading').hide();
                $(get_div_id()).css({'opacity':1,'position':'auto'});

                $('.woocommerce-ordering').on('change','.orderby',function(e){
                    e.preventDefault();

                    var self = $(this);
                    var nurl = $('.ajaxsearchform').attr('action');
                    var textfield = $('.dokan-ajax-search-textfield').val();
                    var selectfield = $('.dokan-ajax-search-category').val();
                    var ordershort = self.val();

                    for_onkeyup_onchange(e, self, nurl, textfield, selectfield, ordershort );

                });

            });

            return xhr;
        }
    });

})(jQuery)
