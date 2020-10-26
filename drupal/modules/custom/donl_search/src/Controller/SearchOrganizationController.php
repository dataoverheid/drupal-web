<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchOrganizationController extends SearchController {

  protected const DEFAULT_SORT = 'title asc';

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'organization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.organization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 organization', '@count organizations');
  }

}
