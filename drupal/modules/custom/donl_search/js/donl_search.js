(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.donl_search = {
    attach: function (context, settings) {
      const resultContainer = $('.suggester-result-container');
      const typeSelect = $('#edit-type-select');
      const doc = $(document);

      function processResult(data) {
        resultContainer.empty().append(data);
      }

      let req;

      function getUrl(url) {
        // Abort a running ajax request.
        if (req) {
          req.abort();
        }
        req = $.get(url, function (data) {
          processResult(data);
        });
      }

      doc.on('keyup focus', '.donl-search-form .suggester-input', function () {
        if (typeSelect.val() === 'dataset') {
          const term = $(this).val();
          if (term.length === 0) {
            resultContainer.empty();
          } else if (term.length > 2) {
            getUrl('/suggest/' + encodeURI(term));
          }
        }
      });

      doc.mouseup(function (e) {
        if (!resultContainer.is(e.target) && resultContainer.has(e.target).length === 0) {
          resultContainer.empty();
        }
      });

      $('.chosen').chosen({'disable_search_threshold': 10});
    }
  };
}(jQuery, Drupal));
