<?php

namespace Drupal\donl_search;

/**
 *
 */
interface SearchPaginationInterface {

  /**
   * Get the pagination for a search page.
   *
   * @param string $routeName
   *   The name of the route.
   * @param array $routeParams
   *   An associative array of route parameter names and values.
   * @param int $numberOfRecords
   *   The total number of records found.
   * @param int $page
   *   The page to link towards.
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
  public function getPagination($routeName, array $routeParams, $numberOfRecords, $page, $recordsPerPage, $search, $sort, array $activeFacets);

}
