<?php

namespace Drupal\donl_statistics;

/**
 *
 */
interface PiwikStatisticsInterface {

  /**
   *
   */
  public function getMostUsedSearches();

  /**
   *
   */
  public function getPopulairDatasets();

  /**
   *
   */
  public function getUniqueVisitors();

}
