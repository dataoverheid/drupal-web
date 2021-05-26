<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 * Synchronize applications.
 */
class SyncApplication extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $tags = [];
    foreach ($node->get('field_tags')->referencedEntities() ?? [] as $tag) {
      $tags[] = $tag->getName();
    }

    $this->updateIndex([
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => $this->resolveIdentifierService->resolve($node),
      'sys_type' => 'appliance',

      'relation_dataset' => $this->getDatasetRelations($node, 'relation_application_dataset'),

      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'field_description'),
      'contact_point' => [
        $this->getNodeValue($node, 'field_contactperson_email'),
        $this->getNodeValue($node, 'field_contactperson_name'),
        $this->getNodeValue($node, 'field_contactperson_organization'),
        $this->getNodeValue($node, 'field_contactperson_phone'),
      ],
      'url' => $node->get('field_link_application')->getValue()[0]['uri'] ?? NULL,
      'authority' => $this->getNodeValue($node, 'field_organization'),
      'theme' => $this->getNodeValue($node, 'theme'),
      'keyword' => $tags,
    ]);
  }

}
