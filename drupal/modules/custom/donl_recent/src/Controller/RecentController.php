<?php

namespace Drupal\donl_recent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\donl_recent\RecentNodeServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class RecentController extends ControllerBase {

  /**
   * The 'recent' node service.
   *
   * @var \Drupal\donl_recent\RecentNodeServiceInterface
   */
  private $recentNodeService;

  /**
   * Needed to make the teaser image.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $imageStyleStorage;

  /**
   * Needed to format created date.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var mixed|null
   */
  private $filter;

  /**
   * @var array|\Drupal\Core\StringTranslation\TranslatableMarkup[]
   */
  private $types;

  /**
   * RecentController constructor.
   *
   * @param \Drupal\donl_recent\RecentNodeServiceInterface $recentNodeService
   *   The 'recent' node service used to get the menu, nodes and title of
   *   the 'recent' nodes.
   */
  public function __construct(RecentNodeServiceInterface $recentNodeService, EntityTypeManagerInterface $entityTypeManager, DateFormatterInterface $dateFormatter, RequestStack $requestStack) {
    $this->recentNodeService = $recentNodeService;
    $this->imageStyleStorage = $entityTypeManager->getStorage('image_style');
    $this->dateFormatter = $dateFormatter;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->types = $this->recentNodeService->getTypes();
    $this->filter = ($filter = $this->currentRequest->get('filter')) ? $filter : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_recent.recent_node_service'),
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('request_stack')
    );
  }

  protected function determineDisplayType($type) {
    switch ($type) {
      case 'communities':
        return 'community';
      case 'evenementen':
        return 'dataset';
      case 'nieuws':
        return 'news';
      case 'impact-story':
        return 'appliance';

      default:
        return $type;
    }
  }

  /**
   *
   */
  public function index(): Response {
    $items = [];
    $start = $this->currentRequest->get('start');
    $end = $this->currentRequest->get('end');

    foreach ($this->recentNodeService->getNodes($this->filter, $start, $end) as $node) {
      $type = (($type = $node->get('recent_type')[0]->getValue()['value']) && $this->types[$type]) ? $type : '';

      $items[$node->id()] = [
        '#theme' => 'recent_item',
        '#group' => $this->determineDisplayType($type),
        '#title' => $node->label(),
        '#result_type' => $this->types[$type]->render(),
        '#created' => $this->dateFormatter->format($node->getCreatedTime(), 'custom', 'd-m-Y'),
        '#url' => $node->toUrl(),
      ];

      // Get the teaser.
      if ($node->hasField('teaser') && ($node->get('teaser')[0] && $val = $node->get('body')[0]->getValue()['value'])) {
        $items[$node->id()]['#teaser'] = $val;
      }
      // if no teaser is set, substring the body.
      elseif (($node->get('body')[0] && $val = $node->get('body')[0]->getValue()['value'])) {
        $items[$node->id()]['#teaser'] = html_entity_decode(substr(strip_tags($val), 0, 100));
      }

      if (($image = $node->get('recent_image')->entity) && $style = $this->imageStyleStorage->load('teaser_image_400_x_225')) {
        $items[$node->id()]['#image_url'] = $style->buildUrl($image->get('uri')
          ->getString());
      }
    }

    $response = new Response();
    $response->setContent(render($items));
    return $response;
  }

  /**
   *
   */
  public function main(): array {
    $filterSelect = [
      '#type' => 'select2',
      '#title' => $this->t('Filter'),
      '#value' => $this->filter,
      '#required' => FALSE,
      '#options' => ['' => $this->t('All')] + $this->types,
      '#attributes' => [
        'class' => ['recent-filter'],
        'data-baseurl' => $this->currentRequest->getHost() . '/actueel',
      ],
      '#select2' => [
        'allowClear' => FALSE,
        'minimumResultsForSearch' => -1,
      ],
    ];

    $build = [
      '#theme' => 'recent_index',
      '#title' => $this->t('Recent'),
      '#filter' => $filterSelect,
    ];

    $build['#attached']['library'][] = 'donl_recent/recent';
    $build['#attached']['drupalSettings']['recent']['category'] = $this->filter ?? 'all';
    return $build;
  }

}
