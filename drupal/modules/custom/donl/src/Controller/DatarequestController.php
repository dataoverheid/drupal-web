<?php

namespace Drupal\donl\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\donl_search\Form\SearchForm;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class DatarequestController extends ControllerBase {

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\donl_search_backlink\BackLinkService
   */
  protected $backLinkService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('date.formatter'),
      $container->get('donl_search_backlink.backlink')
    );
  }

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest, DateFormatterInterface $dateFormatter, BackLinkService $backLinkService) {
    $this->userStorage = $this->entityTypeManager()->getStorage('user');
    $this->solrRequest = $solrRequest;
    $this->dateFormatter = $dateFormatter;
    $this->backLinkService = $backLinkService;
  }

  /**
   *
   */
  public function getTitle(NodeInterface $datarequest) {
    return $datarequest->getTitle();
  }

  /**
   *
   */
  public function content(NodeInterface $datarequest): array {
    $editLinks = [];
    if (($user = $this->userStorage->load($this->currentUser()->id())) && $user->isAdministrator()) {
      $editLinks['view'] = [
        '#type' => 'link',
        '#title' => $this->t('View'),
        '#url' => Url::fromRoute('donl.datarequest', ['datarequest' => $datarequest->id()]),
        '#attributes' => [
          // Currently this link is always active.
          'class' => ['buttonswitch__button', 'is-active'],
        ],
      ];
      $editLinks['edit'] = [
        '#type' => 'link',
        '#title' => $this->t('Edit'),
        '#url' => Url::fromRoute('entity.node.edit_form', ['node' => $datarequest->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
      $editLinks['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromRoute('entity.node.delete_form', ['node' => $datarequest->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
      $editLinks['translate'] = [
        '#type' => 'link',
        '#title' => $this->t('Translate'),
        '#url' => Url::fromRoute('entity.node.content_translation_overview', ['node' => $datarequest->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
    }

    $extraInfo = [];
    $date = $datarequest->getCreatedTime();
    $extraInfo['lastChanged'] = $this->dateFormatter->formatInterval(time() - $date) . ' ' . $this->t('ago');

    $tabs = [];
    $panels = [];

    $tabs['panel-request'] = $this->t('Request');
    $panels['request'] = [
      '#theme' => 'panel',
      '#id' => 'request',
      '#title' => $this->t('What data are you looking for?'),
      '#content' => [
        '#theme' => 'datarequest-panel-request',
        '#datarequest' => $datarequest,
      ],
    ];

    $tabs['panel-reply'] = $this->t('Reply dataoverheid');
    $panels['reply'] = [
      '#theme' => 'panel',
      '#id' => 'reply',
      '#title' => $this->t('Reply dataoverheid'),
      '#content' => [
        '#theme' => 'datarequest-panel-reply',
        '#datarequest' => $datarequest,
      ],
    ];

    // Relations tab.
    $comparableLinks = [];
    foreach ($this->solrRequest->getComparableData('datarequest', $datarequest->id()) as $name => $title) {
      $comparableLinks[] = Link::createFromRoute($title, 'ckan.dataset.view', ['dataset' => $name]);
    }
    $tabs['panel-relations'] = $this->t('Relations');
    $panels['relations'] = [
      '#theme' => 'panel',
      '#id' => 'relations',
      '#title' => $this->t('Relations'),
      '#content' => [
        '#theme' => 'datarequest-panel-relations',
        '#datarequest' => $datarequest,
        '#comparableLinks' => $comparableLinks,
      ],
    ];

    return [
      '#theme' => 'datarequest',
      '#node' => $datarequest,
      '#extraInfo' => $extraInfo,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all @type', ['@type' => $this->t('data requests')]), 'donl_search.search.datarequest'),
      '#editLinks' => $editLinks,
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

}
