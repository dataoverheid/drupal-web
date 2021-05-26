<?php

namespace Drupal\donl_search_backlink;

use Drupal\Core\Link;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;

/**
 * BackLinkService class.
 */
class BackLinkService {

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * BackLinkService constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $privateTempStoreFactory
   */
  public function __construct(PrivateTempStoreFactory $privateTempStoreFactory) {
    $this->tempStore = $privateTempStoreFactory->get('donl_search_backlink');
  }

  /**
   * Create the back link.
   *
   * @param string $title
   * @param string $route
   * @param array $routeParams
   *
   * @return \Drupal\Core\Link
   */
  public function createBackLink($title, $route, array $routeParams = []): Link {
    $options = ['attributes' => ['class' => ['link', 'cta__backwards']]];

    $url = Url::fromRoute($route, $routeParams, ['absolute' => TRUE])->toString();
    $referer = $this->tempStore->get('previous_request');
    if (strpos($referer, $url) === 0) {
      $url = Url::fromUri($referer, $options);
      return Link::fromTextAndUrl($title, $url);
    }

    return Link::createFromRoute($title, $route, $routeParams, $options);
  }

}
