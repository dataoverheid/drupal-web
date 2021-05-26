<?php

namespace Drupal\donl_search\Controller\Organization;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search datarequests within the organization.
 */
class OrganizationSearchDatarequestController extends OrganizationSearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'datarequest';
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
  protected function getRouteName(): string {
    return 'donl_search.organization.datarequest.view';
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
