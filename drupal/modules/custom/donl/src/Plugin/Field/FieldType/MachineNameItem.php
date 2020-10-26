<?php

namespace Drupal\donl\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_machine_name' field type.
 *
 * @FieldType(
 *   id = "field_machine_name",
 *   label = @Translation("Machine name"),
 *   module = "donl",
 *   description = @Translation("An url safe name to generate an URI."),
 *   default_widget = "field_machine_name_text",
 *   default_formatter = "field_machine_name_text",
 * )
 */
class MachineNameItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'small',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Machine name'));

    return $properties;
  }

}
