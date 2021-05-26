<?php

namespace Drupal\ckan\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\donl_value_list\ValueListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dataset settings form.
 */
class DatasetSettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\donl_value_list\ValueListInterface
   */
  protected $valueList;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\donl_value_list\ValueListInterface $valueList
   */
  public function __construct(ConfigFactoryInterface $config_factory, ValueListInterface $valueList) {
    parent::__construct($config_factory);
    $this->valueList = $valueList;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('donl.value_list')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ckan.dataset.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckan_dataset_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ckan.dataset.settings');

    $form['resources'] = [
      '#type' => 'details',
      '#title' => $this->t('Resource settings'),
      '#open' => TRUE,
    ];

    $form['resources']['allowed_file_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions'),
      '#required' => TRUE,
      '#description' => $this->t('A list with all allowed file extension for the resource file upload.'),
      '#default_value' => implode(' ', ($config->get('resource.allowed_file_extensions') ?? [])),
    ];

    $form['resources']['webservice'] = [
      '#type' => 'select2',
      '#title' => $this->t('Webservice file extensions'),
      '#options' => $this->valueList->getList('mdr:filetype_nal'),
      '#multiple' => TRUE,
      '#description' => $this->t('A list with all allowed file extension for the resource type webservice.'),
      '#default_value' => $config->get('resource.webservice'),
    ];

    $form['resources']['documentation'] = [
      '#type' => 'select2',
      '#title' => $this->t('Documentation file extensions'),
      '#options' => $this->valueList->getList('mdr:filetype_nal'),
      '#multiple' => TRUE,
      '#description' => $this->t('A list with all allowed file extension for the resource type documentation.'),
      '#default_value' => $config->get('resource.documentation'),
    ];

    $form['preview_functionality'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable preview functionality'),
      '#default_value' => $config->get('preview_functionality'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = array_intersect($form_state->getValue('webservice') ?? [], $form_state->getValue('documentation') ?? []);
    if ($values) {
      $form_state->setErrorByName('documentation', $this->t('A filetype may not be included in both lists. Currently the following file types are duplicated: @filetypes', ['@filetypes' => implode(' ', $values)]));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ckan.dataset.settings');
    $config->set('resource.webservice', array_values($form_state->getValue('webservice')) ?? []);
    $config->set('resource.documentation', array_values($form_state->getValue('documentation')) ?? []);
    $config->set('resource.allowed_file_extensions', explode(' ', $form_state->getValue('allowed_file_extensions')));
    $config->set('preview_functionality', $form_state->getValue('preview_functionality'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
