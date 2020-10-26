<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 *
 */
class SyncCatalog extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $catalog = [
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => $this->getNodeValue($node, 'identifier'),
      'sys_type' => 'catalog',

      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'catalog_description'),
    ];

    $this->solrRequest->updateIndex(json_encode($catalog));
  }

}
