<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchNewsController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'news';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.news';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 news item', '@count news items');
  }

}
