<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 * Synchronize communities.
 */
class SyncCommunity extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $themes = [];
    if ($values = $node->get('themes')->getValue()) {
      foreach ($values as $v) {
        $themes[] = $v['value'];
      }
    }

    $this->updateIndex([
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => $this->resolveIdentifierService->resolve($node),
      'sys_type' => 'community',

      'relation_appliance' => $this->getRelations($node, 'relation_community_application'),
      'relation_datarequest' => $this->getRelations($node, 'relation_community_datarequest'),
      'relation_group' => $this->getRelations($node, 'relation_community_group'),
      'relation_news' => $this->getRelations($node, 'relation_community_recent'),
      'relation_organization' => $this->getRelations($node, 'relation_community_organization'),

      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'html_description'),
      'theme' => $themes,
    ]);
  }

}
