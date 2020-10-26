<?php

namespace Drupal\ckan\Entity;

/**
 *
 */
class License {

  /**
   * An URI representing the id of the license.
   *
   * @var string
   */
  public $id;

  /**
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param string $id
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * Return a string representation of the object.
   *
   * @return string
   */
  public function __toString() {
    return $this->id;
  }

}
