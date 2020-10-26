(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.donl_search = {
    attach: function (context, settings) {

      $('select').chosen({'disable_search_threshold': 10});
    }
  };
}(jQuery, Drupal));
