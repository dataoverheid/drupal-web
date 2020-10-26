<?php

namespace Drupal\donl_recent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\donl_recent\RecentNodeServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class RecentController extends ControllerBase {

  /**
   * The 'recent' node service.
   *
   * @var \Drupal\donl_recent\RecentNodeServiceInterface
   */
  private $recentNodeService;

  /**
   * RecentController constructor.
   *
   * @param \Drupal\donl_recent\RecentNodeServiceInterface $recentNodeService
   *   The 'recent' node service used to get the menu, nodes and title of
   *   the 'recent' nodes.
   */
  public function __construct(RecentNodeServiceInterface $recentNodeService) {
    $this->recentNodeService = $recentNodeService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_recent.recent_node_service')
    );
  }

  /**
   *
   */
  public function index(): array {
    $menuItems = [];
    foreach ($this->recentNodeService->getTypes() as $typeId => $typeConfig) {
      $menuItems[$typeId] = $this->recentNodeService->buildMenuItems($typeId, NULL, TRUE);
      $menuItems[$typeId]['sub_items'] = array_slice($menuItems[$typeId]['sub_items'] ?? [], 0, 3);
      $menuItems[$typeId]['img'] = $typeConfig['img'] ?? NULL;
      $menuItems[$typeId]['more_link'] = Link::createFromRoute($typeConfig['more_label'], 'donl_recent.type_overview', ['type' => $typeId]);
    }

    return [
      '#theme' => 'recent_index',
      '#title' => $this->t('Recent'),
      '#menu_items' => $menuItems,
    ];
  }

  /**
   * Show an overview of recent items (filtered by type).
   *
   * @param string|null $type
   *   The 'recent' type to filter on.
   *
   * @return array
   */
  public function overview(string $type = NULL): array {
    return [
      '#theme' => 'recent_overview',
      '#title' => $this->recentNodeService->getTitle($type),
      '#menu_items' => $this->recentNodeService->buildMenuItems($type),
      '#items' => $this->recentNodeService->getNodeTeasers($type),
    ];
  }

  /**
   * Show the title of the overview of recent items.
   *
   * @param string $type
   *   The current type of recent items to show.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function title(string $type = NULL): TranslatableMarkup {
    return $this->recentNodeService->getTitle($type);
  }

}
