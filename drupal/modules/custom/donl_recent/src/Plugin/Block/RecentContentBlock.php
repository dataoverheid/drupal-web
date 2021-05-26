<?php

namespace Drupal\donl_recent\Plugin\Block;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_entities\DonlEntitiesServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the recent content block.
 *
 * @Block(
 *  id = "donl_recent_content_block",
 *  admin_label = @Translation("DONL recent content block"),
 *  category = @Translation("DONL"),
 * )
 */
class RecentContentBlock extends BlockBase implements ContainerFactoryPluginInterface, FormInterface {

  /**
   * Default limit of content per category;
   */
  public const DEFAULT_LIMIT_PER_CATEGORY = 1;

  /**
   * Default limit of content when filtered by specific category.
   */
  public const DEFAULT_LIMIT_FILTERED_BY_CATEGORY = 9;

  /**
   * Default limit of characters for the description.
   */
  public const DEFAULT_LIMIT_DESCRIPTION_CHARACTERS = 150;

  /**
   * Exclude these types from the recent content.
   */
  public const EXCLUDE_TYPES = ['catalog', 'community', 'organization'];

  /**
   * The solr request.
   *
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The recent content config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The DONL entity service.
   *
   * @var \Drupal\donl_entities\DonlEntitiesServiceInterface
   */
  protected $donlEntitiesService;

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
   *   The DONL entity service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SolrRequestInterface $solrRequest, DateFormatterInterface $dateFormatter, FormBuilderInterface $formBuilder, RouteMatchInterface $routeMatch, ConfigFactoryInterface $configFactory, DonlEntitiesServiceInterface $donlEntitiesService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->solrRequest = $solrRequest;
    $this->dateFormatter = $dateFormatter;
    $this->formBuilder = $formBuilder;
    $this->routeMatch = $routeMatch;
    $this->config = $configFactory->get('donl_recent.recent_content_settings');
    $this->donlEntitiesService = $donlEntitiesService;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'donl_recent_content';
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return $this->formBuilder->getForm($this);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attributes']['class'][] = 'recent-content-form';
    $form['#attached']['library'][] = 'donl_recent/recent_content';

    $form['header'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'recent-content-header',
          'container',
          'columns',
          'row',
          'transitional-size',
        ],
      ],
    ];

    $form['header']['title'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => $this->t('Current on this site'),
    ];

    $categoryFilter = $form_state->getUserInput()['category_filter'] ?? NULL;

    $form['header']['filters'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['filters'],
      ],
    ];

    $form['header']['filters']['filter_label'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('Show messages of'),
      '#attributes' => [
        'class' => ['filter-label'],
      ],
    ];

    $form['header']['filters']['category_filter'] = [
      '#type' => 'select',
      '#options' => $this->donlEntitiesService->getEntitiesAsOptionsList('recent_content'),
      '#empty_option' => $this->t('All', [], ['context' => 'Dutch: Alles']),
      '#attributes' => [
        'class' => [
          'category-filter',
          'select2',
        ],
        'data-minimum-results-for-search' => -1,
      ],
      '#ajax' => [
        'callback' => '::ajaxCategoryFilterCallback',
        'wrapper' => 'recent-content-container',
        'event' => 'change',
      ],
    ];

    // Set default limit.
    $limit = $this->config->get('general')['limit_per_type'] ?? self::DEFAULT_LIMIT_PER_CATEGORY;

    // Set limit of the category filter.
    if ($categoryFilter) {
      $limit = $this->config->get('general')['limit_per_filter'] ?? self::DEFAULT_LIMIT_FILTERED_BY_CATEGORY;
    }

    $form['content'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'recent-content-container',
        'class' => ['recent-content-container'],
      ],
      'block' => [
        '#theme' => 'recent_content',
        '#items' => $this->getItems($limit, $categoryFilter),
      ],
    ];

    return $form;
  }

  /**
   * Get the items.
   *
   * @param int $limit
   *   The number of results to return.
   * @param string|null $categoryFilter
   *   Limit result to a specific type.
   * @param string|null $communityIdentifier
   *   The community identifier.
   *
   * @return array
   */
  protected function getItems($limit, $categoryFilter, ?string $communityIdentifier = NULL): array {
    $items = [];
    foreach ($this->solrRequest->getRecentContentData($communityIdentifier, $limit, $categoryFilter, self::EXCLUDE_TYPES) as $groupId => $group) {
      foreach ($group as $result) {
        $description = $result->description ?? '';
        $truncatedDescription = strlen($description) > self::DEFAULT_LIMIT_DESCRIPTION_CHARACTERS ? rtrim(substr($description, 0, self::DEFAULT_LIMIT_DESCRIPTION_CHARACTERS)) . '...' : $description;

        $items[] = [
          '#theme' => 'recent_item',
          '#title' => $result->title,
          '#teaser' => $truncatedDescription,
          '#result_type' => $this->t(ucfirst($result->type)),
          '#created' => $this->formatCkanDate($result->metadata_created),
          '#url' => $result->url,
          '#group' => $groupId,
        ];
      }
    }

    return $items;
  }

  /**
   * Format a CKAN date string.
   *
   * @param string $value
   *   The given date string
   *
   * @return string
   *   The formatted date.
   */
  public function formatCkanDate(?string $value): string {
    if ($value) {
      preg_match('/^(\d{2,4})-(\d{1,2})-(\d{1,2})/', $value, $matches);
      $timestamp = mktime(0, 0, 0, (int) $matches[2], (int) $matches[3], (int) $matches[1]);
      return $this->dateFormatter->format($timestamp, 'custom', 'd-m-Y');
    }
    return '';
  }

  /**
   * Ajax callback for the category filter.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return AjaxResponse
   *   The recent content container.
   */
  public function ajaxCategoryFilterCallback(array $form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#recent-content-container', $form['content']));
    $response->addCommand(new InvokeCommand(NULL, 'ajaxCategoryFilterCallback'));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Not needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Done by ajax.
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
