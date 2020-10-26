(function ($, Drupal, window, document, undefined) {
  'use strict';

  Drupal.behaviors.go_plans = {
    attach: function (context, settings) {
      // Hide profile dropdown when clicking outside of it.
      $(document).on('click', document, function (e) {
        if ($(e.target).attr('aria-controls') !== 'profile-options-1') {
          $('#profile-options-1').attr('hidden', true);
        }
      });

      var hidden = true;
      $(document).on('click', '.theme-show-more', function () {
        $(this).toggleClass('link--down link--up').siblings().children('ul').attr('hidden', !hidden);
        if (hidden) {
          $(this).html(Drupal.t('Verberg subthema\'s'));
        } else {
          $(this).html(Drupal.t('Toon subthema\'s'));
        }
        hidden = !hidden;
      });

      $('table.table-js-fix').each(function () {
          var it = $(this);
          var heads = it.find('tr:first-child td');
          var headTitles = [];
          heads.each(function () {
            headTitles.push(this.innerHTML.replace(/(<([^>]+)>)/ig, ""));
          });
          it.find('td').each(function () {
            var it = $(this);
            it.attr('data-before', headTitles[it.index()]);
          });
          heads.parents('tr').hide();
        }
      );

      function stringToHash(string) {
        var hash = 0;
        for (var i = 0; i < string.length; i++) {
          hash = ((hash << 5) - hash) + string.charCodeAt(i);
          hash = hash & hash;
        }

        return hash;
      }

      //Custom tab functionality
      function setActive(selector) {
        const it = $(selector);
        it.addClass('active').siblings('li').removeClass('active');
        $('#' + it.attr('data-target')).show().siblings('.panel').hide();
        sessionStorage.setItem(stringToHash(window.location.href) + '-active-tab', it.attr('data-target'));
      }

      //set active when panel id is set in url.
      if (location.hash) {
        const id = location.hash.replace('#', '');
        setActive('li[data-target="panel-' + id + '"]');
      }
      //else check if a tab is in storage
      else {
        const store = sessionStorage.getItem(stringToHash(window.location.href) + '-active-tab');
        if (store) {
          setActive('li[data-target="' + store + '"]');
        }
      }
      $(document).on('click', '.tabs li', function () {
        setActive(this);
      });
      //End Custom tab functionality

      //Link copy
      var timeOut;
      $(document).on('click', '.permanent-link', function () {
        var $temp = $("<input>");
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

      //Facet toggle
      $(document).on('click', '.facet--label', function () {
        $(this).parent('.facet--group').toggleClass('active');
      });

      //Chosen on all chosen classes
      $('.chosen').chosen({'disable_search_threshold': 10});
    }
  }
})
(jQuery, Drupal, this, this.document);
