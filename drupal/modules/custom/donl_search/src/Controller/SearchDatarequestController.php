<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search datarequest controller.
 */
class SearchDatarequestController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.search.datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 data request', '@count data requests');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSort(): string {
    return 'score desc,sys_created desc';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTemplate(string $routeName = ''): string {
    return 'donl_searchrecord_datarequest';
  }

}
