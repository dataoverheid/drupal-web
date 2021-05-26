<?php

declare(strict_types = 1);

namespace Drupal\donl_recent\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the config settings for the recent content block.
 */
class DonlRecentContentSettingsForm extends ConfigFormBase {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs the DonlRecentContentSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
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
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donl_recent_content_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['donl_recent.recent_content_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('donl_recent.recent_content_settings');

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General settings'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
    ];

    $form['general']['limit_per_type'] = [
      '#title' => $this->t('Limit items per type'),
      '#type' => 'number',
      '#default_value' => $config->get('general')['limit_per_type'] ?? 1,
      '#description' => $this->t('The amount of items to show of each type when the block isn\'t filtered on the homepage.'),
    ];

    $form['general']['limit_per_filter'] = [
      '#title' => $this->t('Limit items per filter'),
      '#type' => 'number',
      '#default_value' => $config->get('general')['limit_per_filter'] ?? 10,
      '#description' => $this->t('The amount of items to show when the block is filtered on the homepage.'),
    ];

    $form['communities'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Community settings'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
    ];

    $form['communities']['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('The amount of items to show on each community page.'),
    ];

    /** @var \Drupal\node\NodeInterface $community */
    foreach ($this->nodeStorage->loadByProperties(['type' => 'community']) as $nid => $community) {
      $form['communities'][$nid] = [
        '#type' => 'fieldset',
        '#title' => $community->label(),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#tree' => TRUE,
      ];

      $form['communities'][$nid]['limit'] = [
        '#title' => $this->t('Limit items'),
        '#type' => 'number',
        '#default_value' => $config->get('communities')[$nid]['limit'] ?? 10,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('donl_recent.recent_content_settings');
    $config->set('general', $form_state->getValue('general'));
    $config->set('communities', $form_state->getValue('communities'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
