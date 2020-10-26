<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search datasets within the community.
 */
class SearchDatasetCommunityController extends BaseCommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'dataset';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('datasets');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_community.search.dataset';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.dataset';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 data request', '@count datasets');
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
        'dataset' => $solrResult->name ?? $solrResult->id,
      ];
      $url = Url::fromRoute('donl_community.dataset.view', $routeParams);
      $solrResult->updateUrl($url);
      $rows[] = [
        '#theme' => 'donl_searchrecord_dataset',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
