<?php

namespace Drupal\donl\Theme;

use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 *
 */
class ThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $routeName = $route_match->getRouteName();

    if ($routeName === 'node.add' && ($nodeType = $route_match->getParameter('node_type'))) {
      if (in_array($nodeType->get('type'), ['appliance', 'datarequest', 'dataservice'])) {
        return TRUE;
      }
    }

    if ($routeName === 'entity.node.edit_form' && ($node = $route_match->getParameter('node'))) {
      if (in_array($node->getType(), ['appliance', 'dataservice'])) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'koop_overheid';
  }

}
