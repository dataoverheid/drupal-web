<?php

namespace Drupal\ckan\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class DatasetTextSettingsForm extends ConfigFormBase {

  /**
   * @var array
   *   Array with the text_format form fields.
   */
  private $fields;

  /**
   *
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $languages = [
      '' => $this->t('Dutch'),
      '_en' => $this->t('English'),
    ];
    $fields = [
      'description' => $this->t('Description text'),
      'webservices' => $this->t('Webservices text'),
      'downloadable-files' => $this->t('Downloadable files text'),
      'documentation' => $this->t('Documentation text'),
      'condition' => $this->t('Condition text'),
      'example' => $this->t('Example text'),
      'forum' => $this->t('Forum text'),
      'metadata' => $this->t('Metadata text'),
      'relations_groups' => $this->t('Relations groups text'),
      'relations_comparable' => $this->t('Relations comparable text'),
      'relations_related_resources' => $this->t('Relations related resources text'),
      'relations_sourcess' => $this->t('Relations this dataset is based on text'),
    ];
    foreach ($fields as $key => $field) {
      foreach ($languages as $suffix => $language) {
        $this->fields[$key . '_text' . $suffix] = $field . ' ' . $language;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ckan.datasettext.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckan_datasettext_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(current($this->getEditableConfigNames()));

    foreach ($this->fields as $name => $title) {
      $form[$name] = [
        '#type' => 'text_format',
        '#title' => $title,
        '#default_value' => $config->get($name)['value'],
        '#format' => 'full_html',
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(current($this->getEditableConfigNames()));
    foreach ($this->fields as $name => $title) {
      $config->set($name, $form_state->getValue($name));
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
