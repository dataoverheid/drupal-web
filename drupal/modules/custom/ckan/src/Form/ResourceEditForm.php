<?php

namespace Drupal\ckan\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class ResourceEditForm extends ResourceBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_edit_resource_form';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($this->requireValidateForm($form_state)) {
      $response = $this->ckanRequest->setCkanUser($this->getUser())
        ->updateResource($this->getValues($form_state));
      if (!$response) {
        $this->setErrors($form, $form_state, $this->ckanRequest->getErrors());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger->addMessage($this->t('Resource updated'));
  }

}
