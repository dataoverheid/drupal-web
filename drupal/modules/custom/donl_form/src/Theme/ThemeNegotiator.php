<?php

namespace Drupal\donl_form\Theme;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * The theme negotiator.
 */
class ThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * ThemeNegotiator constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   */
  public function __construct(ThemeHandlerInterface $themeHandler) {
    $this->themeHandler = $themeHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($route_match->getRouteName() === 'entity.node.delete_form') {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $route_match->getParameter('node');
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
    return $this->themeHandler->getDefault();
  }

}
