(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.donlForm = {
    attach: function (context, settings) {

      // Trigger a change on the textarea when typing in the markdown editor.
      $(document).on('keyup', '.CodeMirror', function() {
        const codeMirror = $(this)[0].CodeMirror;
        const $textarea = $(this).siblings('.form-textarea');

        if (codeMirror && $textarea.length) {
          $textarea.val(codeMirror.getValue()).trigger('change');
        }
      });

      const formItems = '.form-url, .form-text, .form-select, .form-textarea, .form-email, .form-number';

      function checkField(field) {
        const $field = $(field);
        const fieldIsNotDefault = (field.value !== $field.data('default-value'));

        if (field.value !== '' && fieldIsNotDefault && !($field.hasClass('form-select') && field.value === '_none')) {
          return 'filled'
        }
        else if (($field.hasClass('required') || $field.prop('required')) && fieldIsNotDefault) {
          return 'missing';
        }
        // Process grouped fields. ( Contact point ).
        else if ($field.hasClass('grouped-required')) {
          const fields = $('.input[data-group="' + $field.data('group') + '"]').toArray();
          for (let i = 0; i <= fields.length; i++) {
            if (fields[i] && fields[i].value !== '') {
              return 'filled'
            }
          }
          return 'missing'
        }
        return 'empty';
      }

      function checkStarted(subStep, fieldStatus) {
        let activate = false;
        if (fieldStatus.indexOf('filled') > -1) {
          activate = true;
        }
        changeStatus(activate, 'started', subStep);
        return activate;
      }

      function checkCompleted(subStep, fieldStatus) {
        let activate = false;
        subStep.children('.submit-overlay-wrapper').remove();
        if (fieldStatus.indexOf('missing') < 0) {
          activate = true;
          cloneButton(subStep);
        }
        changeStatus(activate, 'completed', subStep);
        return activate;
      }

      function addNextStepButton(subStep) {
        let fieldStatus = [];
        $($(subStep).find(formItems)).each(function () {
          fieldStatus.push(checkField(this));
        });
        subStep.children('.submit-overlay-wrapper').remove();
        if (fieldStatus.indexOf('missing') < 0) {
          cloneButton(subStep);
        }
      }

      function cloneButton(subStep) {
        if (!$('.donl-form').hasClass('donl-form-edit')) {
          $('.donl-form-sidebar .submit-overlay-wrapper').clone().appendTo(subStep);
        }
      }

      function changeStatus(activate, status, subStep) {
        let id = subStep.data('sub-step');
        if (activate) {
          subStep.addClass(status);
          $('.form-sub-step-' + id).addClass(status);
        }
        else {
          subStep.removeClass(status);
          $('.form-sub-step-' + id).removeClass(status);
        }
      }

      function checkStep(subStep) {
        subStep = $(subStep);
        const elements = $(subStep.find(formItems));
        let fieldStatus = [];
        elements.each(function () {
          fieldStatus.push(checkField(this));
        });

        checkStarted(subStep, fieldStatus);
        checkCompleted(subStep, fieldStatus);

        const completedCount = $('.sub-step.completed').length;
        const submitOverlay = $('.submit-overlay');

        if (submitOverlay.length) {
          submitOverlay.each(function (i, submit) {
            const $submit = $(submit);
            if (completedCount === getRequiredCount()) {
              if ($submit.is('input')) {
                $submit.val($submit.data('next-form-text'));
              }
              else {
                $submit.html($submit.data('next-form-text'));
              }
            }
            else {
              if ($submit.is('input')) {
                $submit.val($submit.data('in-form-text'));
              }
              else {
                $submit.html($submit.data('in-form-text'));
              }
            }
          });
        }

        const $datasourcesButton = $('.button--datasources');
        if ($datasourcesButton.length) {
          const inFormHidden = typeof $datasourcesButton.attr('data-in-form-hidden') !== 'undefined';
          if (completedCount === getRequiredCount()) {
            if (inFormHidden) {
              $datasourcesButton.removeClass('hidden');
            }
          }
          else {
            if (inFormHidden) {
              $datasourcesButton.addClass('hidden');
            }
          }
        }
      }

      function getStepId() {
        return location.hash.replace('#', '') || 0;
      }

      function setStep(id) {
        window.location.hash = id;
      }

      function switchStep(id, scrollTo) {
        checkStep($('.sub-step[open="open"]')[0]);
        // Set active in side bar.
        $('.sub-steps .form-sub-step').removeClass('active');
        $('.sub-steps .form-sub-step-' + id).addClass('active');
        // Open the step.
        $(".sub-step:not([data-sub-step='" + id + "'])").removeAttr('open');
        let opendSubStep = $(".sub-step[data-sub-step='" + id + "']");
        opendSubStep.attr('open', 'open');
        addNextStepButton(opendSubStep);
        if (scrollTo) {
          $('html, body').animate({
            scrollTop: $(".sub-step[data-sub-step='" + id + "']").offset().top - 90
          }, 300);
          // There must be a better way, but this resize makes the markdown
          // editor correctly show up within sub-steps that where otherwise
          // closed during the original page load.
          setTimeout(function () {
            $(window).trigger('resize');
          }, 300);
        }
      }

      function advancedActive() {
        return $('#full-form-wrapper').hasClass('advanced');
      }

      function getRequiredCount() {
        if (advancedActive()) {
          return $('.donl-form .donl-form-sidebar .sub-steps .form-sub-step').length;
        }
        return $('.donl-form .donl-form-sidebar .sub-steps .form-sub-step:not(.advanced)').length;
      }

      function nextStep() {
        let isValid = false;
        let next = getStepId();

        while (!isValid) {
          next++;

          if (next >= getRequiredCount()) {
            next = 0;
          }

          if (!$('.form-sub-step-' + next).hasClass('completed')) {
            isValid = true;
          }

          // Failsafe.
          if (next > 50) {
            console.log('ERROR: unending loop detected. ending loop.');
            isValid = true;
          }
        }

        setStep(next);
      }

      function changeAdvancedCheckbox(it, value, userAction) {
        if (userAction) {
          $('.advance-swap .selects').hide();
          $('.donl-form .new-loader').show();
        }
        $('.advance-swap .option').removeClass('checked');
        $(it).addClass('checked');
        $('input[value="' + value + '"].js-edit-advance').click();
      }

      $(document).once('check step').on('change', formItems, function () {
        checkStep($(this).parents('.sub-step'));
      });

      $('.basic .sub-step').each(function () {
        checkStep(this);
      });
      if ($('.donl-form').hasClass('donl-form-edit') && advancedActive()) {
        $('.advanced .sub-step').each(function () {
          checkStep(this);
        });
      }

      // Set the correct step ( from hash )
      switchStep(getStepId(), false);

      // Check if step is in advanced mode. If so, switch to it.
      $(document).ready(function () {
        if (!advancedActive() && getStepId() > getRequiredCount()) {
          $('.js-edit-advance').click();
        }
      });

      window.onhashchange = function () {
        switchStep(getStepId(), true);
      };

      $(document).once('detail summary').on('click', '.details-replacement-summary', function () {
        setStep($(this).parent().data('sub-step'));
      });

      // Go to the next step, if completed go to the next step
      $(document).once('submit-overlay').on('click', '.submit-overlay', function (e) {
        if ($('.sub-step.completed').length === getRequiredCount()) {
          const submitButton = $('#donl_form_general_form_alter_submit');
          if (submitButton.length) {
            submitButton.click();
          }
          else {
            $('.js-form-submit').click();
          }
        }
        else {
          nextStep();
        }
      });

      if (advancedActive()) {
        changeAdvancedCheckbox($('.advance-swap .option.advanced'), 1, false)
      }
      else {
        changeAdvancedCheckbox($('.advance-swap .option.basic'), 0, false)
      }

      $(document).once('advanced swap advanced').on('click', '.advance-swap .option.advanced:not(.checked)', function () {
        changeAdvancedCheckbox(this, 1, true)
      });

      $(document).once('advanced swap basic').on('click', '.advance-swap .option.basic:not(.checked)', function () {
        // Switch back to step 1 to avoid issues.
        setStep(0);
        changeAdvancedCheckbox(this, 0, true)
      });

      (function ($) {
        $.fn.updateHeader = function () {
          if (this[0].nodeName === 'SELECT') {
            if (this.val()) {
              $('#' + this.data('header-target')).html($('option[value="' + this.val() + '"]').html());
            }
          }
          else {
            $('#' + this.data('header-target')).html(this.val());
          }
          return this;
        };
      })(jQuery);

      // Auto fill logic.
      $('.donl-form-header table tr.dynamic').each(function () {
        const header_field = $(this).find('td');
        const field = $('[name="' + header_field.data('field') + '"]');

        field.addClass('js-header-changer');
        field.attr('data-header-target', header_field.attr('id'));
        field.updateHeader();
      });

      $(document).once('js-header-changer').on('change keyup', '.js-header-changer', function () {
        $(this).updateHeader();
      });

    }
  };

}(jQuery, Drupal));
