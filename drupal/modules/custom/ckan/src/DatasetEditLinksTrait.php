<?php

namespace Drupal\ckan;

use Drupal\ckan\Entity\Dataset;
use Drupal\ckan\User\CkanUserInterface;
use Drupal\Core\Url;

/**
 * Manage the edit links for a dataset.
 */
trait DatasetEditLinksTrait {

  /**
   * Get the edit links.
   *
   * @param \Drupal\ckan\Entity\Dataset $dataset
   *   The dataset.
   * @param \Drupal\ckan\User\CkanUserInterface $ckanUser
   *   The ckan user.
   * @param string $activeTab
   *   The tab to mark as active.
   *
   * @return array
   *   The edit links
   */
  public function getEditLinks(Dataset $dataset, CkanUserInterface $ckanUser, string $activeTab = 'view'): array {
    $editLinks = [];
    if ($ckanUser->isAdministrator() || $ckanUser->getCkanId() === $dataset->getCreatorUserId()) {
      $editLinks['view'] = [
        '#type' => 'link',
        '#title' => $this->t('View'),
        '#url' => Url::fromRoute('ckan.dataset.view', ['dataset' => $dataset->getName()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];
      $editLinks['edit'] = [
        '#type' => 'link',
        '#title' => $this->t('Edit dataset'),
        '#url' => Url::fromRoute('ckan.dataset.edit', ['dataset' => $dataset->getName()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];

      $editLinks['data-sources'] = [
        '#type' => 'link',
        '#title' => $this->t('Manage data sources'),
        '#url' => Url::fromRoute('ckan.dataset.datasources', ['dataset' => $dataset->getId()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];

      $publishText = $this->t('Publish dataset');
      if (!$dataset->getPrivate()) {
        $publishText = $this->t('Unpublish dataset');
      }

      $editLinks['publish'] = [
        '#type' => 'link',
        '#title' => $publishText,
        '#url' => Url::fromRoute('ckan.dataset.publish', ['dataset' => $dataset->getName()]),
        '#attributes' => [
          'class' => ['buttonswitch__button'],
        ],
      ];

      $editLinks['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromRoute('ckan.dataset.delete', ['dataset' => $dataset->getId()]),
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

}
