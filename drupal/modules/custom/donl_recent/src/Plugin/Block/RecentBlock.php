<?php

namespace Drupal\donl_recent\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_recent\RecentNodeServiceInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the menu block for recent nodes.
 *
 * @Block(
 *  id = "donl_recent_menu_block",
 *  admin_label = @Translation("Recent Menu Block"),
 *  category = @Translation("DONL"),
 * )
 */
class RecentBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The 'recent' node service.
   *
   * @var \Drupal\donl_recent\RecentNodeServiceInterface
   */
  private $recentNodeService;

  /**
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch, RecentNodeServiceInterface $recentNodeService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->routeMatch = $routeMatch;
    $this->recentNodeService = $recentNodeService;
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
      $container->get('donl_recent.recent_node_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $type = NULL;
    if ($node = $this->getCurrentNode()) {
      $type = $node->hasField('recent_type') ? $node->get('recent_type')->getString() : NULL;
    }

    return [
      '#theme' => 'recent_menu_block',
      '#menu_items' => $this->recentNodeService->buildMenuItems($type, $node),
    ];
  }

  /**
   * Get the current node from the routing (if there is one).
   *
   * @return \Drupal\node\NodeInterface|null
   */
  private function getCurrentNode(): ?NodeInterface {
    if ($node = $this->routeMatch->getParameter('node')) {
      // If it is a node id and not the real node, load it from the database.
      if (!$node instanceof NodeInterface) {
        $node = $this->nodeStorage->load($node);
      }

      return $node;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cacheTags = parent::getCacheTags();

    // Get the cache tags from the current node.
    if ($node = $this->getCurrentNode()) {
      $cacheTags = Cache::mergeTags($cacheTags, $node->getCacheTags());
    }

    return $cacheTags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // If you depend on \Drupal::routeMatch(), you must set context of this
    // block with 'route' context tag. Every new route this block will rebuild.
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
