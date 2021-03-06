<?php

namespace Drupal\donl_search;

/**
 *
 */
interface SolrRequestInterface {

  /**
   * Preform a search request.
   *
   * @param int $page
   *   The requested page with results.
   * @param int $recordsPerPage
   *   The amount of records to be shown a page.
   * @param string|null $search
   *   The search parameter.
   * @param string|null $sort
   *   The sort parameter.
   * @param string $type
   *   The type of data we are searching.
   * @param array $activeFacets
   *   Array containing all active facets.
   * @param bool $sendSignals
   *   Send signals to SOLR.
   *
   * @return array
   */
  public function search($page, $recordsPerPage, $search, $sort, $type = '', array $activeFacets = [], $sendSignals = FALSE): array;

  /**
   * Preform a count request.
   *
   * @param string|null $search
   *   The search parameter.
   * @param array $activeFacets
   *   Array containing all active facets.
   * @param string $type
   *   The type of data we are searching.
   *
   * @return int
   */
  public function getSearchCount($search, array $activeFacets = [], $type = ''): int;

  /**
   * Get a hierarchical theme list with usages.
   *
   * @return array
   */
  public function getHierarchicalThemeListWithUsages(): array;

  /**
   * Get the selected themes.
   *
   * @param array $selectedThemes
   * @param string|null $communityIdentifier
   *
   * @return array
   */
  public function getCountForSelectedThemes(array $selectedThemes, $communityIdentifier = NULL): array;

  /**
   * Get a list with tags themes.
   *
   * @param string|null $communityIdentifier
   *
   * @return array
   */
  public function getTagCloud($communityIdentifier = NULL): array;

  /**
   * Get a list with comparable datasets.
   *
   * @param string $type
   * @param string $id
   *
   * @return array
   */
  public function getComparableData($type, $id): array;

  /**
   * Autocomplete function for the dataset field.
   *
   * @param string $type
   * @param string $search
   *
   * @return array
   */
  public function autocomplete($type, $search): array;

  /**
   * Get an array with search suggestions.
   *
   * @param string $search
   *   The search string we want suggestions for.
   *
   * @return array
   */
  public function getSearchSuggestions($search): array;

  /**
   * Add/Update the given document to SOLR.
   *
   * @param string $document
   *   A json object of the document.
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  public function updateIndex($document);

  /**
   * Delete the given document to SOLR.
   *
   * @param int $id
   *   The id of the document.
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  public function deleteIndex($id);

}
