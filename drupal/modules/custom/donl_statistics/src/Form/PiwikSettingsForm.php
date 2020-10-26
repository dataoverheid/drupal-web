<?php

namespace Drupal\donl_statistics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class PiwikSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['donl_statistics.piwik.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_statistics_piwik_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load config.
    $config = $this->config('donl_statistics.piwik.settings');

    $form['api_url'] = [
      '#type' => 'url',
      '#title' => $this->t('API URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_url'),
    ];

    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $config->get('token'),
    ];

    $form['site_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site id'),
      '#default_value' => $config->get('site_id'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('donl_statistics.piwik.settings');
    $config->set('api_url', $form_state->getValue('api_url'));
    $config->set('token', $form_state->getValue('token'));
    $config->set('site_id', $form_state->getValue('site_id'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
