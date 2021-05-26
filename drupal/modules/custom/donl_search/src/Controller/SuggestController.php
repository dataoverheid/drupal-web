<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\donl_entities\DonlEntitiesServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Suggest Controller.
 */
class SuggestController extends ControllerBase {

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * @var \Drupal\donl_entities\DonlEntitiesServiceInterface
   */
  protected $donlEntitiesService;

  /**
   * SuggestController constructor.
   *
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\donl_entities\DonlEntitiesServiceInterface $donlEntitiesService
   */
  public function __construct(SolrRequestInterface $solrRequest, RendererInterface $renderer, RequestStack $requestStack, DonlEntitiesServiceInterface $donlEntitiesService) {
    $this->solrRequest = $solrRequest;
    $this->renderer = $renderer;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->donlEntitiesService = $donlEntitiesService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('renderer'),
      $container->get('request_stack'),
      $container->get('donl_entities.entities'),
    );
  }

  /**
   * Render the suggestions.
   *
   * @param string $search
   *   The search term.
   * @param string $type
   *   The type for the given content.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function resultList($search, $type = 'suggestions'): Response {
    $links = [];

    foreach ($suggestions = $this->getSearchSuggestions($type, $search) as $key => $suggestion_group) {
      foreach ($suggestion_group as $suggestion) {
        $term = $this->t(strip_tags($suggestion['term']));
        $this->addSearchLink($links, $key, $type, $term, $suggestion['payload']);
      }
    }

    $build['suggester'] = [
      '#theme' => 'suggester',
      '#did_you_mean' => $suggestions['did_you_mean'][0]['payload'] ?? NULL,
      '#suggestions' => $links,
    ];

    if ($type !== 'suggestions') {
      $build['suggester']['#searchType'] = $this->t('Suggested @type', ['@type' => $this->donlEntitiesService->getEntityName($type, FALSE, FALSE)]);
    }
    else {
      $build['suggester']['#searchType'] = $this->t('Suggested');
    }

    $response = new Response();
    $response->setContent($this->renderer->renderRoot($build));

    return $response;
  }

  /**
   * Get the suggestor link.
   *
   * @param array $links
   * @param string $category
   * @param string $type
   * @param string $term
   * @param string $payload
   */
  protected function addSearchLink(array &$links, $category, $type, $term, $payload): void {
    switch ($category) {
      case 'appliance_suggester':
        if ($url = $this->getUrlFromRoute('donl.application', ['application' => $payload])) {
          if ($type === 'application') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Applications'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'catalog_suggester':
        if ($url = $this->getUrlFromRoute('donl_search.catalog.view', ['catalog' => $payload])) {
          if ($type === 'catalog') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Catalogs'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'community_suggester':
        if ($url = $this->getUrlFromUri('/communities/' . $payload)) {
          if ($type === 'community') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Communities'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'dataset_suggester':
        if ($url = $this->getUrlFromRoute('ckan.dataset.view', ['dataset' => $payload])) {
          if ($type === 'dataset') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Datasets'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'datarequest_suggester':
        if ($url = $this->getUrlFromRoute('donl.datarequest', ['datarequest' => $payload])) {
          if ($type === 'datarequest') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Datarequests'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'dataservice_suggester':
        if ($url = $this->getUrlFromRoute('donl.dataservice', ['dataservice' => $payload])) {
          if ($type === 'dataservice') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Dataservices'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'group_suggester':
        if ($url = $this->getUrlFromRoute('donl_search.group.view', ['group' => $payload])) {
          if ($type === 'group') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Groups'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'news_suggester':
        if ($url = $this->getUrlFromRoute('entity.node.canonical', ['node' => $payload])) {
          if ($type === 'news') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['News items'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'organization_suggester':
        if ($type === 'dataset') {
          if ($url = $this->getUrlFromRoute('donl_search.search.dataset', [], ['query' => ['facet_authority[0]' => $payload]])) {
            $links['Search dataset on organisation'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        elseif ($type === 'organization') {
          if ($url = $this->getUrlFromRoute('donl_search.organization.view', ['organization' => $payload])) {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        else {
          if ($url = $this->getUrlFromRoute('donl_search.organization.view', ['organization' => $payload])) {
            $links['Organizations'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'support_suggester':
        if ($url = $this->getUrlFromRoute('entity.node.canonical', ['node' => $payload])) {
          if ($type === 'support') {
            $links['Based on title'][] = Link::fromTextAndUrl($term, $url);
          }
          else {
            $links['Support pages'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;

      case 'theme_suggester':
        if ($type === 'dataset') {
          if ($url = $this->getUrlFromRoute('donl_search.search.dataset', [], ['query' => ['facet_theme[0]' => $payload]])) {
            $links['Search dataset on theme'][] = Link::fromTextAndUrl($term, $url);
          }
        }
        break;
    }
  }

  /**
   * Get an array with search suggestions.
   *
   * @param string $type
   *   The type for the given content.
   * @param string $search
   *   The search term.
   *
   * @return array
   */
  protected function getSearchSuggestions($type, $search): array {
    return $this->solrRequest->getSearchSuggestions($type, $search);
  }

  /**
   * Creates a new Url object for a URL that has a Drupal route.
   *
   * @param string $routeName
   *   The name of the route
   * @param array $routeParams
   *   (optional) An associative array of route parameter names and values.
   * @param array $options
   *   See \Drupal\Core\Url::fromUri() for details.
   *
   * @return \Drupal\Core\Url|null
   *   An Url object for the given route if valid.
   */
  protected function getUrlFromRoute(string $routeName, array $routeParams = [], $options = []): ?Url {
    try {
      $url = Url::fromRoute($routeName, $routeParams, $options);
      $url->toString();
      return $url;
    }
    catch (\Exception $e) {
      // Do nothing.
    }
    return NULL;
  }

  /**
   * Creates a new Url object from a URI.
   *
   * @param string $path
   *   The Drupal path of the resource.
   *
   * @return \Drupal\Core\Url|null
   *   An Url object for the given route if valid.
   * @see \Drupal\Core\Url::fromUserInput()
   *
   */
  protected function getUrlFromUri(string $path): ?Url {
    try {
      $url = Url::fromUserInput($path);
      $url->toString();
      return $url;
    }
    catch (\Exception $e) {
      // Do nothing.
    }
    return NULL;
  }

}
