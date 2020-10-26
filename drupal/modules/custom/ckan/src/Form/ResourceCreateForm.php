<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class ResourceCreateForm extends ResourceBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckan_create_resource_form';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($this->requireValidateForm($form_state)) {
      $response = $this->ckanRequest->setCkanUser($this->getUser())
        ->createResource($this->getValues($form_state));
      if (!$response) {
        $this->setErrors($form, $form_state, $this->ckanRequest->getErrors());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger->addMessage($this->t('Resource created'));

    // Get dataset from arguments (if available).
    $buildInfo = $form_state->getBuildInfo();
    $dataset = $buildInfo['args'][0] ?? NULL;
    if ($dataset instanceof Dataset) {
      $form_state->setRedirect('ckan.dataset.datasources', ['dataset' => $dataset->getName()]);
    }
  }

}
