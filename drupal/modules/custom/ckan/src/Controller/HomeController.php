<?php

namespace Drupal\ckan\Controller;

use Drupal\ckan\MappingServiceInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\donl_search\SearchUrlServiceInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class HomeController extends ControllerBase {

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\ckan\MappingServiceInterface
   */
  protected $mappingService;

  /**
   * @var \Drupal\donl_search\SearchUrlServiceInterface
   */
  protected $searchUrlService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('ckan.mapping'),
      $container->get('donl_search.search_url')
    );
  }

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest, MappingServiceInterface $mappingService, SearchUrlServiceInterface $searchUrlService) {
    $this->solrRequest = $solrRequest;
    $this->mappingService = $mappingService;
    $this->searchUrlService = $searchUrlService;
  }

  /**
   *
   */
  public function content() {
    $themes = [];
    foreach ($this->solrRequest->getHierarchicalThemeListWithUsages() as $k => $v) {
      $themes[$k] = [
        'label' => $v['label'],
        'count' => $v['count'],
        'url' => $this->searchUrlService->simpleSearchUrl('donl_search.search.dataset', ['facet_theme' => [$k]]),
        'class' => $this->mappingService->getThemeClass($k),
      ];
    }

    ksort($themes);

    return [
      '#theme' => 'homepage',
      '#themes' => $themes,
      '#cache' => [
        'max-age' => 86400,
      ],
    ];
  }

}
