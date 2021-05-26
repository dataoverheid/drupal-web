(function ($, LocalFocus) {
  Drupal.behaviors.visualisation = {
    attach(context, settings) {
      let needsInit = true;

      function init() {
        if (needsInit) {
          jQuery(".localfocus-div").each(function () {
            LocalFocus.init(this, this.getAttribute("data-url"));
            needsInit = false;
          });
        }
      }

      $(document).on('click', '#tabs li[data-target="panel-visualization"], #tabs li[data-target="panel-visualisatie"]', function () {
        init();
      });

      if (jQuery(".localfocus-div:visible").length) {
        init();
      }
    },
  };
})(jQuery, LocalFocus);
