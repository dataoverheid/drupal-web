<?php

declare(strict_types = 1);

namespace Drupal\indicia_profile\Exception;

use Drupal;
use Drupal\Core\Installer\Exception\InstallerException;

/**
 * Exception thrown if settings.php cannot be written.
 */
class SettingsFileException extends InstallerException {

  /**
   * Constructs a SettingsException object.
   *
   * @param array $values
   *   List of values that should be added to the settings file manually.
   * @param bool $interactive
   *   Whether or not the exception will be displayed on an interactive
   *   interface.
   */
  public function __construct(array $values, bool $interactive = FALSE) {
    $title = $this->t('Access settings.php');
    $message = $this->t("Can't write to settings.php file, please add the following manually or allow the installation to write to the settings.php file: @list", [
      '@list' => $this->renderList($values),
    ]);
    parent::__construct($message, $title);
  }

  /**
   * Render an item list.
   *
   * @param array $values
   *   List items.
   * @param bool $interactive
   *   Whether or not the list will be displayed on an interactive interface.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   *   The rendered item list HTML.
   */
  protected function renderList(array $values, bool $interactive = FALSE) {
    if ($interactive) {
      /* @var \Drupal\Core\Render\RendererInterface $renderer */
      $renderer = Drupal::service('renderer');

      $element = [
        '#theme' => 'item_list',
        '#items' => $values,
      ];

      return $renderer->renderPlain($element);
    }

    // Format list to be displayed in a terminal.
    array_walk($values, static function ($value): string {
      return '- ' . $value;
    });

    return PHP_EOL . implode(PHP_EOL, $values);
  }

}
