<?php

namespace Drupal\donl\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 *
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($collection->all() as $routeName => $route) {
      if ($routeName === 'user.page') {
        $route->setDefault('_controller', '\Drupal\donl\Controller\ProfileController::content');
        $route->setDefault('_title', 'Profile');
        $route->setPath('/gebruiker');
      }
      elseif ($routeName === 'entity.user.canonical') {
        $route->setDefault('_controller', '\Drupal\donl\Controller\ProfileController::content');
        $route->setDefault('_title', 'Profile');
        $route->setPath('/gebruiker/{user}');
      }
      elseif (strpos($route->getPath(), '/user') === 0) {
        $route->setPath(preg_replace('/^\/user/', '/gebruiker', $route->getPath()));
      }
    }
  }

}
