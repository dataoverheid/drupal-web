<?php

namespace Drupal\ckan\Entity;

/**
 *
 */
class Catalog {

  /**
   * The internal unique CKAN id.
   *
   * @var string
   */
  public $id;

  /**
   * @var string
   */
  public $title;

  /**
   * @var string|null
   */
  public $description;

  /**
   * @var string
   */
  public $name;

  /**
   * @var int
   */
  public $package_count;

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
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * @param string $title
   */
  public function setTitle(string $title): void {
    $this->title = $title;
  }

  /**
   * @return null|string
   */
  public function getDescription(): ?string {
    return $this->description;
  }

  /**
   * @param null|string $description
   */
  public function setDescription(string $description = NULL): void {
    $this->description = $description;
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
   * @return int
   */
  public function getPackageCount(): int {
    return $this->package_count;
  }

  /**
   * @param int $package_count
   */
  public function setPackageCount(int $package_count): void {
    $this->package_count = $package_count;
  }

  /**
   * Returns the object as Array.
   *
   * @return array
   */
  public function toArray() {
    return (array) $this;
  }

}
