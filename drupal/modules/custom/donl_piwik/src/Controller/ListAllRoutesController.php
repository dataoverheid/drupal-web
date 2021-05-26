<?php

namespace Drupal\donl_piwik\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * List all routes Controller.
 */
class ListAllRoutesController extends ControllerBase {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('router.route_provider')
    );
  }

  /**
   * ListAllRoutesController constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   */
  public function __construct(Connection $connection, RouteProviderInterface $routeProvider) {
    $this->connection = $connection;
    $this->routeProvider = $routeProvider;
  }

  /**
   * Build page.
   */
  public function content() {
    $ignoreRoutes = [
      '<.*>',
      'admin_toolbar\.run\.cron',
      'block\.category_autocomplete',
      'block_content\..*',
      'contextual\.render',
      'donl_search\.autocomplete',
      'editor\..*',
      'entity\.block_content\..*',
      'entity\.node\.preview',
      'entity\.node\.revision',
      'entity\.node\.version_history',
      '.*\.content_translation_add',
      '.*\.content_translation_delete',
      '.*\.content_translation_edit',
      '.*\.content_translation_overview',
      'entity\.webform\.assets\..*',
      'entity\.webform\.test_form',
      'entity\.webform\.user\.submission.*',
      'entity\.webform\.user\.drafts',
      'file\.ajax_progress',
      'filter\..*',
      'image\..*',
      'simple_sitemap\..*',
      'system\..*',
      'token\..*',
      'user\.well-known\.change_password',
      'views\.ajax',
      'webform\.element\.autocomplete',
      'webform\.element\.message\.close',
    ];

    $rows = [];
    foreach ($this->routeProvider->getAllRoutes() as $k => $v) {
      $path = $v->getPath();
      // Ignore all admin paths and some specific routes.
      if (strpos($path, '/admin/') !== 0 && !preg_match('/^(' . implode(')|(', $ignoreRoutes) . ')$/', $k)) {
        $rows[$k] = [NULL, $k, $path];
      }
    }

    $dataLayer = $this->connection->select('donl_piwik_datalayer', 'd')
      ->fields('d', ['route'])
      ->execute()
      ->fetchAllKeyed(0, 0);
    $keys = array_keys($rows);
    foreach ($dataLayer as $route) {
      foreach (array_values(preg_grep('/^' . str_replace('.', '\.', $route) . '/', $keys)) as $i) {
        $rows[$i][0] = 'x';
      }
    }

    return [
      [
        '#theme' => 'table',
        '#header' => ['', 'Route name', 'Path'],
        '#rows' => $rows,
        '#cache' => [
          'max-age' => 86400,
        ],
      ],
    ];
  }

}
