<?php

namespace Drupal\donl_migrate\Commands;

use Drupal\ckan\CkanRequestInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\donl_value_list\ValueListInterface;
use Drush\Commands\DrushCommands;

/**
 *
 */
class CleanupDatarequest extends DrushCommands {

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  protected $valueList;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   *
   */
  public function __construct(CkanRequestInterface $ckanRequest, EntityTypeManagerInterface $entityTypeManager, ValueListInterface $valueList) {
    parent::__construct();
    $this->ckanRequest = $ckanRequest;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->valueList = $valueList;
  }

  /**
   * Cleanup the fields for datarequests.
   *
   * @command donl_migrate:cleanup-datarequests
   * @aliases donl-migrate-cleanup-datarequests
   * @usage donl_migrate:cleanup-datarequests
   *   Cleanup the fields for datarequests.
   */
  public function cleanup() {
    $nodes = $this->nodeStorage->loadByProperties(['type' => 'datarequest']);

    /** @var \Drupal\node\Entity\Node $node */
    foreach ($nodes as $node) {
      // Update datasets.
      $identifiers = [];
      $oldDatasets = $node->get('url_dataset')->getValue() ?? [];
      foreach ($oldDatasets as $v) {
        $v = explode('/', $v['uri']);
        $uuid = end($v);
        if (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid) === 1) {
          if ($dataset = $this->ckanRequest->getDataset($uuid)) {
            $identifiers[] = $dataset->identifier ?? '';
          }
        }
      }
      $node->set('datasets', $identifiers);

      $node->Save();
    }
  }

}
