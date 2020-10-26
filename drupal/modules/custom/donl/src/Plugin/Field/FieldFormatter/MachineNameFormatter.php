<?php

namespace Drupal\donl\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_machine_name_text' formatter.
 *
 * @FieldFormatter(
 *   id = "field_machine_name_text",
 *   label = @Translation("Machine name"),
 *   field_types = {
 *     "field_machine_name"
 *   }
 * )
 */
class MachineNameFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Machine name.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = ['#markup' => $item->value];
    }

    return $element;
  }

}
