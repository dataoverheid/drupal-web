<?php

namespace Drupal\donl_search;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\donl_value_list\ValueListInterface;

/**
 *
 */
class FacetRenameService implements FacetRenameServiceInterface {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $termStorage;

  /**
   * @var array
   */
  private $lists;

  /**
   * @var string
   */
  private $languageCode;

  /**
   *
   */
  public function __construct(ValueListInterface $valueList, CacheBackendInterface $cacheBackend, TranslationInterface $stringTranslation, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager) {
    $this->cacheBackend = $cacheBackend;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->stringTranslation = $stringTranslation;
    $this->languageCode = $languageManager->getCurrentLanguage()->getId();

    $terms = $this->getTerms();
    $this->lists = [
      'facet_sys_type' => $terms,
      'facet_related_to' => $terms,
      'facet_access_rights' => $valueList->getList('overheid:openbaarheidsniveau', FALSE),
      'facet_authority' => $valueList->getList('donl:organization', FALSE),
      'facet_theme' => $valueList->getList('overheid:taxonomiebeleidsagenda', FALSE),
      'facet_license' => $valueList->getList('overheid:license', FALSE),
      'facet_sys_language' => $valueList->getList('donl:language', FALSE),
      'facet_format' => $valueList->getList('mdr:filetype_nal', FALSE),
      'facet_catalog' => $valueList->getList('donl:catalogs', FALSE),
      'facet_classification' => $terms,
      'facet_status' => $valueList->getList('overheid:datasetStatus', FALSE),
      'facet_community' => $this->getCommunities(),
      'facet_group' => $this->getGroups(),
      'facet_recent' => $this->getRecentOptions(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rename($title, $type) {
    if ($type === 'facet_theme') {
      $explodedTitle = explode('|', $title);
      $title = end($explodedTitle);
    }

    if ($type === 'facet_keyword') {
      return (string) $title;
    }

    if ($type === 'facet_community') {
      foreach ($this->lists[$type] as $key => $community) {
        $explodedKey = explode('/', $key);
        $toMatchKey = array_pop($explodedKey);
        $explodedTitle = explode('/', $title);
        $toMatchTitle = array_pop($explodedTitle);

        if ($toMatchKey === $toMatchTitle) {
          return $community;
        }
      }
    }

    return $this->lists[$type][$title] ?? $title;
  }

  /**
   * Get a list with all available terms.
   *
   * @return array
   */
  private function getTerms(): array {
    $cid = 'facet_rename:terms:' . $this->languageCode;
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    $values = [];
    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach ($this->termStorage->loadTree('term_translations', 0, NULL, TRUE) as $term) {
      if ($term->hasTranslation($this->languageCode)) {
        $term = $term->getTranslation($this->languageCode);
      }
      $values[$term->get('name')->getString()] = $term->get('translation')->getString();
    }
    $this->cacheBackend->set($cid, $values, CacheBackendInterface::CACHE_PERMANENT);
    return $values;
  }

  /**
   * Rename the facet_recent options to a readable name.
   *
   * @return array
   */
  private function getRecentOptions() {
    return [
      '* TO NOW-1YEAR' => $this->t('More than a year ago'),
      'NOW-1YEAR TO NOW-1MONTH' => $this->t('Last year'),
      'NOW-1MONTH TO NOW+1DAY' => $this->t('Last month'),
    ];
  }

  /**
   * Rename the facet_community to a readable name.
   *
   * @return array
   *   An array with the readable names.
   */
  private function getCommunities(): array {
    $cid = 'facet_rename:community:' . $this->languageCode;
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    $values = [];
    /** @var \Drupal\node\Entity\Node $node */
    foreach ($this->nodeStorage->loadByProperties(['type' => 'community']) as $node) {
      $identifier = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();
      if ($node->hasTranslation($this->languageCode)) {
        $node = $node->getTranslation($this->languageCode);
      }
      $values[$identifier] = $node->getTitle();
    }
    $this->cacheBackend->set($cid, $values, CacheBackendInterface::CACHE_PERMANENT);
    return $values;
  }

  /**
   * Rename the facet_group to a readable name.
   *
   * @return array
   *   An array with the readable names.
   */
  private function getGroups(): array {
    $cid = 'facet_rename:group:' . $this->languageCode;
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    $values = [];
    /** @var \Drupal\node\Entity\Node $node */
    foreach ($this->nodeStorage->loadByProperties(['type' => 'group']) as $node) {
      $identifier = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();
      if ($node->hasTranslation($this->languageCode)) {
        $node = $node->getTranslation($this->languageCode);
      }
      $values[$identifier] = $node->getTitle();
    }
    $this->cacheBackend->set($cid, $values, CacheBackendInterface::CACHE_PERMANENT);
    return $values;
  }

}
