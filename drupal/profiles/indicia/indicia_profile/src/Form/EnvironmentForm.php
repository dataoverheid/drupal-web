<?php

declare(strict_types = 1);

namespace Drupal\indicia_profile\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\indicia_profile\Util;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the environment configuration form.
 */
class EnvironmentForm extends FormBase {

  /**
   * Constructs an EnvironmentForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'indicia_profile_environment';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#title'] = $this->t('Choose environment');

    $form['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('Environment'),
      '#options' => $this->getEnvironmentList(),
      '#default_value' => Util::ENVIRONMENT_DEFAULT,
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    Util::rewriteSettingsFile([
      'settings' => [
        'config' => [
          'indicia_profile' => [
            'environment' => $form_state->getValue('environment'),
          ],
        ],
      ],
    ], TRUE);
  }

  /**
   * Retrieve a list of environments which can be used for select fields.
   *
   * @return array
   *   An associative array containing the labels of the environments, keyed by
   *   environment name.
   */
  protected function getEnvironmentList(): array {
    $environments = [];
    foreach (Util::getEnvironments() as $name => $environment) {
      $environments[$name] = $environment['label'];
    }
    return $environments;
  }

}
