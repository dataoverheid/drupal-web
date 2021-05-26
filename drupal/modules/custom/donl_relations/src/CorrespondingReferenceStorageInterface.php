<?php

namespace Drupal\donl_relations;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\node\NodeInterface;

/**
 * Defines the interface for corresponding reference storage.
 */
interface CorrespondingReferenceStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Loads the valid corresponding reference config entities for the given entity.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return \Drupal\donl_relations\Entity\CorrespondingReferenceInterface[]
   *   The valid corresponding references.
   */
  public function loadValid(NodeInterface $node);

}
