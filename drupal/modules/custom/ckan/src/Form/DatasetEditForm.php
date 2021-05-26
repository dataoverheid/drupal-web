<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class DatasetEditForm extends DatasetBaseForm {

  /**
   * @var \Drupal\ckan\Entity\Dataset|null
   */
  private $dataset;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_edit_dataset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL): array {
    if (!$dataset) {
      throw new NotFoundHttpException();
    }

    $this->dataset = $dataset;
    $form = parent::buildForm($form, $form_state, $dataset);
    $form['#attributes']['class'][] = 'donl-form-edit';

    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $dataset->getId(),
    ];

    $form['full_form_wrapper']['header']['#steps']['resource']['#url'] = Url::fromRoute('ckan.dataset.datasources', ['dataset' => $dataset->id])->toString();

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['submit-overlay-wrapper']['submit_overlay']['#attributes']['data-next-form-text'] = $this->t('Save and view');

    $form['full_form_wrapper']['wrapper']['sidebar']['sidebar_nav']['actions']['datasources'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue to datasources'),
      '#attributes' => [
        'type' => 'button',
        'class' => ['button', 'button--primary', 'button--datasources'],
        'data-in-form-hidden' => '',
        // Do not change this as it will be used to trigger the submit handler.
        'data-submit' => 'true',
      ],
      '#submit' => ['::continueToDatasources'],
    ];

    if (count($dataset->resources) > 1) {
      $form['full_form_wrapper']['header']['#steps']['resource']['#completed'] = TRUE;
    }
    if (!$dataset->private) {
      $form['full_form_wrapper']['header']['#steps']['finish']['#completed'] = TRUE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($this->requireValidateForm($form_state)) {
      if ($errors = $this->dcatValidationService->dataset($form_state->cleanValues()->getValues())) {
        $this->setErrors($form, $form_state, $errors);
      }
      else {
        $response = $this->ckanRequest->setCkanUser($this->getUser())->updateDataset($this->getValues($form_state));
        if (!$response) {
          $this->setErrors($form, $form_state, $this->ckanRequest->getErrors());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger->addMessage($this->t('Dataset updated'));

    $form_state->setRedirect('donl_search.search.dataset');
    if ($id = $this->dataset->getId()) {
      $form_state->setRedirect('ckan.dataset.view', ['dataset' => $this->dataset->getName()]);
    }
  }

  /**
   * Submits the dataset and redirects to the datasets.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function continueToDatasets(array &$form, FormStateInterface $form_state): void {
    $this->messenger->addMessage($this->t('Dataset created.'));
    $form_state->setRedirect('donl_search.search.dataset');
    if ($id = $form_state->get('dataset')) {
      $form_state->setRedirect('ckan.dataset.datasources', ['dataset' => $id]);
    }
  }

  /**
   * Submits the dataset and redirects to the data resources.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function continueToDatasources(array &$form, FormStateInterface $form_state): void {
    $this->messenger->addMessage($this->t('Dataset updated.'));
    $form_state->setRedirect('donl_search.search.dataset');
    if ($id = $this->dataset->getId()) {
      $form_state->setRedirect('ckan.dataset.datasources', ['dataset' => $id]);
    }
  }

}
