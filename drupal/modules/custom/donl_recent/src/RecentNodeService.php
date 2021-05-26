<?php

namespace Drupal\donl_recent;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Recent node service.
 */
class RecentNodeService implements RecentNodeServiceInterface {

  use StringTranslationTrait;

  /**
   * The node storage to find and load 'recent' nodes.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The node view builder to generate the teaser views of the nodes.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * The current language object.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $currentLanguage;

  /**
   * Available recent types array.
   *
   * @var array
   */
  private $types;

  /**
   * RecentNodeService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager used to load the node storage.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager used to get the current language.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The translation service used to translate the strings.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, TranslationInterface $stringTranslation) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->nodeViewBuilder = $entityTypeManager->getViewBuilder('node');
    $this->currentLanguage = $languageManager->getCurrentLanguage();
    $this->stringTranslation = $stringTranslation;

    $this->types = [
      'communities' => $this->t('Communities'),
      'evenementen' => $this->t('Events'),
      'nieuws' => $this->t('News'),
      'impact-story' => $this->t('Impact stories'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTypes(): array {
    return $this->types;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(string $type = NULL): TranslatableMarkup {
    return $this->types[$type] ?? $this->t('Recent');
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeTeasers(string $type = NULL): array {
    return $this->nodeViewBuilder->viewMultiple($this->getNodes($type), 'teaser');
  }

  /**
   * {@inheritdoc}
   */
  public function getNodes(string $type = NULL, int $start = 0, $end = NULL): array {
    $query = $this->getDefaultRecentQuery($type, $start, $end);
    $ids = $query->execute();
    return $this->nodeStorage->loadMultiple($ids);
  }

  /**
   * Get the default query for 'recent' nodes (filtered by type).
   *
   * @param string|null $type
   *   The 'recent' type to filter on.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   */
  private function getDefaultRecentQuery(string $type = NULL, int $start = 0, $end = NULL): QueryInterface {
    $query = $this->nodeStorage->getQuery();
    $query
      ->condition('type', 'recent')
      ->condition('status', '1')
      ->sort('created', 'DESC')
      ->condition('langcode', $this->currentLanguage->getId());

    if ($end) {
      $query->range($start, $end);
    }

    if ($type !== NULL && $type !== 'all') {
      $query->condition('recent_type', $type);
    }

    return $query;
  }

}
