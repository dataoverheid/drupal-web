(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.resourceOverview = {
    attach: function (context, settings) {

      window.onhashchange = function () {
        var show = location.hash.replace('#', '') || 0;
        $(".details-replacement").removeAttr('open');
        $("." + show).attr('open', 'open');
      };
      window.onhashchange();

    }
  };

}(jQuery, Drupal));

