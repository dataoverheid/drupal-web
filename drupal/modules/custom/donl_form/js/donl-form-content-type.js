(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.donlFormContentType = {
    attach: function (context, settings) {

      // Add the advanced label to the advanced fields.
      $('.donl-form .main div.advanced .details-replacement').once('donl_form_add_advanced_labels').each(function () {
        $(this).find('.details-replacement-summary').append('<em> ' + Drupal.t("(advanced)") + '</en>');
      });

      // Create the sub steps in the sidebar & header.
      $('.donl-form .main .details-replacement').once('donl_form_add_substeps_to_sidebar').each(function (i) {
        const advanced = $(this).parents('.form-wrapper').hasClass('advanced');
        const label = $(this).find('.details-replacement-summary').html();

        const sub_step = '<a href="#' + i + '" class="form-sub-step form-sub-step-' + i + (advanced ? ' advanced' : '') + '" data-sub-step="' + i + '">' +
          '<div class="status"></div><div class="title">' + label + '</div></a>';

        $(this).attr('data-sub-step', i).addClass('sub-step');
        $('.donl-form .donl-form-sidebar .sub-steps').append(sub_step);
        $('.donl-form .donl-form-header .sub-steps').append(sub_step);
      });
      $('.donl-form .sub-steps .form-sub-step-0').addClass('active');

      // Move the action buttons.
      const actions = $('#edit-actions');
      actions.addClass('sidebar-nav-actions');
      $('.donl-form .donl-form-sidebar .sidebar-nav-actions').replaceWith(actions);

      // Specific actions for the dataservice form.
      $('#edit-dataservice-explanation-costs-wrapper').find('label').after('<small>Veplicht veld</small>');
      $('#edit-relation-dataservice-dataset-wrapper').find('h4.label').after('<small>Veplicht veld</small>');
    }
  };

}(jQuery, Drupal));
