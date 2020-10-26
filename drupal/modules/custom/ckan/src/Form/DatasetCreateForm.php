<?php

namespace Drupal\ckan\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class DatasetCreateForm extends DatasetBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_create_dataset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($this->requireValidateForm($form_state)) {
      $response = $this->ckanRequest->setCkanUser($this->getUser())
        ->createDataset($this->getValues($form_state));

      $form_state->set('dataset', $response->name ?? NULL);
      if (!$response) {
        $this->setErrors($form, $form_state, $this->ckanRequest->getErrors());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger->addMessage($this->t('Dataset created'));

    if ($id = $form_state->get('dataset')) {
      $form_state->setRedirect('ckan.dataset.view', ['dataset' => $id]);
    }
    else {
      $form_state->setRedirect('donl_search.search.dataset');
    }
  }

}
