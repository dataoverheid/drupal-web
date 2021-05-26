<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search news controller.
 */
class SearchNewsController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'news';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.search.news';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 news item', '@count news items');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTemplate(string $routeName = ''): string {
    return 'donl_searchrecord_news';
  }

}
