<?php

namespace Drupal\ckan\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_dataset' field type.
 *
 * @FieldType(
 *   id = "field_dataset",
 *   label = @Translation("Dataset"),
 *   module = "ckan",
 *   description = @Translation("Relation to an existing dataset"),
 *   default_widget = "field_dataset_text",
 *   default_formatter = "field_dataset_text",
 * )
 */
class DatasetItem extends FieldItemBase {

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
    $properties['value'] = DataDefinition::create('string')->setLabel(t('Dataset'));

    return $properties;
  }

}
