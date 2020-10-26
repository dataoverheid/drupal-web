<?php

namespace Drupal\donl_search;

/**
 *
 */
interface FacetRenameServiceInterface {

  /**
   * Renames the given facet URI to a human readable name.
   *
   * @param string $title
   * @param string $type
   *
   * @return string
   */
  public function rename($title, $type);

}
