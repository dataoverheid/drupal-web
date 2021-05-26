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
 *  id = "donl_homepage_impact_items_block",
 *  admin_label = @Translation("Impact items block Block"),
 *  category = @Translation("DONL"),
 * )
 */
class ImpactItemsBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
      $container->get('donl_recent.recent_node_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'recent_index',
    ];

    $build['#attached']['library'][] = 'donl_recent/recent';
    $build['#attached']['drupalSettings']['recent']['category'] = 'impact-story';

    return $build;
  }

}
