<?php

namespace Drupal\ckan\Controller;

use Drupal\ckan\Entity\Dataset;
use Drupal\node\NodeInterface;

/**
 *
 */
class DatasetGroupController extends DatasetController {

  /**
   * {@inheritdoc}
   */
  public function content(Dataset $dataset, NodeInterface $group = NULL) {
    $build = parent::content($dataset);

    if ($group) {
      $title = $this->t('Back to all datasets in :group', [':group' => $group->getTitle()]);
      $routeParams = ['group' => $group->get('machine_name')->getValue()[0]['value']];
      $build['#backLink'] = $this->backLinkService->createBackLink($title, 'donl_search.group.view', $routeParams);
    }

    return $build;
  }

}
