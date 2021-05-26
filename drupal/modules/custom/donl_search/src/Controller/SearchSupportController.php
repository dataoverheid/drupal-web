<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search support controller.
 */
class SearchSupportController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'support';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.search.support';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 support page', '@count support pages');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTemplate(string $routeName = ''): string {
    return 'donl_searchrecord_support';
  }

}
