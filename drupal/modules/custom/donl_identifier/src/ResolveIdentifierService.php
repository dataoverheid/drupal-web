<?php

namespace Drupal\donl_identifier;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Resolve identifier service.
 */
class ResolveIdentifierService implements ResolveIdentifierServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve(NodeInterface $node): string {
    switch ($node->getType()) {
      case 'appliance':
        if ($node->hasField('machine_name')) {
          $uri = 'https://data.overheid.nl/community/toepassingen/' . $node->get('machine_name')[0]->value;
        }
        break;

      case 'catalog':
      case 'organization':
        if ($node->hasField('identifier')) {
          $uri = $node->get('identifier')[0]->value;
        }
        break;

      case 'community':
        if ($node->hasField('machine_name')) {
          $uri = 'https://data.overheid.nl/communities/' . $node->get('machine_name')[0]->value;
        }
        break;

      case 'datarequest':
        if ($node->hasField('machine_name')) {
          $uri = 'https://data.overheid.nl/community/dataverzoeken/' . $node->get('machine_name')[0]->value;
        }
        break;

      case 'dataservice':
        if ($node->hasField('machine_name')) {
          $uri = 'https://data.overheid.nl/dataservices/' . $node->get('machine_name')[0]->value;
        }
        break;

      case 'group':
        if ($node->hasField('machine_name')) {
          $uri = 'https://data.overheid.nl/community/groepen/' . $node->get('machine_name')[0]->value;
        }
        break;
    }

    return $uri ?? 'https://data.overheid.nl' . Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
  }

}
