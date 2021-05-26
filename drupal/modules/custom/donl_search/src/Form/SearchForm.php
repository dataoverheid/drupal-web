<?php

namespace Drupal\donl_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_entities\DonlEntitiesServiceInterface;
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
   * @var \Drupal\donl_entities\DonlEntitiesServiceInterface
   */
  protected $donlEntitiesService;

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
      $container->get('donl_search.search_url'),
      $container->get('donl_entities.entities'),
    );
  }

  /**
   * SearchForm constructor.
   *
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\donl_search\SearchFacetsInterface $searchFacets
   * @param \Drupal\Core\Path\PathMatcher $pathMatcher
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   * @param \Drupal\donl_search\SearchUrlServiceInterface $searchUrlService
   * @param \Drupal\donl_entities\DonlEntitiesServiceInterface $donlEntitiesService
   */
  public function __construct(SolrRequestInterface $solrRequest, LanguageManagerInterface $languageManager, SearchFacetsInterface $searchFacets, PathMatcher $pathMatcher, RouteMatchInterface $routeMatch, SearchUrlServiceInterface $searchUrlService, DonlEntitiesServiceInterface $donlEntitiesService) {
    $this->solrRequest = $solrRequest;
    $this->languageManager = $languageManager;
    $this->searchFacets = $searchFacets;
    $this->isFrontPage = $pathMatcher->isFrontPage();
    $this->currentRoute = $routeMatch->getRouteName();
    $this->searchUrlService = $searchUrlService;
    $this->donlEntitiesService = $donlEntitiesService;
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
    $searchTerms = $this->getRequest()->query->all();

    if ($this->isFrontPage) {
      $form['label'] = [
        '#type' => 'inline_template',
        '#template' => "<h2>{{ 'Find one of the <a href=\"@url\">@count</a> available search results'|t({ '@url': url, '@count': ckan_format_number(count) }) }}</h2>",
        '#context' => [
          'url' => $this->searchUrlService->simpleSearchUrl('donl_search.search')
            ->toString(),
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
      '#options' => $this->donlEntitiesService->getEntitiesAsOptionsList('search'),
      '#empty_option' => $this->t('All', [], ['context' => 'Dutch: Alles']),
      '#attributes' => [
        'class' => ['select2'],
        'data-minimum-results-for-search' => -1,
      ],
    ];

    $form['searchbar']['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
      '#default_value' => $searchTerms['search'] ?? '',
      '#placeholder' => $this->t('What are you looking for?'),
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

    $form['suggestions_full_form_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'suggester-result-container',
      ],
    ];

    $form['#attributes']['class'][] = 'donl-suggester-form';
    $form['#attached'] = [
      'library' => ['donl_search/donl_search'],
      'drupalSettings' => [
        'donl_search' => [
          'suggestor_url' => '/suggest/',
        ],
      ],
    ];

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
