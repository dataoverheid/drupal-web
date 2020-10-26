<?php

namespace Drupal\ckan\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_dataset_text' widget.
 *
 * @FieldWidget(
 *   id = "field_dataset_text",
 *   module = "ckan",
 *   label = @Translation("Dataset"),
 *   field_types = {
 *     "field_dataset"
 *   }
 * )
 */
class DatasetWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = $items[$delta]->value ?? '';
    $element += [
      '#type' => 'textfield',
      '#autocomplete_route_name' => 'donl_search.autocomplete',
      '#autocomplete_route_parameters' => ['type' => 'dataset'],
      '#default_value' => $value,
      '#maxlength' => 255,
      '#element_validate' => [
        [static::class, 'validate'],
      ],
    ];
    return ['value' => $element];
  }

  /**
   * Validate the dataset field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if ($value != '') {
      // @todo create validation check so only valid dataset uri's are entered.
    }
  }

}
