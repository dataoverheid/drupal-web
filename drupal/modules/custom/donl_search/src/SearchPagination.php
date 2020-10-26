<?php

namespace Drupal\donl_search;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\Markup;

/**
 *
 */
class SearchPagination implements SearchPaginationInterface {

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   *
   */
  public function __construct(FormBuilderInterface $formBuilder, SearchUrlServiceInterface $searchUrlService) {
    $this->formBuilder = $formBuilder;
    $this->searchUrlService = $searchUrlService;
  }

  /**
   * {@inheritdoc}
   */
  public function getPagination($routeName, array $routeParams, $numberOfRecords, $page, $recordsPerPage, $search, $sort, array $activeFacets) {
    $links = [];

    // Don't show paging if the results fit on a single page.
    if ($numberOfRecords > $recordsPerPage) {
      $last = ceil($numberOfRecords / $recordsPerPage);
      $start = (($page - 1) > 0) ? $page - 1 : 1;
      $end = (($page + 1) < $last) ? $page + 1 : $last;

      // Add the previous link if we aren't on the first page.
      if ($page !== 1) {
        $links[] = [
          'type' => 'link',
          'link' => $this->buildLink('&laquo;', $routeName, $routeParams, $page - 1, $recordsPerPage, $search, $sort, $activeFacets),
        ];
      }

      // If we aren't on the first page so a link to the first page.
      if ($start > 1) {
        $links[] = [
          'type' => 'link',
          'link' => $this->buildLink(1, $routeName, $routeParams, 1, $recordsPerPage, $search, $sort, $activeFacets),
        ];

        // Only add a separator if there is a gab between the numbers.
        if ($page - 2 > 1) {
          $links[] = [
            'type' => 'separator',
          ];
        }
      }

      // Add the current page and links for the numbers around it.
      for ($i = $start; $i <= $end; $i++) {
        if ($page === $i) {
          $links[] = [
            'type' => 'active',
            'label' => $i,
          ];
        }
        else {
          $links[] = [
            'type' => 'link',
            'link' => $this->buildLink($i, $routeName, $routeParams, $i, $recordsPerPage, $search, $sort, $activeFacets),
          ];
        }
      }

      // If we aren't on the last page so a link to the last page.
      if ($end < $last) {
        // Only add a separator if there is a gab between the numbers.
        if ($page + 2 < $last) {
          $links[] = [
            'type' => 'separator',
          ];
        }

        $links[] = [
          'type' => 'link',
          'link' => $this->buildLink($last, $routeName, $routeParams, $last, $recordsPerPage, $search, $sort, $activeFacets),
        ];
      }

      // Add the next link if we aren't on the last page.
      if ($page !== $last) {
        $links[] = [
          'type' => 'link',
          'link' => $this->buildLink('&raquo;', $routeName, $routeParams, $page + 1, $recordsPerPage, $search, $sort, $activeFacets),
        ];
      }
    }

    return [
      '#theme' => 'donl_search_pagination',
      '#pagination' => $links,
      //'#filters' => $this->formBuilder->getForm(\Drupal\donl_search\Form\PaginationFiltersForm::class, $recordsPerPage, $routeName, $routeParams),
    ];
  }

  /**
   *
   */
  private function buildLink($title, $routeName, array $routeParams, $page, $recordsPerPage, $search, $sort, array $activeFacets) {
    return [
      '#type' => 'link',
      '#title' => Markup::create($title),
      '#url' => $this->searchUrlService->completeSearchUrl($routeName, $routeParams, $page, $recordsPerPage, $search, $sort, $activeFacets),
      '#attributes' => [
        'rel' => 'nofollow',
      ],
    ];
  }

}
