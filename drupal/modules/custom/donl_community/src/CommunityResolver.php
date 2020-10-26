<?php

namespace Drupal\donl_community;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\donl_community\Entity\Community;
use Drupal\node\NodeInterface;

/**
 *
 */
class CommunityResolver implements CommunityResolverInterface {

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   *
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(): ?Community {
    // Get the route parameter 'node'.
    if (($node = $this->routeMatch->getParameter('node')) || ($node = $this->routeMatch->getParameter('community'))) {

      // If it is a node id and not the real node, load it from the database.
      if (!$node instanceof NodeInterface && is_numeric($node)) {
        $node = $this->nodeStorage->load($node);
      }

      if ($node !== NULL) {
        return $this->nodeToCommunity($node);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function nodeToCommunity(NodeInterface $node): ?Community {
    // If there is a node and it is of type landing page.
    if ($node->bundle() === 'community') {

      // Create a community object.
      $community = new Community();
      $community->setTitle($node->label());

      // Get background image.
      if ($node->hasField('field_background_image') && ($fid = $node->get('field_background_image')->getValue()[0]['target_id']) && ($backgroundImage = $this->fileStorage->load($fid))) {
        $community->setBackgroundImage(file_url_transform_relative(file_create_url($backgroundImage->getFileUri())));
      }

      // Get colour.
      if ($node->hasField('colour') && ($colour = $node->get('colour')->getValue()[0]['value'] ?? NULL)) {
        $community->setColour($colour);
      }

      // Get description.
      if ($node->hasField('community_description') && ($description = $node->get('community_description')->getValue()[0]['value'] ?? NULL)) {
        $community->setDescription($description);
      }

      // Get shortName.
      if ($node->hasField('short_name') && ($shortName = $node->get('short_name')->getValue()[0]['value'] ?? NULL)) {
        $community->setShortName($shortName);
      }

      $identifier = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();
      $community->setIdentifier($identifier);
      $community->setMachineName($node->get('machine_name')[0]->value);

      $themes = [];
      foreach ($node->get('themes') ?? [] as $theme) {
        $themes[] = $theme->value;
      }
      $community->setThemes($themes);

      return $community;
    }

    return NULL;
  }

}
