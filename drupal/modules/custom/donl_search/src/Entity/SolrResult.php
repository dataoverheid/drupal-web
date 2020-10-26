<?php

namespace Drupal\donl_search\Entity;

use Drupal\ckan\Entity\Resource;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 *
 */
class SolrResult {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Url
   */
  public $url;

  /**
   * @var string
   */
  public $uri;

  /**
   * @var string
   */
  public $metadata_created;

  /**
   * @var string
   */
  public $metadata_modified;

  /**
   * @var string
   */
  public $type;

  /**
   * @var string
   */
  public $title;

  /**
   * @var string
   */
  public $description;

  /**
   * @var string[]
   */
  public $theme;

  /**
   * @var string[]
   */
  public $authority;

  /**
   * @var array
   */
  public $format;

  /**
   * @var string
   */
  public $state;

  /**
   * @var string[]
   */
  public $distributionTypes;

  /**
   * @var string
   */
  public $icon;

  /**
   *
   */
  public function __construct($doc = NULL) {
    if ($doc['sys_type'] === 'appliance') {
      $doc['sys_type'] = 'application';
    }

    if (isset($doc)) {
      $this->id = explode('|', $doc['sys_id'])[0];;
      $this->name = $doc['sys_name'] ?? NULL;
      $this->metadata_created = $doc['sys_created'];
      $this->metadata_modified = $doc['sys_modified'];
      $this->type = $doc['sys_type'];
      $this->title = $doc['title'];
      $this->description = !empty($doc['description']) ? html_entity_decode($doc['description']) : '';
      $this->theme = $doc['theme'] ?? [];
      $this->authority = $doc['authority'] ?? [];
      $this->format = $doc['format'] ?? [];
      $this->state = $doc['status'] ?? '';
      $this->url = $this->getUrl($doc);
      $this->uri = $doc['sys_uri'];
      $this->icon = $this->getIcon($doc['sys_type'] ?? '');

      $this->distributionTypes = [];
      foreach ($this->format as $format) {
        $resource = new Resource();
        $resource->setFormat($format);
        $type = $resource->getResourceType();
        $this->distributionTypes[$type] = $type;
      }
    }
  }

  /**
   * Update the url.
   *
   * @param \Drupal\Core\Url $url
   *   The new URL.
   */
  public function updateUrl(Url $url): void {
    $this->url = $url;
  }

  /**
   * Get the icon for the given type name.
   *
   * @param string $type
   *
   * @return string
   */
  private function getIcon(string $type): string {
    switch ($type) {
      case 'news':
      case 'support':
        return 'icon-nieuws.svg';

      case 'catalog':
      case 'community':
      case 'group':
      case 'organization':
        return 'icon-community.svg';
    }

    return 'icon-data.svg';
  }

  /**
   * Get the correct URL for the given resource type.
   *
   * @param array $doc
   *
   * @return \Drupal\Core\Url
   */
  private function getUrl(array $doc) {
    $id = explode('|', $doc['sys_id'])[0];
    $sysName = $doc['sys_name'] ?? NULL;

    switch ($doc['sys_type']) {
      case 'application':
        return Url::fromRoute('donl.application', ['application' => $id]);

      case 'catalog':
        return Url::fromRoute('donl_search.catalog.view', ['catalog' => $sysName]);

      case 'dataset':
        return Url::fromRoute('ckan.dataset.view', ['dataset' => $sysName ?? $id]);

      case 'datarequest':
        return Url::fromRoute('donl.datarequest', ['datarequest' => $id]);

      case 'group':
        return Url::fromRoute('donl_search.group.view', ['group' => $sysName]);

      case 'organization':
        return Url::fromRoute('donl_search.organization.view', ['organization' => $sysName]);

      case 'community':
      case 'news':
      case 'support':
        return Url::fromRoute('entity.node.canonical', ['node' => $id]);
    }

    return Url::fromUserInput('#');
  }

}
