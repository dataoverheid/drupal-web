<?php

namespace Drupal\donl_statistics\Controller;

use Drupal\ckan\MappingService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class IWDashboardController extends ControllerBase {

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The mappings service.
   *
   * @var \Drupal\ckan\MappingService
   */
  protected $mappingService;

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
      $container->get('database'),
      $container->get('ckan.mapping'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   * IWDashboardController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   * @param \Drupal\ckan\MappingService $mappingService
   *   The mappings service.
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   *   The community resolver.
   */
  public function __construct(Connection $database, MappingService $mappingService, CommunityResolverInterface $communityResolver) {
    $this->database = $database;
    $this->mappingService = $mappingService;
    $this->communityResolver = $communityResolver;
  }

  /**
   * Get the title.
   *
   * @param \Drupal\node\NodeInterface|null $community
   *   The community node.
   *
   * @return string
   *   The title.
   */
  public function getTitle(NodeInterface $community = NULL): string {
    return $this->t('Monitor') . ($community ? ': ' . $community->getTitle() : '');
  }

  /**
   * @param \Drupal\node\NodeInterface $community|null
   *   The community node.
   *
   * @return array
   *   The render array.
   */
  public function content(NodeInterface $community = NULL): array {
    $source = 'https://data.overheid.nl';
    if ($community && ($communityEntity = $this->communityResolver->nodeToCommunity($community))) {
      $source = $communityEntity->getIdentifier();
    }

    return [
      [
        $this->chartDatasetsPerOwner($source),
      ],
      [
        '#theme' => 'IWDashboard',
        '#status_table' => $this->tableDatasetsState($source),
        '#dataset_quality' => $this->tableDatasetsQuality($source),
        '#most_recent' => $this->listRecentDatasets($source),
        '#cache' => ['max-age' => 86000],
      ]
    ];
  }

  /**
   * Show a list with 'Recent Datasets'.
   *
   * @param string $source
   *   The source.
   *
   * @return array
   *   The render array.
   */
  private function listRecentDatasets(string $source): array {
    $subquery = $this->database->select('donl_statistics', 's');
    $subquery->addExpression('MAX(s.date)', 'maxdate');
    $subquery->condition('topic', 'most_recent_dataset', '=');
    $subquery->condition('source', $source, '=');

    $query = $this->database->select('donl_statistics', 's');
    $query->fields('s', ['key']);
    $query->condition('s.topic', 'most_recent_dataset', '=');
    $query->condition('s.source', $source, '=');
    $query->condition('s.date', $subquery, '=');
    $result = $query->execute();

    $items = [];
    while ($record = $result->fetchAssoc()) {
      $items[] = Link::fromTextAndUrl($record['key'], Url::fromUri($record['key']));
    }

    return [
      '#theme' => 'item_list',
      '#attributes' => ['class' => 'list list--linked'],
      '#items' => $items,
    ];
  }

  /**
   * Show the table for 'Datasets quality'.
   *
   * @param string $source
   *   The source.
   *
   * @return array
   *   The render array.
   */
  private function tableDatasetsQuality(string $source): array {
    $subquery = $this->database->select('donl_statistics', 's');
    $subquery->addExpression('MAX(s.date)', 'maxdate');
    $subquery->condition('topic', 'facet_dataset_quality', '=');
    $subquery->condition('source', $source, '=');

    $query = $this->database->select('donl_statistics', 's');
    $query->fields('s', ['key', 'value']);
    $query->condition('s.topic', 'facet_dataset_quality', '=');
    $query->condition('s.source', $source, '=');
    $query->condition('s.date', $subquery, '=');
    $result = $query->execute();

    $rows = [];
    while ($record = $result->fetchAssoc()) {
      $rows[] = [
        'Quality' => $this->mappingService->getQualityName((int) $record['key']),
        'Amount' => $record['value'],
      ];
    }
    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Quality'),
        $this->t('Amount of datasets'),
      ],
      '#rows' => $rows,
    ];
  }

  /**
   * Show the table for 'Datasets state'.
   *
   * @param string $source
   *   The source.
   *
   * @return array
   *   The render array.
   */
  private function tableDatasetsState(string $source): array {
    $subquery = $this->database->select('donl_statistics', 's');
    $subquery->addExpression('MAX(s.date)', 'maxdate');
    $subquery->condition('topic', 'facet_dataset_status', '=');
    $subquery->condition('source', $source, '=');

    $query = $this->database->select('donl_statistics', 's');
    $query->fields('s', ['key', 'value']);
    $query->condition('s.topic', 'facet_dataset_status', '=');
    $query->condition('s.source', $source, '=');
    $query->condition('s.date', $subquery, '=');
    $result = $query->execute();

    $rows = [];
    while ($record = $result->fetchAssoc()) {
      $rows[] = [
        'Status' => $this->mappingService->getStatusName($record['key']),
        'Amount' => $record['value'],
      ];
    }
    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Status'),
        $this->t('Amount'),
      ],
      '#rows' => $rows,
    ];
  }

  /**
   * Show the chart for 'Datasets per owner'.
   *
   * @param string $source
   *   The source.
   *
   * @return array
   *   The render array.
   */
  private function chartDatasetsPerOwner(string $source): array {
    $data = [
      'jsOwners' => [],
      'jsOwnerTotal' => 0,
      'otherOwners' => [],
      'otherOwnersTotal' => 0,
    ];

    $subQuery = $this->database->select('donl_statistics', 's');
    $subQuery->addExpression('MAX(s.date)', 'maxdate');
    $subQuery->condition('s.topic', 'facet_authority', '=');
    $subQuery->condition('s.value', 0, '>');
    $subQuery->condition('s.source', $source, '=');

    $query = $this->database->select('donl_statistics', 's');
    $query->fields('s', ['key', 'value']);
    $query->condition('s.topic', 'facet_authority', '=');
    $query->condition('s.source', $source, '=');
    $query->condition('s.value', 0, '>');
    $query->condition('s.date', $subQuery, '=');
    $query->orderBy('s.value', 'DESC');
    $result = $query->execute();

    while ($record = $result->fetchAssoc()) {
      $arr = [
        'name' => $this->mappingService->getOrganizationName($record['key']),
        'value' => $record['value'],
      ];
      if (count($data['jsOwners']) < 11) {
        $data['jsOwners'][] = $arr;
        $data['jsOwnerTotal'] += $record['value'];
      }
      else {
        $data['otherOwners'][] = $arr;
        $data['otherOwnersTotal'] += $record['value'];
      }
    }
    $data['jsOwnersCount'] = count($data['jsOwners']);
    $data['otherOwnersCount'] = count($data['otherOwners']);

    if ($data['otherOwnersTotal']) {
      $data['jsOwners'][] = [
        'name' => $this->t('Rest of owners'),
        'value' => $data['otherOwnersTotal'],
      ];

      $data['table'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Amount'),
        ],
        '#rows' => $data['otherOwners'],
        '#attributes' => ['class' => ['table--condensed']],
      ];
    }

    return [
      '#theme' => 'statistics_owners_per_chart',
      '#data' => $data,
      '#attached' => [
        'library' => [
          'donl_statistics/chartDatasetsPerOwner',
        ],
        'drupalSettings' => ['chartDatasetsPerOwner' => $data['jsOwners']],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }

  /**
   * Show the chart for 'Datasets per month'.
   *
   * @return array
   *   The render array.
   */
  public function chartDatasetsPerMonth(): array {
    $query = $this->database->select('donl_statistics', 's');
    $query->addExpression('ROUND(AVG(s.value), 0)', 'value');
    $query->addExpression("DATE_FORMAT(FROM_UNIXTIME(s.date), '%m')", 'month');
    $query->addExpression("DATE_FORMAT(FROM_UNIXTIME(s.date), '%Y')", 'year');
    $query->condition('s.topic', 'datasets', '=');
    $query->condition('s.source', 'https://data.overheid.nl', '=');
    $query->condition('s.date', strtotime('now -2 year'), '>=');
    $query->groupBy('year');
    $query->groupBy('month');
    $query->orderBy('year', 'ASC');
    $query->orderBy('month', 'ASC');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'date' => $record['month'] . '-' . $record['year'],
        'value' => $record['value'],
      ];
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'id' => 'chart-datasets-per-month',
        'class' => ['statistics-chart'],
      ],
      '#attached' => [
        'library' => [
          'donl_statistics/chartDatasetsPerMonth',
        ],
        'drupalSettings' => ['chartDatasetsPerMonth' => $data],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }

  /**
   * Show the chart for 'Datasets per catalog'.
   *
   * @return array
   *   The render array.
   */
  public function chartDatasetsPerCatalog(): array {
    $subQuery = $this->database->select('donl_statistics', 's');
    $subQuery->addExpression('MAX(s.date)', 'maxdate');
    $subQuery->condition('s.topic', 'facet_source_catalog', '=');
    $subQuery->condition('s.source', 'https://data.overheid.nl', '=');

    $query = $this->database->select('donl_statistics', 's');
    $query->fields('s', ['key', 'value']);
    $query->condition('s.topic', 'facet_source_catalog', '=');
    $query->condition('s.source', 'https://data.overheid.nl', '=');
    $query->condition('s.date', $subQuery, '=');
    $query->orderBy('s.value', 'DESC');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'name' => $this->mappingService->getSourceCatalogName($record['key']),
        'value' => $record['value'],
      ];
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'id' => 'chart-datasets-per-catalog',
        'class' => ['statistics-chart'],
      ],
      '#attached' => [
        'library' => [
          'donl_statistics/chartDatasetsPerCatalog',
        ],
        'drupalSettings' => ['chartDatasetsPerCatalog' => $data],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }


  /**
   * Show the chart for 'Datasets state'.
   *
   * @return array
   *   The render array.
   */
  public function chartDatasetsState(): array {
    $query = $this->database->select('donl_statistics', 's');
    $query->fields('s', ['key']);
    $query->addExpression('ROUND(AVG(s.value), 0)', 'value');
    $query->addExpression("DATE_FORMAT(FROM_UNIXTIME(s.date), '%m')", 'month');
    $query->addExpression("DATE_FORMAT(FROM_UNIXTIME(s.date), '%Y')", 'year');
    $query->condition('s.topic', 'facet_dataset_status', '=');
    $query->condition('s.source', 'https://data.overheid.nl', '=');
    $query->condition('s.date', strtotime('now -2 year'), '>=');
    $query->groupBy('s.key');
    $query->groupBy('year');
    $query->groupBy('month');
    $query->orderBy('year', 'ASC');
    $query->orderBy('month', 'ASC');
    $result = $query->execute();

    $values = [];
    while ($record = $result->fetchAssoc()) {
      $date = $record['month'] . '-' . $record['year'];
      $values[$date]['date'] = $record['month'] . '-' . $record['year'];
      $values[$date][$record['key']] = $record['value'];
    }

    $data = [];
    foreach ($values as $v) {
      $data[] = [
        'month' => $v['date'],
        'beschikbaar' => $v['http://data.overheid.nl/status/beschikbaar'] ?? 0,
        'in_onderzoek' => $v['http://data.overheid.nl/status/in_onderzoek'] ?? 0,
        'niet_beschikbaar' => $v['http://data.overheid.nl/status/niet_beschikbaar'] ?? 0,
      ];
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'id' => 'chart-datasets-state',
        'class' => ['statistics-chart'],
      ],
      '#attached' => [
        'library' => [
          'donl_statistics/chartDatasetsState',
        ],
        'drupalSettings' => ['chartDatasetsState' => $data],
      ],
      '#cache' => ['max-age' => 86000],
    ];
  }

}
