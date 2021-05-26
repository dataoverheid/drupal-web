<?php

namespace Drupal\donl_search;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\donl_identifier\ResolveIdentifierServiceInterface;
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
   * @var \Drupal\donl_identifier\ResolveIdentifierServiceInterface
   */
  protected $resolveIdentifierService;

  /**
   * @var array
   */
  private $lists;

  /**
   * @var string
   */
  private $languageCode;

  /**
   * FacetRenameService constructor.
   *
   * @param \Drupal\donl_value_list\ValueListInterface $valueList
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\donl_identifier\ResolveIdentifierServiceInterface $resolveIdentifierService
   */
  public function __construct(ValueListInterface $valueList, CacheBackendInterface $cacheBackend, TranslationInterface $stringTranslation, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, ResolveIdentifierServiceInterface $resolveIdentifierService) {
    $this->cacheBackend = $cacheBackend;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->resolveIdentifierService = $resolveIdentifierService;
    $this->stringTranslation = $stringTranslation;
    $this->languageCode = $languageManager->getCurrentLanguage()->getId();

    $terms = $this->getTermTranslations();
    $this->lists = [
      'facet_sys_type' => $terms,
      'facet_related_to' => $terms,
      'facet_access_rights' => $valueList->getList('overheid:openbaarheidsniveau'),
      'facet_authority' => $valueList->getList('donl:organization'),
      'facet_authority_kind' => $this->getOrganizationTypes(),
      'facet_theme' => $valueList->getList('overheid:taxonomiebeleidsagenda'),
      'facet_license' => $valueList->getList('overheid:license'),
      'facet_sys_language' => $valueList->getList('donl:language'),
      'facet_format' => $valueList->getList('mdr:filetype_nal'),
      'facet_catalog' => $valueList->getList('donl:catalogs'),
      'facet_classification' => $terms,
      'facet_status' => $valueList->getList('overheid:datasetStatus'),
      'facet_community' => $this->getNodes('community'),
      'facet_group' => $this->getNodes('group'),
      'facet_recent' => $this->getRecentOptions(),
      'facet_kind' => $this->getOrganizationTypes(),
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

    return $this->lists[$type][$title] ?? $title;
  }

  /**
   * Get a list with all available organization types.
   *
   * @return array
   *   An array with the readable names.
   */
  private function getOrganizationTypes(): array {
    $cid = 'facet_rename:donl_organization_types:' . $this->languageCode;
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    $values = [];
    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach ($this->termStorage->loadTree('donl_organization_types', 0, NULL, TRUE) as $term) {
      if ($term->hasTranslation($this->languageCode)) {
        $term = $term->getTranslation($this->languageCode);
      }
      $values[$term->get('identifier')->getString()] = $term->get('name')->getString();
    }
    $this->cacheBackend->set($cid, $values, CacheBackendInterface::CACHE_PERMANENT);
    return $values;
  }

  /**
   * Get a list with all available term translations.
   *
   * @return array
   *   An array with the readable names.
   */
  private function getTermTranslations(): array {
    $cid = 'facet_rename:term_translations:' . $this->languageCode;
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
   * Rename node related facets.
   *
   * @param string $type
   *   The entity type.
   *
   * @return array
   *   An array with the readable names.
   */
  private function getNodes(string $type): array {
    $cid = 'facet_rename:' . $type . ':' . $this->languageCode;
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }

    $values = [];
    /** @var \Drupal\node\Entity\Node $node */
    foreach ($this->nodeStorage->loadByProperties(['type' => $type]) as $node) {
      $identifier = $this->resolveIdentifierService->resolve($node);
      if ($node->hasTranslation($this->languageCode)) {
        $node = $node->getTranslation($this->languageCode);
      }
      $values[$identifier] = $node->getTitle();
    }
    $this->cacheBackend->set($cid, $values, CacheBackendInterface::CACHE_PERMANENT);
    return $values;
  }

  /**
   * Rename the facet_recent options to a readable name.
   *
   * @return array
   *   An array with the readable names.
   */
  private function getRecentOptions(): array {
    return [
      '* TO NOW-1YEAR' => $this->t('More than a year ago'),
      'NOW-1YEAR TO NOW-1MONTH' => $this->t('Last year'),
      'NOW-1MONTH TO NOW+1DAY' => $this->t('Last month'),
    ];
  }

}
