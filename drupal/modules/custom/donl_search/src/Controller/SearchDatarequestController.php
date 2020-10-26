<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchDatarequestController extends SearchController {

  protected const DEFAULT_SORT = 'sys_created desc';

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 data request', '@count data requests');
  }

}
