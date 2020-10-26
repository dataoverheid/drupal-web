<?php

namespace Drupal\donl_statistics\Controller;

use Drupal\ckan\MappingService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_statistics\getCurrentStatistics;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class IWDashboardController extends ControllerBase {

  /**
   * The current statistics service.
   *
   * @var \Drupal\donl_statistics\getCurrentStatistics
   */
  private $currentStatistics;

  /**
   * The mappings service.
   *
   * @var \Drupal\ckan\MappingService
   */
  private $mappingService;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_statistics.current_statistics'),
      $container->get('ckan.mapping'),
      $container->get('language_manager'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   * IWDashboardController constructor.
   *
   * @param \Drupal\donl_statistics\getCurrentStatistics $currentStatistics
   *   The current statistics service.
   * @param \Drupal\ckan\MappingService $mappingService
   *   The mappings service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   *   The community resolver.
   */
  public function __construct(getCurrentStatistics $currentStatistics, MappingService $mappingService, LanguageManagerInterface $languageManager, CommunityResolverInterface $communityResolver) {
    $this->currentStatistics = $currentStatistics;
    $this->mappingService = $mappingService;
    $this->languageManager = $languageManager;
    $this->communityResolver = $communityResolver;
  }

  /**
   * @param \Drupal\node\NodeInterface $community|null
   *
   * @return array
   *   The render array.
   */
  public function content(NodeInterface $community = NULL): array {
    $source = 'https://data.overheid.nl';
    if ($community && ($communityEntity = $this->communityResolver->nodeToCommunity($community))) {
      $source = $communityEntity->getIdentifier();
    }

    $datasetOwners = ['jsOwners' => [], 'jsOwnerTotal' => 0, 'otherOwners' => [], 'otherOwnersTotal' => 0];

    foreach ($this->currentStatistics->getDatasetOwners($source) as $authority) {
      $arr = [
        'name' => $this->mappingService->getOrganizationName($authority['name']),
        'value' => $authority['value'],
      ];
      if (count($datasetOwners['jsOwners']) < 11) {
        $datasetOwners['jsOwners'][] = $arr;
        $datasetOwners['jsOwnerTotal'] += $authority['value'];
      }
      else {
        $datasetOwners['otherOwners'][] = $arr;
        $datasetOwners['otherOwnersTotal'] += $authority['value'];
      }
    }

    $datasetOwners['jsOwnersCount'] = count($datasetOwners['jsOwners']);
    $datasetOwners['otherOwnersCount'] = count($datasetOwners['otherOwners']);

    if ($datasetOwners['otherOwnersTotal']) {
      $datasetOwners['jsOwners'][] = ['name' => $this->t('Rest of owners'), 'value' => $datasetOwners['otherOwnersTotal']];

      $datasetOwners['table'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Amount'),
        ],
        '#rows' => $datasetOwners['otherOwners'],
        '#attributes' => ['class' => ['table--condensed']],
      ];
    }
    $rows = [];
    foreach ($this->currentStatistics->datasets_by_status($source) as $status) {
      $rows[] = [
        'Status' => $this->mappingService->getStatusName($status['name']),
        'Amount' => $status['value'],
      ];
    }

    $statusTable = [
      '#type' => 'table',
      '#header' => [
        $this->t('Status'),
        $this->t('Amount'),
      ],
      '#rows' => $rows,
    ];

    $rows = [];
    foreach ($this->currentStatistics->getDatasetQualities($source) as $status) {
      $rows[] = [
        'Quality' => $this->mappingService->getQualityName($status['name']),
        'Amount' => $status['value'],
      ];
    }

    $qualityTable = [
      '#type' => 'table',
      '#header' => [
        $this->t('Quality'),
        $this->t('Amount of datasets'),
      ],
      '#rows' => $rows,
    ];

    $mostRecent = NULL;
    if ($recentDatasets = $this->currentStatistics->getMostRecentDatasets($source)) {
      $mostRecent = [
        '#theme' => 'item_list',
        '#attributes' => ['class' => 'list list--linked'],
      ];
      foreach ($recentDatasets as $recent) {
        $mostRecent['#items'][] = Link::fromTextAndUrl($recent['name'], Url::fromUri($recent['name']));
      }
    }

    return [
      '#theme' => 'IWDashboard',
      '#dataset_owners' => $datasetOwners,
      '#status_table' => $statusTable,
      '#dataset_quality' => $qualityTable,
      '#most_recent' => $mostRecent,
      '#attached' => [
        'library' => [
          'donl_statistics/pieChart',
        ],
        'drupalSettings' => ['pieData' => $datasetOwners['jsOwners']],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }

  /**
   *
   */
  public function title(NodeInterface $community = NULL): string {
    return $this->t('Monitor') . ($community ? ': ' . $community->getTitle() : '');
  }

  /**
   *
   */
  public function monthlyDatasetsGraph(): array {
    $sets = array_values($this->currentStatistics->getMonthlyDatasetCount());

    return [
      '#theme' => 'lineGraph',
      '#attached' => [
        'library' => [
          'donl_statistics/monthgraph',
        ],
        'drupalSettings' => ['graphData' => $sets],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }

  /**
   *
   */
  public function datasetSourcesGraph(): array {
    $jsData = [];
    foreach ($this->currentStatistics->getDatasetSources() as $sets) {
      $jsData[] = [
        'name' => $this->mappingService->getSourceCatalogName($sets['name']),
        'value' => $sets['value'],
      ];
    }
    return [
      '#theme' => 'pieChart',
      '#attached' => [
        'library' => [
          'donl_statistics/pieChart',
        ],
        'drupalSettings' => ['pieData' => $jsData],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }

  /**
   *
   */
  public function monthlyDatasetStatusChart(): array {
    $jsData = $this->currentStatistics->getMonthlyDatasetStatus();
    $sortedData = [];
    foreach ($jsData as $item) {
      $sortedData[] = [
        'month' => $item['name'],
        'beschikbaar' => $item['http://data.overheid.nl/status/beschikbaar'] ?? 0,
        'in_onderzoek' => $item['http://data.overheid.nl/status/in_onderzoek'] ?? 0,
        'niet_beschikbaar' => $item['http://data.overheid.nl/status/niet_beschikbaar'] ?? 0,
      ];
    }

    return [
      '#theme' => 'stackedBarChart',
      '#attached' => [
        'library' => [
          'donl_statistics/stackedBarChart',
        ],
        'drupalSettings' => ['barData' => $sortedData],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }

}
