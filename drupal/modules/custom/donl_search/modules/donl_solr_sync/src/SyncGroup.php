<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 * Synchronize groups.
 */
class SyncGroup extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $this->updateIndex([
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => $this->resolveIdentifierService->resolve($node),
      'sys_type' => 'group',

      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'group_description'),
      'relation_dataset' => $this->getDatasetRelations($node, 'relation_group_dataset'),
    ]);
  }

}
