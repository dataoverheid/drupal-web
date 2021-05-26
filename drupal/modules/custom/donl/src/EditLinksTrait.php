<?php

namespace Drupal\donl;

use Drupal\ckan\User\CkanUserInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Manage the edit links for a dataset.
 */
trait EditLinksTrait {

  /**
   * Get the edit links.
   *
   * @param \Drupal\Core\Url $viewUrl
   *   The URL to the view mode.
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param \Drupal\ckan\User\CkanUserInterface $ckanUser
   *   The ckan user.
   * @param string $activeTab
   *   The tab to mark as active.
   *
   * @return array
   *   The edit links.
   */
  public function getEditLinks(Url $viewUrl, NodeInterface $node, CkanUserInterface $ckanUser, string $activeTab = 'view'): array {
    $editLinks = [];
    if ($ckanUser->isAdministrator()) {
      $editLinks['view'] = [
        '#type' => 'link',
        '#title' => $this->t('View'),
        '#url' => $viewUrl,
        '#attributes' => [
          'class' => ['buttonswitch__button'],
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

      $type = $this->getType($node);
      $publishText = $this->t('Publish @type', ['@type' => $this->t($type)]);
      if ($node->isPublished()) {
        $publishText = ucfirst($this->t('unpublish @type', ['@type' => $this->t($type)]));
      }

      $editLinks['publish'] = [
        '#type' => 'link',
        '#title' => $publishText,
        '#url' => Url::fromRoute('donl_form.node.publish', ['node' => $node->id()]),
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

      if (isset($editLinks[$activeTab])) {
        $editLinks[$activeTab]['#attributes']['class'][] = 'is-active';
      }
    }

    return $editLinks;
  }

  /**
   * Return the correct node type name.
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return string
   */
  private function getType(NodeInterface $node): string {
    switch ($node->getType()) {
      case 'appliance':
        return 'application';

      case 'datarequest':
        return 'data request';

      default:
        return $node->getType();
    }
  }

}
