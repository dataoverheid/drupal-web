<?php

namespace Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator;

/**
 * Class OrganizationUrlGenerator.
 *
 * @package Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator
 *
 * @UrlGenerator(
 *   id = "donl_organization",
 *   label = @Translation("Organization URL generator"),
 *   description = @Translation("Generates organization URLs."),
 * )
 */
class OrganizationUrlGenerator extends UrlGenerator {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'organization';
  }

}
