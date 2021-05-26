<?php

namespace Drupal\donl_search\Routing;

use Symfony\Component\Routing\Route;

/**
 *
 */
class SearchRoutes {

  /**
   *
   */
  public function routes() {
    $searchPages = [
      'donl_search.search' => [
        'title' => 'Search',
        'path_en' => '/search',
        'path_nl' => '/zoek',
        'controller' => 'SearchController',
      ],
      'donl_search.search.application' => [
        'title' => 'Search',
        'path_en' => '/community/applications',
        'path_nl' => '/community/toepassingen',
        'controller' => 'SearchApplicationController',
      ],
      'donl_search.search.catalog' => [
        'title' => 'Search',
        'path_en' => '/search/catalogs',
        'path_nl' => '/search/catalogi',
        'controller' => 'SearchCatalogController',
      ],
      'donl_search.search.community' => [
        'title' => 'Search',
        'path_en' => '/search/communities',
        'path_nl' => '/search/communities',
        'controller' => 'SearchCommunityController',
      ],
      'donl_search.search.datarequest' => [
        'title' => 'Search',
        'path_en' => '/community/datarequests',
        'path_nl' => '/community/dataverzoeken',
        'controller' => 'SearchDatarequestController',
      ],
      'donl_search.search.dataset' => [
        'title' => 'Search',
        'path_en' => '/datasets',
        'path_nl' => '/datasets',
        'controller' => 'SearchDatasetController',
      ],
      'donl_search.search.dataservice' => [
        'title' => 'Search',
        'path_en' => '/zoeken/dataservices',
        'path_nl' => '/search/dataservices',
        'controller' => 'SearchDataserviceController',
      ],
      'donl_search.search.group' => [
        'title' => 'Search',
        'path_en' => '/community/groups',
        'path_nl' => '/community/groepen',
        'controller' => 'SearchGroupController',
      ],
      'donl_search.search.news' => [
        'title' => 'Search',
        'path_en' => '/search/news',
        'path_nl' => '/zoek/nieuws',
        'controller' => 'SearchNewsController',
      ],
      'donl_search.search.organization' => [
        'title' => 'Search',
        'path_en' => '/community/organizations',
        'path_nl' => '/community/organisaties',
        'controller' => 'SearchOrganizationController',
      ],
      'donl_search.search.support' => [
        'title' => 'Search',
        'path_en' => '/search/support',
        'path_nl' => '/zoek/support',
        'controller' => 'SearchSupportController',
      ],
    ];

    $routes = [];
    foreach ($searchPages as $id => $vars) {
      $routes[$id] = $this->buildSearchRoute($vars['title'], $vars['path_nl'], $vars['controller']);
      $routes[$id . '.en'] = $this->buildSearchRoute($vars['title'], $vars['path_en'], $vars['controller']);
    }

    return $routes;
  }

  /**
   *
   */
  private function buildSearchRoute($title, $path, $controller) {
    return new Route(
      $path . '/{page}/{recordsPerPage}',
      [
        '_controller' => '\Drupal\donl_search\Controller\\' . $controller . ':content',
        '_title' => $title,
        'page' => 1,
        'recordsPerPage' => 10,
      ],
      [
        '_permission' => 'access content',
        'page' => '^[1-9][0-9]*$',
        'recordsPerPage' => '^[1-9][0-9]*$',
      ]
    );
  }

}
