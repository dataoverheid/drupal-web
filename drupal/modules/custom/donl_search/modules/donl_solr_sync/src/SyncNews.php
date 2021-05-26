<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 *
 */
class SyncNews extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $this->updateIndex([
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $node->id(),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => $this->resolveIdentifierService->resolve($node),
      'sys_type' => 'news',

      'relation_appliance' => $this->getRelations($node, 'relation_recent_application'),
      'relation_catalog' => $this->getRelations($node, 'relation_recent_catalog'),
      'relation_community' => $this->getRelations($node, 'relation_recent_community'),
      'relation_dataset' => $this->getDatasetRelations($node, 'relation_recent_dataset'),
      'relation_datarequest' => $this->getRelations($node, 'relation_recent_datarequest'),
      'relation_dataservice' => $this->getRelations($node, 'relation_recent_dataservice'),
      'relation_group' => $this->getRelations($node, 'relation_recent_group'),
      'relation_organization' => $this->getRelations($node, 'relation_recent_organization'),

      'title' => $node->getTitle(),
      'description' => $this->cleanupText($this->getNodeValue($node, 'body')),
      'text' => $this->getParagraphData($node, 'field_paragraphs'),
    ]);
  }

}
