<?php

namespace Drupal\donl\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_machine_name_text' widget.
 *
 * @FieldWidget(
 *   id = "field_machine_name_text",
 *   module = "donl",
 *   label = @Translation("Machine name"),
 *   field_types = {
 *     "field_machine_name"
 *   }
 * )
 */
class MachineNameWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = $items[$delta]->value ?? '';
    $element += [
      '#type' => 'textfield',
      '#default_value' => $value,
      '#element_validate' => [
        [static::class, 'validate'],
      ],
      '#disabled' => !empty($value),
    ];
    return ['value' => $element];
  }

  /**
   * Validate the color text field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if ($value != '' && !preg_match('/^[0-9a-z-_]*$/', $value)) {
      $form_state->setError($element, t('The field %field may contain only lowercase letters, numbers, underscores and hyphens.', ['%field' => $element['#title']]));
      return;
    }

    if ($element['#parents'][0] !== 'default_value_input' && ($nodeForm = $form_state->getBuildInfo()['callback_object']) && ($entity = $nodeForm->getEntity())) {
      $routeMatch = \Drupal::routeMatch();
      $entityType = $entity->getEntityTypeId();

      $query = \Drupal::entityQuery($entityType);
      $query->condition($element['#parents'][0], $value, '=');

      if ($entityType === 'node') {
        $query->condition('type', $entity->getType(), '=');
        if ($node = $routeMatch->getParameter('node')) {
          $query->condition('nid', $node->id(), '!=');
        }
      }
      elseif ($entityType === 'taxonomy_term') {
        $query->condition('vid', $entity->bundle(), '=');
        if ($term = $routeMatch->getParameter('taxonomy_term')) {
          $query->condition('tid', $term->id(), '!=');
        }
      }

      if (count($query->execute()) !== 0) {
        $form_state->setError($element, t('The field %field must be unique, but the given value %value is already in use.', ['%field' => $element['#title'], '%value' => $value]));
      }
    }
  }

}
