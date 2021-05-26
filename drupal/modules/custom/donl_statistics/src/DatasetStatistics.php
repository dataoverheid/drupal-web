<?php

namespace Drupal\donl_statistics;

use Drupal\ckan\CkanRequestInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\Core\Url;

/**
 *
 */
class DatasetStatistics implements DatasetStatisticsInterface {

  protected $facets = [
    'facet_status' => 'facet_dataset_status',
    'facet_authority' => 'facet_authority',
    'facet_theme' => 'facet_theme',
    'facet_high_value',
    'facet_referentie_data',
    'facet_basis_register',
    'facet_national_coverage',
    'facet_format' => 'facet_format',
    'facet_license' => 'facet_license',
    'facet_catalog' => 'facet_source_catalog',
  ];

  protected $classificationFacets = [
    'high_value' => 'facet_high_value',
    'referentie_data' => 'facet_referentie_data',
    'basis_register' => 'facet_basis_register',
    'national_coverage' => 'facet_national_coverage',
  ];

  protected $customFacets = [
    'dataset_quality',
    'legal_foundation_ref',
    'conforms_to',
  ];

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * CkanStatistics constructor.
   *
   * @param \Drupal\ckan\CkanRequestInterface $ckanRequest
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   */
  public function __construct(CkanRequestInterface $ckanRequest, SolrRequestInterface $solrRequest) {
    $this->ckanRequest = $ckanRequest;
    $this->solrRequest = $solrRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $communityIdentifier = NULL): array {
    $stats = [];
    $source = $communityIdentifier ?? 'https://data.overheid.nl';
    $date = strtotime('yesterday');

    $activeFacets = [];
    if ($communityIdentifier) {
      $activeFacets['facet_community'][] = $communityIdentifier;
    }
    $solrResult = $this->solrRequest->search(1, 10, NULL, NULL, 'dataset', $activeFacets);

    $q = $communityIdentifier !== NULL ? 'facet_communities:"' . $communityIdentifier . '"' : '*:*';
    $ckanResult = $this->ckanRequest->searchDatasets(1, 10, $q, 'metadata_created desc', [], $this->customFacets);

    // Dataset count.
    if (isset($solrResult['numFound'])) {
      $stats[] = [
        'topic' => 'datasets',
        'key' => 'count',
        'value' => $solrResult['numFound'],
        'source' => $source,
        'date' => $date,
      ];
    }

    // Solr facets.
    foreach ($this->facets as $facet => $key) {
      if (isset($solrResult['facets'][$facet])) {
        foreach ($solrResult['facets'][$facet] as $facetKey => $facetValue) {
          $stats[] = [
            'topic' => $key,
            'key' => $facetKey,
            'value' => $facetValue,
            'source' => $source,
            'date' => $date,
          ];
        }
      }
    }

    // Classification facets.
    foreach ($this->classificationFacets as $facet => $key) {
      if (isset($solrResult['facets']['facet_classification'][$facet])) {
        $stats[] = [
          'topic' => $key,
          'key' => $facet,
          'value' => $solrResult['facets']['facet_classification'][$facet],
          'source' => $source,
          'date' => $date,
        ];
      }
    }

    // Ckan facets.
    foreach ($this->customFacets as $facet) {
      if (isset($ckanResult['facets'][$facet])) {
        foreach ($ckanResult['facets'][$facet] as $facetKey => $facetValue) {
          $stats[] = [
            'topic' => 'facet_' . $facet,
            'key' => $facetKey,
            'value' => $facetValue,
            'source' => $source,
            'date' => $date,
          ];
        }
      }
    }

    // Most recent datasets.
    if (isset($ckanResult['datasets'])) {
      foreach ($ckanResult['datasets'] as $idx => $dataset) {
        $stats[] = [
          'topic' => 'most_recent_dataset',
          'key' => 'https://data.overheid.nl' . Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getName()])->toString(),
          'value' => $idx + 1,
          'source' => $source,
          'date' => $date,
        ];
      }
    }

    return $stats;
  }

}
