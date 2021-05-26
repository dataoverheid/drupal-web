<?php

namespace Drupal\donl_entities;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Donl entities service interface.
 */
interface DonlEntitiesServiceInterface {

  /**
   * Get the entities as option list.
   *
   * @param string $useCase
   *   The use case for this list.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   A list with entities.
   */
  public function getEntitiesAsOptionsList(string $useCase): array;

  /**
   * Helper function to get the entity name.
   *
   * @param string $machineName
   *   The machine name of the entity.
   * @param bool $singular
   *   Should the name be singular (TRUE) or plural (FALSE).
   * @param bool $capitalize
   *   Should the first letter be capitalized.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The entity name.
   */
  public function getEntityName(string $machineName, bool $singular = FALSE, bool $capitalize = TRUE): TranslatableMarkup;

}
