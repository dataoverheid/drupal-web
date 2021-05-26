<?php

namespace Drupal\donl_identifier;

use Drupal\node\NodeInterface;

/**
 *
 */
interface ResolveIdentifierServiceInterface {

  public function resolve(NodeInterface $node): string;

}
