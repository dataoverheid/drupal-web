<?php

namespace Drupal\donl\EventSubscriber;

use Drupal\serialization\EventSubscriber\DefaultExceptionSubscriber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Last-chance handler for exceptions.
 *
 * This handler will catch any exceptions not caught elsewhere and send a themed
 * error page as a response.
 */
class ExceptionSubscriber extends DefaultExceptionSubscriber {

  /**
   * {@inheritdoc}
   */
  public function onException(ExceptionEvent $event) {
    parent::onException($event);
    $content = file_get_contents(DRUPAL_ROOT . '/themes/custom/koop_overheid/custom_50x.html');
    $error = '';

    if (_drupal_get_error_level() !== 'hide') {
      $exception = $event->getThrowable();
      $error = '<hr>' . $exception->getMessage();
      $error .= '<br>In: ' . $exception->getFile() . ':' . $exception->getLine();
      $error .= '<hr><br><br>Stacktrace:<pre>' . $exception->getTraceAsString() . '</pre>';
    }

    $content = str_replace('{% error %}', $error, $content);
    $response = new Response($content, 500);
    $event->setResponse($response);
  }

}
