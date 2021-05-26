<?php

namespace Drupal\donl\Controller;

use Drupal\ckan\CkanRequest;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\donl\EditLinksTrait;
use Drupal\donl_search_backlink\BackLinkService;
use Drupal\donl_search\Form\SearchForm;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dataservice.
 */
class DataserviceController extends ControllerBase {
  use EditLinksTrait;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * @var \Drupal\ckan\CkanRequest
   */
  protected $ckanRequest;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * @var \Drupal\donl_search_backlink\BackLinkService
   */
  protected $backLinkService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('donl_search_backlink.backlink')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\ckan\CkanRequest $ckanRequest
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   */
  public function __construct(CkanRequest $ckanRequest, BackLinkService $backLinkService) {
    $this->userStorage = $this->entityTypeManager()->getStorage('user');
    $this->ckanRequest = $ckanRequest;
    $this->termStorage = $this->entityTypeManager()->getStorage('taxonomy_term');
    $this->backLinkService = $backLinkService;
  }

  /**
   * Get the title.
   *
   * @param \Drupal\node\NodeInterface $dataservice
   *
   * @return string
   */
  public function getTitle(NodeInterface $dataservice) {
    return $dataservice->getTitle();
  }

  /**
   * Build the content page.
   *
   * @param \Drupal\node\NodeInterface $dataservice
   *
   * @return array
   */
  public function content(NodeInterface $dataservice): array {
    $tabs = [];
    $panels = [];

    // Description tab.
    $tabs['panel-description'] = $this->t('Description');
    $panels['description'] = [
      '#theme' => 'panel',
      '#id' => 'description',
      '#content' => [
        '#theme' => 'dataservice-panel-description',
        '#node' => $dataservice,
      ],
    ];

    // Metadata tab.
    $tabs['panel-metadata'] = $this->t('Metadata');
    $panels['metadata'] = [
      '#theme' => 'panel',
      '#id' => 'metadata',
      '#content' => [
        '#theme' => 'dataservice-panel-metadata',
        '#node' => $dataservice,
      ],
    ];

    return [
      '#theme' => 'dataservice',
      '#node' => $dataservice,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all @type', ['@type' => $this->t('dataservices')]), 'donl_search.search.dataservice'),
      '#editLinks' => $this->getEditLinks(Url::fromRoute('donl.dataservice', ['dataservice' => $dataservice->id()]), $dataservice, $this->userStorage->load($this->currentUser()->id())),
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

}
