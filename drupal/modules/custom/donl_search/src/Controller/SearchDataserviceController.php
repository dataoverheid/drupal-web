<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search dataservice controller.
 */
class SearchDataserviceController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'dataservice';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.search.dataservice';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 dataservice', '@count dataservices');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTemplate(string $routeName = ''): string {
    return 'donl_searchrecord_dataservice';
  }

}
