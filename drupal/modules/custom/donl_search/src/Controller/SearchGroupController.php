<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchGroupController extends SearchController {

  protected const DEFAULT_SORT = 'title asc';

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'group';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.group';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 group', '@count groups');
  }

}
