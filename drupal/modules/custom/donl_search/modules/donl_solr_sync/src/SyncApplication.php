<?php

namespace Drupal\donl_solr_sync;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 *
 */
class SyncApplication extends SyncService {

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

    $tags = [];
    foreach ($node->get('field_tags')->referencedEntities() ?? [] as $tag) {
      $tags[] = $tag->getName();
    }

    $application = [
      'sys_id' => $this->getSolrId($node),
      // 'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString(),
      'sys_type' => 'appliance',

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
      // 'authority_type' => $this->getSelectKey($node, 'field_made_by'),
      'keyword' => $tags,
      // 'appliance_type' => $this->getSelectKey($node, 'field_appliance_type'),
      'relation_dataset' => $datasets,
    ];

    $this->solrRequest->updateIndex(json_encode($application));
  }

}
