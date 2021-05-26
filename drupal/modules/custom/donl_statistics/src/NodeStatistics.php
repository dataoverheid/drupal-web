<?php

namespace Drupal\donl_statistics;

use Drupal\donl_identifier\ResolveIdentifierServiceInterface;
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
   * @var \Drupal\donl_identifier\ResolveIdentifierServiceInterface
   */
  protected $resolveIdentifierService;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\donl_identifier\ResolveIdentifierServiceInterface $resolveIdentifierService
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ResolveIdentifierServiceInterface $resolveIdentifierService) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->resolveIdentifierService = $resolveIdentifierService;
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicationCount(): int {
    $query = $this->nodeStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED, '=')
      ->condition('type', 'appliance', '=');
    return (int) $query->count()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberOfDatarequests(string $state = 'open'): int {
    $query = $this->nodeStorage->getQuery()
      ->condition('status', NodeInterface::PUBLISHED, '=')
      ->condition('type', 'datarequest', '=')
      ->condition('state', $state, '=');
    return (int) $query->count()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getCommunityIdentifiers(): array {
    $identifiers = [];
    foreach ($this->nodeStorage->loadByProperties(['type' => 'community']) as $node) {
      $identifiers[] = $this->resolveIdentifierService->resolve($node);
    }
    return $identifiers;
  }

}
