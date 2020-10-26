<?php

namespace Drupal\ckan\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class DatasetEditForm extends DatasetBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_edit_dataset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($this->requireValidateForm($form_state)) {
      $response = $this->ckanRequest->setCkanUser($this->getUser())
        ->updateDataset($this->getValues($form_state));
      if (!$response) {
        $this->setErrors($form, $form_state, $this->ckanRequest->getErrors());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger->addMessage($this->t('Dataset updated'));
  }

}
