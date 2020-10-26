<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\Form\CommunitySearchForm;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_search\FacetRenameServiceInterface;
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
 * Base community search controller.
 */
abstract class BaseCommunitySearchController extends SearchController {

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * BaseCommunitySearchController constructor.
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
   * @param \Drupal\donl_search\FacetRenameServiceInterface $facetRename
   *   The facet rename service.
   * @param \Drupal\donl_search\SearchUrlServiceInterface $searchUrlService
   *   The search url service.
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   *   The back link service.
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   *   The community resolver.
   */
  public function __construct(SolrRequestInterface $solrRequest, RequestStack $requestStack, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager, SearchFacetsInterface $searchFacets, SearchPaginationInterface $searchPagination, FacetRenameServiceInterface $facetRename, SearchUrlServiceInterface $searchUrlService, BackLinkService $backLinkService, CommunityResolverInterface $communityResolver) {
    parent::__construct($solrRequest, $requestStack, $routeMatch, $languageManager, $searchFacets, $searchPagination, $facetRename, $searchUrlService, $backLinkService);
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
      $container->get('donl_search.search.facetRename'),
      $container->get('donl_search.search_url'),
      $container->get('donl_search_backlink.backlink'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   * Build the page.
   *
   * @param int $page
   *   The page to link towards.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   * @param \Drupal\node\NodeInterface|null $community
   *   The community node.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content($page, $recordsPerPage, ?NodeInterface $community = NULL): array {
    if (!$communityObject = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }
    $build = parent::content($page, $recordsPerPage);

    $page = (int) ($page ?? 1);
    $recordsPerPage = (int) ($recordsPerPage ?? 10);
    // Limit the records per page to 200 items max.
    $recordsPerPage = ($recordsPerPage > 200 ? 200 : $recordsPerPage);

    $values = $this->currentRequest->query->all();
    $sort = $values['sort'] ?? $this::DEFAULT_SORT;
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
      '#count' => $this->t('There are @count results within @type of:', [
        '@count' => $result['numFound'] ?? 0,
        '@type' => $this->getCommunityBlockTypeLabel(),
      ]),
      '#community' => $this->facetRename->rename($communityObject->getTitle(), 'facet_community'),
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
  abstract protected function getCommunityBlockTypeLabel(): string;

  /**
   * Get the route for the community block.
   *
   * @return string
   *   The community block route.
   */
  abstract protected function getCommunityBlockRoute(): string;

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

}
