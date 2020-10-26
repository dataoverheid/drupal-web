<?php

namespace Drupal\donl_search\Controller;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 *
 */
abstract class SearchDatasetContentTypeController extends SearchDatasetController {

  /**
   * Create the edit links.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return array
   *   A Drupal render array.
   */
  protected function getEditLinks(NodeInterface $node) {
    $editLinks = [];
    if (($user = $this->userStorage->load($this->currentUser()->id())) && $user->isAdministrator()) {
      $editLinks['view'] = [
        '#type' => 'link',
        '#title' => $this->t('View'),
        '#url' => Url::fromRoute($this->getRouteName(), $this->getRouteParams()),
        '#attributes' => [
          // Currently this link is always active.
          'class' => ['buttonswitch__button', 'is-active'],
        ],
      ];
      $editLinks['edit'] = [
        '#type' => 'link',
        '#title' => $this->t('Edit'),
        '#url' => Url::fromRoute('entity.node.edit_form', ['node' => $node->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
      $editLinks['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromRoute('entity.node.delete_form', ['node' => $node->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
      $editLinks['translate'] = [
        '#type' => 'link',
        '#title' => $this->t('Translate'),
        '#url' => Url::fromRoute('entity.node.content_translation_overview', ['node' => $node->id()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
    }

    return $editLinks;
  }

}
