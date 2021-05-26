<?php

namespace Drupal\donl_relations;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\node\NodeInterface;

/**
 * Defines the Corresponding Reference storage.
 */
class CorrespondingReferenceStorage extends ConfigEntityStorage implements CorrespondingReferenceStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadValid(NodeInterface $node) {
    // @todo: Figure out a way to filter this query on the node bundle.
    $result = $this->getQuery()
      ->condition('enabled', 1)
      ->execute();

    if (empty($result)) {
      return [];
    }

    return $this->loadMultiple($result);
  }

}
