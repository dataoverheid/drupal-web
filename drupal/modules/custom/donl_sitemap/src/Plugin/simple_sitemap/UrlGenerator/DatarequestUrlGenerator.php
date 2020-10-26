<?php

namespace Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator;

/**
 * Class DatarequestUrlGenerator.
 *
 * @package Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator
 *
 * @UrlGenerator(
 *   id = "donl_datarequest",
 *   label = @Translation("Datarequest URL generator"),
 *   description = @Translation("Generates datarequest URLs."),
 * )
 */
class DatarequestUrlGenerator extends UrlGenerator {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'datarequest';
  }

}
