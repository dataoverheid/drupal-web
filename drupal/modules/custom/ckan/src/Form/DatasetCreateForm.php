<?php

namespace Drupal\ckan\Form;

use Drupal\ckan\Entity\Dataset;
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
  public function buildForm(array $form, FormStateInterface $form_state, Dataset $dataset = NULL): array {
    $form = parent::buildForm($form, $form_state, $dataset);
    $form['#attributes']['class'][] = 'donl-form-create';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($this->requireValidateForm($form_state)) {
      $dataset = $this->getValues($form_state);

      // Set a default value for identifier.
      if (!$identifier = $form_state->cleanValues()->getValue('identifier')) {
        $identifier = $this->getRequest()->getSchemeAndHttpHost() . '/dataset/' . $dataset->getName();
        $form_state->setValue('identifier', $this->getRequest()->getSchemeAndHttpHost() . '/dataset/' . $dataset->getName());
        $dataset->setIdentifier($identifier);
      }

      if ($errors = $this->dcatValidationService->dataset($form_state->getValues())) {
        $this->setErrors($form, $form_state, $errors);
      }
      else {
        $response = $this->ckanRequest->setCkanUser($this->getUser())->createDataset($dataset);
        $form_state->set('dataset', $response->name ?? NULL);
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
    $this->messenger->addMessage($this->t('Dataset created.'));
    $form_state->setRedirect('donl_search.search.dataset');
    if ($id = $form_state->get('dataset')) {
      $form_state->setRedirect('ckan.dataset.datasources', ['dataset' => $id]);
    }
  }

}
