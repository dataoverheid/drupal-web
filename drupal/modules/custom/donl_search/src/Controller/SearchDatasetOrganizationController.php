<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\Url;
use Drupal\donl_search\Form\SearchForm;
use Drupal\node\NodeInterface;

/**
 *
 */
class SearchDatasetOrganizationController extends SearchDatasetContentTypeController {

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_search.organization.view';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteParams() {
    $organization = $this->routeMatch->getParameter('organization');
    return ['organization' => $organization->get('machine_name')->getValue()[0]['value']];
  }

  /**
   * {@inheritdoc}
   */
  protected function getHiddenFacets() {
    $facets = parent::getHiddenFacets();

    $organization = $this->routeMatch->getParameter('organization');
    $facets['facet_authority'][] = $organization->get('identifier')->getValue()[0]['value'];
    return $facets;
  }

  /**
   * Get the title for the page.
   */
  public function getTitle(NodeInterface $organization) {
    return $organization->getTitle();
  }

  /**
   * Create the Organization page.
   *
   * @param \Drupal\node\NodeInterface $organization
   *   The organization node.
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function view(NodeInterface $organization, $page, $recordsPerPage) {
    $panels = $tabs = [];
    $panels['datasets'] = [
      '#theme' => 'panel',
      '#id' => 'datasets',
      '#content' => $this->content($page, $recordsPerPage),
    ];
    $tabs['panel-datasets'] = $this->t('Datasets');

    $maxRelations = 10;
    $relations = [
      'application' => [
        'route' => 'donl_search.search.application',
        'name' => $this->t('applications'),
      ],
      'community' => [
        'route' => 'donl_search.search.community',
        'name' => $this->t('communities')
      ],
      'datarequest' => [
        'route' => 'donl_search.search.datarequest',
        'name' => $this->t('data requests'),
      ],
      'group' => [
        'route' => 'donl_search.search.group',
        'name' => $this->t('groups'),
      ],
    ];
    foreach ($relations as $relation => $values) {
      $contents = $this->solrRequest->search(1, $maxRelations, '', '', $relation, [
        'authority' => [$organization->get('identifier')->getString()],
      ]);
      if ($contents['numFound']) {
        $panelContent = [
          '#type' => 'html_tag',
          '#tag' => 'ul',
          '#attributes' => ['class' => 'panel-results'],
        ];
        foreach ($contents['rows'] as $content) {
          $panelContent[] = [
            '#theme' => 'donl_searchrecord_' . $relation,
            '#record' => $content,
          ];
        }
        if ($contents['numFound'] > $maxRelations) {
          $panelContent['links'] = [
            '#theme' => 'item_list',
            '#attributes' => ['class' => ['list', 'list--inline']],
            '#items' => [
              [
                '#type' => 'link',
                '#title' => t('View all @count @type for this organization', [
                  '@count' => $contents['numFound'],
                  '@type' => $values['name'],
                ]),
                '#url' => Url::fromRoute($values['route'], [], [
                  'query' => ['authority' => [$organization->get('identifier')->getString()]],
                  'attributes' => ['class' => ['cta']],
                ]),
              ],
            ],
          ];
        }

        $panels[$relation] = [
          '#theme' => 'panel',
          '#id' => $relation,
          '#content' => $panelContent,
        ];
        $tabs['panel-' . $relation] = $this->t(ucfirst($values['name']));
      }
    }

    return [
      '#theme' => 'organization',
      '#node' => $organization,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all organizations'), 'donl_search.search.organization'),
      '#editLinks' => $this->getEditLinks($organization),
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

}
