<?php

namespace Drupal\ckan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\donl_identifier\ResolveIdentifierServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class JsonEndpointController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @var \Drupal\donl_identifier\ResolveIdentifierServiceInterface
   */
  protected $resolveIdentifierService;

  /**
   * JsonEndpointController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\donl_identifier\ResolveIdentifierServiceInterface $resolveIdentifierService
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack, ResolveIdentifierServiceInterface $resolveIdentifierService) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->requestStack = $requestStack;
    $this->resolveIdentifierService = $resolveIdentifierService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('donl_identifier.resolver')
    );
  }

  /**
   * Return a json object with all relations for the given dataset.
   *
   * Requires an dataset identifier to be send as a GET parameter.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function datasetRelations() {
    $relations = [];
    if ($identifier = $this->requestStack->getCurrentRequest()->query->get('identifier')) {
      $nids = $this->nodeStorage->getQuery()
        ->condition('datasets', $identifier, '=')
        ->execute();

      /** @var \Drupal\node\Entity\Node $node */
      foreach ($this->nodeStorage->loadMultiple($nids) as $node) {
        $relations[$node->getType()][] = $this->resolveIdentifierService->resolve($node);
      }
    }

    return JsonResponse::create([
      'identifier' => $identifier,
      'applications' => $relations['appliance'] ?? [],
      'communities' => $relations['community'] ?? [],
      'datarequests' => $relations['datarequest'] ?? [],
      'groups' => $relations['group'] ?? [],
    ]);
  }

}
