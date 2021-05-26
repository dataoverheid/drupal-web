<?php

namespace Drupal\donl_piwik\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Piwik Settings Form.
 */
class PiwikSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['donl_piwik.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_piwik_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('donl_piwik.settings');

    $form['header'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Piwik header code'),
      '#default_value' => $config->get('header'),
    ];

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Piwik body code'),
      '#default_value' => $config->get('body'),
    ];

    $form['dataLayerEnabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable DataLayers'),
      '#default_value' => $config->get('dataLayerEnabled'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('donl_piwik.settings');
    $config->set('header', $form_state->getValue('header'));
    $config->set('body', $form_state->getValue('body'));
    $config->set('dataLayerEnabled', $form_state->getValue('dataLayerEnabled'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
