'use strict';

(function($) {
  // search product
  $(document).on('change', '.woosv-product-search', function() {
    var _val = $(this).val();

    if (Array.isArray(_val)) {
      $(this).
          closest('td').
          find('input[name="woosv_hide_parent_exclude"]').
          val(_val.join()).trigger('change');
    } else {
      if (_val === null) {
        $(this).
            closest('td').
            find('input[name="woosv_hide_parent_exclude"]').
            val('').trigger('change');
      } else {
        $(this).
            closest('td').
            find('input[name="woosv_hide_parent_exclude"]').
            val(String(_val)).trigger('change');
      }
    }
  });
})(jQuery);