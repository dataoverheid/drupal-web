<?php

namespace Drupal\donl_search\Controller;

use Drupal\donl_search\Form\SearchForm;
use Drupal\node\NodeInterface;

/**
 *
 */
class SearchDatasetCatalogController extends SearchDatasetContentTypeController {

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.catalog.view';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteParams() {
    $catalog = $this->routeMatch->getParameter('catalog');
    return ['catalog' => $catalog->get('machine_name')->getValue()[0]['value']];
  }

  /**
   * {@inheritdoc}
   */
  protected function getHiddenFacets() {
    $facets = parent::getHiddenFacets();

    $catalog = $this->routeMatch->getParameter('catalog');
    $facets['facet_catalog'][] = $catalog->get('identifier')->getValue()[0]['value'];
    return $facets;
  }

  /**
   * Get the title for the page.
   */
  public function getTitle(NodeInterface $catalog) {
    return $catalog->getTitle();
  }

  /**
   * Create the Catalog page.
   *
   * @param \Drupal\node\NodeInterface $catalog
   *   The catalog node.
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function view(NodeInterface $catalog, $page, $recordsPerPage) {
    $panels = $tabs = [];
    $panels['datasets'] = [
      '#theme' => 'panel',
      '#id' => 'datasets',
      '#content' => $this->content($page, $recordsPerPage),
    ];
    $tabs['panel-datasets'] = $this->t('Datasets');

    return [
      '#theme' => 'catalog',
      '#node' => $catalog,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all catalogs'), 'donl_search.search.catalog'),
      '#editLinks' => $this->getEditLinks($catalog),
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

}
