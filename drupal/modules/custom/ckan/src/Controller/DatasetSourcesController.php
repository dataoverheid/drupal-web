<?php

namespace Drupal\ckan\Controller;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\MappingServiceInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\donl_search_backlink\BackLinkService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DatasetSourcesController.
 */
class DatasetSourcesController extends ControllerBase {

  /**
   * The mapping service.
   *
   * @var \Drupal\ckan\MappingServiceInterface
   */
  protected $mappingService;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The back link service.
   *
   * @var \Drupal\donl_search_backlink\BackLinkService
   */
  protected $backLinkService;

  /**
   * DatasetSourcesController Constructor.
   *
   * @param \Drupal\ckan\MappingServiceInterface $mappingService
   *    The mapping service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *    The entity type manager
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   *   The back link service.
   */
  public function __construct(MappingServiceInterface $mappingService, EntityTypeManagerInterface $entityTypeManager, BackLinkService $backLinkService) {
    $this->mappingService = $mappingService;
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->backLinkService = $backLinkService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.mapping'),
      $container->get('entity_type.manager'),
      $container->get('donl_search_backlink.backlink')
    );
  }

  /**
   *
   */
  public function title(Dataset $dataset) {
    return $dataset->getTitle();
  }

  /**
   *
   */
  public function content(Dataset $dataset) {
    $canEdit = FALSE;
    /** @var \Drupal\ckan\User\CkanUserInterface $ckanUser */
    if ($ckanUser = $this->userStorage->load($this->currentUser()->id())) {
      $canEdit = $ckanUser->getCkanId() === $dataset->getCreatorUserId() || $ckanUser->isAdministrator();
    }

    $addLink = NULL;
    if ($canEdit) {
      $options = ['attributes' => ['class' => ['button', 'button--primary']]];
      $addLink = Link::createFromRoute($this->t('Add data source'), 'ckan.resource.create', ['dataset' => $dataset->getId()], $options);
    }

    if (!empty($resources = $dataset->getResources())) {
      foreach ($resources as $resource) {
        if ($canEdit) {
          $resource->editUrl = Link::createFromRoute($this->t('Edit'), 'ckan.resource.edit', [
            'dataset' => $dataset->getId(),
            'resource' => $resource->getId(),
          ]);
          $resource->removeUrl = Link::createFromRoute($this->t('Delete'), 'ckan.resource.delete', [
            'dataset' => $dataset->getId(),
            'resource' => $resource->getId(),
          ]);
        }
      }
    }

    return [
      '#theme' => 'dataset_edit_datasources',
      '#dataset' => $dataset,
      '#backLink' => $this->backLinkService->createBackLink($this->t('Back to dataset @datasetTitle', ['@datasetTitle' => $dataset->getTitle()]), 'ckan.dataset.view', ['dataset' => $dataset->getName()]),
      '#addLink' => $addLink,
      '#canEdit' => $canEdit,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
