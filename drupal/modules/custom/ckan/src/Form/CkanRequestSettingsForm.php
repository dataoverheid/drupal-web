<?php

namespace Drupal\ckan\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class CkanRequestSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ckan.request.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckan_request_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load config.
    $config = $this->config('ckan.request.settings');

    $form['ckan_api'] = [
      '#type' => 'details',
      '#title' => $this->t('CKAN API Settings'),
      '#open' => TRUE,
    ];

    $form['ckan_api']['ckan_url'] = [
      '#type' => 'url',
      '#title' => $this->t('CKAN Url'),
      '#required' => TRUE,
      '#default_value' => $config->get('ckan_url'),
    ];

    $form['ckan_api']['ckan_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CKAN Api Key'),
      '#default_value' => $config->get('ckan_api_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ckan.request.settings');
    $config->set('ckan_url', $form_state->getValue('ckan_url'));
    $config->set('ckan_api_key', $form_state->getValue('ckan_api_key'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
