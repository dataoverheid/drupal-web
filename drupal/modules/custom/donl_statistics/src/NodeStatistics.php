<?php

namespace Drupal\donl_statistics;

use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 *
 */
class NodeStatistics implements NodeStatisticsInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   *
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicationCount(): int {
    $query = $this->nodeStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', 'appliance');
    return (int) $query->count()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberOfDatarequests(string $state = 'open'): int {
    $query = $this->nodeStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', 'datarequest')
      ->condition('state', $state);
    return (int) $query->count()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getCommunityIdentifiers(): array {
    $identifiers = [];
    foreach ($this->termStorage->loadByProperties(['vid' => 'donl_communities']) as $term) {
      if ($term->hasField('field_identifier') && ($identifier = $term->get('field_identifier')->getValue()[0]['value'] ?? NULL)) {
        $identifiers[] = $identifier;
      }
    }
    return $identifiers;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrganizationLayers(): array {
    $layers = [];
    foreach ($this->termStorage->loadByProperties(['vid' => 'dcatapdonl_donl_organization']) as $term) {
      if ($term->hasField('identifier') && $term->hasField('organization_type') && ($identifier = $term->get('identifier')->getValue()[0]['value'] ?? NULL) && ($layer = $term->get('organization_type')->getValue()[0]['value'] )) {
        $layers[$layer][] = $identifier;
      }
    }
    return $layers;
  }

}
