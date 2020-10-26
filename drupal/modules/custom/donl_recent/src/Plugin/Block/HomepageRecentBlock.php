<?php

namespace Drupal\donl_recent\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\donl_recent\RecentNodeServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the menu block for recent nodes.
 *
 * @Block(
 *  id = "donl_homepage_recent_menu_block",
 *  admin_label = @Translation("Homepage Recent Menu Block"),
 *  category = @Translation("DONL"),
 * )
 */
class HomepageRecentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The 'recent' node service.
   *
   * @var \Drupal\donl_recent\RecentNodeServiceInterface
   */
  private $recentNodeService;

  /**
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RecentNodeServiceInterface $recentNodeService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('donl_recent.recent_node_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $nodes = $this->recentNodeService->getNodes(NULL, 3);
    $items = [];
    foreach ($nodes as $key => $node) {
      $items[$key]['id'] = $node->id();
      $items[$key]['title'] = $node->get('title')->getString();
      $items[$key]['body'] = $node->get('body');
      $body = $node->get('body')->getValue();
      if ($body) {
        $body = $body[0]['summary'] ?? $body[0]['value'];

        if (strlen($body) > 190) {
          $body = str_split($body, 190)[0] . '...';
        }
      }
      $items[$key]['body'] = $body;
    }

    return [
      '#theme' => 'home_recent_menu_block',
      '#items' => $items,
      '#attributes' => ['class' => ['teaser-block']],
      '#cache' => [
        'tags' => [
          'node_type:recent',
        ],
      ],

    ];
  }

}
