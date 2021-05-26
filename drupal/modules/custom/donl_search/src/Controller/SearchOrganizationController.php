<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search organization controller.
 */
class SearchOrganizationController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'organization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.search.organization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 organization', '@count organizations');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSort(): string {
    return 'score desc,title asc';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTemplate(string $routeName = ''): string {
    return 'donl_searchrecord_organization';
  }

}
