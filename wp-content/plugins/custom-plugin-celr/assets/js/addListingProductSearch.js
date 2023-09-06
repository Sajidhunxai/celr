jQuery(document).ready(function($) {
    var searchInput = $('#product_search');
    var productDropdown = $('#productDropdown');
    var searchResults = $('#product_search_results');

    searchInput.on('input', function() {
        var searchTerm = searchInput.val().trim();

        if (searchTerm.length > 0) {
            var ajaxData = {
                action: 'product_search_ajax',
                product_search: searchTerm
            };

            $.ajax({
                url: productSearchAjax.ajaxurl, // Access the AJAX URL through the localized object
                method: 'GET',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        searchResults.html(response.data);
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