<?php

namespace Drupal\donl\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class DonlSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['donl.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load config.
    $config = $this->config('donl.settings');

    $form['show_facets_with_less_than'] = [
      '#type' => 'number',
      '#title' => $this->t('Automatically show facets with less than ... items'),
      '#default_value' => $config->get('show_facets_with_less_than'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('donl.settings');
    $config->set('show_facets_with_less_than', $form_state->getValue('show_facets_with_less_than'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
