<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\MappingServiceInterface;
use Drupal\ckan\SortDatasetResourcesServiceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resource order form.
 */
class ResourceOrderForm extends FormBase {

  /**
   * The ckan request.
   *
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * The mapping service.
   *
   * @var \Drupal\ckan\MappingServiceInterface
   */
  protected $mappingService;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * The sort dataset resource service.
   *
   * @var \Drupal\ckan\SortDatasetResourcesServiceInterface
   */
  protected $sortDatasetResourcesService;

  /**
   * ResourceOrderForm constructor.
   *
   * @param \Drupal\ckan\CkanRequestInterface $ckanRequest
   *   The ckan request.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\ckan\MappingServiceInterface $mappingService
   *   The mapping service.
   * @param \Drupal\ckan\SortDatasetResourcesServiceInterface $sortDatasetResourcesService
   *   The sort dataset resource service.
   */
  public function __construct(CkanRequestInterface $ckanRequest, EntityTypeManagerInterface $entityTypeManager, MappingServiceInterface $mappingService, SortDatasetResourcesServiceInterface $sortDatasetResourcesService) {
    $this->ckanRequest = $ckanRequest;
    $this->mappingService = $mappingService;
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->sortDatasetResourcesService = $sortDatasetResourcesService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('entity.manager'),
      $container->get('ckan.mapping'),
      $container->get('ckan.sort_dataset_resources')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_order_resource_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL): array {
    if (!$dataset) {
      throw new NotFoundHttpException();
    }
    $form_state->set('dataset', $dataset);

    $i = 0;
    $form['resources']['#tree'] = TRUE;
    foreach ($this->sortDatasetResourcesService->getSortedResources($dataset) as $group => $resources) {
      $form['resources'][$i] = [
        '#type' => 'table',
        '#caption' => $this->t($group),
        '#header' => [
          $this->t('Name'),
          $this->t('File type'),
          $this->t('Created'),
          $this->t('Weight'),
        ],
        '#empty' => $this->t('No resources found.'),
        '#tableselect' => FALSE,
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'resources-order-weight',
          ],
        ],
      ];

      /** @var \Drupal\ckan\Entity\Resource $resource */
      foreach ($resources as $resource) {
        $form['resources'][$i][$resource->getId()] = [
          'name' => [
            '#plain_text' => $resource->getName(),
          ],
          'file_type' => [
            '#plain_text' => $this->mappingService->getFileFormatName($resource->getFormat()),
          ],
          'created' => [
            '#plain_text' => ($created = $resource->getCreated()) ? $created->format('d-m-Y') : '',
          ],
          'weight' => [
            '#type' => 'weight',
            '#title' => $this->t('Weight for @title', ['@title' => $resource->getName()]),
            '#title_display' => 'invisible',
            '#default_value' => $resource->getPosition() ?? 0,
            '#attributes' => [
              'class' => ['resources-order-weight']
            ],
          ],
          '#weight' => $resource->getPosition() ?? 0,
          '#attributes' => [
            'class' => ['draggable'],
          ],
        ];
      }
      $i++;
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $dataset = $form_state->get('dataset');
    $currentResources = [];
    foreach ($dataset->getResources() as $resource) {
      $currentResources[$resource->getId()] = $resource;
    }

    $resources = [];
    $position = 0;
    foreach ($form_state->getValue('resources') ?? [] as $values) {
      foreach (array_keys($values) as $id) {
        $resource = $currentResources[$id];
        $resource->setPosition($position);
        $resources[] = $resource;
        $position++;
      }
    }

    $dataset->setResources($resources);
    $this->ckanRequest->setCkanUser($this->userStorage->load($this->currentUser()->id()));
    $this->ckanRequest->updateDataset($dataset);
  }

}
