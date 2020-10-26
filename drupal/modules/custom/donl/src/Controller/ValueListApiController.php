<?php

namespace Drupal\donl\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class ValueListApiController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   *
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * Return the organization data.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json data.
   */
  public function organizations(): JsonResponse {
    $data = [];

    $nids = $this->nodeStorage->getQuery()->condition('type', 'organization', '=')->execute();
    /** @var \Drupal\node\NodeInterface $node */
    foreach ($this->nodeStorage->loadMultiple($nids) as $node) {
      $translation = $node;
      if ($node->hasTranslation('en')) {
        $translation = $node->getTranslation('en');
      }

      $data[] = [
        'name' => $node->getTitle(),
        'description' => $node->get('organization_description')->getValue()[0]['value'] ?? NULL,
        'identifier' => $node->get('identifier')->getValue()[0]['value'],
        'organization_type' => $node->get('organization_type')->getValue()[0]['value'] ?? NULL,
        'label_nl' => $node->getTitle(),
        'label_en' => $translation->getTitle(),
      ];
    }

    return new JsonResponse($data);
  }

  /**
   * Return the catalog data.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json data.
   */
  public function catalogs(): JsonResponse {
    $data = [];

    $nids = $this->nodeStorage->getQuery()->condition('type', 'catalog', '=')->execute();
    /** @var \Drupal\node\NodeInterface $node */
    foreach ($this->nodeStorage->loadMultiple($nids) as $node) {
      $translation = $node;
      if ($node->hasTranslation('en')) {
        $translation = $node->getTranslation('en');
      }

      $data[] = [
        'name' => $node->getTitle(),
        'description_en' => $translation->get('catalog_description')->getValue()[0]['value'] ?? NULL,
        'description_nl' => $node->get('catalog_description')->getValue()[0]['value'] ?? NULL,
        'identifier' => $node->get('identifier')->getValue()[0]['value'],
        'label_nl' => $node->getTitle(),
        'label_en' => $translation->getTitle(),
        'name_slug' => $node->get('machine_name')->getValue()[0]['value'],
      ];
    }

    return new JsonResponse($data);
  }

}
