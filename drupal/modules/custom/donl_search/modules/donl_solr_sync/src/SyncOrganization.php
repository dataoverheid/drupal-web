<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 *
 */
class SyncOrganization extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $languageCode = $node->language()->getId();

    // @todo replace this line once the old field is removed.
    $kind = $this->getNodeValue($node, 'organization_type');
    if ($terms = $node->get('organization_type_term')->referencedEntities()) {
      $kind = $terms[0]->get('identifier')->getString();
    }

    $orgLogoReference = NULL;
    if ($logo = $node->get('organisation_logo')->entity) {
      $orgLogoReference = file_create_url($logo->getFileUri());
      $orgLogoReference = substr($orgLogoReference, strlen($GLOBALS['base_url']));
    }

    $synonyms = [];
    foreach ($node->get('organization_synonyms')->getValue() as $v) {
      $synonyms[] = $v['value'];
    }

    $this->updateIndex([
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($languageCode),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => $this->resolveIdentifierService->resolve($node),
      'sys_type' => 'organization',
      'asset_logo' => $orgLogoReference,
      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'organization_description'),
      'kind' => $kind,
      'user_defined_synonyms' => $synonyms,
    ]);
  }

}
