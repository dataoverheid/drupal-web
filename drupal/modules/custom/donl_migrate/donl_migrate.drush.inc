<?php

/**
 * @file
 */

/**
 * Implements hook_drush_command().
 */
function donl_migrate_drush_command() {
  $items['catalog-migrate'] = [
    'description' => 'Migrate all catalogs from taxonomy to nodes.',
    'callback' => 'donl_migrate_catalogs',
    'drupal dependencies' => ['donl_migrate'],
    'aliases' => ['donl-migrate-catalog'],
  ];

  $items['organization-migrate'] = [
    'description' => 'Migrate all organizations from taxonomy to nodes.',
    'callback' => 'donl_migrate_organizations',
    'drupal dependencies' => ['donl_migrate'],
    'aliases' => ['donl-migrate-organization'],
  ];

  $items['cleanup-applications'] = [
    'description' => 'Cleanup the fields for applications',
    'callback' => 'donl_migrate_cleanup_applications',
    'drupal dependencies' => ['donl_migrate'],
    'aliases' => ['donl-migrate-cleanup-application'],
  ];

  $items['cleanup-datarequests'] = [
    'description' => 'Cleanup the fields for datarequests',
    'callback' => 'donl_migrate_cleanup_datarequests',
    'drupal dependencies' => ['donl_migrate'],
    'aliases' => ['donl-migrate-cleanup-datarequests'],
  ];

  return $items;
}

/**
 *
 */
function donl_migrate_catalogs() {
  \Drupal::service('donl_migrate.migrate_catalog')->migrate();
}

/**
 *
 */
function donl_migrate_organizations() {
  \Drupal::service('donl_migrate.migrate_organization')->migrate();
}

/**
 *
 */
function donl_migrate_cleanup_applications() {
  \Drupal::service('donl_migrate.cleanup_application')->cleanup();
}

/**
 *
 */
function donl_migrate_cleanup_datarequests() {
  \Drupal::service('donl_migrate.cleanup_datarequests')->cleanup();
}
