(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.donl_search_facets = {
    attach: function (context, settings) {
      const max = 30;

      function getMaxItem(element) {
        if (element.hasClass('.facet-data-eigenaar')) {
          return 999;
        }
        return max;
      }

      function updateText(element, shown, searching) {
        let text = '';

        if (shown >= max) {
          text = Drupal.t('Only the first @count filters are shown.', {'@count': shown});
        }
        else if (searching > 0) {
          if (shown > 0) {
            text = Drupal.formatPlural(shown, '1 filter matched your search.', '@count filters matched your search.', {'@count': shown});
          }
          else {
            text = Drupal.t('No results found.');
          }
        }
        element.html(text);
      }

      $('#facets').find('.facet--group').each(function () {
        const facet_list = $(this).find('ul.list--facet');
        const full_list = $(this).find('ul.list--facet > li');
        let max_items = getMaxItem(facet_list);
        if (full_list.length > max_items) {
          facet_list.before('<div class="facet--search-wrapper"><small></small><div class="facet--search"><input type="text" placeholder="' + Drupal.t('Search filters') + '"/><img class="icon-search" src="/themes/custom/koop_overheid/images/icon-search.svg" alt="" data-drupal-selector="edit-icon"></div></div>');
          full_list.hide();
          let count = max_items;
          if (full_list.length < max_items) {
            count = max_items;
          }
          updateText($(this).find('.facet--search-wrapper').find('small'), count, 0);
          $(this).find('ul.list--facet > li:nth-child(-n+' + count + ')').show();
        }
      });

      $('.facet--search-wrapper input').on('keyup', function(e) {
        const input = $(this);
        const facet_search = input.parent('.facet--search').parent('.facet--search-wrapper');
        const full_list = facet_search.parent('.facet--group').find('ul.list--facet > li');
        const matching_list = full_list.filter(function(i, li){
          return ~$(li).text().toUpperCase().indexOf(input.val().toUpperCase());
        });

        full_list.hide();
        const shown_list = matching_list.slice(0, getMaxItem(facet_search.parent('.facet--group').find('ul.list--facet')));
        shown_list.show();
        updateText(facet_search.find('small'), shown_list.length, input.val().length);
      });
    }
  };

})(jQuery, Drupal);
