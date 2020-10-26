<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_search\Controller\SearchDatasetOrganizationController;
use Drupal\donl_search\FacetRenameServiceInterface;
use Drupal\donl_search\SearchFacetsInterface;
use Drupal\donl_search\SearchPaginationInterface;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Community Organization Controller.
 */
class SearchDatasetOrganizationCommunityController extends SearchDatasetOrganizationController {

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

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
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\donl_search\SearchFacetsInterface $searchFacets
   * @param \Drupal\donl_search\SearchPaginationInterface $searchPagination
   * @param \Drupal\donl_search\FacetRenameServiceInterface $facetRename
   * @param \Drupal\donl_search\SearchUrlServiceInterface $searchUrlService
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   */
  public function __construct(SolrRequestInterface $solrRequest, RequestStack $requestStack, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager, SearchFacetsInterface $searchFacets, SearchPaginationInterface $searchPagination, FacetRenameServiceInterface $facetRename, SearchUrlServiceInterface $searchUrlService, BackLinkService $backLinkService, CommunityResolverInterface $communityResolver) {
    parent::__construct($solrRequest, $requestStack, $routeMatch, $languageManager, $searchFacets, $searchPagination, $facetRename, $searchUrlService, $backLinkService);
    $this->communityResolver = $communityResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function view(NodeInterface $organization, $page, $recordsPerPage) {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    $build = parent::view($organization, $page, $recordsPerPage);

    $title = $this->t('Back to all @type in community :community', [
      '@type' => $this->t('organizations'),
      ':community' => $community->getTitle(),
    ]);
    $routeParams = ['community' => $community->getMachineName()];
    $build['#backLink'] = $this->backLinkService->createBackLink($title, 'donl_community.search.organization', $routeParams);

    return $build;
  }

}
