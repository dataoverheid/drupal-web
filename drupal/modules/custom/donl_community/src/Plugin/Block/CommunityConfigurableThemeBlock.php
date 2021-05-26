<?php

namespace Drupal\donl_community\Plugin\Block;

use Drupal\ckan\MappingServiceInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\CommunityResolver;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\donl_value_list\ValueListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the community configurable theme block.
 *
 * @Block(
 *  id = "ckan_community_configurable_theme_block",
 *  admin_label = @Translation("Community Configurable Theme Block"),
 *  category = @Translation("DONL Community"),
 * )
 */
class CommunityConfigurableThemeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  protected $valueList;

  /**
   * @var \Drupal\ckan\MappingServiceInterface
   */
  protected $mappingService;

  /**
   * @var \Drupal\donl_community\CommunityResolver
   */
  protected $communityResolver;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ValueListInterface $valueList, SolrRequestInterface $solrRequest, MappingServiceInterface $mappingService, CommunityResolver $communityResolver, RouteMatchInterface $routeMatch, SearchUrlServiceInterface $searchUrlService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->valueList = $valueList;
    $this->solrRequest = $solrRequest;
    $this->mappingService = $mappingService;
    $this->communityResolver = $communityResolver;
    $this->routeMatch = $routeMatch;
    $this->searchUrlService = $searchUrlService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('donl.value_list'),
      $container->get('donl_search.request'),
      $container->get('ckan.mapping'),
      $container->get('donl_community.community_resolver'),
      $container->get('current_route_match'),
      $container->get('donl_search.search_url')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $community = $this->communityResolver->resolve();
    if (!$community) {
      return [];
    }

    $themes = [];
    foreach ($this->solrRequest->getCountForSelectedThemes($community->getThemes(), $community->getIdentifier()) as $uri => $count) {
      $themes[$uri] = [
        'count' => $count,
        'url' => $this->searchUrlService->simpleSearchUrlWithRouteParams('donl_community.search.dataset', ['community' => $community->getMachineName()], ['facet_theme' => [$this->mappingService->getThemeFacetValue($uri)]]),
      ];
    }

    return [
      '#theme' => 'community_theme_block',
      '#description' => $community->getDescription(),
      '#themes' => $themes,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 3600;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    if ($node = $this->routeMatch->getParameter('node')) {
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
    }

    return parent::getCacheTags();
  }

}
