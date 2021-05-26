<?php

namespace Drupal\donl_community\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\donl_community\CommunityResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class ValueListApiController extends ControllerBase {

  /**
   * @var \Drupal\donl_community\CommunityResolverInterface
   */
  protected $communityResolver;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('donl_community.community_resolver')
    );
  }

  /**
   *
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, CommunityResolverInterface $communityResolver) {
    $this->communityResolver = $communityResolver;
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function communities() {
    $data = [];

    $nids = $this->nodeStorage->getQuery()->condition('type', 'community', '=')->execute();
    /** @var \Drupal\node\NodeInterface $node */
    foreach ($this->nodeStorage->loadMultiple($nids) as $node) {
      $translation = $node;
      if ($node->hasTranslation('en')) {
        $translation = $node->getTranslation('en');
      }

      $description = NULL;
      if ($value = $node->get('html_description')->getValue()) {
        $description = (string) check_markup($value[0]['value'], $value[0]['format']);
      }

      if ($community = $this->communityResolver->nodeToCommunity($node)) {
        $data[] = [
          'name' => $community->getTitle(),
          'description' => $description,
          'field_identifier' => $community->getIdentifier(),
          'field_slug' => $community->getMachineName(),
          'label_nl' => $community->getTitle(),
          'label_en' => $translation->label(),
        ];
      }
    }

    return new JsonResponse($data);
  }

}
