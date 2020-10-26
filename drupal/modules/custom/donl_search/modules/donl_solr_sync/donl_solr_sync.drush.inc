<?php

/**
 * @file
 */

/**
 * Implements hook_drush_command().
 */
function donl_solr_sync_drush_command() {
  $items['update_solr_index'] = [
    'description' => 'Update the SOLR index.',
    'callback' => 'drush_donl_solr_sync_update_solr_index',
    'arguments' => [
      'type' => 'The content type to update',
    ],
    'drupal dependencies' => ['donl_solr_sync'],
    'aliases' => ['update-solr-index'],
  ];

  return $items;
}

/**
 *
 */
function drush_donl_solr_sync_update_solr_index($type) {
  \Drupal::service('donl_solr_sync.commands')->update($type);
}
