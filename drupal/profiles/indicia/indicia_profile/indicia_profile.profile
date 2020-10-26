<?php

/**
 * @file
 * Define install tasks for the Indicia install profile.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Installer\Exception\InstallerException;
use Drupal\indicia_profile\Form\EnvironmentForm;
use Drupal\indicia_profile\Util;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function indicia_profile_form_install_configure_form_alter(array &$form, FormStateInterface $form_state) {
  $form_state->loadInclude('indicia_profile', 'inc', 'includes/form_alter.install_configure');
  indicia_profile_install_configure_form_alter($form, $form_state);
}

/**
 * Implements hook_theme_registry_alter().
 */
function indicia_profile_theme_registry_alter(&$theme_registry) {
  $theme_registry['install_page']['path'] = drupal_get_path('profile', 'indicia_profile') . '/templates';
}

/**
 * Implements hook_preprocess_install_page().
 */
function indicia_profile_preprocess_install_page(&$variables) {
  $variables['#attached']['library'][] = 'indicia_profile/install';
  $variables['logo'] = [
    '#theme' => 'image',
    '#uri' => drupal_get_path('profile', 'indicia_profile') . '/images/logo.png',
  ];
}

/**
 * Implements hook_install_tasks().
 */
function indicia_profile_install_tasks(&$install_state) {
  $tasks = [
    'indicia_profile_config_initialize' => [
      'type' => 'normal',
    ],
    'indicia_profile_environment_install' => [
      'type' => 'normal',
    ],
    'indicia_profile_file_private_install' => [
      'type' => 'normal',
    ],
    'indicia_profile_trusted_host_install' => [
      'type' => 'normal',
    ],
    'indicia_profile_uuid_install' => [
      'type' => 'normal',
    ],
  ];

  if ($install_state['interactive']) {
    $tasks['indicia_profile_install_environment'] = [
      'display_name' => t('Choose environment'),
      'type' => 'form',
      'function' => EnvironmentForm::class,
    ];
  }

  return $tasks;
}

/**
 * Install task that initializes the config directory.
 *
 * @param array $install_state
 *   An array of information about the current installation state.
 */
function indicia_profile_config_initialize(array &$install_state): void {
  $interactive = !empty($install_state['interactive']);

  Util::rewriteSettingsFile([
    'settings' => [
      'config_sync_directory' => '../config/sync',
    ],
  ], $interactive);
}

/**
 * Install task that installs the correct environment.
 *
 * @param array $install_state
 *   An array of information about the current installation state.
 */
function indicia_profile_environment_install(array &$install_state): void {
  $install_state['forms']['environment'] = $install_state['forms']['environment'] ?? NULL;

  if (!$environment = Util::getEnvironment($install_state['forms']['environment'])) {
    throw new InstallerException(t('The environment @environment is invalid.', [
      '@environment' => $install_state['forms']['environment'],
    ]), t('Invalid environment'));
  }

  Util::rewriteSettingsFile([
    'config' => [
      'indicia_profile' => [
        'environment' => $environment,
      ],
    ],
  ]);
}

/**
 * Install task that sets the private files directory of the site.
 *
 * @param array $install_state
 *   An array of information about the current installation state.
 */
function indicia_profile_file_private_install(array &$install_state): void {
  $install_state['forms']['environment'] = $install_state['forms']['environment'] ?? NULL;
  $interactive = !empty($install_state['interactive']);

  $privateDir = DRUPAL_ROOT . '/sites/default/files/private';
  if (!Util::isDevelopmentEnvironment($install_state['forms']['environment'])) {
    $privateDir = DRUPAL_ROOT . '/../../../files/private';
  }

  // Attempt to create the private files directory if it doesn't exist.
  if (!is_dir($privateDir) && !Drupal::service('file_system')->mkdir($privateDir, NULL, TRUE)) {
    throw new InstallerException(t('Private directory @dir does not exist and could not be created.', [
      '@dir' => $privateDir,
    ]));
  }

  Util::rewriteSettingsFile([
    'settings' => [
      'file_private_path' => realpath($privateDir),
    ],
  ], $interactive);
}

/**
 * Install task that sets the trusted host patterns.
 *
 * @param array $install_state
 *   An array of information about the current installation state.
 */
function indicia_profile_trusted_host_install(array &$install_state): void {
  $install_state['forms']['environment'] = $install_state['forms']['environment'] ?? NULL;
  $interactive = !empty($install_state['interactive']);

  // Skip if the current environment is a development environment.
  if (!Util::isDevelopmentEnvironment($install_state['forms']['environment'])) {

    // Skip if no host could be found.
    if (($host = Drupal::request()->getHost()) && $host !== 'default') {
      Util::rewriteSettingsFile([
        'settings' => [
          'trusted_host_patterns' => [
            'main' => '^' . preg_quote($host, '/') . '$',
          ],
        ],
      ], $interactive);
    }
  }
}

/**
 * Install task that sets the UUID of the site.
 *
 * @param array $install_state
 *   An array of information about the current installation state.
 */
function indicia_profile_uuid_install(array &$install_state): void {
  /* @var \Drupal\Core\Config\StorageInterface $storage */
  $storage = Drupal::service('config.storage.sync');
  $config = Drupal::configFactory()->getEditable('system.site');

  // Only set UUID if one is available from config, skip otherwise.
  if (($storageConfig = $storage->read('system.site')) && isset($storageConfig['uuid'])) {
    $config->set('uuid', $storageConfig['uuid'])->save();
    return;
  }

  // Generate the system.site config file.
  $storage->write('system.site', $config->get());
}
