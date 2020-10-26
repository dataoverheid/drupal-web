<?php

namespace Drupal\donl_migrate\Commands;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;

/**
 *
 */
class MigrateOrganization extends DrushCommands {

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   *
   */
  public function __construct(MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct();
    $this->messenger = $messenger;
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * Migrate the files to the drupal storage.
   *
   * @command donl_migrate:organization-migrate
   * @aliases donl-migrate-organization
   * @usage donl_migrate:organization-migrate
   *   Migrate all organizations from taxonomy to nodes.
   */
  public function migrate() {
    $terms = $this->termStorage->loadTree('dcatapdonl_donl_organization', 0, NULL, TRUE);

    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach ($terms as $term) {
      $identifier = $term->get('identifier')->getValue()[0]['value'];
      $explode = explode('/', $identifier);
      $machineName = end($explode);
      $machineName = str_replace('-', '_', Html::getId($machineName));

      $nodes = $this->nodeStorage->loadByProperties([
        'type' => 'organization',
        'machine_name' => $machineName,
      ]);
      if (!$nodes) {
        $node = Node::create([
          'type' => 'organization',
          'langcode' => 'nl',
          'title' => $term->get('label_nl')->getValue()[0]['value'],
          'identifier' => $identifier,
          'machine_name' => $machineName,
          'organization_type' => trim($term->get('organization_type')->getValue()[0]['value']) ?? '',
        ]);
        $node->save();
      }
      else {
        $this->messenger->addWarning('Couldn\'t transfer taxonomy term: ' . $term->getName() . ' (' . $term->id() . ').');
      }
    }

  }

}
