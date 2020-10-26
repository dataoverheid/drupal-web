<?php

namespace Drupal\donl_community;

use Drupal\donl_community\Entity\Community;
use Drupal\node\NodeInterface;

/**
 *
 */
interface CommunityResolverInterface {

  /**
   * Get the current community (if there is one).
   *
   * @return \Drupal\donl_community\Entity\Community|null
   */
  public function resolve(): ?Community;

  /**
   * Turn a node into a community object.
   *
   * @return \Drupal\donl_community\Entity\Community|null
   */
  public function nodeToCommunity(NodeInterface $node): ?Community;

}
