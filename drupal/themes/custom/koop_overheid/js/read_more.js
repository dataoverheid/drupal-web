/**
 * Creates functionality to toggle text when it's over the defined limit.
 */
(function () {

  // Default config.
  var TEXT_LIMIT = 250,
    READ_MORE_TEXT = 'Lees meer',
    READ_LESS_TEXT = 'Lees minder',
    TRUNCATE_TEXT = '...';

  // Initialising read more buttons.
  var elements = document.querySelectorAll('[data-decorator="read-more-text"]');
  if (elements.length) {
    elements.forEach(function (element) {
      // Overwrite default config if the element has defined them.
      var textLimit = TEXT_LIMIT;
      var readMoreText = READ_MORE_TEXT;
      var readLessText = READ_LESS_TEXT;
      var truncateText = TRUNCATE_TEXT;

      if (element.dataset) {
        if (element.dataset.textLimit) {
          textLimit = element.dataset.textLimit;
        }

        if (element.dataset.readMoreText) {
          readMoreText = element.dataset.readMoreText;
        }

        if (element.dataset.readLessText) {
          readLessText = element.dataset.readLessText;
        }

        if (element.dataset.truncateText) {
          truncateText = element.dataset.truncateText;
        }
      }

      // Add the read more functionality if the element's text passed the text limit.
      if (element.textContent.length > textLimit) {
        var text = element.textContent;

        // Creates the toggle button.
        var toggle = document.createElement('span');
        toggle.classList.add('read-more', 'link', 'link--up');
        toggle.innerHTML = readLessText;

        // Toggle functionality.
        toggle.addEventListener('click', function (e) {
          var textLength = element.textContent.length - toggle.innerHTML.length - truncateText.length;

          if (textLength > textLimit) {
            element.textContent = element.textContent.slice(0, textLimit) + truncateText;
            toggle.innerHTML = readMoreText;
            toggle.classList.remove('link--up');
            toggle.classList.add('link--down');
            element.appendChild(toggle);
          }
          else {
            element.textContent = text;
            toggle.innerHTML = readLessText;
            toggle.classList.remove('link--down');
            toggle.classList.add('link--up');
            element.appendChild(toggle);
          }
        });

        // Appends the toggle button to the element.
        element.appendChild(toggle);

        // Toggle text on load.
        toggle.click();
      }
    });
  }

})();
