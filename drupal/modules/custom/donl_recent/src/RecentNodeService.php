<?php

namespace Drupal\donl_recent;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\node\NodeInterface;

/**
 * Class to define the menu_link breadcrumb builder.
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
      'nieuws' => [
        'label' => $this->t('News'),
        'img' => 'nieuws.jpg',
        'more_label' => $this->t('Show all news items'),
      ],
      'evenementen' => [
        'label' => $this->t('Events'),
        'img' => 'evenementen.jpg',
        'more_label' => $this->t('Show all events'),
      ],
      'bijeenkomsten' => [
        'label' => $this->t('Meetings'),
        'img' => 'bijeenkomst.jpg',
        'more_label' => $this->t('Show all meetings'),
      ],
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
    return $this->types[$type]['label'] ?? $this->t('Recent');
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
  public function getNodes(string $type = NULL, int $limit = NULL): array {
    $query = $this->getDefaultRecentQuery($type, $limit);
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
  private function getDefaultRecentQuery(string $type = NULL, int $limit = NULL): QueryInterface {
    $query = $this->nodeStorage->getQuery();
    $query
      ->condition('type', 'recent')
      ->condition('status', '1')
      ->sort('created', 'DESC')
      ->condition('langcode', $this->currentLanguage->getId());
    if ($limit) {
      $query->range(0, $limit);
    }
    if ($type !== NULL) {
      $query->condition('recent_type', $type);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function buildMenuItems(string $type = NULL, NodeInterface $current_node = NULL, bool $returnTypeOnly = FALSE): array {
    $menuItems = [];

    foreach ($this->types as $typeId => $typeConfig) {
      $items = [];
      $options = [];
      if ($typeId === $type) {
        $options = ['attributes' => ['class' => ['is-active']]];
        /** @var NodeInterface $node */
        foreach ($this->getNodes($type) as $node) {
          $translated_node = $node->getTranslation($this->currentLanguage->getId());
          $link = Link::fromTextAndUrl($translated_node->label(), $translated_node->toUrl())
            ->toRenderable();
          if ($current_node && $node->id() === $current_node->id()) {
            $link['#attributes']['class'][] = 'is-active';
          }
          $items[] = [
            'link' => $link,
          ];
        }
      }
      $menuItems[$typeId] = [
        'link' => Link::createFromRoute($typeConfig['label'], 'donl_recent.type_overview', ['type' => $typeId], $options),
        'sub_items' => $items,
      ];
    }

    return $returnTypeOnly ? $menuItems[$type] : $menuItems;
  }

}
