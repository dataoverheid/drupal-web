<?php

namespace Drupal\ckan\User;

use Drupal\user\Entity\User;

/**
 *
 */
class CkanUser extends User implements CkanUserInterface {

  /**
   * {@inheritdoc}
   */
  public function getCatalogs(): array {
    $catalogs = [];

    // Return everything if the user is an administrator.
    if ($this->isAdministrator()) {
      return \Drupal::service('donl.value_list')->getList('donl:catalogs');
    }

    if ($this->hasField('field_catalog') && ($catalog = $this->get('field_catalog')->getValue()[0]['value'] ?? NULL)) {
      $catalogs[$catalog] = 1;
    }

    // Always include data.overheid.nl as possible catalog for data owners.
    if ($this->isDataOwner()) {
      $catalogs['https://data.overheid.nl'] = 1;
    }

    return $catalogs;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthority(): ?string {
    if ($this->hasField('field_authority')) {
      return $this->get('field_authority')->getValue()[0]['value'] ?? NULL;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPublisher(): ?string {
    if ($this->hasField('field_publisher')) {
      return $this->get('field_publisher')->getValue()[0]['value'] ?? NULL;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getApiKey(): ?string {
    if ($this->hasField('field_ckan_api_key')) {
      return $this->get('field_ckan_api_key')->getValue()[0]['value'] ?? NULL;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCkanId(): ?string {
    if ($this->hasField('field_ckan_id')) {
      return $this->get('field_ckan_id')->getValue()[0]['value'] ?? NULL;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isDataOwner(): bool {
    return \in_array('data_owner', $this->getRoles(), TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function isAdministrator(): bool {
    return \in_array('administrator', $this->getRoles(), TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function hasStorageAccess(): bool {
    if ($this->hasField('field_access_ckan_storage')) {
      return (bool) ($this->get('field_access_ckan_storage')->getValue()[0]['value'] ?? NULL);
    }

    return FALSE;
  }

}
