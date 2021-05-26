<?php

namespace Drupal\donl_dcat_validation;

/**
 * Validate resources against the DCAT standards.
 */
interface DcatValidationServiceInterface {

  /**
   * Validate a dataset.
   *
   * @param array $values
   *   Array containing all the values of a dataset.
   *
   * @return array
   *   An array with errors.
   */
  public function dataset(array $values): array;

  /**
   * Validate a resource.
   *
   * @param array $values
   *   Array containing all the values of a resource.
   *
   * @return array
   *   An array with errors.
   */
  public function resource(array $values): array;

}
