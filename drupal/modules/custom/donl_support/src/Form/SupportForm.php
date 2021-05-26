<?php

namespace Drupal\donl_support\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class SupportForm extends ConfigFormBase {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeStorage;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['donl_support.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_support_form';
  }

  /**
   * SupportForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($config_factory);
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load config.
    $config = $this->config('donl_support.settings');

    $options = [];
    $pageNodes = $this->nodeStorage->loadByProperties([
      'type' => 'landingpage',
    ]);

    usort($pageNodes, static function ($a, $b) {
      return $a->toArray()["created"][0]['value'] < $b->toArray()["created"][0]['value'];
    });

    foreach ($pageNodes as $pageNode) {
      $node = $pageNode->toArray();
      $options[$pageNode->id()] = $pageNode->label() . ' (' . date('d-m-Y', $node["created"][0]['value']) . ')';
    }

    $form['left_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Left title'),
      '#default_value' => $config->get('left_title'),
    ];

    $form['left_links'] = [
      '#type' => 'select2',
      '#title' => $this->t('Left links'),
      '#options' => $options,
      '#multiple' => TRUE,
      '#default_value' => $config->get('left_links'),
    ];

    $form['center_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Center title'),
      '#default_value' => $config->get('center_title'),
    ];
    $form['center_links'] = [
      '#type' => 'select2',
      '#title' => $this->t('Center links'),
      '#options' => $options,
      '#multiple' => TRUE,
      '#default_value' => $config->get('center_links'),
    ];

    $form['right_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Right title'),
      '#default_value' => $config->get('right_title'),
    ];
    $form['right_links'] = [
      '#type' => 'select2',
      '#title' => $this->t('Right links'),
      '#options' => $options,
      '#multiple' => TRUE,
      '#default_value' => $config->get('right_links'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('donl_support.settings');
    $config->set('left_title', $form_state->getValue('left_title'));
    $config->set('left_links', $form_state->getValue('left_links'));
    $config->set('center_title', $form_state->getValue('center_title'));
    $config->set('center_links', $form_state->getValue('center_links'));
    $config->set('right_title', $form_state->getValue('right_title'));
    $config->set('right_links', $form_state->getValue('right_links'));
    $config->save();
  }

}
