<?php

/**
 * @file
 * Contains the code to generate the custom drush commands.
 */

/**
 * Implements hook_drush_command().
 */
function donl_statistics_drush_command() {
  $items = [];
  $items['collect'] = [
    'description' => 'Collect the DONL statistics',
    'callback' => 'drush_donl_statistics_collect',
    'drupal dependencies' => ['donl_statistics'],
    'aliases' => ['donl_statistics:collect'],
  ];
  return $items;
}

/**
 * Call back function drush_custom_drush_command_donl_statistics_collect().
 */
function drush_donl_statistics_collect() {
  \Drupal::service('donl_statistics.collect')->collect();
}
