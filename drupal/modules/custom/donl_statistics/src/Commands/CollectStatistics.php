<?php

namespace Drupal\donl_statistics\Commands;

use Drupal\Core\Database\Connection;
use Drupal\donl_statistics\DatasetStatisticsInterface;
use Drupal\donl_statistics\NodeStatisticsInterface;
use Drupal\donl_statistics\PiwikStatisticsInterface;
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
   * @var \Drupal\donl_statistics\DatasetStatisticsInterface
   */
  protected $datasetStatistics;

  /**
   * @var \Drupal\donl_statistics\PiwikStatisticsInterface
   */
  protected $piwikStatistics;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * CollectStatistics constructor.
   *
   * @param \Drupal\donl_statistics\NodeStatisticsInterface $nodeStatistics
   * @param \Drupal\donl_statistics\DatasetStatisticsInterface $datasetStatistics
   * @param \Drupal\donl_statistics\PiwikStatisticsInterface $piwikStatistics
   * @param \Drupal\Core\Database\Connection $connection
   */
  public function __construct(NodeStatisticsInterface $nodeStatistics, DatasetStatisticsInterface $datasetStatistics, PiwikStatisticsInterface $piwikStatistics, Connection $connection) {
    parent::__construct();
    $this->nodeStatistics = $nodeStatistics;
    $this->datasetStatistics = $datasetStatistics;
    $this->piwikStatistics = $piwikStatistics;
    $this->connection = $connection;
  }

  /**
   * Collect the DONL statistics.
   *
   * @command donl_statistics:collect
   * @aliases collect-donl-statistics
   * @usage donl_statistics:collect
   *   Collect the DONL statistics.
   */
  public function collect() {
    // Stats from CKAN.
    if ($stats = $this->datasetStatistics->get()) {
      foreach ($stats as $values) {
        $this->mergeRow($values);
      }
    }

    // Community stats from CKAN.
    foreach ($this->nodeStatistics->getCommunityIdentifiers() as $identifier) {
      if ($stats = $this->datasetStatistics->get($identifier)) {
        foreach ($stats as $values) {
          $this->mergeRow($values);
        }
      }
    }

    // Application stats.
    $this->mergeRow([
      'topic' => 'appliance',
      'key' => 'count',
      'value' => $this->nodeStatistics->getApplicationCount(),
      'source' => 'https://data.overheid.nl',
      'date' => strtotime('yesterday'),
    ]);

    // Datarequest stats.
    $this->mergeRow([
      'topic' => 'datarequest_open',
      'key' => 'count',
      'value' => $this->nodeStatistics->getNumberOfDatarequests('open'),
      'source' => 'https://data.overheid.nl',
      'date' => strtotime('yesterday'),
    ]);
    $this->mergeRow([
      'topic' => 'datarequest_done',
      'key' => 'count',
      'value' => $this->nodeStatistics->getNumberOfDatarequests('done'),
      'source' => 'https://data.overheid.nl',
      'date' => strtotime('yesterday'),
    ]);

    // Stats from Piwik.
    //phpcs:disable
    /*if ($stats = $this->piwikStatistics->getUniqueVisitors()) {
      $this->mergeRow($stats);
    }
    if ($stats = $this->piwikStatistics->getPopulairDatasets()) {
      foreach ($stats as $values) {
        $this->mergeRow($values);
      }
    }
    if ($stats = $this->piwikStatistics->getMostUsedSearches()) {
      foreach ($stats as $values) {
        $this->mergeRow($values);
      }
    }*/
    //phpcs:enable
  }

  /**
   * Merge the row into the database.
   *
   * @param array $row
   *   The row with values.
   */
  public function mergeRow(array $row) {
    $this->connection->merge('donl_statistics')
      ->keys([
        'topic' => $row['topic'],
        'key' => $row['key'],
        'source' => $row['source'],
        'date' => $row['date'],
      ])
      ->fields([
        'value' => $row['value']
      ])
      ->execute();
  }

}
