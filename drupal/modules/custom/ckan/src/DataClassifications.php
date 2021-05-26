<?php

declare(strict_types = 1);

namespace Drupal\ckan;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class DataClassifications
 *
 * @package Drupal\ckan
 */
class DataClassifications implements DataClassificationsInterface {

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $taxonomyTermStorage;

  /**
   * DataClassifications constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->taxonomyTermStorage = $entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public function getDataClassification(string $name): ?EntityInterface {
    $properties = [
      'name' => $name,
      'vid' => self::DATA_CLASSIFICATIONS_TAXONOMY_TREE,
    ];
    return array_values($this->taxonomyTermStorage->loadByProperties($properties))[0] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataClassifications(): array {
    return $this->taxonomyTermStorage->loadTree(self::DATA_CLASSIFICATIONS_TAXONOMY_TREE);
  }

  /**
   * {@inheritdoc}
   */
  public function getTooltipForm(EntityInterface $classification): string {
    if ($classification->hasField('field_tooltip_forms')) {
      return $classification->get('field_tooltip_forms')->getValue()[0]['value'] ?? '';
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getTooltipViews(EntityInterface $classification): string {
    if ($classification->hasField('field_tooltip_views')) {
      return $classification->get('field_tooltip_views')->getValue()[0]['value'] ?? '';
    }
    return '';
  }

}
