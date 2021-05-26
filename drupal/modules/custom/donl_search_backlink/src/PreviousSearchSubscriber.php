<?php

namespace Drupal\donl_search_backlink;

use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * PreviousSearchSubscriber class.
 */
class PreviousSearchSubscriber implements EventSubscriberInterface {

  /**
   * The private temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  public $tempStore;

  /**
   * PreviousSearchSubscriber constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $privateTempStoreFactory
   *   The private temp store factory.
   */
  public function __construct(PrivateTempStoreFactory $privateTempStoreFactory) {
    $this->tempStore = $privateTempStoreFactory->get('donl_search_backlink');
  }

  /**
   * Save the current and previous request url to the PHP session.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The Event to process.
   */
  public function saveRequestUrl(RequestEvent $event): void {
    $request = $event->getRequest();
    $savedRequestUri = $this->tempStore->get('current_request');
    $currentRequestUri = $request->getSchemeAndHttpHost() . $request->getRequestUri();
    if ($savedRequestUri !== $currentRequestUri) {
      try {
        $this->tempStore->set('previous_request', $savedRequestUri);
        $this->tempStore->set('current_request', $currentRequestUri);
      }
      catch (TempStoreException $e) {
        // This isn't important enough to specifically add error handling.
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['saveRequestUrl', 20];
    return $events;
  }

}
