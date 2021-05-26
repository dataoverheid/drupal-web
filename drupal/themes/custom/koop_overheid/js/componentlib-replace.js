(function ($, Drupal) {
  'use strict';

  $('html').addClass('has-js');

  function componentLibToggleElement(element, hideElements) {
    if (!element.get(0).hasAttribute('hidden')) {
      if (typeof hideElements !== 'undefined') {
        $(hideElements).attr('hidden', true);
      }
      element.attr('hidden', true);
    }
    else {
      if (typeof hideElements !== 'undefined') {
        $(hideElements).attr('hidden', true);
      }
      element.removeAttr('hidden');
    }
  }

  Drupal.behaviors.profile_toggle = {
    attach: function (context, settings) {

      $('[data-decorator="init-profile-toggle"]').each(function() {
        let profileOptions = $(this).find('.profile__options');
        $(this).find("[data-toggler]").append('<button type="button" aria-controls="' + profileOptions.attr('id') + '" aria-expanded="true" data-handler="toggle-profile-options">' + Drupal.t('Options') + '</button>');
        profileOptions.attr('hidden', true);
        $(this).click(function() {
          componentLibToggleElement(profileOptions, '.profile__options');
        })
      });

      // Hide profile dropdown when clicking outside of it.
      $(document).on('click', document, function (e) {
        if ($(e.target).data('handler') !== 'toggle-profile-options') {
          $('[data-decorator="init-profile-toggle"]').find('.profile__options').attr('hidden', true);
        }
      });

    }
  };

  Drupal.behaviors.toggle_other_sites = {
    attach: function (context, settings) {

      $('[data-decorator="init-toggle-other-sites"]').each(function () {
        let toggledElement = $($(this).attr('href'));
        $(this).attr('aria-controls', toggledElement.attr('id'));
        $(this).attr('aria-expanded', false);
        $(this).addClass('header__more--closed');
        toggledElement.attr('hidden', true);
        $(this).click(function(e) {
          e.preventDefault();
          componentLibToggleElement(toggledElement);
        });
      });

    }
  };

  Drupal.behaviors.toggle_explanation = {
    attach: function (context, settings) {

      $('[data-handler="toggle-explanation"]').each(function () {
        let toggledElement = $($(this).attr('href'));
        toggledElement.attr('hidden', true);
        $(this).click(function(e) {
          e.preventDefault();
          componentLibToggleElement(toggledElement, '.question-explanation__content');
        });
      });

      $('[data-handler="close-explanation"]').each(function () {
        $(this).click(function(e) {
          e.preventDefault();
          componentLibToggleElement($(this).parent('.question-explanation__content'));
        });
      });

    }
  }

}(jQuery, Drupal));
