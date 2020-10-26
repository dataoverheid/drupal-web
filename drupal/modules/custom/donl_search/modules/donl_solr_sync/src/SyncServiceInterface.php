<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 *
 */
interface SyncServiceInterface {

  /**
   * Update the node within the SOLR schema.
   *
   * @param \Drupal\node\Entity\Node $node
   * @param string $action
   *   The action that's being done on the node (update or delete)
   */
  public function sync(Node $node, $action);

}
