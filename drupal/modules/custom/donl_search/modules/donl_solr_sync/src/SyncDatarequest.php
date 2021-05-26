<?php

namespace Drupal\donl_solr_sync;

use Drupal\node\Entity\Node;

/**
 *
 */
class SyncDatarequest extends SyncService {

  /**
   * {@inheritdoc}
   */
  protected function update(Node $node) {
    $requestStartDate = $this->getNodeValue($node, 'requested_start_and_end_date');
    $requestEndDate = $node->get('requested_start_and_end_date')->getValue()[0]['end_value'] ?? NULL;

    $this->updateIndex([
      'sys_id' => $this->getSolrId($node),
      'sys_name' => $this->getNodeValue($node, 'machine_name'),
      'sys_language' => $this->langidToUri($node->language()->getId()),
      'sys_created' => date('Y-m-d\TH:i:s\Z', $node->getCreatedTime()),
      'sys_modified' => date('Y-m-d\TH:i:s\Z', $node->getChangedTime()),
      'sys_uri' => $this->resolveIdentifierService->resolve($node),
      'sys_type' => 'datarequest',

      'relation_dataset' => $this->getDatasetRelations($node, 'relation_datarequest_dataset'),
      'relation_community' => $this->getRelations($node, 'relation_datarequest_community'),

      'title' => $node->getTitle(),
      'description' => $this->getNodeValue($node, 'requested_data'),
      'theme' => $this->getNodeValue($node, 'theme'),
      'authority' => $this->getNodeValue($node, 'data_owner'),
      'phase' => $this->getSelectKey($node, 'phase'),
      'format' => $this->getNodeValue($node, 'requested_dataformat'),
      'temporal' => [
        ($requestStartDate ? $requestStartDate . 'T00:00:00Z' : NULL),
        ($requestEndDate ? $requestEndDate . 'T00:00:00Z' : NULL),
      ],
      'status' => $this->getSelectKey($node, 'state'),
      'text' => [
        $this->getSelectKey($node, 'state_datarequest'),
        $this->getSelectKey($node, 'result_datarequest'),
        $this->getSelectKey($node, 'request_source'),
        $this->getSelectKey($node, 'target_audience'),
        $this->getNodeValue($node, 'want_to_use_it_for'),
        $this->getNodeValue($node, 'possible_owner'),
        $this->getNodeValue($node, 'explanation_state'),
      ],
    ]);
  }

}
