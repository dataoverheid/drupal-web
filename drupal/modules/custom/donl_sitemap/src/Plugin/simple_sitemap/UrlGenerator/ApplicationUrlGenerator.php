<?php

namespace Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator;

/**
 * Class ApplicationUrlGenerator.
 *
 * @package Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator
 *
 * @UrlGenerator(
 *   id = "donl_application",
 *   label = @Translation("Application URL generator"),
 *   description = @Translation("Generates application URLs."),
 * )
 */
class ApplicationUrlGenerator extends UrlGenerator {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'application';
  }

}
