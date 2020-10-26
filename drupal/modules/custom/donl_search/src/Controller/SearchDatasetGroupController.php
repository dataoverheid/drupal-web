<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\Url;
use Drupal\donl_search\Form\SearchForm;
use Drupal\node\NodeInterface;

/**
 *
 */
class SearchDatasetGroupController extends SearchDatasetContentTypeController {

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.group.view';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteParams() {
    $group = $this->routeMatch->getParameter('group');
    return ['group' => $group->get('machine_name')->getValue()[0]['value']];
  }

  /**
   * {@inheritdoc}
   */
  protected function getHiddenFacets() {
    $facets = parent::getHiddenFacets();

    $group = $this->routeMatch->getParameter('group');
    $facets['facet_group'][] = Url::fromRoute('entity.node.canonical', ['node' => $group->id()], ['absolute' => TRUE])->toString();
    return $facets;
  }

  /**
   * Get the title for the page.
   */
  public function getTitle(NodeInterface $group) {
    return $group->getTitle();
  }

  /**
   * Create the group page.
   *
   * @param \Drupal\node\NodeInterface $group
   *   The group node.
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function view(NodeInterface $group, $page, $recordsPerPage) {
    $panels = $tabs = [];
    $panels['datasets'] = [
      '#theme' => 'panel',
      '#id' => 'datasets',
      '#content' => $this->content($page, $recordsPerPage),
    ];
    $tabs['panel-datasets'] = $this->t('Datasets');

    return [
      '#theme' => 'group',
      '#node' => $group,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all groups'), 'donl_search.search.group'),
      '#editLinks' => $this->getEditLinks($group),
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

}
