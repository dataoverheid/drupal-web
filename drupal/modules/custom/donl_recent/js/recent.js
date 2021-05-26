(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.recent = {
    attach: function (context, settings) {
      const $grid = $('.recent-items'), $loadMore = $('.load-more'),
        $loader = $('.loader').remove();
      const category = drupalSettings.recent.category || 'all';
      let xhr;

      function loadItems(start, end) {
        $loadMore.remove();
        $loader.insertAfter($grid);
        let url = "/rest/actueel/" + category;
        url += '/' + start + '/' + end;
        xhr = $.ajax({
          type: "GET",
          url: url,
        }).done(function (data) {
          if (data) {
            if (start === 0) {
              $grid.append(data).masonry({
                itemSelector: '.masonry-item',
                percentPosition: true,
              });
            } else {
              const $items = jQuery(data);
              $grid.append($items).masonry('appended', $items)
            }
            $loadMore.insertAfter($grid);
          }
          checkForLoadMore();
          $loader.remove();
        });
      }

      let start = 0, end = 10;

      function loadMore(param = 'all') {
        loadItems(start, end, param);
        start = start + 5;
        end = end + 5;
      }

      const checkForLoadMore = function () {
        const trigger = $('.load-more'), w = $(window);
        if (trigger.length) {
          const eT = trigger.offset().top - 300,
            eB = eT + trigger.outerHeight(), vT = w.scrollTop(),
            vB = vT + w.height();

          if (eB > vT && eT < vB) {
            loadMore();
          }
        }
      }

      loadMore();

      $(window).scroll(checkForLoadMore);

      $(document).on('click', '.load-more', loadMore);

      $(document).on('change', '.recent-filter', function () {
        let baseUrl = new URL(window.location.protocol + this.getAttribute('data-baseurl'));
        if (this.value) {
          baseUrl += '/' + this.value
        }
        location.href = baseUrl;
      });
    }
  }
}(jQuery, Drupal, drupalSettings));
