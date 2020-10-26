<?php

namespace Drupal\donl_migrate\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;

/**
 *
 */
class MigrateCatalog extends DrushCommands {

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
    $mapping = [
      'https://dataderden.cbs.nl/ODataCatalog/' => 'centraal-bureau-voor-de-statistiek-derden',
      'http://opendata.arnhem.nl/' => 'gemeente-arnhem',
      'https://data.amsterdam.nl/' => 'gemeente-amsterdam',
      'https://data.overheid.nl' => 'data-overheid-nl',
      'https://open-data.nijmegen.nl/' => 'gemeente-nijmegen',
      'https://www.dataplatform.nl/' => 'dataplatform',
      'https://opendata.cbs.nl/ODataCatalog/' => 'centraal-bureau-voor-de-statistiek',
      'https://opendata.rdw.nl' => 'opendata-rdw-nl',
      'https://www.rijksoverheid.nl/ministeries/ministerie-van-sociale-zaken-en-werkgelegenheid' => 'socialezaken',
      'https://nationaalgeoregister.nl' => 'nationaalgeoregister-nl',
    ];

    $terms = $this->termStorage->loadTree('dcatapdonl_donl_catalogs', 0, NULL, TRUE);
    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach ($terms as $term) {
      $nodes = $this->nodeStorage->loadByProperties([
        'type' => 'catalog',
        'machine_name' => $term->get('name_slug')->getValue()[0]['value'],
      ]);
      if (!$nodes) {
        $node = Node::create([
          'uid' => 1,
          'type' => 'catalog',
          'langcode' => 'nl',
          'title' => $term->get('label_nl')->getValue()[0]['value'],
          'identifier' => $term->get('identifier')->getValue()[0]['value'],
          'machine_name' => $term->get('name_slug')->getValue()[0]['value'],
          'catalog_description' => $term->get('description_nl')->getValue()[0]['value'],
          'ckan_organization_mapping' => $mapping[$term->get('identifier')->getValue()[0]['value']] ?? 'https://data.overheid.nl',
        ]);
        $node->save();
      }
      else {
        $this->messenger->addWarning('Couldn\'t transfer taxonomy term: ' . $term->getName() . ' (' . $term->id() . ').');
      }
    }

  }

}
