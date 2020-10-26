<?php

namespace Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator;

/**
 * Class GroupUrlGenerator.
 *
 * @package Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator
 *
 * @UrlGenerator(
 *   id = "donl_group",
 *   label = @Translation("Group URL generator"),
 *   description = @Translation("Generates group URLs."),
 * )
 */
class GroupUrlGenerator extends UrlGenerator {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'group';
  }

}
