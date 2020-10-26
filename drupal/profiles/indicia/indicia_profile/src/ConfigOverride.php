<?php

declare(strict_types = 1);

namespace Drupal\indicia_profile;

use Drupal;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorableConfigBase;
use Drupal\Core\Config\StorageInterface;

/**
 * Configuration override.
 *
 * We can't apply proper dependency injection here because everything has a
 * dependency on ConfigFactory, which has a dependency on this class.
 */
class ConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * The config split prefix.
   */
  protected const CONFIG_SPLIT_PREFIX = 'config_split.config_split.';

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names): array {
    $overrides = [];

    // Loop through config files.
    foreach ($names as $name) {
      // Find the config split config files.
      if (strpos($name, self::CONFIG_SPLIT_PREFIX) === 0) {
        $overrides[$name]['status'] = FALSE;

        // Read environment variable from settings.php file.
        if ($config = Drupal::configFactory()->get('indicia_profile')) {
          if ($environment = $config->get('environment')) {
            // Dynamically set corresponding environment as active config split.
            if ($name === self::CONFIG_SPLIT_PREFIX . $environment) {
              $overrides[$name]['status'] = TRUE;
            }
          }
        }
      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix(): string {
    return 'ConfigOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION): ?StorableConfigBase {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name): CacheableMetadata {
    return new CacheableMetadata();
  }

}
