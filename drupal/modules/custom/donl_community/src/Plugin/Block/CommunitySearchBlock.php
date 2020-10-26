<?php

namespace Drupal\donl_community\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_community\Form\CommunitySearchForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the community search block.
 *
 * @Block(
 *  id = "community_search_block",
 *  admin_label = @Translation("Community Search Block"),
 *  category = @Translation("DONL Community"),
 * )
 */
class CommunitySearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder, CommunityResolverInterface $communityResolver, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->formBuilder = $formBuilder;
    $this->communityResolver = $communityResolver;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('donl_community.community_resolver'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $community = $this->communityResolver->resolve();

    return [
      '#theme' => 'community_search_block',
      '#form' => $this->formBuilder->getForm(CommunitySearchForm::class, $community),
      '#community' => $community,
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
