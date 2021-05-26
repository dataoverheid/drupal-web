<?php

namespace Drupal\donl_search\Controller\Group;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Group search dataset controller.
 */
class GroupSearchDatasetController extends GroupSearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'dataset';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 dataset', '@count datasets');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_search.group.view';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTemplate(string $routeName = ''): string {
    return 'donl_searchrecord_dataset';
  }

}
