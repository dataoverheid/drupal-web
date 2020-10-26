<?php

namespace Drupal\ckan\User;

use Drupal\user\UserInterface;

/**
 *
 */
interface CkanUserInterface extends UserInterface {

  /**
   * @return array
   */
  public function getCatalogs(): array;

  /**
   * @return string|null
   */
  public function getAuthority(): ?string;

  /**
   * @return string|null
   */
  public function getPublisher(): ?string;

  /**
   * @return string|null
   */
  public function getApiKey(): ?string;

  /**
   * @return string|null
   */
  public function getCkanId(): ?string;

  /**
   * @return bool
   */
  public function isDataOwner(): bool;

  /**
   * @return bool
   */
  public function isAdministrator(): bool;

  /**
   * @return bool
   */
  public function hasStorageAccess(): bool;

}
