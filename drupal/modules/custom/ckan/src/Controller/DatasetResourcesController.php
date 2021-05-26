<?php

namespace Drupal\ckan\Controller;

use Drupal\ckan\DatasetEditLinksTrait;
use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\MappingServiceInterface;
use Drupal\ckan\SortDatasetResourcesServiceInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\donl_search_backlink\BackLinkService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DatasetResourcesController.
 */
class DatasetResourcesController extends ControllerBase {

  use DatasetEditLinksTrait;

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
   * The sort dataset resources service.
   *
   * @var \Drupal\ckan\SortDatasetResourcesServiceInterface
   */
  protected $sortDatasetResourcesService;

  /**
   * DatasetResourcesController Constructor.
   *
   * @param \Drupal\ckan\MappingServiceInterface $mappingService
   *    The mapping service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *    The entity type manager
   * @param \Drupal\donl_search_backlink\BackLinkService $backLinkService
   *   The back link service.
   * @param \Drupal\ckan\SortDatasetResourcesServiceInterface $sortDatasetResourcesService
   *   The sort dataset resources service.
   */
  public function __construct(MappingServiceInterface $mappingService, EntityTypeManagerInterface $entityTypeManager, BackLinkService $backLinkService, SortDatasetResourcesServiceInterface $sortDatasetResourcesService) {
    $this->mappingService = $mappingService;
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->backLinkService = $backLinkService;
    $this->sortDatasetResourcesService = $sortDatasetResourcesService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.mapping'),
      $container->get('entity_type.manager'),
      $container->get('donl_search_backlink.backlink'),
      $container->get('ckan.sort_dataset_resources')
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
    /* @var \Drupal\ckan\User\CkanUserInterface $ckanUser */
    $ckanUser = $this->userStorage->load($this->currentUser()->id());

    if (!$ckanUser || !($ckanUser->isAdministrator() || $ckanUser->getCkanId() === $dataset->getCreatorUserId())) {
      throw new NotFoundHttpException();
    }
    $header = [
      '#theme' => 'donl_form_header',
      '#type' => 'dataset',
      '#summary' => [
        '#theme' => 'donl_form_summary',
        '#title' => $this->t('Dataset'),
        '#step_title' => $this->t('Manage data sources'),
        '#fields' => [
          'title' => [
            'title' => $this->t('Title'),
            'value' => $dataset->getTitle(),
          ],
          'owner' => [
            'title' => $this->t('Owner'),
            'value' => $this->mappingService->getOrganizationName($dataset->getAuthority()),
          ],
          'licence' => [
            'title' => $this->t('Licence'),
            'value' => $this->mappingService->getLicenseName($dataset->getLicenseId()),
          ],
          'changed' => [
            'title' => $this->t('Changed'),
            'value' => $dataset->getModified()->format('d-m-Y H:i'),
          ],
          'status' => [
            'title' => $this->t('Status'),
            'value' => $this->mappingService->getStatusName($dataset->getDatasetStatus()),
          ],
          'published' => [
            'title' => $this->t('Published'),
            'value' => ($dataset && !$dataset->getPrivate()) ? $this->t('Yes') : $this->t('No'),
          ],
        ],
      ],
      '#steps' => [
        'dataset' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Register dataset'),
          '#short_title' => $this->t('Dataset'),
          '#completed' => TRUE,
        ],
        'resource' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Add data source'),
          '#short_title' => $this->t('Data source'),
          '#icon' => 'icon-databron',
          '#active' => TRUE,
          '#sub_steps' => [
            [
              '#theme' => 'donl_form_substep',
              '#id' => 0,
              '#title' => $this->t('Basic data source data'),
              '#completed' => (bool) count($dataset->resources),
            ],
          ],
        ],
        'finish' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Wrap up'),
          '#short_title' => $this->t('Wrap up'),
          '#icon' => 'icon-connected-globe',
        ],
      ],
    ];

    $sortedResources = [];
    if ($resources = $this->sortDatasetResourcesService->getSortedResources($dataset)) {
      foreach ($resources as $title => $resource) {
        usort($resource, static function ($a, $b) {
          return strcmp($a->created, $b->created);
        });
        $key = strtolower(str_replace(' ', '-', $title));
        $sortedResources[$key] = $resource;
      }
    }

    $build = [
      '#theme' => 'dataset_resources_manage',
      '#dataset' => $dataset,
      '#sortedResources' => $sortedResources,
      '#header' => $header,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    $build['#attached']['library'][] = 'ckan/resource-overview';
    return $build;
  }

}
