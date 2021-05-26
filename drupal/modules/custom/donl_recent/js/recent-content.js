(function ($, Drupal) {
  'use strict';

  $.fn.ajaxCategoryFilterCallback = function (argument) {
    var $items = $('#recent-content-container .recent-content .masonry-item');
    $items.addClass('transition-reload');

    // Animation on ajax success.
    $items.each(function (index, item) {
      setTimeout(function () {
        $(item).addClass('transition-enter');
      }, index * 50);
    });
  };

  Drupal.behaviors.recentContent = {
    attach: function (context, settings) {
      var $filters = $('.recent-content-header .filters');
      var $categoryFilter = $filters.find($('.category-filter'));
      var $items = $('#recent-content-container .recent-content .masonry-item');
      $('#recent-content-container .recent-content').masonry();

      // Add loader.
      $categoryFilter.once('recentContent').change(function () {
        $('#recent-content-container').append($('<span>').addClass('indicia-loader'));
      });

      // Animation on ajax start.
      $categoryFilter.change(function () {
        $items.removeClass('transition-reload transition-enter').addClass('transition-leave').fadeTo(100, 0);
      });
    }
  }

}(jQuery, Drupal));
