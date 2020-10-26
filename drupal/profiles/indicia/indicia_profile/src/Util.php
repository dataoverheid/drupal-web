<?php

declare(strict_types = 1);

namespace Drupal\indicia_profile;

use Drupal;
use Drupal\Component\Utility\DiffArray;
use Drupal\Core\DrupalKernel;
use Drupal\indicia_profile\Exception\SettingsFileException;
use Symfony\Component\HttpFoundation\Request;
use function count;
use function in_array;
use function is_array;

/**
 * Utility class for Indicia profile.
 */
class Util {

  /**
   * Default environment. Used if no environment is set.
   */
  public const ENVIRONMENT_DEFAULT = 'development';

  /**
   * Replaces values in settings.php with values in the submitted array.
   *
   * @param array $settings
   *   An array of settings that need to be updated.
   * @param bool $interactive
   *   Whether or not the installation is running using an interactive
   *   interface.
   */
  public static function rewriteSettingsFile(array $settings, bool $interactive = FALSE): void {
    if ($settings = DiffArray::diffAssocRecursive($settings, $GLOBALS)) {

      // Check if we can write to the settings file.
      if (!static::validateSettingsFile()) {
        throw new SettingsFileException(static::getSettingsDump($settings), $interactive);
      }

      static::convertSettings($settings);
      drupal_rewrite_settings($settings);
    }
  }

  /**
   * Validate if the settings.php is writable.
   *
   * @return bool
   *   TRUE if settings.php is writable, FALSE otherwise.
   */
  public static function validateSettingsFile(): bool {
    // TODO: Check this.
    return drupal_verify_install_file(static::getSettingsFilePath(), FILE_WRITABLE);
  }

  /**
   * Retrieve the location of the settings.php file.
   *
   * @return string
   *   Path to the settings file.
   */
  public static function getSettingsFilePath(): string {
    // Find the site path. Kernel service is not always available at this point,
    // but is preferred, when available.
    $sitePath = Drupal::hasService('kernel') ? Drupal::service('site.path') : DrupalKernel::findSitePath(Request::createFromGlobals());
    return $sitePath . '/settings.php';
  }

  /**
   * Check if the given environment is a development environment.
   *
   * @param string|null $name
   *   Name or alias of the environment.
   *
   * @return bool
   *   TRUE if the given environment is a development environment, FALSE
   *   otherwise.
   */
  public static function isDevelopmentEnvironment(?string $name = NULL): bool {
    return static::getEnvironment($name) === 'development';
  }

  /**
   * Retrieve the environment based on the given name or alias.
   *
   * @param string|null $name
   *   Name or alias of the environment.
   *
   * @return string|null
   *   Name of the environment if it exists, NULL otherwise.
   */
  public static function getEnvironment(?string $name = NULL): ?string {
    if (!$name) {
      return self::ENVIRONMENT_DEFAULT;
    }

    $environments = static::getEnvironments();
    if (isset($environments[$name])) {
      return $name;
    }

    foreach ($environments as $key => $environment) {
      if (!empty($environment['aliases']) && in_array($name, $environment['aliases'], TRUE)) {
        return $key;
      }
    }

    return NULL;
  }

  /**
   * Retrieve a list of all available environments.
   *
   * @return array
   *   Associative array, keyed by environment name.
   */
  public static function getEnvironments(): array {
    return [
      'development' => [
        'label' => t('Development'),
        'aliases' => ['dev', 'local'],
      ],
      'test' => [
        'label' => t('Test'),
        'aliases' => ['testing'],
      ],
      'acceptance' => [
        'label' => t('Acceptance'),
        'aliases' => ['acc', 'acceptatie'],
      ],
      'production' => [
        'label' => t('Production'),
        'aliases' => ['prod', 'productie'],
      ],
    ];
  }

  /**
   * Retrieve an array of string, containing the relevant value properties.
   *
   * @param array $settings
   *   An array containing associative arrays, which should contain the
   *   following keys:
   *   - parents: An array containing the parents of the setting.
   *   - value: Value of the setting.
   *
   * @return array
   *   An array of strings containing valid PHP code of the settings suitable
   *   for placing into settings.php.
   */
  protected static function getSettingsDump(array $settings): array {
    $dump = [];
    foreach (static::getSettingsTree($settings) as $setting) {
      $variable = '$' . array_shift($setting['parents']);

      foreach ($setting['parents'] as $parent) {
        $variable .= "['" . $parent . "']";
      }

      $variable .= ' = ' . var_export($setting['value'], TRUE) . ';';
      $dump[] = $variable;
    }

    return $dump;
  }

  /**
   * Retrieve the tree of the given settings list.
   *
   * @param mixed $settings
   *   List of settings for which to retrieve the tree.
   * @param array $parents
   *   (optional) List of parents.
   *
   * @return array
   *   An array containing associative arrays, which will contain the following
   *   keys:
   *   - parents: An array containing the parents of the setting.
   *   - value: Value of the setting.
   */
  protected static function getSettingsTree($settings, array $parents = []): array {
    $tree = [];

    if (is_array($settings) && static::isAssociative($settings)) {
      foreach ($settings as $name => $setting) {
        $tree[] = static::getSettingsTree($setting, array_merge($parents, [$name]));
      }
      return array_merge(...$tree);
    }

    $tree[] = [
      'parents' => $parents,
      'value' => $settings,
    ];

    return $tree;
  }

  /**
   * Converts a value to the syntax excepted by drupal_rewrite_settings().
   *
   * @param mixed $settings
   *   Array of settings to convert or setting value.
   *
   * @see drupal_rewrite_settings()
   */
  protected static function convertSettings(&$settings): void {
    if (is_array($settings) && static::isAssociative($settings)) {
      foreach ($settings as &$setting) {
        static::convertSettings($setting);
      }
      return;
    }

    $settings = (object) [
      'value' => $settings,
      'required' => TRUE,
    ];
  }

  /**
   * Validate if the provided array is an associative array.
   *
   * @param array $array
   *   The array to check.
   *
   * @return bool
   *   TRUE if the variable is an associative array, FALSE otherwise.
   */
  protected static function isAssociative(array $array): bool {
    return array_keys($array) !== range(0, count($array) - 1);
  }

}
