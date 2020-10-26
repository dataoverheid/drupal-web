<?php

namespace Drupal\donl_value_list\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class ValueListSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['donl_value_list.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_value_list_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load config.
    $config = $this->config('donl_value_list.settings');

    $form['locations'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Value list locations'),
      '#tree' => TRUE,
      '#prefix' => '<div id="location-wrapper">',
      '#suffix' => '</div>',
      '#field_prefix' => $this->t('Either use the full base url ending with a slash, or the full path on the server ending with a slash.'),
    ];

    $locations = $config->get('locations') ?? [];
    $locationCount = $form_state->get('locationCount');
    if (empty($locationCount)) {
      $locationCount = \count($locations) + 1;
      $form_state->set('locationCount', $locationCount);
    }
    for ($i = 0; $i < $locationCount; $i++) {
      $form['locations'][$i] = [
        '#type' => 'textfield',
        '#title' => 'location ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $locations[$i] ?? NULL,
      ];
    }

    $form['locations']['addLocation'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another @title', ['@title' => $this->t('location')]),
      '#submit' => ['::addOneLocation'],
      '#ajax' => [
        'callback' => '::addMoreLocationCallback',
        'wrapper' => 'location-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('donl_value_list.settings');
    $config->set('locations', $this->getMultiValue($form_state->getValue('locations') ?? []));
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   *
   */
  public function addOneLocation(array &$form, FormStateInterface $form_state): void {
    $form_state->set('locationCount', $form_state->get('locationCount') + 1);
    $form_state->setRebuild();
  }

  /**
   *
   */
  public function addMoreLocationCallback(array &$form, FormStateInterface $form_state) {
    return $form['locations'];
  }

  /**
   * Create the correct value array for fields with an add more button.
   *
   * @param array $value
   *
   * @return array
   */
  protected function getMultiValue(array $value) {
    $return = [];
    foreach ($value as $k => $v) {
      if (is_int($k) && !empty($v)) {
        $return[] = $v;
      }
    }

    return $return;
  }

}
