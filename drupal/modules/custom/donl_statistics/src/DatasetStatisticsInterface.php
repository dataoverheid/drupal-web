<?php

namespace Drupal\donl_statistics;

/**
 *
 */
interface DatasetStatisticsInterface {

  /**
   * Get the statistics.
   *
   * @param string|null $communityIdentifier
   *
   * @return array
   */
  public function get(string $communityIdentifier = NULL): array;

}
