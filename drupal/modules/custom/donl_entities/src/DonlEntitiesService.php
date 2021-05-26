<?php

namespace Drupal\donl_entities;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Donl entities service.
 */
class DonlEntitiesService implements DonlEntitiesServiceInterface {
  use StringTranslationTrait;

  /**
   * DonlEntitiesService constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   */
  public function __construct(TranslationInterface $stringTranslation) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntitiesAsOptionsList(string $useCase): array {
    $entities = [];

    switch ($useCase) {
      case 'search':
        $entities['catalog'] = $this->getEntityName('catalog');
        $entities['community'] = $this->getEntityName('community');
        $entities['dataset'] = $this->getEntityName('dataset');
        $entities['datarequest'] = $this->getEntityName('datarequest');
        $entities['group'] = $this->getEntityName('group');
        $entities['news'] = $this->getEntityName('news');
        $entities['organization'] = $this->getEntityName('organization');
        $entities['support'] = $this->getEntityName('support');
        $entities['application'] = $this->getEntityName('application');
        break;

      case 'community_search':
        $entities['dataset'] = $this->getEntityName('dataset');
        $entities['datarequest'] = $this->getEntityName('datarequest');
        $entities['group'] = $this->getEntityName('group');
        $entities['news'] = $this->getEntityName('news');
        $entities['organization'] = $this->getEntityName('organization');
        $entities['application'] = $this->getEntityName('application');
        break;

      case 'recent_content':
        $entities['community'] = $this->getEntityName('community');
        $entities['dataset'] = $this->getEntityName('dataset');
        $entities['datarequest'] = $this->getEntityName('datarequest');
        $entities['group'] = $this->getEntityName('group');
        $entities['news'] = $this->getEntityName('news');
        $entities['support'] = $this->getEntityName('support');
        $entities['application'] = $this->getEntityName('application');
        break;
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityName(string $machineName, bool $singular = FALSE, bool $capitalize = TRUE): TranslatableMarkup {
    $entities = [
      'catalog' => [
        'singular' => 'catalog',
        'plural' => 'catalogs',
      ],
      'community' => [
        'singular' => 'community',
        'plural' => 'communities',
      ],
      'dataset' => [
        'singular' => 'dataset',
        'plural' => 'datasets',
      ],
      'datarequest' => [
        'singular' => 'data request',
        'plural' => 'data requests',
      ],
      'dataservice' => [
        'singular' => 'dataservice',
        'plural' => 'dataservices',
      ],
      'group' => [
        'singular' => 'group',
        'plural' => 'groups',
      ],
      'news' => [
        'singular' => 'news item',
        'plural' => 'news items',
      ],
      'organization' => [
        'singular' => 'organization',
        'plural' => 'organizations',
      ],
      'support' => [
        'singular' => 'support page',
        'plural' => 'support pages',
      ],
      'application' => [
        'singular' => 'application',
        'plural' => 'applications',
      ],
    ];

    $name = $entities[$machineName][$singular ? 'singular' : 'plural'] ?? $machineName;
    // phpcs:ignore
    return $this->t(($capitalize ? ucfirst($name) : $name));
  }

}
