<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\donl_identifier\ResolveIdentifierServiceInterface;
use Drupal\donl_search\SearchFacetsInterface;
use Drupal\donl_search\SearchPaginationInterface;
use Drupal\donl_search\SearchRoutesTrait;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\donl_search_backlink\BackLinkService;
use NumberFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Search controller.
 */
class SearchController extends ControllerBase {

  use SearchRoutesTrait;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var \NumberFormatter
   */
  protected $numberFormatter;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\donl_search\SearchFacetsInterface
   */
  protected $searchFacets;

  /**
   * @var \Drupal\donl_search\SearchPaginationInterface
   */
  protected $searchPagination;

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * @var \Drupal\donl_search_backlink\BackLinkService
   */
  protected $backLinkService;

  /**
   * @var \Drupal\donl_identifier\ResolveIdentifierServiceInterface
   */
  protected $resolveIdentifierService;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   *   The solr request service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\donl_search\SearchFacetsInterface $searchFacets
   *   The search facets service.
   * @param \Drupal\donl_search\SearchPaginationInterface $searchPagination
   *   The search pagination service.
   * @param \Drupal\donl_search\SearchUrlServiceInterface $searchUrlService
   *   The search url service.
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   *   The back link service.
   * @param \Drupal\donl_identifier\ResolveIdentifierServiceInterface $resolveIdentifierService
   *   Identifier resolver service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(SolrRequestInterface $solrRequest, RequestStack $requestStack, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager, SearchFacetsInterface $searchFacets, SearchPaginationInterface $searchPagination, SearchUrlServiceInterface $searchUrlService, BackLinkService $backLinkService, ResolveIdentifierServiceInterface $resolveIdentifierService, RendererInterface $renderer) {
    $this->solrRequest = $solrRequest;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->routeMatch = $routeMatch;
    $this->numberFormatter = new NumberFormatter($languageManager->getCurrentLanguage()->getId(), NumberFormatter::DECIMAL);
    $this->searchPagination = $searchPagination;
    $this->searchFacets = $searchFacets;
    $this->searchUrlService = $searchUrlService;
    $this->backLinkService = $backLinkService;
    $this->renderer = $renderer;
    $this->resolveIdentifierService = $resolveIdentifierService;
    $this->userStorage = $this->entityTypeManager()->getStorage('user');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('language_manager'),
      $container->get('donl_search.search.facets'),
      $container->get('donl_search.search.pagination'),
      $container->get('donl_search.search_url'),
      $container->get('donl_search_backlink.backlink'),
      $container->get('donl_identifier.resolver'),
      $container->get('renderer')
    );
  }

  /**
   * Create the search page.
   *
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content($page, $recordsPerPage): array {
    $page = (int) ($page ?? 1);
    $recordsPerPage = (int) ($recordsPerPage ?? 10);
    // Limit the records per page to 200 items max.
    $recordsPerPage = ($recordsPerPage > 200 ? 200 : $recordsPerPage);

    $values = $this->currentRequest->query->all();
    $sort = $values['sort'] ?? $this->getDefaultSort();
    $search = isset($values['search']) ? trim($values['search']) : NULL;
    $spellcheck = isset($values['spellcheck']) ? (bool) $values['spellcheck'] : TRUE;
    unset($values['sort'], $values['search'], $values['spellcheck']);

    return $this->renderContent($page, $recordsPerPage, $search, $sort, $values, $spellcheck);
  }

  /**
   * Get the results from SOLR.
   *
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   * @param string|null $search
   *   The search parameter.
   * @param string|null $sort
   *   The sort parameter.
   * @param array $searchFacets
   *   The search facets.
   *
   * @return array
   *   The results from SOLR.
   */
  protected function getSolrResult(int $page, int $recordsPerPage, ?string $search, ?string $sort, array $searchFacets): array {
    $result = &drupal_static('solrResult:' . md5($page . '-' . $recordsPerPage . '-' . $search . '-' . $sort . '-' . json_encode($searchFacets)));
    if (!$result) {
      $result['numFound'] = 0;

      // Drupal will first rebuild the whole page to check if the form contains
      // errors before reloading the page with the actual new values. This first
      // reload will however also trigger a new SRU search request which we don't
      // want. The following check should prevent this unnecessary double request.
      $form_id = $this->currentRequest->get('form_id');
      if (!$form_id || $form_id !== 'donl_search_form') {
        $result = $this->solrRequest->search($page, $recordsPerPage, $search, $sort, $this->getType(), $searchFacets, TRUE);
      }
    }
    return $result;
  }

  /**
   * Create the render array for the search page.
   *
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   * @param string|null $search
   *   The search parameter.
   * @param string|null $sort
   *   The sort parameter.
   * @param array $values
   *   Array containing all active values.
   * @param bool $spellcheck
   *   Enable spellcheck corrections.
   *
   * @return array
   *   A Drupal render array.
   */
  private function renderContent(int $page, int $recordsPerPage, ?string $search, ?string $sort, array $values, bool $spellcheck = TRUE): array {
    $activeFacets = $values;
    $searchFacets = array_merge_recursive($values, $this->getHiddenFacets());
    $result = $this->getSolrResult($page, $recordsPerPage, $search, $sort, $searchFacets);

    // If we have any facet active add an robots noindex, nofollow header to the
    // page to prevent duplicated index pages in search engines like Google.
    $http_headers = [];

    if (!empty($values)) {
      $http_headers[] = ['X-Robots-Tag', 'noindex, nofollow'];
    }

    // If we've got zero results for our current search request and a search on
    // the returned suggestion
    if ($spellcheck && !empty($result['suggestion']) && $result['numFound'] === 0) {
      $build = $this->renderContent($page, $recordsPerPage, $result['suggestion'], $sort, $values, FALSE);

      $correctedUrl = $this->searchUrlService->completeSearchUrl($this->getRouteName(), $this->getRouteParams(), 1, 25, $result['suggestion'], NULL, $searchFacets);
      $originalUrl = $this->searchUrlService->completeSearchUrl($this->getRouteName(), $this->getRouteParams(), 1, 25, $search, NULL, $searchFacets, FALSE);
      $build['#spellcheck'] = $this->t('<span class="corrected">Showing results for @corrected</span><span class="original">Search instead for @original</span>', [
        '@corrected' => Link::fromTextAndUrl($result['suggestion'], $correctedUrl)->toString(),
        '@original' => Link::fromTextAndUrl($search, $originalUrl)->toString(),
      ]);
      return $build;
    }

    // Store these values in the session so we can use them in the piwik module.
    $searchFilters = [];
    foreach ($this->searchFacets->activeFacetsToReadable($activeFacets) as $k => $v) {
      $searchFilters[$k] = implode(', ', $v);
    }
    $this->currentRequest->getSession()->set('donl_piwik.search', [
      'search_term' => $search,
      'search_page' => $page,
      'search_results' => $result['numFound'] ?? 0,
      'search_filters' => $searchFilters,
    ]);

    return [
      '#theme' => 'donl_searchpage',
      '#rows' => $this->themeRows($result['rows'] ?? []),
      '#facets' => $this->getFacets($result['facets'] ?? $this->getRouteParams(), $recordsPerPage, $search, $sort, $activeFacets),
      '#pagination' => $this->searchPagination->getPagination($this->getRouteName(), $this->getRouteParams(), $result['numFound'] ?? 0, $page, $recordsPerPage, $search, $sort, $activeFacets),
      '#total_results' => $this->getTotalResultsMessage($result['numFound'] ?? 0),
      '#context_block' => $search ? $this->getContextBlock($search, $result['numFound'] ?? 0) : [],
      '#attached' => [
        'http_header' => $http_headers,
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Get the type of data we are searching on this searchpage.
   *
   * @return string
   */
  protected function getType(): string {
    return '';
  }

  /**
   * Get the route name for the searchpage.
   *
   * @return string
   */
  protected function getRouteName(): string {
    return 'donl_search.search';
  }

  /**
   * Get additional route parameters for the searchpage.
   *
   * @return array
   */
  protected function getRouteParams(): array {
    return [];
  }

  /**
   * Get the total results found message.
   *
   * @param int $numFound
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected function getTotalResultsMessage($numFound): TranslatableMarkup {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 search result', '@count search results');
  }

  /**
   * Get the default sorting.
   *
   * @return string
   */
  protected function getDefaultSort(): string {
    return 'score desc,sys_modified desc';
  }

  /**
   * Get the row template file.
   *
   * @param string $routeName
   *   (optional) The route name.
   *
   * @return string
   */
  protected function getRowTemplate(string $routeName = ''): string {
    switch ($routeName) {
      case 'ckan.dataset.view':
        return 'donl_searchrecord_dataset';

      case 'donl_search.catalog.view':
        return 'donl_searchrecord_catalog';

      case 'donl.datarequest':
        return 'donl_searchrecord_datarequest';
    }
    return 'donl_searchrecord';
  }

  /**
   * Turn the records from the result in a Drupal render array.
   *
   * @param array $result
   *
   * @return array
   *   A Drupal render array.
   */
  protected function themeRows(array $result): array {
    $rows = [];

    /** @var \Drupal\donl_search\Entity\SolrResult $row */
    foreach ($result as $row) {
      $routeName = '';
      if ($row->url instanceof Url) {
        $routeName = $row->url->getRouteName();
      }
      $rows[] = [
        '#theme' => $this->getRowTemplate($routeName),
        '#record' => $row,
      ];
    }

    return $rows;
  }

  /**
   * Get the facets for a search page.
   *
   * @param array $availableFacets
   *   An array with all available facets from SOLR.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   * @param string|null $search
   *   The search parameter.
   * @param string|null $sort
   *   The sort parameter.
   * @param array $activeFacets
   *   Array containing all active facets.
   *
   * @return array
   *   A Drupal render array.
   */
  protected function getFacets(array $availableFacets, $recordsPerPage, $search, $sort, array $activeFacets): array {
    unset($availableFacets['facet_publisher'], $availableFacets['facet_frequency']);

    foreach (array_keys($this->getHiddenFacets()) as $key) {
      unset($availableFacets[$key]);
    }

    return $this->searchFacets->getFacets($this->getRouteName(), $this->getRouteParams(), $availableFacets, $recordsPerPage, $search, $sort, $activeFacets);
  }

  /**
   * Get the hidden facets for the search page.
   *
   * In some cases we need to search on a specific subset of the data without
   * showing the user an active facet. For example a search page for all
   * datasets for a given group. With this setting we can actively filter the
   * datasets on that given group without showing it as an active facet in the
   * facet list.
   *
   * @return array
   *   An array with hidden active facets.
   */
  protected function getHiddenFacets(): array {
    return [];
  }

  /**
   * Builds the context block for the search page.
   *
   * @param string $search
   *   The search parameter.
   * @param int $totalResults
   *   The total amount of results.
   *
   * @return array
   *   The context block build array.
   */
  protected function getContextBlock(string $search, $totalResults = 0): array {
    if ($this->routeMatch->getRouteName() === 'donl_search.search.dataset') {
      $formattedTotal = $this->numberFormatter->format($totalResults);
      $build = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['donl-search-context-block'],
        ],
      ];

      $build['content'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t("You searched for <b>'@search'</b>. There were <b>@totalResults datasets</b> found.<br>Can't find the dataset you are looking for below? Make a datarequest.", [
          '@search' => $search,
          '@totalResults' => $formattedTotal,
        ]),
      ];

      if ($totalResults === 1) {
        $build['content']['#value'] = $this->t("You searched for <b>'@search'</b>. We've found <b>@totalResults dataset</b>.<br>Can't find the dataset you are looking for below? Make a datarequest.", [
          '@search' => $search,
          '@totalResults' => $formattedTotal,
        ]);
      }

      $build['action'] = Link::createFromRoute($this->t('Make datarequest'), 'node.add', ['node_type' => 'datarequest'])->toRenderable();
      $build['action']['#attributes']['class'] = ['button', 'bordered'];

      return $build;
    }

    return [];
  }

}
