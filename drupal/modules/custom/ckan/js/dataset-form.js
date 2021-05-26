(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.datasetForm = {
    attach: function (context, settings) {

      const datePlanned = $('.js-date-planned').parents('.form__element').hide();
      const plannedChanger = $('.js-date-planned-changer');
      const accessRightsField = $('select[name="access_rights"]');
      const accessRightsReason = $('div.form-item-access-rights-reason');

      function checkPlanned() {
        if (['http://data.overheid.nl/status/gepland', 'http://data.overheid.nl/status/in_onderzoek'].indexOf(plannedChanger.val()) > -1) {
          datePlanned.slideDown();
        }
        else {
          datePlanned.slideUp();
        }
      }

      function toggleAccessRightsReason(value) {
        accessRightsReason.hide();
        accessRightsReason.find('select').removeClass('required');
        if (value === 'http://publications.europa.eu/resource/authority/access-right/NON_PUBLIC') {
          accessRightsReason.show();
          accessRightsReason.find('select').addClass('required');
        }
      }

      // Hide/show the 'Expected date available' field.
      checkPlanned();
      $(document).once('data planned shower').on('change', plannedChanger, checkPlanned);

      // The "Data publisher" field should be the same as the "Data owner" field.
      $(document).once('js-authority').on('change', '.js-authority', function () {
        $('.js-authority-target').val(this.value)
      });

      // Add toggle to the dynamic access_rights_reason field.
      accessRightsReason.find('label').after('<small>' + Drupal.t('Required field') + '</small>');
      toggleAccessRightsReason(accessRightsField.val());
      accessRightsField.change(function() {
        toggleAccessRightsReason(this.value);
      });

    }
  };

}(jQuery, Drupal));
