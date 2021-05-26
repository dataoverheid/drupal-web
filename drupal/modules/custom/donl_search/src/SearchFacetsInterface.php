<?php

namespace Drupal\donl_search;

/**
 *
 */
interface SearchFacetsInterface {

  /**
   * Get the facets for a search page.
   *
   * @param string $routeName
   *   The name of the route.
   * @param array $routeParams
   *   An associative array of route parameter names and values.
   * @param array $availableFacets
   *   An array with all available facets from SOLR.
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
  public function getFacets($routeName, array $routeParams, array $availableFacets, $recordsPerPage, $search, $sort, array $activeFacets = []): array;

  /**
   * Get the delete link for all the active facets for a search page.
   *
   * @param string $routeName
   *   The name of the route.
   * @param array $routeParams
   *   An associative array of route parameter names and values.
   * @param array $activeFacets
   *   Array containing all active facets.
   *
   * @return array
   *   A Drupal render array.
   */
  public function getFacetDeleteLinks(string $routeName, $routeParams, array $activeFacets = []): array;

  /**
   * Return the readable names for the facets in the order they should appear.
   *
   * @return array
   */
  public function getFacetNamesInOrder(): array;

  /**
   * Turns the activeFacets array into human readable values.
   *
   * @return array
   */
  public function activeFacetsToReadable($activeFacets): array;

}
