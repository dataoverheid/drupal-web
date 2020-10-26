<?php

namespace Drupal\donl_solr_sync;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 *
 */
class SyncGroup extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $datasets = [];
    if ($node->get('datasets')->getValue()) {
      foreach ($node->get('datasets')->getValue() as $v) {
        $datasets[] = $v['value'];
      }
    }

    $group = [
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString(),
      'sys_type' => 'group',

      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'group_description'),
      'relation_dataset' => $datasets,
    ];

    $this->solrRequest->updateIndex(json_encode($group));
  }

}
