<?php

namespace Drupal\donl_community\Plugin\Block;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl\Plugin\Block\TagCloudBlock;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a tag cloud block.
 *
 * @Block(
 *  id = "donl_community_tag_cloud_block",
 *  admin_label = @Translation("Community Tag Cloud Block"),
 *  category = @Translation("DONL Community"),
 * )
 */
class CommunityTagCloudBlock extends TagCloudBlock {

  /**
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('donl_search.request'),
      $container->get('donl_search.search_url'),
      $container->get('module_handler'),
      $container->get('donl_community.community_resolver'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SolrRequestInterface $solrRequest, SearchUrlServiceInterface $searchUrlService, ModuleHandlerInterface $moduleHandler,CommunityResolverInterface $communityResolver, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $solrRequest, $searchUrlService, $moduleHandler);

    $this->communityResolver = $communityResolver;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTags() {
    $tags = [];
    if ($community = $this->communityResolver->resolve()) {
      // Get tag cloud.
      if ($tagCloud = $this->solrRequest->getTagCloud($community->getIdentifier())) {
        // Only get the first 30 tags.
        $tagCloud = \array_slice($tagCloud, 0, 30);

        foreach ($tagCloud as $tag) {
          $routeParams = [
            'community' => $community->getMachineName(),
          ];
          $activeFacets = ['facet_keyword' => [$tag]];
          $tags[] = $this->createLink($tag, $routeParams, $activeFacets);
        }
      }
    }

    return $tags;
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
