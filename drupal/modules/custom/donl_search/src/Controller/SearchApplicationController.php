<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchApplicationController extends SearchController {

  protected const DEFAULT_SORT = 'sys_created desc';

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 application', '@count applications');
  }

}
