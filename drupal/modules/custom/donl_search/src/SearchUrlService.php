<?php

namespace Drupal\donl_search;

use Drupal\Core\Url;

/**
 *
 */
class SearchUrlService implements SearchUrlServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function simpleSearchUrl(string $routeName, array $activeFacets = [], array $options = []): Url {
    return $this->completeSearchUrl($routeName, [], 1, 10, NULL, NULL, $activeFacets, TRUE, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function simpleSearchUrlWithRouteParams(string $routeName, array $routeParams, array $activeFacets = [], array $options = []): Url {
    return $this->completeSearchUrl($routeName, $routeParams, 1, 10, NULL, NULL, $activeFacets, TRUE, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function simpleSearchUrlWithSearchTerm(string $routeName, string $search, array $activeFacets = [], array $options = []): Url {
    return $this->completeSearchUrl($routeName, [], 1, 10, $search, NULL, $activeFacets, TRUE, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function completeSearchUrl(string $routeName, array $routeParams, int $page, int $recordsPerPage, ?string $search = NULL, ?string $sort = NULL, array $activeFacets = [], bool $spellcheck = TRUE, array $options = []): Url {
    $options['query'] = array_filter($activeFacets);
    if (!empty($search)) {
      $options['query']['search'] = $search;
    }
    if (!empty($sort)) {
      $options['query']['sort'] = $sort;
    }
    if (!$spellcheck) {
      $options['query']['spellcheck'] = 0;
    }

    $routeParams['page'] = $page;
    $routeParams['recordsPerPage'] = $recordsPerPage;
    return Url::fromRoute($routeName, $routeParams, $options);
  }

}
