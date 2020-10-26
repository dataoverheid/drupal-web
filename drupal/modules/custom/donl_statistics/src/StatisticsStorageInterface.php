<?php

namespace Drupal\donl_statistics;

/**
 *
 */
interface StatisticsStorageInterface {

  /**
   * Write rows to statistics storage.
   *
   * @param array $row
   *
   * @return bool
   */
  public function write(array $row): bool;

}
