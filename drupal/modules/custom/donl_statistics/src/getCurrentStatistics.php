<?php

namespace Drupal\donl_statistics;

use Drupal\Core\Database\Connection;
use PDO;

/**
 * Class getCurrentStatistics.
 *
 * @package Drupal\donl_statistics
 */
class getCurrentStatistics {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   *
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   *
   */
  public function datasets_totals($sTopic, $sKey): int {
    $query = $this->database
      ->select('donl_statistics', 'stats');

    $query
      ->addExpression('stats.value', 'datasets_total');

    // Add subquery for max date.
    $subquery = $this->database
      ->select('donl_statistics', 'stats_max_date');
    $subquery->addExpression('MAX(date)', 'maxdate');

    $query
      ->fields('stats')
      ->condition('topic', $sTopic)
      ->condition('key', $sKey)
      ->condition('source', $this->source)
      ->condition('`date`', $subquery);

    return $query->execute()->fetchField(3);
  }

  /**
   *
   */
  public function datasets_themecount(): int {
    $query = $this->database
      ->select('donl_statistics', 'stats');

    // Add subquery for max date.
    $subquery = $this->database
      ->select('donl_statistics', 'stats_max_date');
    $subquery->addExpression('MAX(date)', 'maxdate');

    $query
      ->fields('stats')
      ->condition('topic', 'facet_theme')
      ->condition('source', $this->source)
      ->condition('`date`', $subquery)
      ->condition('value', '0', '>');

    return count($query->execute()->fetchAll());
  }

  /**
   *
   */
  public function datasets_by_source_catalog($source): array {
    $query = $this->database
      ->select('donl_statistics', 'stats');

    $query
      ->addExpression('DATE_FORMAT(FROM_UNIXTIME(stats.date), \'%m-%Y\')', 'month');
    $query
      ->addExpression('MAX(stats.value)', 'value');
    $query->addField('stats', 'key', 'key');

    $query
      ->condition('topic', 'facet_source_catalog')
      ->condition('source', $source);

    if ($this->source !== 'https://data.overheid.nl') {
      $query->condition('stats.value', 0, '>');
    }

    $query->groupBy('month');
    $query->groupBy('stats.key');

    $months = [];
    $data = [];

    foreach ($query->execute()->fetchAll(PDO::FETCH_ASSOC) as $result) {
      $months[$result['month']][$result['key']] = $result['value'];
    }

    foreach ($months as $month => $result) {
      $data[] = ['month' => $month] + $result;
    }

    return $data;
  }

  /**
   *
   */
  public function datasets_by_status($source): array {
    $query = $this->database
      ->select('donl_statistics', 'stats');

    $query->addField('stats', 'key', 'name');
    $query->addField('stats', 'value');

    // Add subquery for max date.
    $subquery = $this->database
      ->select('donl_statistics', 'stats_max_date');
    $subquery->addExpression('MAX(date)', 'maxdate');

    $query
      ->condition('topic', 'facet_dataset_status')
      ->condition('source', $source)
      ->condition('`date`', $subquery);

    return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   *
   */
  public function getDatasetOwners($source, $minimalDatasets = 0): array {
    $query = $this->database->select('donl_statistics', 'stats');
    $query->addField('stats', 'key', 'name');
    $query->addField('stats', 'value');

    $query->condition('topic', 'facet_authority');
    $query->condition('value', $minimalDatasets, '>');
    $query->condition('source', $source);

    $subquery = $this->database
      ->select('donl_statistics', 'stats_max_date');
    $subquery->addExpression('MAX(date)', 'maxdate');
    $query->condition('`date`', $subquery);

    $query->orderBy('value', 'DESC');

    return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   *
   */
  public function getDatasetQualities($source): array {
    $query = $this->database->select('donl_statistics', 'stats');
    $query->addField('stats', 'key', 'name');
    $query->addField('stats', 'value');

    $query->condition('topic', 'facet_dataset_quality');
    $query->condition('source', $source);

    $subquery = $this->database
      ->select('donl_statistics', 'stats_max_date');
    $subquery->addExpression('MAX(date)', 'maxdate');
    $query->condition('`date`', $subquery);

    return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   *
   */
  public function getMostRecentDatasets($source): array {
    $query = $this->database->select('donl_statistics', 'stats');
    $query->addField('stats', 'key', 'name');

    $query->condition('topic', 'most_recent_dataset');
    $query->condition('source', $source);

    $subquery = $this->database
      ->select('donl_statistics', 'stats_max_date');
    $subquery->addExpression('MAX(date)', 'maxdate');
    $query->condition('`date`', $subquery);

    return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   *
   */
  public function getMonthlyDatasetCount(): array {
    $query = $this->database->select('donl_statistics', 'stats');
    $query->addField('stats', 'value');
    $query->addExpression("DATE_FORMAT(FROM_UNIXTIME(date), '%m-%Y')", 'month');
    $query->condition('topic', 'datasets');
    $query->condition('source', 'https://data.overheid.nl');
    $sets = $query->distinct()->execute()->fetchAll(PDO::FETCH_ASSOC);

    $sortedSets = [];
    foreach ($sets as $set) {
      $sortedSets[$set['month']] = $set;
    }
    ksort($sortedSets);
    return $sortedSets;
  }

  /**
   *
   */
  public function getDatasetSources(): array {
    $query = $this->database->select('donl_statistics', 'stats');
    $query->addField('stats', 'key', 'name');
    $query->addField('stats', 'value');

    $query->condition('topic', 'facet_source_catalog');
    $query->condition('source', 'https://data.overheid.nl');

    $subquery = $this->database
      ->select('donl_statistics', 'stats_max_date');
    $subquery->addExpression('MAX(date)', 'maxdate');
    $query->condition('`date`', $subquery);

    $query->orderBy('value', 'Desc');

    return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   *
   */
  public function getMonthlyDatasetStatus(): array {
    $query = $this->database->select('donl_statistics', 'stats');
    $query->addField('stats', 'value');
    $query->addField('stats', 'key');
    $query->addExpression("DATE_FORMAT(FROM_UNIXTIME(date), '%m-%Y')", 'month');

    $query->condition('topic', 'facet_dataset_status');
    $query->condition('source', 'https://data.overheid.nl');
    $query->orderBy('date', 'ASC');

    $sets = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

    $sortedSets = [];
    foreach ($sets as $set) {
      $sortedSets[$set['month']]['name'] = $set['month'];
      $sortedSets[$set['month']][$set['key']] = $set['value'];
    }

    return $sortedSets;
  }

}
