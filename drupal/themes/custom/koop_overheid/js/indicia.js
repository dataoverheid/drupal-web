(function ($, Drupal, window, document) {
  'use strict';

  Drupal.behaviors.indicia = {
    attach: function (context, settings) {

      let hidden = true;
      $(document).on('click', '.theme-show-more', function () {
        $(this).toggleClass('link--down link--up').siblings().children('ul').attr('hidden', !hidden);
        if (hidden) {
          $(this).html(Drupal.t('Hide sub-themes'));
        }
        else {
          $(this).html(Drupal.t('Show sub-themes'));
        }
        hidden = !hidden;
      });

      $('table.table-js-fix').each(function () {
        let it = $(this);
        let heads = it.find('tr:first-child td');
        let headTitles = [];
        heads.each(function () {
          headTitles.push(this.innerHTML.replace(/(<([^>]+)>)/ig, ""));
        });
        it.find('td').each(function () {
          let it = $(this);
          it.attr('data-before', headTitles[it.index()]);
        });
        heads.parents('tr').hide();
      });

      function stringToHash(string) {
        let hash = 0;
        for (let i = 0; i < string.length; i++) {
          hash = ((hash << 5) - hash) + string.charCodeAt(i);
          hash = hash & hash;
        }

        return hash;
      }

      // Custom tab functionality.
      function setActive(selector) {
        const tab = $(selector);
        tab.addClass('active').siblings('li').removeClass('active');

        if (tab.attr('data-tab-type') === 'ajax') {
          $.get(tab.attr('data-ajax-callback'), function (data) {
            $('#' + tab.attr('data-target')).replaceWith(data);
            // Because we replaced the panel we'll have to preform the show()
            // on a separated equal selector again.
            $('#' + tab.attr('data-target')).show();
            tab.removeAttr('data-ajax-callback');
            tab.attr('data-tab-type', 'tab');
          });
        }

        $('#' + tab.attr('data-target')).show().siblings('.panel').hide();
        sessionStorage.setItem(stringToHash(window.location.href) + '-active-tab', tab.attr('data-target'));
      }

      // Set active when panel id is set in url.
      if (location.hash) {
        const id = location.hash.replace('#', '');
        setActive('li[data-target="panel-' + id + '"]');
      }
      // Else check if a tab is in storage
      else {
        const store = sessionStorage.getItem(stringToHash(window.location.href) + '-active-tab');
        if (store) {
          setActive('li[data-target="' + store + '"]');
        }
      }
      $(document).on('click', '.tabs li', function () {
        setActive(this);
      });
      // End Custom tab functionality.

      // Link copy.
      let timeOut;
      $(document).on('click', '.permanent-link', function () {
        let $temp = $("<input>");
        $('body').append($temp);
        $temp.val($('.permalink-copy').text()).select();
        document.execCommand('copy');
        $temp.remove();
        clearTimeout(timeOut);
        jQuery('.copied').fadeIn(500, function () {
          timeOut = setTimeout(function () {
            jQuery('.copied').fadeOut();
          }, 1000);
        });
      });

      // Facet toggle.
      $(document).on('click', '.facet--label', function () {
        $(this).attr('aria-pressed', function (i, attr) {
          return attr === 'true' ? 'false' : 'true'
        });
        $(this).parent('.facet--group').toggleClass('active');
      });
      $(document).on("keypress", ".facet--label", function (e) {
        if (e.which === 13 || e.which === 32) {
          e.preventDefault();
          $(this).click();
        }
      });

      // Select2 on all select2 classes.
      const selects = $('.select2');
      for (let i = 0; i <= selects.length; i++) {
        const it = $(selects[i]);
        it.select2({
          minimumResultsForSearch: 10,
          placeholder: it.attr('placeholder'),
          allowClear: it.data('allow-clear'),
          language: {
            noResults: function (params) {
              return Drupal.t('No results found.');
            }
          }
        });
      }

      // Open and close more information.
      $(document).once('js-more-informaton').on('click', '.js-more-information', function (e) {
        e.stopPropagation();
        const openItems = $('.more-information-content.open').slideUp();

        if (!$(this).siblings('.more-information-content').hasClass('open')) {
          $(this).siblings('.more-information-content').addClass('open').slideDown();
        }

        openItems.removeClass('open');
      });

      $(document).on('click', '.js-close', function () {
        $(this).parents('.more-information-content').removeClass('open').slideUp()
      });

      $(document).click('close-rest', function (e) {
        if ($(e.target).closest(".js-more-information").length === 0) {
          $('.more-information-content').removeClass('open').slideUp();
        }
      });

      // Details logic replacement
      $(document).on('click', '.details-replacement .details-replacement-summary', function () {
        var it = $(this);
        if (it.parent().attr('open') === 'open') {
          $(this).parent().removeAttr('open');
        }
        else {
          $(this).parent().attr('open', 'open');
        }
      });

      $(document).on('click', '.button--icon-hamburger', function () {
        $('.header__nav').stop().slideToggle().toggleClass('header__nav--closed');
      });
    }
  }

  Drupal.behaviors.indiciaContentStickyHeaderBlock = {
    attach: function (context, settings) {
      const $contentHeader = $('.donl-content-header-block');
      const $stickyHeader = $('.donl-content-sticky-header-block');

      if ($contentHeader.length && $stickyHeader.length) {
        $stickyHeader.find('.search-toggle').once('indiciaContentStickyHeaderBlock').on('click', function () {
          $stickyHeader.find('.search').toggleClass('active');
        });

        $(window).on('load scroll', function () {
          if ($(window).scrollTop() >= $contentHeader.offset().top + $contentHeader.outerHeight()) {
            $stickyHeader.addClass('active');
          }
          else {
            $stickyHeader.removeClass('active');
          }
        })
      }
    }
  }

})
(jQuery, Drupal, window, document);
