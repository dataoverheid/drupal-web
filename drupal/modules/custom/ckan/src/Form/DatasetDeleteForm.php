<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class DatasetDeleteForm extends DatasetBaseForm {

  /**
   * @var \Drupal\ckan\Entity\Dataset
   */
  private $dataset;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_delete_dataset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL): array {
    if (!$dataset) {
      throw new NotFoundHttpException();
    }

    $this->dataset = $dataset;

    $form['header'] = [
      '#theme' => 'donl_form_header',
      '#type' => 'dataset',
      '#summary' => [
        '#theme' => 'donl_form_summary',
        '#title' => $this->t('Dataset'),
        '#step_title' => $this->t('Delete dataset'),
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
        'delete' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Delete'),
          '#short_title' => $this->t('Delete'),
          '#icon' => 'icon-bin',
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
        '@entityType' => $this->t('dataset'),
        '%title' => $dataset->getTitle(),
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
      '#url' => Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getName()]),
    ];

    $form['#attributes'] = ['class' => ['donl-form', 'step-form']];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->ckanRequest->setCkanUser($this->getUser())->deleteDataset($this->dataset->getId());
    $form_state->setRedirect('donl_search.search.dataset');
  }

}
