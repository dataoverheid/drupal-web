<?php

namespace Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator;

/**
 * Class CatalogUrlGenerator.
 *
 * @package Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator
 *
 * @UrlGenerator(
 *   id = "donl_catalog",
 *   label = @Translation("Catalog URL generator"),
 *   description = @Translation("Generates catalog URLs."),
 * )
 */
class CatalogUrlGenerator extends UrlGenerator {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'catalog';
  }

}
