(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.result_page_pagination_filters = {
    attach: function (context, settings) {
      $('form.donl-search-pagination-filters-form select.pagination-filters-amount', context).each(function () {
        $(this).on('change', function () {
          $(this).closest('form').submit();
        });
      });
    }
  };

})(jQuery, Drupal);
