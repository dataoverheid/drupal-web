<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search datarequests within the community.
 */
class SearchDatarequestCommunityController extends BaseCommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('data requests');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_community.search.datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.datarequest';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 data request', '@count data requests');
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
        '#theme' => 'donl_searchrecord_datarequest',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
