<?php

namespace Drupal\ckan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
  }

  /**
   *
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->requestStack = $requestStack;
  }

  /**
   * Return a json object with all relations for the given dataset.
   *
   * Requires an dataset identifier to be send as a GET parameter.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function datasetRelations() {
    $applications = [];
    $communities = [];
    $datarequests = [];
    $groups = [];

    if ($identifier = $this->requestStack->getCurrentRequest()->query->get('identifier')) {
      $nids = $this->nodeStorage->getQuery()
        ->condition('datasets', $identifier, '=')
        ->execute();

      $nodes = $this->nodeStorage->loadMultiple($nids);
      /** @var \Drupal\node\Entity\Node $node */
      foreach ($nodes as $node) {
        switch ($node->getType()) {
          case 'appliance':
            $applications[] = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();
            break;

          case 'datarequest':
            $datarequests[] = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();
            break;

          case 'group':
            $groups[] = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();
            break;
        }
      }
    }

    return JsonResponse::create([
      'identifier' => $identifier,
      'applications' => $applications,
      'communities' => $communities,
      'datarequests' => $datarequests,
      'groups' => $groups,
    ]);
  }

}
