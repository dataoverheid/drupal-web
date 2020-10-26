<?php

namespace Drupal\donl_search;

/**
 *
 */
interface SearchSortingInterface {

  /**
   * Get the sorting for a search page.
   *
   * @param string $routeName
   *   The name of the route.
   * @param array $routeParams
   *   An associative array of route parameter names and values.
   * @param array $availableSorting
   *   An array with the available sorting options.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   * @param string|null $search
   *   The search parameter.
   * @param string|null $sort
   *   The sort parameter.
   * @param array $activeFacets
   *   Array containing all active facets.
   *
   * @return array
   *   A Drupal render array.
   */
  public function getSort($routeName, array $routeParams, array $availableSorting, $recordsPerPage, $search, $sort, array $activeFacets);

}
