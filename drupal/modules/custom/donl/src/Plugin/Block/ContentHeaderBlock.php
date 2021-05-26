<?php

namespace Drupal\donl\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\donl_search\Form\SearchForm;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content header block.
 *
 * @Block(
 *   id = "donl_content_header_block",
 *   admin_label = @Translation("Content header block"),
 *   category = @Translation("DONL"),
 * )
 */
class ContentHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The file storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * ContentHeaderBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param $plugin_id
   *   The plugin id.
   * @param $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, CurrentRouteMatch $currentRouteMatch, FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->routeMatch = $currentRouteMatch;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // Check if the current page is a node.
    if (!$node = $this->getCurrentNode()) {
      return [];
    }

    $type = $node->get('field_type')->getValue()[0]['value'] ?? '';
    $build = [
      '#theme' => 'donl_content_header_block',
      '#title' => $node->getTitle(),
      '#type' => $type,
    ];

    // Retrieve the header image.
    if ($node->hasField('field_header_image') && $headerImage = $node->get('field_header_image')->getValue()) {
      $fid = $headerImage[0]['target_id'] ?? '';
      if ($fid && $file = $this->fileStorage->load($fid)) {
        $build['#image'] = $file->createFileUrl();
      }
    }

    // Retrieve the content of the header block.
    if ($node->hasField('body') && $body = $node->get('body')->getValue()) {
      $build['#content'] = [
        '#type' => 'processed_text',
        '#text' => $body[0]['value'] ?? '',
        '#format' => $body[0]['format'] ?? '',
      ];
    }

    // Retrieve the links of the header block.
    if ($node->hasField('field_header_links') && $links = $node->get('field_header_links')->getValue()) {
      $build['#links'] = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => [],
        '#attributes' => ['class' => ['list', 'list--linked']],
      ];

      foreach ($links as $link) {
        if (!empty($link['uri']) && !empty($link['title'])) {
          $build['#links']['#items'][] = Link::fromTextAndUrl($link['title'], Url::fromUri($link['uri']));
        }
      }
    }

    // Retrieve the search form and update the default type to the node's type.
    $searchForm = $this->formBuilder->getForm(SearchForm::class);
    $mobileSearchForm = $this->formBuilder->getForm(SearchForm::class);

    $options = $searchForm['searchbar']['type_select']['#options'] ?? [];
    if ($options && array_key_exists($type, $options)) {
      $searchForm['searchbar']['type_select']['#value'] = $type;
      $mobileSearchForm['searchbar']['type_select']['#value'] = $type;
    }

    // Set a different form id for the mobile search form so it doesn't conflict with the desktop search form.
    $mobileSearchForm['#attributes']['id'] = 'donl-mobile-search-form';

    $build['#search_form'] = $searchForm;
    $build['#mobile_search_form'] = $mobileSearchForm;

    return $build;
  }

  /**
   * Retrieves the node of the current page.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node or NULL if the current page isn't a node.
   */
  protected function getCurrentNode(): ?NodeInterface {
    if ($node = $this->routeMatch->getParameter('node')) {
      return $node instanceof NodeInterface ? $node : NULL;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    $tags = [];
    if ($node = $this->getCurrentNode()) {
      $tags = ["node:{$node->id()}"];
    }
    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

}
