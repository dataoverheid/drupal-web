<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\donl_search\FacetRenameServiceInterface;
use Drupal\donl_search\SearchFacetsInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_search\SearchPaginationInterface;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use NumberFormatter;

/**
 *
 */
class SearchController extends ControllerBase {

  protected const DEFAULT_SORT = 'sys_modified desc';

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
   * @var \Drupal\donl_search\FacetRenameServiceInterface
   */
  protected $facetRename;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * @var \Drupal\donl_search_backlink\BackLinkService
   */
  protected $backLinkService;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

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
      $container->get('donl_search.search.facetRename'),
      $container->get('donl_search.search_url'),
      $container->get('donl_search_backlink.backlink')
    );
  }

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest, RequestStack $requestStack, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager, SearchFacetsInterface $searchFacets, SearchPaginationInterface $searchPagination, FacetRenameServiceInterface $facetRename, SearchUrlServiceInterface $searchUrlService, BackLinkService $backLinkService) {
    $this->solrRequest = $solrRequest;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->routeMatch = $routeMatch;
    $this->numberFormatter = new NumberFormatter($languageManager->getCurrentLanguage()->getId(), NumberFormatter::DECIMAL);
    $this->searchPagination = $searchPagination;
    $this->searchFacets = $searchFacets;
    $this->facetRename = $facetRename;
    $this->searchUrlService = $searchUrlService;
    $this->backLinkService = $backLinkService;
    $this->userStorage = $this->entityTypeManager()->getStorage('user');
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
  public function content($page, $recordsPerPage) {
    $page = (int) ($page ?? 1);
    $recordsPerPage = (int) ($recordsPerPage ?? 10);
    // Limit the records per page to 200 items max.
    $recordsPerPage = ($recordsPerPage > 200 ? 200 : $recordsPerPage);

    $values = $this->currentRequest->query->all();
    $sort = $values['sort'] ?? $this::DEFAULT_SORT;
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

    return [
      '#theme' => 'donl_searchpage',
      '#rows' => $this->themeRows($result['rows'] ?? []),
      '#facets' => $this->getFacets($result['facets'] ?? $this->getRouteParams(), $recordsPerPage, $search, $sort, $activeFacets),
      '#pagination' => $this->searchPagination->getPagination($this->getRouteName(), $this->getRouteParams(), $result['numFound'] ?? 0, $page, $recordsPerPage, $search, $sort, $activeFacets),
      '#total_results' => $this->getTotalResultsMessage($result['numFound'] ?? 0),
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
  protected function getType() {
    return '';
  }

  /**
   * Get the route name for the searchpage.
   *
   * @return string
   */
  protected function getRouteName() {
    return 'donl_search.search';
  }

  /**
   * Get additional route parameters for the searchpage.
   *
   * @return array
   */
  protected function getRouteParams() {
    return [];
  }

  /**
   * Get the total results found message.
   *
   * @param int $numFound
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 search result', '@count search results');
  }

  /**
   * Return an array with all available sorting options.
   *
   * @return array
   */
  protected function getAvailableSorting() {
    return [
      [
        'label' => $this->t('Title'),
        'sort' => ['title'],
      ],
      [
        'label' => $this->t('Last modified'),
        'sort' => ['sys_modified'],
      ],
    ];
  }

  /**
   * Determine the correct template.
   *
   * @param string $type
   *   The type of the record.
   *
   * @return string
   *   The name of the template.
   */
  protected function determineTemplate($type): string {
    $templates = [
      'donl_searchrecord_application',
      'donl_searchrecord_catalog',
      'donl_searchrecord_community',
      'donl_searchrecord_datarequest',
      'donl_searchrecord_dataset',
      'donl_searchrecord_group',
      'donl_searchrecord_news',
      'donl_searchrecord_organization',
      'donl_searchrecord_support',
    ];

    $template = 'donl_searchrecord_' . $type;
    if (in_array($template, $templates, TRUE)) {
      return $template;
    }

    $this->getLogger('donl_search')
      ->warning("Search result template '$template' not found.");
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
    foreach ($result as $row) {
      $rows[] = [
        '#theme' => $this->determineTemplate($row->type),
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
  protected function getFacets(array $availableFacets, $recordsPerPage, $search, $sort, array $activeFacets) {
    unset($availableFacets['facet_publisher'], $availableFacets['facet_frequency']);

    return $this->searchFacets->getFacets($this->getRouteName(), $this->getRouteParams(), $availableFacets, $recordsPerPage, $search, $sort, $activeFacets);
  }

  /**
   * Get the hidden facets for the search page.
   *
   * In some cases we need to search on a specific subset of the data without
   * showing the user an active facet. For example a search page for all
   * datasets for a given group. With this setting we can actively filter the
   * datasets on that given group withouth showing it as an active facet in the
   * facet list.
   *
   * @return array
   *   An array with hidden active facets.
   */
  protected function getHiddenFacets() {
    return [];
  }

}
