<?php

namespace Drupal\donl_search\Controller\Catalog;

use Drupal\Core\Url;
use Drupal\donl_search\Controller\SearchController;
use Drupal\donl_search\Form\SearchForm;
use Drupal\node\NodeInterface;

/**
 * Catalog search controller.
 */
Abstract class CatalogSearchController extends SearchController {

  /**
   * Create the edit links.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return array
   *   A Drupal render array.
   */
  protected function getEditLinks(NodeInterface $node): array {
    $editLinks = [];
    if (($user = $this->userStorage->load($this->currentUser()->id())) && $user->isAdministrator()) {
      $editLinks['view'] = [
        '#type' => 'link',
        '#title' => $this->t('View'),
        '#url' => Url::fromRoute($this->getRouteName(), $this->getRouteParams()),
        '#attributes' => [
          // Currently this link is always active.
          'class' => ['buttonswitch__button', 'is-active'],
        ],
      ];
      $editLinks['edit'] = [
        '#type' => 'link',
        '#title' => $this->t('Edit'),
        '#url' => Url::fromRoute('entity.node.edit_form', ['node' => $node->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
      $editLinks['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromRoute('entity.node.delete_form', ['node' => $node->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
      $editLinks['translate'] = [
        '#type' => 'link',
        '#title' => $this->t('Translate'),
        '#url' => Url::fromRoute('entity.node.content_translation_overview', ['node' => $node->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
    }

    return $editLinks;
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteParams(): array {
    $catalog = $this->routeMatch->getParameter('catalog');
    return ['catalog' => $catalog->get('machine_name')->getValue()[0]['value']];
  }

  /**
   * {@inheritdoc}
   */
  protected function getHiddenFacets(): array {
    $facets = parent::getHiddenFacets();

    $catalog = $this->routeMatch->getParameter('catalog');
    $facets['facet_catalog'][] = $catalog->get('identifier')->getValue()[0]['value'];
    return $facets;
  }

  /**
   * Get the title for the page.
   *
   * @param \Drupal\node\NodeInterface $catalog
   *   The catalog node.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
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
  public function view(NodeInterface $catalog, $page, $recordsPerPage): array {
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
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all @type', ['@type' => $this->t('catalogs')]), 'donl_search.search.catalog'),
      '#editLinks' => $this->getEditLinks($catalog),
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

}
