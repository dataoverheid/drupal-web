<?php

namespace Drupal\donl_community\Controller\Search;

use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Search applications within the community.
 */
class SearchApplicationCommunityController extends BaseCommunitySearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockTypeLabel(): string {
    return $this->t('applications');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName() {
    return 'donl_community.search.application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommunityBlockRoute(): string {
    return 'donl_search.search.application';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTotalResultsMessage($numFound) {
    $count = $this->numberFormatter->format($numFound);
    return $this->formatPlural($count, '1 application', '@count applications');
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
        'organization' => $solrResult->name ?? $solrResult->id,
      ];
      $url = Url::fromRoute('donl_community.application.view', $routeParams);
      $solrResult->updateUrl($url);
      $rows[] = [
        '#theme' => 'donl_searchrecord_application',
        '#record' => $solrResult,
      ];
    }

    return $rows;
  }

}
