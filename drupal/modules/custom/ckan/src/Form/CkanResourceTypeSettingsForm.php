<?php

namespace Drupal\ckan\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\donl_value_list\ValueListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class CkanResourceTypeSettingsForm extends ConfigFormBase {

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
    return ['ckan.resourcetype.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckan_resource_type_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load config.
    $config = $this->config('ckan.resourcetype.settings');

    $form['webservice'] = [
      '#type' => 'select',
      '#title' => $this->t('Webservice file extensions'),
      '#options' => $this->valueList->getList('mdr:filetype_nal', FALSE),
      '#multiple' => TRUE,
      '#description' => $this->t('A list with all allowed file extension for the resource type webservice.'),
      '#default_value' => $config->get('webservice'),
    ];

    $form['documentation'] = [
      '#type' => 'select',
      '#title' => $this->t('Documentation file extensions'),
      '#options' => $this->valueList->getList('mdr:filetype_nal', FALSE),
      '#multiple' => TRUE,
      '#description' => $this->t('A list with all allowed file extension for the resource type documentation.'),
      '#default_value' => $config->get('documentation'),
    ];

    $form['#attached']['library'][] = 'ckan/chosen_init';

    return parent::buildForm($form, $form_state);
  }

  /**
   *
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
    $config = $this->config('ckan.resourcetype.settings');
    $config->set('webservice', array_values($form_state->getValue('webservice')) ?? []);
    $config->set('documentation', array_values($form_state->getValue('documentation')) ?? []);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
