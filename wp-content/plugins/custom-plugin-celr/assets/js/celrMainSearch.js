// window.addEventListener('click', outsideClickHandler);
jQuery(document).ready(function($) {
    var searchInput = $('#celr_search');
    var productDropdown = $('#celr_search_results');
    var searchResults = $('#celr_search_results');

    searchInput.on('input', function() {
        var searchTerm = searchInput.val().trim();
        productDropdown.show();

        if (searchTerm.length > 0) {
            var ajaxData = {
                action: 'celr_search_ajax',
                celr_search: searchTerm
            };

            $.ajax({
                url: celrSearchAjax.ajaxurl, // Access the AJAX URL through the localized object
                method: 'GET',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        searchResults.html(response.data);
                        // console.log(.searchTerm);
                        var viewMoreLink = '<a href="' + document.location.origin + '/celr/search-result/?celr_search=' + searchTerm + '">View More</a>';
                        searchResults.append(viewMoreLink);
                        productDropdown.show();
                    } else {
                        searchResults.html('No products found.');
                        productDropdown.show();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        } else {
            searchResults.empty();
            productDropdown.hide();
        }
    });
});

