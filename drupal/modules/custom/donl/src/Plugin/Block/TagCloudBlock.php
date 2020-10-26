<?php

namespace Drupal\donl\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a tag cloud block.
 *
 * @Block(
 *  id = "donl_tag_cloud_block",
 *  admin_label = @Translation("Tag Cloud Block"),
 *  category = @Translation("DONL"),
 * )
 */
class TagCloudBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SolrRequestInterface $solrRequest, SearchUrlServiceInterface $searchUrlService, ModuleHandlerInterface $moduleHandler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->solrRequest = $solrRequest;
    $this->searchUrlService = $searchUrlService;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'donl_tag_cloud_block',
      '#tags' => $this->getTags(),
    ];
  }

  /**
   * Helper function which will retrieve all tags.
   *
   * @return array
   */
  protected function getTags() {
    $tags = [];
    if ($tagCloud = $this->solrRequest->getTagCloud()) {
      // Only get the first 30 tags.
      $tagCloud = \array_slice($tagCloud, 0, 30);

      foreach ($tagCloud as $tag) {
        $tags[] = $this->createLink($tag, [], ['facet_keyword' => [$tag]]);
      }
    }

    return $tags;
  }

  /**
   * Create the tag link.
   *
   * @param string $tag
   * @param array $routeParams
   * @param array $activeFacets
   *
   * @return array
   */
  protected function createLink($tag, array $routeParams = [], array $activeFacets = []) {
    if ($this->moduleHandler->moduleExists('donl_community')) {
      $options = ['attributes' => ['class' => ['label']]];
      $url = $this->searchUrlService->simpleSearchUrlWithRouteParams('donl_community.search.dataset', $routeParams, $activeFacets, $options);
      return Link::fromTextAndUrl($tag, $url)->toRenderable();
    }

    return [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $tag,
      '#attributes' => [
        'class' => ['label'],
      ],
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

}
