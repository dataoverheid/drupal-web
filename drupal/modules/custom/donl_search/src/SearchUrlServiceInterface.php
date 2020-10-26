<?php

namespace Drupal\donl_search;

use Drupal\Core\Url;

/**
 *
 */
interface SearchUrlServiceInterface {

  /**
   * A simple way to create a search url.
   *
   * @param string $routeName
   *   The name of the route.
   * @param array $activeFacets
   *   Array containing all active facets.
   * @param array $options
   *   See \Drupal\Core\Url::fromUri() for details.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed (internal to Drupal) URL.
   */
  public function simpleSearchUrl(string $routeName, array $activeFacets = [], array $options = []): Url;

  /**
   * A simple way to create a search url with active route parameters.
   *
   * @param string $routeName
   *   The name of the route.
   * @param array $routeParams
   *   An associative array of route parameter names and values.
   * @param array $activeFacets
   *   Array containing all active facets.
   * @param array $options
   *   See \Drupal\Core\Url::fromUri() for details.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed (internal to Drupal) URL.
   */
  public function simpleSearchUrlWithRouteParams(string $routeName, array $routeParams, array $activeFacets = [], array $options = []): Url;

  /**
   * A simple way to create a search url with an active search term.
   *
   * @param string $routeName
   *   The name of the route.
   * @param string $search
   *   The search parameter.
   * @param array $activeFacets
   *   Array containing all active facets.
   * @param array $options
   *   See \Drupal\Core\Url::fromUri() for details.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed (internal to Drupal) URL.
   */
  public function simpleSearchUrlWithSearchTerm(string $routeName, string $search, array $activeFacets = [], array $options = []): Url;

  /**
   * Create a search url.
   *
   * @param string $routeName
   *   The name of the route.
   * @param array $routeParams
   *   An associative array of route parameter names and values.
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
   * @param bool $spellcheck
   *   Enable spellcheck corrections.
   * @param array $options
   *   See \Drupal\Core\Url::fromUri() for details.
   *
   * @return \Drupal\Core\Url
   *   A new Url object for a routed (internal to Drupal) URL.
   */
  public function completeSearchUrl(string $routeName, array $routeParams, int $page, int $recordsPerPage, ?string $search = NULL, ?string $sort = NULL, array $activeFacets = [], bool $spellcheck = TRUE, array $options = []): Url;

}
