<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\MappingServiceInterface;
use Drupal\ckan\SortDatasetResourcesServiceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\donl_dcat_validation\DcatValidationServiceInterface;
use Drupal\donl_value_list\ValueListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resource order form.
 */
class ResourceOrderForm extends BaseForm {

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
   * @param \Drupal\donl_value_list\ValueListInterface $valueList
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   * @param \Drupal\donl_dcat_validation\DcatValidationServiceInterface $dcatValidationService
   * @param \Drupal\ckan\SortDatasetResourcesServiceInterface $sortDatasetResourcesService
   *   The sort dataset resource service.
   */
  public function __construct(CkanRequestInterface $ckanRequest, ValueListInterface $valueList, MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager, RequestStack $request, DcatValidationServiceInterface $dcatValidationService, MappingServiceInterface $mappingService, SortDatasetResourcesServiceInterface $sortDatasetResourcesService) {
    parent::__construct($ckanRequest, $valueList, $messenger, $entityTypeManager, $request, $dcatValidationService, $mappingService);
    $this->sortDatasetResourcesService = $sortDatasetResourcesService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request'),
      $container->get('donl.value_list'),
      $container->get('messenger'),
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('donl_dcat_validation.validation_service'),
      $container->get('ckan.mapping'),
      $container->get('ckan.sort_dataset_resources'),
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

    $form['full_form_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => ['full-form-wrapper']],
    ];

    $form['full_form_wrapper']['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'main-wrapper',
        ],
      ],
    ];

    $form['full_form_wrapper']['wrapper']['main'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'main',
        ],
      ],
    ];

    $subSteps = $this->getSubSteps($form, 'basic');
    $subSteps[] = $this->getSubSteps($form, 'advanced', count($subSteps));
    $form['#attributes']['class'] = ['donl-form', 'step-form'];
    $form['full_form_wrapper']['header'] = [
      '#weight' => -45,
      '#theme' => 'donl_form_header',
      '#type' => 'dataset',
      '#summary' => [
        '#theme' => 'donl_form_summary',
        '#title' => $this->t('Dataset'),
        '#step_title' => $this->t('Manage data sources'),
        '#fields' => [
          'title' => [
            'field' => 'title',
            'title' => $this->t('Title'),
            'value' => $dataset->getTitle(),
          ],
          'owner' => [
            'field' => 'owner',
            'title' => $this->t('Owner'),
            'value' => $this->mappingService->getOrganizationName($dataset->getAuthority()),
          ],
          'licence' => [
            'field' => 'licence',
            'title' => $this->t('Licence'),
            'value' => $this->mappingService->getLicenseName($dataset->getLicenseId()),
          ],
          'changed' => [
            'field' => 'changed',
            'title' => $this->t('Changed'),
            'value' => $dataset->getModified()->format('d-m-Y H:i'),
          ],
          'status' => [
            'field' => 'status',
            'title' => $this->t('Status'),
            'value' => $this->mappingService->getStatusName($dataset->getDatasetStatus()),
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
          '#title' => $this->t('Manage data sources'),
          '#short_title' => $this->t('Data source'),
          '#icon' => 'icon-databron',
          '#active' => TRUE,
          '#sub_steps' => $subSteps,
        ],
        'finish' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Wrap up'),
          '#short_title' => $this->t('Wrap up'),
          '#icon' => 'icon-connected-globe',
        ],
      ],
    ];

    $form['full_form_wrapper']['wrapper']['sidebar'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['donl-form-sidebar']],
      '#weight' => -10,
    ];

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'sidebar-nav',
          $form_state->getValue('advanced') ? 'advanced' : '',
        ],
      ],
    ];


    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['explanation'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#attributes' => ['class' => ['sidebar-text']],
      '#value' => $this->t('Use drag and drop to change the order of the items. Then press store to save your changes.'),
    ];


    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['sidebar-nav-actions']],
    ];

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['cancel'] =
      Link::createFromRoute($this->t('Cancel'), 'ckan.dataset.datasources', ['dataset' => $dataset->id])
        ->toRenderable();

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
        'id' => 'donl-form-submit-button',
      ],
    ];

    $i = 0;
    $form['full_form_wrapper']['wrapper']['main']['resources']['#tree'] = TRUE;
    foreach ($this->sortDatasetResourcesService->getSortedResources($dataset) as $group => $resources) {
      $form['full_form_wrapper']['wrapper']['main']['resources'][$i] = [
        '#type' => 'table',
        '#caption' => $this->t($group),
        '#header' => [
          $this->t('Name'),
          $this->t('File type'),
          $this->t('Creation date'),
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
        '#attributes' => ['class' => ['resource-order-table']],
      ];

      /** @var \Drupal\ckan\Entity\Resource $resource */
      foreach ($resources as $resource) {
        $form['full_form_wrapper']['wrapper']['main']['resources'][$i][$resource->getId()] = [
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
              'class' => ['hidden', 'resources-order-weight'],
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
    $form_state->setRedirect('ckan.dataset.datasources', ['dataset' => $dataset->id]);
  }

}
