<?php

namespace Drupal\donl_solr_sync\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;
use Drush\Drush;

/**
 *
 */
class SolrSyncCommands extends DrushCommands {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * SolrSyncCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct();
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * Update the SOLR index.
   *
   * @param string $type
   *
   * @command donl_solr_sync:update
   * @aliases update-solr-index
   * @usage donl_solr_sync:update
   *   Update the SOLR index.
   */
  public function update($type) {
    $nids = [];

    switch ($type) {
      case 'application':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'appliance')->execute();
        break;

      case 'catalog':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'catalog')->execute();
        break;

      case 'community':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'community')->execute();
        break;

      case 'dataservice':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'dataservice')->execute();
        break;

      case 'datarequest':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'datarequest')->execute();
        break;

      case 'group':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'group')->execute();
        break;

      case 'news':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'recent')->execute();
        break;

      case 'organization':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'organization')->execute();
        break;

      case 'support':
        $nids = $this->nodeStorage->getQuery()->condition('type', 'landingpage')->execute();
        break;
    }

    foreach ($this->nodeStorage->loadMultiple($nids) as $node) {
      $node->save();
      Drush::output()->writeln(dt(ucfirst($type) . ' ' . $node->id() . ' send to solr index.'));
    }
  }

}
