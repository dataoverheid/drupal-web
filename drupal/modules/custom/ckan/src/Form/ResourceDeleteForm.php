<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\Entity\Resource;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class ResourceDeleteForm extends ResourceBaseForm {

  /**
   * @var \Drupal\ckan\Entity\Dataset
   */
  private $dataset;

  /**
   * @var \Drupal\ckan\Entity\Resource
   */
  private $resource;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_delete_resource_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL, Resource $resource = NULL): array {
    if (!$dataset || !$resource) {
      throw new NotFoundHttpException();
    }

    $this->dataset = $dataset;
    $this->resource = $resource;

    $form['full_form_wrapper']['header'] = [
      '#weight' => -45,
      '#theme' => 'donl_form_header',
      '#type' => 'dataset',
      '#summary' => [
        '#theme' => 'donl_form_summary',
        '#title' => $this->t('Dataset'),
        '#step_title' => $this->t('Delete data sources'),
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
            'value' => (!$dataset->getPrivate() ? $this->t('Yes') : $this->t('No')),
          ],
        ],
      ],
      '#steps' => [
        'delete' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Delete'),
          '#short_title' => $this->t('Delete'),
          '#icon' => 'icon-connected-globe',
          '#active' => TRUE,
        ],
      ],
    ];

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['main-wrapper']],
    ];

    $form['wrapper']['main'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['main']],
    ];

    $form['wrapper']['main']['text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Are you sure you want to delete the @entityType %title? This action cannot be undone.', [
        '@entityType' => $this->t('data source'),
        '%title' => $resource->name,
      ]),
    ];

    $form['wrapper']['main']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];

    $form['wrapper']['main']['cancel'] = [
      '#title' => $this->t('Cancel'),
      '#type' => 'link',
      '#url' => Url::fromRoute('ckan.dataset.datasources', ['dataset' => $dataset->getName()]),
    ];

    $form['#attributes'] = ['class' => ['donl-form', 'step-form']];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->ckanRequest->setCkanUser($this->getUser())->deleteResource($this->resource->getId());
    $form_state->setRedirect('ckan.dataset.datasources', ['dataset' => $this->dataset->getName()]);
  }

}
