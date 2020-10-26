<?php

namespace Drupal\ckan\Entity;

/**
 *
 */
class Tag {

  /**
   * An URI representing the id of the tag.
   *
   * @var string
   */
  private $id;

  /**
   * The name of the Tag.
   *
   * @var string
   */
  public $name;

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
   * Return a string representation of the object.
   *
   * @return string
   */
  public function __toString(): string {
    return $this->name;
  }

}
