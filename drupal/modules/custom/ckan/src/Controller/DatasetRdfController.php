<?php

namespace Drupal\ckan\Controller;

use Drupal\ckan\CkanRequestInterface;
use Drupal\ckan\Entity\Dataset;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class DatasetRdfController extends ControllerBase {

  /**
   * @var \Drupal\ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckan.request')
    );
  }

  /**
   *
   */
  public function __construct(CkanRequestInterface $ckanRequest) {
    $this->ckanRequest = $ckanRequest;
  }

  /**
   *
   */
  public function content(Dataset $dataset) {
    if ($content = $this->ckanRequest->getDatasetAsRdf($dataset->getId())) {
      $response = CacheableResponse::create($content);
      $response->setMaxAge(86400);
      $response->headers->set('Content-Type', 'application/rdf+xml');
      $response->headers->set('Content-Disposition', 'filename="' . $dataset->getName() . '.rdf"');
      return $response;
    }

    throw new NotFoundHttpException();
  }

}
