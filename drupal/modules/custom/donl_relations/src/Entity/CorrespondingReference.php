<?php

namespace Drupal\donl_relations\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Defines a corresponding reference entity.
 *
 * @ConfigEntityType(
 *   id = "corresponding_reference",
 *   label = @Translation("Corresponding reference"),
 *   handlers = {
 *     "list_builder" = "Drupal\donl_relations\CorrespondingReferenceListBuilder",
 *     "storage" = "Drupal\donl_relations\CorrespondingReferenceStorage",
 *     "form" = {
 *       "add" = "Drupal\donl_relations\Form\CorrespondingReferenceForm",
 *       "edit" = "Drupal\donl_relations\Form\CorrespondingReferenceForm",
 *       "delete" = "Drupal\donl_relations\Form\CorrespondingReferenceDeleteForm",
 *       "sync" = "Drupal\donl_relations\Form\CorrespondingReferenceSyncForm",
 *     }
 *   },
 *   config_prefix = "corresponding_reference",
 *   admin_permission = "administer donl_relations",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "enabled",
 *     "first_field",
 *     "second_field",
 *     "bundles"
 *   },
 *   links = {
 *     "collection" = "/admin/config/content/donl_relations",
 *     "edit-form" = "/admin/config/content/donl_relations/{corresponding_reference}",
 *     "delete-form" = "/admin/config/content/donl_relations/{corresponding_reference}/delete",
 *     "sync-form" = "/admin/config/content/donl_relations/{corresponding_reference}/sync"
 *   }
 * )
 */
class CorrespondingReference extends ConfigEntityBase implements CorrespondingReferenceInterface {

  public const ADD = 'add';
  public const REMOVE = 'remove';

  /**
   * The corresponding reference machine name.
   *
   * @var string
   */
  public $id;

  /**
   * The corresponding reference label.
   *
   * @var string
   */
  public $label;

  /**
   * The first corresponding field ID.
   *
   * @var string
   */
  public $first_field;

  /**
   * The second corresponding field ID.
   *
   * @var string
   */
  public $second_field;

  /**
   * The corresponding bundles keyed by entity type.
   *
   * Example:
   *   [
   *     'community' => 'community',
   *     'group' => 'group',
   *   ]
   *
   * @var array
   */
  public $bundles;

  /**
   * Whether or not this corresponding reference is enabled.
   *
   * @var bool
   */
  public $enabled;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstField() {
    return $this->first_field;
  }

  /**
   * {@inheritdoc}
   */
  public function setFirstField($firstField) {
    $this->first_field = $firstField;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSecondField() {
    return $this->second_field;
  }

  /**
   * {@inheritdoc}
   */
  public function setSecondField($secondFIeld) {
    $this->second_field = $secondFIeld;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundles() {
    return $this->bundles;
  }

  /**
   * {@inheritdoc}
   */
  public function setBundles(array $bundles) {
    $this->bundles = $bundles;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return $this->enabled;
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->enabled = $enabled;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCorrespondingFields() {
    $first = $this->getFirstField();
    $second = $this->getSecondField();

    $correspondingFields = [];

    if (!empty($first)) {
      $correspondingFields[$first] = $first;
    }

    if (!empty($second)) {
      $correspondingFields[$second] = $second;
    }

    return $correspondingFields;
  }

  /**
   * {@inheritdoc}
   */
  public function hasCorrespondingFields(FieldableEntityInterface $entity) {
    foreach ($this->getCorrespondingFields() as $field) {
      if ($entity->hasField($field)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function synchronizeCorrespondingFields(FieldableEntityInterface $entity) {
    if (!$this->isValid($entity)) {
      return;
    }

    foreach ($this->getCorrespondingFields() as $fieldName) {
      if (!$entity->hasField($fieldName)) {
        continue;
      }

      $differences = $this->calculateDifferences($entity, $fieldName);
      $correspondingField = $this->getCorrespondingField($fieldName);

      foreach ($differences as $operation => $entities) {
        /** @var \Drupal\Core\Entity\FieldableEntityInterface $correspondingEntity */
        foreach ($entities as $correspondingEntity) {
          if ($correspondingEntity) {
            $this->synchronizeCorrespondingField($entity, $correspondingEntity, $correspondingField, $operation);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isValid(FieldableEntityInterface $entity) {
    if (!in_array($entity->bundle(), $this->getBundles())) {
      return FALSE;
    }

    if (!$this->hasCorrespondingFields($entity)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCorrespondingField($fieldName) {
    $fields = $this->getCorrespondingFields();

    if (count($fields) === 1) {
      return $fieldName;
    }

    unset($fields[$fieldName]);

    return array_shift($fields);
  }

  /**
   * {@inheritdoc}
   */
  public function synchronizeCorrespondingField(FieldableEntityInterface $entity, FieldableEntityInterface $correspondingEntity, $correspondingFieldName, $operation = NULL) {
    if ($operation === NULL) {
      $operation = self::ADD;
    }

    if (!$correspondingEntity->hasField($correspondingFieldName)) {
      return;
    }

    $field = $correspondingEntity->get($correspondingFieldName);
    $values = $field->getValue();
    $index = NULL;

    foreach ($values as $idx => $value) {
      if ($value['target_id'] == $entity->id()) {
        if ($operation === self::ADD) {
          return;
        }

        $index = $idx;
      }
    }

    $set = FALSE;

    switch ($operation) {
      case self::REMOVE:
        if ($index !== NULL) {
          unset($values[$index]);
          $set = TRUE;
        }
        break;

      case self::ADD:
        $values[] = ['target_id' => $entity->id()];
        $set = TRUE;
        break;
    }

    if ($set) {
      $field->setValue($values);
      $correspondingEntity->save();
    }
  }

  /**
   * Return added and removed entities from the provided field.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The current entity.
   * @param string $fieldName
   *   The field name to check.
   *
   * @return array
   *   The differences keyed by 'added' and 'removed'.
   */
  protected function calculateDifferences(FieldableEntityInterface $entity, $fieldName) {
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $original */
    $original = $entity->original ?? NULL;

    $differences = [
      self::ADD => [],
      self::REMOVE => [],
    ];

    if (!$entity->hasField($fieldName)) {
      return $differences;
    }

    $entityField = $entity->get($fieldName);

    if ($original === NULL) {
      /** @var \Drupal\Core\Field\FieldItemInterface $fieldItem */
      foreach ($entityField as $fieldItem) {
        $differences[self::ADD][] = $fieldItem->entity;
      }

      return $differences;
    }

    $originalField = $original->get($fieldName);

    foreach ($entityField as $fieldItem) {
      if (!$this->entityHasValue($original, $fieldName, $fieldItem->target_id)) {
        $differences[self::ADD][] = $fieldItem->entity;
      }
    }

    foreach ($originalField as $fieldItem) {
      if (!$this->entityHasValue($entity, $fieldName, $fieldItem->target_id)) {
        $differences[self::REMOVE][] = $fieldItem->entity;
      }
    }

    return $differences;
  }

  /**
   * Checks if the given entity has the provided corresponding value.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to check.
   * @param string $fieldName
   *   The field name on the entity to check.
   * @param mixed $id
   *   The corresponding ID to check.
   *
   * @return bool
   *   TRUE if value already exists, FALSE otherwise.
   */
  protected function entityHasValue(FieldableEntityInterface $entity, $fieldName, $id) {
    if (!$entity->hasField($fieldName)) {
      return FALSE;
    }

    foreach ($entity->get($fieldName) as $fieldItem) {
      if ($fieldItem->target_id == $id) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
