<?php

namespace Drupal\donl_community\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\CommunityResolverInterface;
use Drupal\donl_entities\DonlEntitiesServiceInterface;
use Drupal\donl_recent\Plugin\Block\RecentContentBlock;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the recent content block for communities.
 *
 * @Block(
 *  id = "community_recent_content_block",
 *  admin_label = @Translation("Community recent content block"),
 *  category = @Translation("DONL Community"),
 * )
 */
class CommunityRecentContentBlock extends RecentContentBlock {

  /**
   * The community resolver.
   *
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * Constructs the RecentContentBlock object.
   *
   * @param array $configuration
   *   The configuration.
   * @param $plugin_id
   *   The plugin id.
   * @param $plugin_definition
   *   The plugin definition.
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   *   The solr request.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\donl_entities\DonlEntitiesServiceInterface $donlEntitiesService
   *   The donl entity service.
   * @param \Drupal\donl_community\CommunityResolverInterface $communityResolver
   *   The community resolver.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SolrRequestInterface $solrRequest, DateFormatterInterface $dateFormatter, FormBuilderInterface $formBuilder, RouteMatchInterface $routeMatch, ConfigFactoryInterface $configFactory, DonlEntitiesServiceInterface $donlEntitiesService, CommunityResolverInterface $communityResolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $solrRequest, $dateFormatter, $formBuilder, $routeMatch, $configFactory, $donlEntitiesService);
    $this->communityResolver = $communityResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('donl_search.request'),
      $container->get('date.formatter'),
      $container->get('form_builder'),
      $container->get('current_route_match'),
      $container->get('config.factory'),
      $container->get('donl_entities.entities'),
      $container->get('donl_community.community_resolver'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $community = $this->communityResolver->resolve();
    if (!$community) {
      return [];
    }

    $limit = $this->config->get('communities')[$community->getNid()]['limit'] ?? self::DEFAULT_LIMIT_FILTERED_BY_CATEGORY;
    $categoryFilter = $form_state->getUserInput()['category_filter'] ?? NULL;

    unset($form['header']['filters']);
    $form['content']['block']['#items'] = $this->getItems($limit, $categoryFilter, $community->getIdentifier());

    return $form;
  }

}
