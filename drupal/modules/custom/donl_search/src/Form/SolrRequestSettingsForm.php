<?php

namespace Drupal\donl_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class SolrRequestSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['donl_search.sorl_request.settings'];
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
    $config = $this->config('donl_search.sorl_request.settings');

    $form['solr_url'] = [
      '#type' => 'url',
      '#title' => $this->t('SOLR Url'),
      '#default_value' => $config->get('solr_url'),
    ];

    $form['solr_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SOLR Username'),
      '#default_value' => $config->get('solr_username'),
    ];

    $form['solr_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SOLR Password'),
      '#default_value' => $config->get('solr_password'),
    ];

    $form['solr_cores'] = [
      '#type' => 'details',
      '#title' => $this->t('SOLR Cores'),
      '#open' => TRUE,
    ];

    $form['solr_cores']['solr_search_core'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search core'),
      '#default_value' => $config->get('solr_search_core'),
    ];

    $form['solr_cores']['solr_suggest_core'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Suggest core'),
      '#default_value' => $config->get('solr_suggest_core'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('donl_search.sorl_request.settings');
    $config->set('solr_url', $form_state->getValue('solr_url'));
    $config->set('solr_username', $form_state->getValue('solr_username'));
    $config->set('solr_password', $form_state->getValue('solr_password'));
    $config->set('solr_search_core', $form_state->getValue('solr_search_core'));
    $config->set('solr_suggest_core', $form_state->getValue('solr_suggest_core'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
