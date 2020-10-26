<?php

namespace Drupal\donl_statistics;

/**
 *
 */
interface getCurrentStatisticsInterface {

  /**
   * Get the latest dataset count (only if count >0) based on their source.
   *
   * This function queries the database for numbers over time for each data
   * source.
   *
   * @return int
   *   Integer with total number of datasets
   */
  public function datasets_totals($sTopic, $sKey): int;

  /**
   * Get the latest theme count (only if count >0) based on their source.
   *
   * This function queries the database for numbers over time for each data
   * source.
   *
   * @return int
   *   Integer with total number of themes
   */
  public function datasets_themecount(): int;

  /**
   * Get the dataset counts based on their source.
   *
   * This function queries the database for numbers over time for each data
   * source.
   *
   * @return array
   *   Array with results based on data source
   */
  public function datasets_by_source_catalog(): array;

  /**
   * Get the dataset counts based on their status.
   *
   * This function queries the database for numbers over time for each status.
   *
   * @return array
   *   Array with results based on status
   */
  public function datasets_by_status($source): array;

  /**
   * Get the owners, if source is empty get all, else get of community.
   *
   * @param string $source
   *
   * @return array
   */
  public function getDatasetOwners($source): array;

  /**
   * Get the qualities of te datasets.
   *
   * @param string $source
   *
   * @return array
   */
  public function getDatasetQualities($source): array;

  /**
   * Gets the most recent datasets.
   *
   * @param string $source
   *
   * @return array
   */
  public function getMostRecentDatasets($source): array;

  /**
   * Gets the dataset counts sorted by months.
   *
   * @return array
   */
  public function getMonthlyDatasetCount(): array;

  /**
   * Gets the dataset sources.
   *
   * @return array
   */
  public function getDatasetSources(): array;

  /**
   * Gets the dataset status sorted by months.
   *
   * @return array
   */
  public function getMonthlyDatasetStatus(): array;

}
