(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.donl_search = {
    attach: function (context, settings) {
      const resultContainer = $('.suggester-result-container'),
        typeSelect = $('#edit-type-select'),
        suggestInput = $('.donl-suggester-form .suggester-input'),
        doc = $(document);

      function processResult(data) {
        resultContainer.empty().append(data);
      }

      let req;

      function getUrl(url) {
        if (req) {
          req.abort();
        }
        req = $.get(url, function (data) {
          processResult(data);
        });
      }

      const suggest = function () {
        const type = typeSelect.val() || 'suggestions';
        const term = suggestInput.val();
        if (term.length === 0) {
          resultContainer.empty();
        } else if (term.length > 2) {
          let suffix = '';
          if (drupalSettings.donl_search.community_sys_name) {
            suffix = '?communitySysName=' + drupalSettings.donl_search.community_sys_name;
          }

          getUrl(drupalSettings.donl_search.suggestor_url + encodeURI(term) + '/' + type + suffix);
        }
      }

      // Events
      doc.on('keyup focus', suggestInput, suggest);
      doc.on('change', typeSelect, suggest);

      doc.mouseup(function (e) {
        if (!resultContainer.is(e.target) && resultContainer.has(e.target).length === 0) {
          resultContainer.empty();
        }
      });

      doc.on('click', '.search-replace', function () {
        jQuery('#edit-search').val($(this).html());
        suggest();
      });
    }
  };
}(jQuery, Drupal, drupalSettings));
