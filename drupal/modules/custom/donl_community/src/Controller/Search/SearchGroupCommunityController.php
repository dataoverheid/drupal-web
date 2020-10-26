<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search groups within the community.
 */
class SearchGroupCommunityController extends BaseCommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'group';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('groups');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_community.search.group';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.group';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 data request', '@count groups');
  }

  /**
   * {@inheritdoc}
   */
  protected function themeRows(array $result): array {
    if (!$community = $this->communityResolver->resolve()) {
      throw new NotFoundHttpException();
    }

    $rows = [];
    /** @var \Drupal\donl_search\Entity\SolrResult $solrResult */
    foreach ($result as $solrResult) {
      $routeParams = [
        'community' => $community->getMachineName(),
        'group' => $solrResult->name ?? $solrResult->id,
      ];
      $url = Url::fromRoute('donl_community.group.view', $routeParams);
      $solrResult->updateUrl($url);
      $rows[] = [
        '#theme' => 'donl_searchrecord_group',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
