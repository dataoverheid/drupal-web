<?php

namespace Drupal\donl_search\Controller\Catalog;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Catalog search dataset controller.
 */
class CatalogSearchDatasetController extends CatalogSearchController {

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
    return 'donl_search.catalog.view';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTemplate(string $routeName = ''): string {
    return 'donl_searchrecord_dataset';
  }

}
