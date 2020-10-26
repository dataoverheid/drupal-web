<?php

namespace Drupal\donl_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_search\SearchFacetsInterface;
use Drupal\donl_search\SearchRoutesTrait;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Search form.
 */
class SearchForm extends FormBase {
  use SearchRoutesTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\donl_search\SearchFacetsInterface
   */
  protected $searchFacets;

  /**
   * @var bool
   */
  protected $isFrontPage;

  /**
   * @var string|null
   */
  protected $currentRoute;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('language_manager'),
      $container->get('donl_search.search.facets'),
      $container->get('path.matcher'),
      $container->get('current_route_match'),
      $container->get('donl_search.search_url')
    );
  }

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest, LanguageManagerInterface $languageManager, SearchFacetsInterface $searchFacets, PathMatcher $pathMatcher, RouteMatchInterface $routeMatch, SearchUrlServiceInterface $searchUrlService) {
    $this->solrRequest = $solrRequest;
    $this->languageManager = $languageManager;
    $this->searchFacets = $searchFacets;
    $this->isFrontPage = $pathMatcher->isFrontPage();
    $this->currentRoute = $routeMatch->getRouteName();
    $this->searchUrlService = $searchUrlService;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $routeName = $this->getRouteMatch()->getRouteName();
    $routeParams = $this->getRouteMatch()->getRawParameters()->all();
    $searchTerms = $this->getRequest()->query->all();

    if ($this->isFrontPage) {
      $form['label'] = [
        '#type' => 'inline_template',
        '#template' => "<h2>{{ 'Find one of the <a href=\"@url\">@count</a> available search results'|t({ '@url': url, '@count': ckan_format_number(count) }) }}</h2>",
        '#context' => [
          'url' => $this->searchUrlService->simpleSearchUrl('donl_search.search')->toString(),
          'count' => $this->solrRequest->getSearchCount(''),
        ],
      ];
    }
    $form['searchbar'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'searchbar',
      ],
    ];

    $form['searchbar']['type_select'] = [
      '#type' => 'select',
      '#default_value' => $this->getTypeFromRoute($this->currentRoute) ?? '',
      '#options' => [
        '' => $this->t('All', [], ['context' => 'Dutch: Alles']),
        'catalog' => $this->t('Catalog'),
        'community' => $this->t('Community'),
        'dataset' => $this->t('Datasets'),
        'datarequest' => $this->t('Data requests'),
        'group' => $this->t('Groups'),
        'news' => $this->t('News items'),
        'organization' => $this->t('Organizations'),
        'support' => $this->t('Support pages'),
        'application' => $this->t('Applications'),
      ],
      '#attributes' => [
        'class' => ['chosen'],
      ],
    ];

    $form['searchbar']['search'] = [
      '#type' => 'textfield',
      '#default_value' => $searchTerms['search'] ?? '',
      '#placeholder' => $this->t('Eg 2018 election results'),
      '#attributes' => [
        'autocomplete' => 'off',
        'class' => ['suggester-input'],
      ],
    ];

    $form['searchbar']['submit-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'submit-wrapper',
      ],
    ];
    $form['searchbar']['submit-wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];
    $form['searchbar']['submit-wrapper']['icon'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'class' => ['icon-search'],
        'src' => '/themes/custom/koop_overheid/images/icon-search.svg',
        'alt' => '',
      ],
    ];

    $form['suggestions_ajax_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'suggester-result-container',
      ],
    ];

    $activeFacets = $this->searchFacets->getFacetDeleteLinks($routeName, $routeParams, $searchTerms);
    $form['facet_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'facet-wrapper',
      ],
    ];

    $form['facet_wrapper'][] = $activeFacets;

    $form['#attached']['library'][] = 'donl_search/donl_search';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $options = [];
    if ($search = $form_state->getValue('search')) {
      $options['query']['search'] = $search;
    }

    $params = [
      'page' => 1,
      'recordsPerPage' => 10,
    ];

    // Get the correct redirect route based on the selected type.
    $route = $this->getSearchRoute($form_state->getValue('type_select'));
    $form_state->setRedirect($route, $params, $options);
  }

}
