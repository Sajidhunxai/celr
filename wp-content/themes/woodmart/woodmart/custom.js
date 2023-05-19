jQuery(document).ready(function($) {
    $('#add-vendor').on('click', function(e) {
        e.preventDefault();
        var row = '<div class="vendor-row">' +
            '<input type="text" name="vendor_name[]" placeholder="Vendor Name" />' +
            '<input type="text" name="vendor_price[]" placeholder="Vendor Price" />' +
            '<input type="text" name="vendor_color[]" placeholder="Vendor Color" />' +
            '<button class="remove-vendor">Remove</button>' +
            '</div>';
        $('#vendor-repeater').append(row);
    });

    $(document).on('click', '.remove-vendor', function(e) {
        e.preventDefault();
        $(this).closest('.vendor-row').remove();
    });
});
