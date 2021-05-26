<?php

namespace Drupal\donl_community;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\donl_community\Entity\Community;
use Drupal\donl_identifier\ResolveIdentifierServiceInterface;
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
   * @var \Drupal\donl_identifier\ResolveIdentifierServiceInterface
   */
  protected $resolveIdentifierService;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $ImageStyleStorage;

  /**
   * CommunityResolver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   * @param \Drupal\donl_identifier\ResolveIdentifierServiceInterface $resolveIdentifierService
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch, ResolveIdentifierServiceInterface $resolveIdentifierService) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->ImageStyleStorage = $entityTypeManager->getStorage('image_style');
    $this->routeMatch = $routeMatch;
    $this->resolveIdentifierService = $resolveIdentifierService;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(): ?Community {
    //phpcs:disable
    $community = NULL;
    //phpcs:enable

    // Get the route parameter 'node'.
    if (($node = $this->routeMatch->getParameter('node')) || ($node = $this->routeMatch->getParameter('community'))) {
      // The route can either give a node object.
      if ($node instanceof NodeInterface && !($community = &drupal_static('community:' . $node->id()))) {
        $community = $this->nodeToCommunity($node);
      }
      // Or a simple node id.
      elseif (is_numeric($node) && !($community = &drupal_static('community:' . $node))) {
        if ($node = $this->nodeStorage->load($node)) {
          $community = $this->nodeToCommunity($node);
        }
      }
    }

    return $community;
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
      $community->setNid($node->id());

      // Get background image.
      if ($node->hasField('field_background_image') && ($fid = $node->get('field_background_image')->getValue()[0]['target_id']) && ($backgroundImage = $this->fileStorage->load($fid))) {
        $style = $this->ImageStyleStorage->load('header_1920x480');
        $community->setBackgroundImage($style->buildUrl($backgroundImage->getFileUri()));
      }

      // Get colour.
      if ($node->hasField('colour') && ($colour = $node->get('colour')->getValue()[0]['value'] ?? NULL)) {
        $community->setColour($colour);
      }

      // Get description.
      if ($node->hasField('html_description') && ($value = $node->get('html_description')->getValue())) {
        $community->setDescription(check_markup($value[0]['value'], $value[0]['format']));
      }

      // Get shortName.
      if ($node->hasField('short_name') && ($shortName = $node->get('short_name')->getValue()[0]['value'] ?? NULL)) {
        $community->setShortName($shortName);
      }

      $community->setIdentifier($this->resolveIdentifierService->resolve($node));
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
