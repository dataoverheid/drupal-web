<?php

namespace Drupal\donl_search;

use Drupal\donl_search\Entity\SolrResult;

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
   * Return a list with all relations for the given content.
   *
   * @param string $sysId
   *   The sys_id for the given content
   * @param string $type
   *   The type for the given content
   *
   * @return array
   */
  public function getRelations(string $sysId, string $type): array;

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
   * Get a list with recent content.
   *
   * @param string|null $communityIdentifier
   *   The community identifier to filter on or null to show all.
   * @param int $limit
   *   The number of results to return.
   * @param string|null $type
   *   Limit result to a specific type.
   * @param array $excludeTypes
   *   Exclude types from result.
   *
   * @return array
   */
  public function getRecentContentData(?string $communityIdentifier = NULL, int $limit = 1, ?string $type = NULL, array $excludeTypes = []): array;

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
   *   The type for the given content.
   * @param string $search
   *   The search term
   *
   * @return array
   */
  public function autocomplete($type, $search): array;

  /**
   * Find a dataset based on the identifier.
   *
   * @param string $identifier
   *   The identifier.
   *
   * @return \Drupal\donl_search\Entity\SolrResult|null
   */
  public function getDatasetResultByIdentifier($identifier): ?SolrResult;

  /**
   * Get an array with search suggestions.
   *
   * @param string $type
   *   The type for the given content.
   * @param string $search
   *   The search term.
   * @param string|null $communitySysName
   *   The community sys_name.
   *
   * @return array
   */
  public function getSearchSuggestions($type, $search, $communitySysName = NULL): array;

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

  /**
   * Check if the identifier is in use.
   *
   * @param string $identifier
   * @param string|null $id
   *   The entity id (sys_id without language code).
   *
   * @return bool
   */
  public function checkIdentifierUsage($identifier, $id = NULL): bool;

}
