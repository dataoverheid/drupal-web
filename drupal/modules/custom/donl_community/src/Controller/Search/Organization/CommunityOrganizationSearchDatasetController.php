<?php

namespace Drupal\donl_community\Controller\Search\Organization;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_community\Form\CommunitySearchForm;
use Drupal\donl_identifier\ResolveIdentifierServiceInterface;
use Drupal\donl_search\Controller\Organization\OrganizationSearchDatasetController;
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
 * Search datasets within a organization within the community.
 */
class CommunityOrganizationSearchDatasetController extends OrganizationSearchDatasetController {

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

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
   * {@inheritdoc}
   */
  public function view(NodeInterface $organization, $page, $recordsPerPage): array {
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

    $build['#search'] = $this->formBuilder()->getForm(CommunitySearchForm::class, $community);

    return $build;
  }

}
