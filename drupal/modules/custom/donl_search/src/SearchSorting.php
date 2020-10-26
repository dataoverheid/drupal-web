<?php

namespace Drupal\donl_search;

/**
 *
 */
class SearchSorting implements SearchSortingInterface {

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   *
   */
  public function __construct(SearchUrlServiceInterface $searchUrlService) {
    $this->searchUrlService = $searchUrlService;
  }

  /**
   * {@inheritdoc}
   */
  public function getSort($routeName, array $routeParams, array $availableSorting, $recordsPerPage, $search, $sort, array $activeFacets) {
    $links = [];
    foreach ($availableSorting as $v) {
      // For the working of the sorting its important to put desc as default.
      $newSort = implode(' desc, ', $v['sort']) . ' desc';
      if ($sort === $newSort) {
        $newSort = implode(' asc, ', $v['sort']) . ' asc';
      }

      $links[] = [
        '#type' => 'link',
        '#url' => $this->searchUrlService->completeSearchUrl($routeName, $routeParams, 1, $recordsPerPage, $search, $newSort, $activeFacets),
        '#title' => $v['label'],
        '#attributes' => [
          'rel' => 'nofollow',
          'class' => [
            (substr($newSort, -4, 4) === 'desc' ? 'sort--descending' : 'sort--ascending'),
          ],
        ],
      ];
    }

    return [
      '#theme' => 'donl_search_sorting',
      '#links' => $links,
    ];
  }

}
