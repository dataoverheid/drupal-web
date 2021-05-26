<?php

namespace Drupal\donl_form\Plugin\Menu\LocalTask;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Local task plugin to render dynamic tab title dynamically.
 */
class NodePublishLocalTask extends LocalTaskDefault {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL) {
    $node = $request->attributes->get('node');
    if ($node instanceof NodeInterface) {
      if ($node->isPublished()) {
        return $this->t('Unpublish');
      }
    }

    return $this->t('Publish');
  }

}