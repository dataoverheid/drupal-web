<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchCommunityController extends SearchController {

  protected const DEFAULT_SORT = 'sys_created desc';

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'community';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.community';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 community', '@count communities');
  }

}
