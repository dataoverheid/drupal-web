<?php

namespace Drupal\ckan\Entity;

/**
 *
 */
class User {

  /**
   * @var string
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $fullName;

  /**
   * @var string
   */
  public $email;

  /**
   * @var string
   */
  public $state;

  /**
   * @var bool
   */
  public $deleted;

  /**
   * @var string
   */
  public $apikey;

  /**
   * @return string
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId(string $id): void {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName(string $name): void {
    $this->name = $name;
  }

  /**
   * @return string|null
   */
  public function getFullName(): ?string {
    return $this->fullName;
  }

  /**
   * @param string|null $fullName
   */
  public function setFullName(string $fullName = NULL): void {
    $this->fullName = $fullName;
  }

  /**
   * @return string|null
   */
  public function getEmail(): ?string {
    return $this->email;
  }

  /**
   * @param string|null $email
   */
  public function setEmail(string $email = NULL): void {
    $this->email = $email;
  }

  /**
   * @return string
   */
  public function getState(): string {
    return $this->state;
  }

  /**
   * @param string $state
   */
  public function setState(string $state): void {
    $this->state = $state;
  }

  /**
   * @return bool
   */
  public function isDeleted(): bool {
    return $this->deleted;
  }

  /**
   * @param bool $deleted
   */
  public function setDeleted(bool $deleted): void {
    $this->deleted = $deleted;
  }

  /**
   * @return string
   */
  public function getApikey(): string {
    return $this->apikey;
  }

  /**
   * @param string $apikey
   */
  public function setApikey(string $apikey): void {
    $this->apikey = $apikey;
  }

  /**
   * Returns the object as Array.
   *
   * @return array
   */
  public function toArray() {
    return (array) $this;
  }

  /**
   * Return a string representation of the object.
   *
   * @return string
   */
  public function __toString(): string {
    return $this->fullName;
  }

}
