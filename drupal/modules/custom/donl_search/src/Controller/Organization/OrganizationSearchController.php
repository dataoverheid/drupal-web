<?php

namespace Drupal\donl_search\Controller\Organization;

use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\donl_search\Controller\SearchController;
use Drupal\donl_search\Form\SearchForm;
use Drupal\node\NodeInterface;

/**
 * Organization search controller.
 */
Abstract class OrganizationSearchController extends SearchController {

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
    $organization = $this->routeMatch->getParameter('organization');
    return ['organization' => $organization->get('machine_name')->getValue()[0]['value']];
  }

  /**
   * {@inheritdoc}
   */
  protected function getHiddenFacets(): array {
    $facets = parent::getHiddenFacets();

    $organization = $this->routeMatch->getParameter('organization');
    $facets['facet_authority'][] = $organization->get('identifier')->getValue()[0]['value'];
    return $facets;
  }

  /**
   * Get the title for the page.
   *
   * @param \Drupal\node\NodeInterface $organization
   *   The organization node.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
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
  public function view(NodeInterface $organization, $page, $recordsPerPage): array {
    $panels = [];
    $panels['datasets'] = [
      '#theme' => 'panel',
      '#id' => 'datasets',
      '#content' => $this->content($page, $recordsPerPage),
    ];

    $tabs = $this->getTabs($organization);
    foreach ($tabs as $id => $tab) {
      if (!isset($panels[$id]) && $tab['type'] !== 'link') {
        $panels[$id] = [
          '#theme' => 'panel',
          '#id' => $id,
          '#content' => Markup::create('<span class="loader"></span>'),
        ];
      }
    }

    return [
      '#theme' => 'organization',
      '#node' => $organization,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all @type', ['@type' => $this->t('organizations')]), 'donl_search.search.organization'),
      '#editLinks' => $this->getEditLinks($organization),
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

  /**
   * Return data for all tabs that we are allowed to show.
   *
   * @return array
   */
  private function getAvailableTabs(): array {
    return [
      'application' => [
        'route' => 'donl_search.organization.application.view',
        'name' => $this->t('applications'),
      ],
      'community' => [
        'route' => 'donl_search.organization.community.view',
        'name' => $this->t('communities'),
      ],
      'datarequest' => [
        'route' => 'donl_search.organization.datarequest.view',
        'name' => $this->t('data requests'),
      ],
      'group' => [
        'route' => 'donl_search.organization.group.view',
        'name' => $this->t('groups'),
      ],
    ];
  }

  /**
   * Get the tabs to be shown.
   *
   * @param \Drupal\node\NodeInterface $organization
   *   The organization node.
   *
   * @return array
   */
  public function getTabs(NodeInterface $organization): array {
    $tabs = [
      'datasets' => [
        'title' => $this->t('Datasets'),
        'type' => 'tab',
        'active' => TRUE,
      ],
    ];
    if (($this->getRouteName() !== 'donl_search.organization.view')) {
      $tabs['datasets'] = [
        'title' => Link::createFromRoute($this->t('Datasets'), 'donl_search.organization.view', $this->getRouteParams()),
        'type' => 'link',
        'active' => FALSE,
      ];
    }

    $availableTabs = $this->getAvailableTabs();
    foreach($this->solrRequest->getRelations($organization->id() . '|nl', 'organization') as $type) {
      if (isset($availableTabs[$type])) {
        if (($this->getRouteName() === $availableTabs[$type]['route'])) {
          $tabs[$type] = [
            'title' => ucfirst($availableTabs[$type]['name']),
            'type' => 'tab',
            'active' => TRUE,
          ];
        }
        else {
          $tabs[$type] = [
            'title' => Link::createFromRoute(ucfirst($availableTabs[$type]['name']), $availableTabs[$type]['route'], $this->getRouteParams()),
            'type' => 'link',
            'active' => FALSE,
          ];
        }
      }
    }

    return $tabs;
  }

}
