<?php

namespace Drupal\donl_relations;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\donl_relations\Entity\CorrespondingReferenceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The list builder for Corresponding Reference entities.
 */
class CorrespondingReferenceListBuilder extends ConfigEntityListBuilder {

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  private $themeManager;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entityType) {
    return new static(
      $entityType,
      $container->get('entity_type.manager')->getStorage($entityType->id()),
      $container->get('theme.manager')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Theme\ThemeManagerInterface
   *   The theme manager.
   */
  public function __construct(EntityTypeInterface $entityType, EntityStorageInterface $storage, ThemeManagerInterface $themeManager) {
    parent::__construct($entityType, $storage);
    $this->themeManager = $themeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'label' => $this->t('Label'),
      'id' => $this->t('Machine name'),
      'fields' => $this->t('Corresponding fields'),
      'enabled' => $this->t('Enabled'),
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\donl_relations\Entity\CorrespondingReferenceInterface $entity */

    $row = [
      'label' => $entity->label(),
      'id' => $entity->id(),
      'fields' => $this->getCorrespondingFields($entity),
      'enabled' => $entity->isEnabled() ? $this->t('Yes') : $this->t('No'),
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getCorrespondingFields(CorrespondingReferenceInterface $entity) {
    $items = [];
    foreach ($entity->getCorrespondingFields() as $field) {
      $items[] = $field;
    }

    return $this->themeManager->render('item_list', ['items' => $items]);
  }

}
