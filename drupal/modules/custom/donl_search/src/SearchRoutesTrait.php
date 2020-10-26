<?php

namespace Drupal\donl_search;

/**
 *
 */
trait SearchRoutesTrait {

  /**
   * Get the search route for the given type;.
   *
   * @param string $type
   *   The type of data we are searching.
   * @param bool $communityRoutes
   *   Get the community version.
   *
   * @return string
   *   The route to the corresponding searchpage.
   */
  public function getSearchRoute(string $type, $communityRoutes = FALSE): string {
    $searchRoutes = $this->getSearchRoutes($communityRoutes);

    return $searchRoutes[$type] ?? 'donl_search.search';
  }

  /**
   * Give a list with all search routes.
   *
   * @param bool $communityRoutes
   *   Get the community version.
   *
   * @return array
   *   An array with all search routes.
   */
  public function getSearchRoutes($communityRoutes = FALSE): array {
    if ($communityRoutes) {
      return [
        'application' => 'donl_community.search.application',
        'datarequest' => 'donl_community.search.datarequest',
        'dataset' => 'donl_community.search.dataset',
        'group' => 'donl_community.search.group',
        'organization' => 'donl_community.search.organization',
      ];
    }

    return [
      'application' => 'donl_search.search.application',
      'catalog' => 'donl_search.search.catalog',
      'community' => 'donl_search.search.community',
      'datarequest' => 'donl_search.search.datarequest',
      'dataset' => 'donl_search.search.dataset',
      'group' => 'donl_search.search.group',
      'news' => 'donl_search.search.news',
      'organization' => 'donl_search.search.organization',
      'support' => 'donl_search.search.support',
    ];
  }

  /**
   * Returns the type based on the given route.
   *
   * @param string $route
   *    The name of the route.
   *
   * @return string|null
   *   The search type this page is about.
   */
  public function getTypeFromRoute(string $route): ?string {
    $routes = array_flip($this->getSearchRoutes());
    $routes += array_flip($this->getSearchRoutes(TRUE));
    $routes['donl.application'] = 'application';
    $routes['donl_search.catalog.view'] = 'catalog';
    $routes['donl.datarequest'] = 'datarequest';
    $routes['ckan.dataset.view'] = 'dataset';
    $routes['donl_custom_http_4xx.system.404'] = 'dataset';
    $routes['ckan.group.dataset.view'] = 'group';
    $routes['donl_search.group.view'] = 'group';
    $routes['donl_search.organization.view'] = 'organization';

    $routes['donl_community.application.view'] = 'application';
    $routes['donl_community.datarequest.view'] = 'datarequest';
    $routes['donl_community.dataset.view'] = 'dataset';
    $routes['donl_community.group.view'] = 'group';
    $routes['donl_community.organization.view'] = 'organization';

    return $routes[$route] ?? NULL;
  }

}
