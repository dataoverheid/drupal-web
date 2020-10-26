<?php

namespace Drupal\donl_statistics\Commands;

use Drupal\donl_statistics\CkanStatisticsInterface;
use Drupal\donl_statistics\NodeStatisticsInterface;
use Drupal\donl_statistics\PiwikStatisticsInterface;
use Drupal\donl_statistics\StatisticsStorageInterface;
use Drush\Commands\DrushCommands;

/**
 *
 */
class CollectStatistics extends DrushCommands {

  /**
   * @var \Drupal\donl_statistics\NodeStatisticsInterface
   */
  protected $nodeStatistics;

  /**
   * @var \Drupal\donl_statistics\CkanStatisticsInterface
   */
  protected $ckanStatistics;

  /**
   * @var \Drupal\donl_statistics\PiwikStatisticsInterface
   */
  protected $piwikStatistics;

  /**
   * @var \Drupal\donl_statistics\StatisticsStorageInterface
   */
  protected $statisticsStorage;

  /**
   *
   */
  public function __construct(NodeStatisticsInterface $nodeStatistics, CkanStatisticsInterface $ckanStatistics, PiwikStatisticsInterface $piwikStatistics, StatisticsStorageInterface $statisticsStorage) {
    parent::__construct();
    $this->nodeStatistics = $nodeStatistics;
    $this->ckanStatistics = $ckanStatistics;
    $this->piwikStatistics = $piwikStatistics;
    $this->statisticsStorage = $statisticsStorage;
  }

  /**
   * Collect the DONL statistics.
   *
   * @command donl_statistics:collect
   * @aliases donl_statistics:collect
   * @usage donl_statistics:collect
   *   Collect the DONL statistics.
   */
  public function collect() {
    // Stats from CKAN.
    if ($stats = $this->ckanStatistics->get()) {
      foreach ($stats as $values) {
        $this->statisticsStorage->write($values);
      }
    }

    // Community stats from CKAN.
    foreach ($this->nodeStatistics->getCommunityIdentifiers() as $identifier) {
      if ($stats = $this->ckanStatistics->get($identifier)) {
        foreach ($stats as $values) {
          $this->statisticsStorage->write($values);
        }
      }
    }

    // Datasets per layer.
    foreach ($this->nodeStatistics->getOrganizationLayers() as $layer => $organizations) {
      $this->statisticsStorage->write([
        'topic' => 'datasets_per_layer',
        'key' => $layer,
        'value' => $this->ckanStatistics->getDatasetCountPerLayer($organizations),
        'source' => 'https://data.overheid.nl',
        'date' => strtotime('yesterday'),
      ]);
    }

    // Application stats.
    $this->statisticsStorage->write([
      'topic' => 'appliance',
      'key' => 'count',
      'value' => $this->nodeStatistics->getApplicationCount(),
      'source' => 'https://data.overheid.nl',
      'date' => strtotime('yesterday'),
    ]);

    // Datarequest stats.
    $this->statisticsStorage->write([
      'topic' => 'datarequest_open',
      'key' => 'count',
      'value' => $this->nodeStatistics->getNumberOfDatarequests('open'),
      'source' => 'https://data.overheid.nl',
      'date' => strtotime('yesterday'),
    ]);
    $this->statisticsStorage->write([
      'topic' => 'datarequest_done',
      'key' => 'count',
      'value' => $this->nodeStatistics->getNumberOfDatarequests('done'),
      'source' => 'https://data.overheid.nl',
      'date' => strtotime('yesterday'),
    ]);

    // Stats from Piwik.
    if ($stats = $this->piwikStatistics->getUniqueVisitors()) {
      $this->statisticsStorage->write($stats);
    }
    if ($stats = $this->piwikStatistics->getPopulairDatasets()) {
      foreach ($stats as $values) {
        $this->statisticsStorage->write($values);
      }
    }
    if ($stats = $this->piwikStatistics->getMostUsedSearches()) {
      foreach ($stats as $values) {
        $this->statisticsStorage->write($values);
      }
    }
  }

}
