<?php

namespace Drupal\donl_search\Controller;

use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\donl_search\SolrRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class SuggestController extends ControllerBase {

  /**
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('donl_search.request'),
      $container->get('renderer')
    );
  }

  /**
   *
   */
  public function __construct(SolrRequestInterface $solrRequest, RendererInterface $renderer) {
    $this->solrRequest = $solrRequest;
    $this->renderer = $renderer;
  }

  /**
   *
   */
  public function title(Dataset $dataset) {
    return $dataset->getTitle();
  }

  /**
   *
   */
  public function resultList($term) {
    $term = urldecode($term);
    $links = [];

    if (($solr_suggestions = $this->solrRequest->getSearchSuggestions($term)) && (!empty($solr_suggestions['dataset_suggester']) || !empty($solr_suggestions['organization_suggester']) || !empty($solr_suggestions['group_suggester']) || !empty($solr_suggestions['datarequest_suggester']))) {
      foreach ($solr_suggestions as $key => $suggestion_group) {
        foreach ($suggestion_group as $suggestion) {
          $term = $this->t(strip_tags($suggestion['term']));
          switch ($key) {
            case 'dataset_suggester':
              $links['Based on title:'][] = Link::createFromRoute($term, 'ckan.dataset.view', ['dataset' => $suggestion['payload']]);
              break;

            case 'organization_suggester':
              $links['Search dataset on organisation:'][] = Link::createFromRoute($term, 'donl_search.search.dataset', [], ['query' => ['facet_authority[0]' => $suggestion['payload']]]);
              break;

            case 'theme_suggester':
              $links['Search dataset on theme:'][] = Link::createFromRoute($term, 'donl_search.search.dataset', [], ['query' => ['facet_theme[0]' => $suggestion['payload']]]);
              break;

            case 'group_suggester':
              $links['Groups'][] = Link::createFromRoute($term, 'donl_search.group.view', ['group' => $suggestion['payload']]);
              break;

            case 'datarequest_suggester';
              $links['Datarequest'][] = Link::createFromRoute($term, 'donl.datarequest', ['datarequest' => $suggestion['payload']]);
              break;
          }
        }
      }
    }

    $build = [
      '#theme' => 'suggester',
      '#suggestions' => $links,
    ];

    $html = $this->renderer->renderRoot($build);
    $response = new Response();
    $response->setContent($html);

    return $response;
  }

}
