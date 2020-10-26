<?php

namespace Drupal\ckan\Access;

use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;

/**
 *
 */
class DatasetAccessCheck implements AccessInterface {

  /**
   *
   */
  public function access(AccountInterface $account, RouteMatch $routeMatch, Dataset $dataset = NULL) {
    $route = explode('.', $routeMatch->getRouteName());

    switch (end($route)) {
      case 'view':
      case 'rdf':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      case 'create':
      case 'edit':
      case 'delete':
      case 'order':
        return AccessResult::allowedIfHasPermission($account, 'manage datasets');
    }

    return AccessResult::forbidden();
  }

}
