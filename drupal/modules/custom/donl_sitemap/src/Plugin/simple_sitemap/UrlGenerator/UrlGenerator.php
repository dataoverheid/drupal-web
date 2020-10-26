<?php

namespace Drupal\donl_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\donl_search\SolrRequestInterface;
use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorBase;
use Drupal\simple_sitemap\Simplesitemap;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UrlGenerator.
 */
abstract class UrlGenerator extends UrlGeneratorBase {

  /**
   * The SORL request.
   *
   * @var \Drupal\donl_search\SolrRequestInterface
   */
  protected $solrRequest;

  /**
   * UrlGenerator constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   * @param \Drupal\simple_sitemap\Logger $logger
   * @param \Drupal\donl_search\SolrRequestInterface $solrRequest
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Simplesitemap $generator, Logger $logger, SolrRequestInterface $solrRequest) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $generator, $logger);
    $this->solrRequest = $solrRequest;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.logger'),
      $container->get('donl_search.request')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDataSets() {
    $datasets = [];
    $page = 1;
    $recordsPerPage = 200;
    do {
      $result = $this->solrRequest->search($page, $recordsPerPage, NULL, 'sys_modified desc', $this->getType());
      /** @var \Drupal\donl_search\Entity\SolrResult $solrResult */
      foreach ($result['rows'] as $solrResult) {
        $modified = NULL;
        if (!empty($solrResult->metadata_modified)) {
          $modified = new DrupalDateTime($solrResult->metadata_modified);
        }

        $datasets[] = [
          'modified' => $modified ? $modified->format('c') : NULL,
          'path' => $solrResult->url->setOption('absolute', FALSE)->toString(),
          'url' => $solrResult->url->setOption('absolute', TRUE)->toString(),
        ];
      }
      $page++;
    } while ($page <= ceil($result['numFound'] / $recordsPerPage));
    return $datasets;
  }

  /**
   * {@inheritdoc}
   */
  protected function processDataSet($data_set) {
    return [
      'url' => $data_set['url'],
      'lastmod' => $data_set['modified'],
      'priority' => '0.5',
      'changefreq' => 'daily',
      'meta' => [
        'path' => $data_set['path'],
      ],
    ];
  }

  /**
   * Get the type.
   *
   * @return string
   */
  abstract public function getType();

}
