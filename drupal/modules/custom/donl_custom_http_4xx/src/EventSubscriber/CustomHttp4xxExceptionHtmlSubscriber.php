<?php

namespace Drupal\donl_custom_http_4xx\EventSubscriber;

use Drupal\Core\EventSubscriber\CustomPageExceptionHtmlSubscriber;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\donl_search\SearchRoutesTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Exception subscriber for handling core custom HTML error pages.
 */
class CustomHttp4xxExceptionHtmlSubscriber extends CustomPageExceptionHtmlSubscriber {
  use SearchRoutesTrait;

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    return -45;
  }

  /**
   * {@inheritdoc}
   */
  public function on404(ExceptionEvent $event) {
    $type = 'page';
    if (($previous = $event->getThrowable()->getPrevious()) && $previous instanceof ParamNotConvertedException) {
      $type = $this->getTypeFromRoute($previous->getRouteName()) ?? 'page';
      $type = in_array($type, ['support', 'news'], TRUE) ? 'page' : $type;
    }

    $this->makeSubrequest($event, '/custom/404/' . $type, Response::HTTP_NOT_FOUND);
  }

}
