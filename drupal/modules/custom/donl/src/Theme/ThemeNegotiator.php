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
    return $route_match->getRouteName() == 'node.add' && $route_match->getParameter('node_type')->get('type') == 'datarequest';
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    // Here you return the actual theme name.
    return 'koop_overheid';
  }

}
