(function ($, Drupal, Papa) {
  'use strict';
  Drupal.behaviors.preview = {
    attach: function (context, settings) {
      const messages = new Drupal.Message();

      function createColumns(fields) {
        let columns = [];
        fields.forEach(function (val) {
          let column = {title: val, data: val};
          columns.push(column);
        });
        return columns;
      }

      $(document).on('click', '.preview-action', function () {
        const it = this;
        const pHolder = $('#preview-' + it.getAttribute('data-preview-id'));
        pHolder.html('<div class="loader"></div>');
        it.remove();
        $.ajax({
          url: this.getAttribute('data-preview-url'),
          // TODO:: estimate a timeout for smaller files.
          timeout: 3000
        }).done(function (data) {
          // Parse the CSV based on the url.
          Papa.parse(data, {
            worker: true,
            header: true,
            skipEmptyLines: true,
            preview: 300,
            //When done, put it into datatables and generate a table.
            complete: function (csvJson) {
              pHolder.empty();
              pHolder.dataTable({
                data: csvJson.data,
                columns: createColumns(csvJson.meta.fields),
                autoWidth: true,
                sScrollX: true,
                deferRender: true,
                bLengthChange: false,
                pageLength: 15,
                language: {
                  emptyTable: Drupal.t("No data available in table"),
                  info: Drupal.t("Showing _START_ to _END_ of _TOTAL_ entries"),
                  infoEmpty: Drupal.t("Showing 0 to 0 of 0 entries"),
                  infoFiltered: Drupal.t("(filtered from _MAX_ total entries)"),
                  infoPostFix: Drupal.t(""),
                  decimal: ",",
                  thousands: ".",
                  loadingRecords: Drupal.t("Loading..."),
                  processing: Drupal.t("Processing..."),
                  search: Drupal.t("Search:"),
                  zeroRecords: Drupal.t("No matching records found."),
                  paginate: {
                    first: Drupal.t("First"),
                    last: Drupal.t("Last"),
                    next: Drupal.t("Next"),
                    previous: Drupal.t("Previous")
                  },
                  aria: {
                    sortAscending: Drupal.t("activate to sort column ascending"),
                    sortDescending: Drupal.t("activate to sort column descending"),
                  }
                },
              });

            }
          });
        }).fail(function () {
          pHolder.empty();
          pHolder.html('<div class="container"><p class="error">' + Drupal.t('We were unable to generate a preview for this file. Please try again later or contact an administrator.') + '</p></div>');
        });
      });
    }
  }
}(jQuery, Drupal, Papa));
