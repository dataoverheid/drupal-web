<?php

namespace Drupal\donl_search\Controller\Organization;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Search communities within the organization.
 */
class OrganizationSearchCommunityController extends OrganizationSearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'community';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 community', '@count communities');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.organization.community.view';
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
    return 'donl_searchrecord_community';
  }

}
