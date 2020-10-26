<?php

namespace Drupal\donl_search\Controller;

/**
 *
 */
class SearchCatalogController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'catalog';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.search.catalog';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 catalog', '@count catalogs');
  }

}
