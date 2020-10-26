<?php

namespace Drupal\donl_statistics;

use Drupal\ckan\CkanRequestInterface;
use Drupal\Core\Url;

/**
 *
 */
class CkanStatistics implements CkanStatisticsInterface {

  protected $facets = [
    'facet_dataset_status',
    'facet_authority',
    'facet_theme',
    'facet_high_value',
    'facet_referentie_data',
    'facet_basis_register',
    'facet_format',
    'facet_national_coverage',
    'facet_organisation',
    'facet_license',
    'facet_source_catalog',
  ];

  protected $customFacets = [
    'dataset_quality',
    'legal_foundation_ref',
    'conforms_to',
  ];

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   *
   */
  public function __construct(CkanRequestInterface $ckanRequest) {
    $this->ckanRequest = $ckanRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $communityIdentifier = NULL): array {
    $stats = [];
    $q = $communityIdentifier !== NULL ? 'facet_communities:"' . $communityIdentifier . '"' : '*:*';
    $results = $this->ckanRequest->searchDatasets(1, 10, $q, 'metadata_created desc', $this->facets, $this->customFacets);
    $date = strtotime('yesterday');

    // Dataset count.
    if (isset($results['count'])) {
      $stats[] = [
        'topic' => 'datasets',
        'key' => 'count',
        'value' => $results['count'],
        'source' => $communityIdentifier ?? 'https://data.overheid.nl',
        'date' => $date,
      ];
    }

    // Default facets.
    foreach ($this->facets as $facet) {
      if (isset($results['facets'][$facet])) {
        foreach ($results['facets'][$facet] as $facetKey => $facetValue) {
          $stats[] = [
            'topic' => $facet,
            'key' => $facetKey,
            'value' => $facetValue,
            'source' => $communityIdentifier ?? 'https://data.overheid.nl',
            'date' => $date,
          ];
        }
      }
    }

    // Custom facets.
    foreach ($this->customFacets as $facet) {
      if (isset($results['facets'][$facet])) {
        foreach ($results['facets'][$facet] as $facetKey => $facetValue) {
          $stats[] = [
            'topic' => 'facet_' . $facet,
            'key' => $facetKey,
            'value' => $facetValue,
            'source' => $communityIdentifier ?? 'https://data.overheid.nl',
            'date' => $date,
          ];
        }
      }
    }

    // Most recent datasets.
    if (isset($results['datasets'])) {
      foreach ($results['datasets'] as $idx => $dataset) {
        $stats[] = [
          'topic' => 'most_recent_dataset',
          'key' => 'https://data.overheid.nl' . Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getName()])
            ->toString(),
          'value' => $idx + 1,
          'source' => $communityIdentifier ?? 'https://data.overheid.nl',
          'date' => $date,
        ];
      }
    }

    return $stats;
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasetCountPerLayer(array $organizations): int {
    $total = 0;

    if ($results = $this->ckanRequest->searchDatasets(1, 0, '*:*', 'metadata_created desc', ['facet_authority'], [])) {
      $authorities = $results['facets']['facet_authority'] ?? [];

      foreach ($authorities as $authority => $count) {
        if (in_array($authority, $organizations, TRUE)) {
          $total += $count;
        }
      }
    }

    return $total;
  }

  /**
   * {@inheritdoc}
   */
  public function splitByTopic(array $statistics): array {
    $splittedStatistics = [];

    foreach ($statistics as $statistic) {
      $splittedStatistics[$statistic['topic']][] = $statistic;
    }

    return $splittedStatistics;
  }

}
