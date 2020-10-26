<?php

namespace Drupal\donl_solr_sync;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 *
 */
class SyncCommunity extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $nodestorage = $this->entityTypeManager->getStorage('node');

    $themes = [];
    if ($values = $node->get('themes')->getValue()) {
      foreach ($values as $v) {
        $themes[] = $v['value'];
      }
    }

    $groups = [];
    if ($values = $node->get('groups')->getValue()) {
      foreach ($values as $v) {
        $groups[] = Url::fromRoute('entity.node.canonical', ['node' => $v['target_id']], ['absolute' => TRUE])->toString();
      }
    }

    $organizations = [];
    if ($values = $node->get('community_organisations')->getValue()) {
      foreach ($values as $v) {
        if ($n = $nodestorage->load($v['target_id'])) {
          $organizations[] = $this->getNodeValue($n, 'identifier');
        }
      }
    }

    $applications = [];
    if ($values = $node->get('community_applications')->getValue()) {
      foreach ($values as $v) {
        $applications[] = Url::fromRoute('entity.node.canonical', ['node' => $v['target_id']], ['absolute' => TRUE])->toString();
      }
    }

    $datarequests = [];
    if ($values = $node->get('community_datarequests')->getValue()) {
      foreach ($values as $v) {
        $datarequests[] = Url::fromRoute('entity.node.canonical', ['node' => $v['target_id']], ['absolute' => TRUE])->toString();
      }
    }

    $community = [
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString(),
      'sys_type' => 'community',

      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'community_description'),
      'theme' => $themes,
      'relation_group' => $groups,
      'relation_organization' => $organizations,
      'relation_appliance' => $applications,
      'relation_datarequest' => $datarequests,
    ];

    $this->solrRequest->updateIndex(json_encode($community));
  }

}
