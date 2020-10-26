<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchDatasetController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'dataset';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.dataset';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 dataset', '@count datasets');
  }

}
