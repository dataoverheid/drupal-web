<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class DatasetPublishForm extends DatasetBaseForm {

  /**
   * @var \Drupal\ckan\Entity\Dataset
   */
  private $dataset;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_publish_dataset_form';
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
        '#step_title' => $this->t('Wrap up dataset'),
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
          '#completed' => TRUE,
        ],
        'finish' => [
          '#theme' => 'donl_form_step',
          '#title' => $this->t('Wrap up'),
          '#short_title' => $this->t('Wrap up'),
          '#icon' => 'icon-connected-globe',
          '#active' => TRUE,
          '#sub_steps' => [
            [
              '#theme' => 'donl_form_substep',
              '#id' => 0,
              '#title' => $this->t('Wrap up'),
              '#completed' => FALSE,
              '#active' => TRUE,
            ],
          ],
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

    if ($dataset->getPrivate()) {
      $text = $this->t('The dataset has been saved. You can now view or publish the dataset.');
      $buttonText = $this->t('Publish');
    }
    else {
      $text = $this->t('The dataset has been saved and published. You can now view or unpublish the dataset.');
      $buttonText = $this->t('Unpublish');
    }

    $form['wrapper']['main']['text'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $text,
      '#attributes' => [
        'class' => ['alert', 'alert--success'],
      ],
    ];

    $form['wrapper']['main']['continue_dataset'] = [
      '#title' => $this->t('View dataset'),
      '#type' => 'link',
      '#attributes' => ['class' => ['button', 'button--primary']],
      '#url' => Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getName()]),
    ];

    $form['wrapper']['main']['submit'] = [
      '#type' => 'submit',
      '#value' => $buttonText,
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];

    $form['#attributes'] = ['class' => ['donl-form', 'step-form']];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($this->dataset->getPrivate()) {
      $this->dataset->setPrivate(FALSE);
      $this->messenger->addMessage($this->t('@type successfully published.', ['@type' => $this->t('Dataset')]));
    }
    else {
      $this->dataset->setPrivate(TRUE);
      $this->messenger->addMessage($this->t('@type successfully unpublished.', ['@type' => $this->t('Dataset')]));
    }

    $this->ckanRequest->setCkanUser($this->getUser())->updateDataset($this->dataset);
    $form_state->setRedirect('ckan.dataset.view', ['dataset' => $this->dataset->getName()]);
  }

}
