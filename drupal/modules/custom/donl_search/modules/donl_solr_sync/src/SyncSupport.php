<?php

namespace Drupal\donl_solr_sync;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 *
 */
class SyncSupport extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $support = [
      'sys_id' => $this->getSolrId($node),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString(),
      'sys_type' => 'support',

      'title' => $node->getTitle(),
      'description' => $this->cleanupText($this->getNodeValue($node, 'body')),
      'text' => $this->getParagraphData($node, 'field_paragraphs'),
    ];

    $this->solrRequest->updateIndex(json_encode($support));
  }

}
