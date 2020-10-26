<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchSupportController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'support';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.support';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 support page', '@count support pages');
  }

}
