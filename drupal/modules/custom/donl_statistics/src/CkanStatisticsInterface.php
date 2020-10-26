<?php

namespace Drupal\donl_statistics;

/**
 *
 */
interface CkanStatisticsInterface {

  /**
   * Get the statistics.
   *
   * @param string|null $communityIdentifier
   *
   * @return array
   */
  public function get(string $communityIdentifier = NULL): array;

  /**
   * Get dataset count per organization layer.
   *
   * @param array $organizations
   *
   * @return int
   */
  public function getDatasetCountPerLayer(array $organizations): int;

  /**
   * Splits the statistics by topic.
   *
   * @param array $statistics
   *
   * @return array
   */
  public function splitByTopic(array $statistics): array;

}
