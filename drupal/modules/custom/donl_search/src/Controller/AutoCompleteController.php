<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class AutoCompleteController extends ControllerBase {

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request')
    );
  }

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest) {
    $this->solrRequest = $solrRequest;
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request, $type) {
    $input = $request->query->get('q');
    $results = $this->solrRequest->autocomplete($type, $input);
    return new JsonResponse($results);
  }

}
