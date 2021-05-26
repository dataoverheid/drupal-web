<?php

namespace Drupal\donl_search\Controller\Organization;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search groups within the organization.
 */
class OrganizationSearchGroupController extends OrganizationSearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'group';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 popular group of datasets', '@count popular groups of datasets');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.organization.group.view';
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
    return 'donl_searchrecord_group';
  }

}
