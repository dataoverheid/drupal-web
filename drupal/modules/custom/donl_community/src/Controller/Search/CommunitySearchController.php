<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\donl_community\Form\CommunitySearchForm;
use Drupal\donl_identifier\ResolveIdentifierServiceInterface;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_search\SearchFacetsInterface;
use Drupal\donl_search\SearchPaginationInterface;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\donl_search\Controller\SearchController;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Community search controller.
 */
class CommunitySearchController extends SearchController {

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * CommunitySearchController constructor.
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
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   *   The community resolver.
   */
  public function __construct(SolrRequestInterface $solrRequest, RequestStack $requestStack, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager, SearchFacetsInterface $searchFacets, SearchPaginationInterface $searchPagination, SearchUrlServiceInterface $searchUrlService, BackLinkService $backLinkService, ResolveIdentifierServiceInterface $resolveIdentifierService, RendererInterface $renderer, CommunityResolverInterface $communityResolver) {
    parent::__construct($solrRequest, $requestStack, $routeMatch, $languageManager, $searchFacets, $searchPagination, $searchUrlService, $backLinkService, $resolveIdentifierService, $renderer);
    $this->communityResolver = $communityResolver;
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
      $container->get('renderer'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   * Build the page.
   *
   * @param \Drupal\node\NodeInterface|null $community
   *   The community node.
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function view(NodeInterface $community, $page, $recordsPerPage): array {
    if (!$communityObject = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }
    $build = $this->content($page, $recordsPerPage);

    $page = (int) ($page ?? 1);
    $recordsPerPage = (int) ($recordsPerPage ?? 10);
    // Limit the records per page to 200 items max.
    $recordsPerPage = ($recordsPerPage > 200 ? 200 : $recordsPerPage);

    $values = $this->currentRequest->query->all();
    $sort = $values['sort'] ?? $this->getDefaultSort();
    $search = isset($values['search']) ? trim($values['search']) : NULL;
    unset($values['sort'], $values['search'], $values['spellcheck']);

    $searchFacets = array_merge_recursive($values, $this->getHiddenFacets());
    $result = $this->getSolrResult($page, $recordsPerPage, $search, $sort, $searchFacets);
    unset($searchFacets['facet_community']);

    // The community block.
    $url = $this->searchUrlService->completeSearchUrl($this->getCommunityBlockRoute(), [], 1, 25, $search, NULL, $searchFacets);
    $linkTitle = $this->t('@count results at data.overheid.nl.', ['@count' => $this->solrRequest->getSearchCount($search, $searchFacets, $this->getType())]);
    $build['#above_facets'] = [
      '#theme' => 'above_facets',
      '#count' => $this->formatPlural($result['numFound'] ?? 0, 'There is 1 result within @type of:', 'There are @count results within @type of:', [
        '@type' => $this->getCommunityBlockTypeLabel(),
      ]),
      '#community' => $communityObject->getTitle(),
      '#outside' => $this->t('Outside of this register are'),
      '#outside_url' => Link::fromTextAndUrl($linkTitle, $url),
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return [
      '#theme' => 'community_search',
      '#node' => $community,
      '#search' => $this->formBuilder()->getForm(CommunitySearchForm::class, $communityObject),
      '#body' => $build,
    ];
  }

  /**
   * Get the type label for the community block.
   *
   * @return string
   *   The community block type label.
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('search results');
  }

  /**
   * Get the route for the community block.
   *
   * @return string
   *   The community block route.
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'donl_community.search';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteParams(): array {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    return [
      'community' => $community->getMachineName(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getHiddenFacets(): array {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    $facets = parent::getHiddenFacets();
    $facets['facet_community'][] = $community->getIdentifier();
    return $facets;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFacets(array $availableFacets, $recordsPerPage, $search, $sort, array $activeFacets): array {
    $facets = parent::getFacets($availableFacets, $recordsPerPage, $search, $sort, $activeFacets);

    // @todo There must be a cleaner way to overwrite the "Type" facet for communities.
    if (isset($facets['#facets']['available']['Type'])) {
      foreach ($facets['#facets']['available']['Type'] as $type => $link) {
        $routeName = $this->getSearchRoute($type, TRUE);
        $routeParams = $link['#url']->getRouteParameters() + $this->getRouteParams();
        $options = $link['#url']->getOptions();
        $facets['#facets']['available']['Type'][$type]['#url'] = Url::fromRoute($routeName, $routeParams, $options);
      }
    }

    return $facets;
  }

}
