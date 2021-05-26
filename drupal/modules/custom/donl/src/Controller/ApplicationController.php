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
 *
 */
class ApplicationController extends ControllerBase {
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
   *
   */
  public function __construct(CkanRequest $ckanRequest, BackLinkService $backLinkService) {
    $this->userStorage = $this->entityTypeManager()->getStorage('user');
    $this->ckanRequest = $ckanRequest;
    $this->termStorage = $this->entityTypeManager()->getStorage('taxonomy_term');
    $this->backLinkService = $backLinkService;
  }

  /**
   *
   */
  public function getTitle(NodeInterface $application) {
    return $application->getTitle();
  }

  /**
   *
   */
  public function content(NodeInterface $application): array {
    $tabs = [];
    $panels = [];

    // Description tab.
    $logoImage = [];
    if (($logo = $application->get('field_appliance_logo')) && $logo->entity) {
      $logoImage['url'] = $logo->entity->createFileUrl();
      $logoImage['alt'] = $logo->getValue()[0]['alt'];
    }

    $tags = [];
    if ($field_tags = $application->get('field_tags')->getValue()) {
      foreach ($field_tags as $tag) {
        if ($term = $this->termStorage->load($tag['target_id'])) {
          $tags[] = $term->getName();
        }
      }
    }

    $tabs['panel-description'] = $this->t('Description');
    $panels['description'] = [
      '#theme' => 'panel',
      '#id' => 'description',
      '#content' => [
        '#theme' => 'application-panel-description',
        '#tags' => $tags,
        '#node' => $application,
        '#logo' => $logoImage,
      ],
    ];

    return [
      '#theme' => 'application',
      '#node' => $application,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to all @type', ['@type' => $this->t('applications')]), 'donl_search.search.application'),
      '#editLinks' => $this->getEditLinks(Url::fromRoute('donl.application', ['application' => $application->id()]), $application, $this->userStorage->load($this->currentUser()->id())),
      '#link' => $application->get('field_link_application')->getString(),
      '#search' => $this->formBuilder()->getForm(SearchForm::class),
      '#panels' => $panels,
      '#tabs' => $tabs,
    ];
  }

}
